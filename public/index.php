<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\PaperController;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Permission;
use App\Core\Session;
use App\Models\Academic;
use App\Models\ExamPaper;
use App\Models\QuestionBank;
use App\Models\User;

require __DIR__ . '/../app/Core/Autoloader.php';

$config = require __DIR__ . '/../config/config.php';
Session::start();
$db = Database::connection($config['db']);

$authController = new AuthController($config);
$adminController = new AdminController($config);
$paperController = new PaperController($config);

$userModel = new User($db);
$academicModel = new Academic($db);
$questionBankModel = new QuestionBank($db);
$paperModel = new ExamPaper($db);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];
$user = Session::get('user');

$needsAuth = static function () use ($user): void {
    if (!$user) {
        header('Location: /');
        exit;
    }
};

$requiresPermission = static function (string $permission) use ($user, $config): void {
    if (!Permission::can($user['role'] ?? null, $permission, $config)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
};

switch ([$method, $uri]) {
    case ['GET', '/']:
        $authController->showLogin();
        break;
    case ['POST', '/login']:
        $authController->login($userModel);
        break;
    case ['POST', '/logout']:
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            exit;
        }
        $authController->logout();
        break;

    case ['GET', '/admin/dashboard']:
        $needsAuth();
        $requiresPermission('admin.dashboard');
        $adminController->dashboard($userModel, $academicModel, $questionBankModel, $paperModel);
        break;
    case ['POST', '/admin/users/create']:
        $needsAuth();
        $requiresPermission('users.manage');
        $adminController->createUser($userModel, $academicModel, $questionBankModel, $paperModel);
        break;
    case ['POST', '/admin/users/update']:
        $needsAuth();
        $requiresPermission('users.manage');
        $adminController->updateUser($userModel);
        break;
    case ['POST', '/admin/users/delete']:
        $needsAuth();
        $requiresPermission('users.manage');
        $adminController->deleteUser($userModel);
        break;
    case ['POST', '/admin/classes/create']:
        $needsAuth();
        $requiresPermission('academic.manage');
        $adminController->createClass($academicModel);
        break;
    case ['POST', '/admin/subjects/create']:
        $needsAuth();
        $requiresPermission('academic.manage');
        $adminController->createSubject($academicModel);
        break;
    case ['POST', '/admin/chapters/create']:
        $needsAuth();
        $requiresPermission('academic.manage');
        $adminController->createChapter($academicModel);
        break;
    case ['POST', '/admin/questions/create']:
        $needsAuth();
        $requiresPermission('questions.manage');
        $adminController->createQuestion($questionBankModel);
        break;
    case ['POST', '/admin/questions/update']:
        $needsAuth();
        $requiresPermission('questions.manage');
        $adminController->updateQuestion($questionBankModel);
        break;
    case ['POST', '/admin/questions/delete']:
        $needsAuth();
        $requiresPermission('questions.manage');
        $adminController->deleteQuestion($questionBankModel);
        break;

    case ['GET', '/user/dashboard']:
        $needsAuth();
        $requiresPermission('papers.create');
        $paperController->dashboard($academicModel, $paperModel);
        break;
    case ['POST', '/paper/generate']:
        $needsAuth();
        $requiresPermission('papers.create');
        $paperController->generate($academicModel, $paperModel, $questionBankModel);
        break;
    case ['GET', '/paper/review']:
        $needsAuth();
        $requiresPermission('papers.edit_own');
        $paperController->review($paperModel, $questionBankModel);
        break;
    case ['POST', '/paper/items/update']:
        $needsAuth();
        $requiresPermission('papers.edit_own');
        $paperController->updateItems($paperModel, $questionBankModel);
        break;
    case ['POST', '/paper/finalize']:
        $needsAuth();
        $requiresPermission('papers.edit_own');
        $paperController->finalize($paperModel);
        break;
    case ['GET', '/paper/print']:
        $needsAuth();
        $paperController->print($paperModel);
        break;

    default:
        http_response_code(404);
        echo 'Not Found';
}
