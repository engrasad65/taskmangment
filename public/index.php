<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\WorkerController;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;
use App\Models\Site;
use App\Models\Task;
use App\Models\User;

require __DIR__ . '/../app/Core/Autoloader.php';

$config = require __DIR__ . '/../config/config.php';
Session::start();
$db = Database::connection($config['db']);

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$authController = new AuthController($config);
$workerController = new WorkerController($config);
$adminController = new AdminController($config);

$userModel = new User($db);
$taskModel = new Task($db);
$siteModel = new Site($db);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];
$user = Session::get('user');

$needsAuth = static function () use ($user): void {
    if (!$user) {
        header('Location: /');
        exit;
    }
};

$adminOnly = static function () use ($user): void {
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
};

$workerOnly = static function () use ($user): void {
    if (!$user || $user['role'] !== 'worker') {
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

    case ['GET', '/worker/dashboard']:
        $needsAuth();
        $workerOnly();
        $workerController->dashboard($taskModel);
        break;
    case ['POST', '/worker/start']:
        $needsAuth();
        $workerOnly();
        $workerController->startWork($taskModel);
        break;
    case ['POST', '/worker/end']:
        $needsAuth();
        $workerOnly();
        $workerController->endWork($taskModel);
        break;

    case ['GET', '/admin/dashboard']:
        $needsAuth();
        $adminOnly();
        $adminController->dashboard($taskModel, $siteModel, $userModel);
        break;
    case ['POST', '/admin/users/create']:
        $needsAuth();
        $adminOnly();
        $adminController->createWorker($userModel, $taskModel, $siteModel);
        break;
    case ['POST', '/admin/sites/create']:
        $needsAuth();
        $adminOnly();
        $adminController->createSite($siteModel, $taskModel, $userModel);
        break;
    case ['POST', '/admin/tasks/create']:
        $needsAuth();
        $adminOnly();
        $adminController->createTask($taskModel, $siteModel, $userModel);
        break;
    case ['POST', '/admin/tasks/delete']:
        $needsAuth();
        $adminOnly();
        $adminController->deleteTask($taskModel, $siteModel, $userModel);
        break;
    case ['GET', '/admin/tasks/export']:
        $needsAuth();
        $adminOnly();
        $adminController->exportTasks($taskModel);
        break;
    case ['POST', '/admin/tasks/import']:
        $needsAuth();
        $adminOnly();
        $adminController->importTasks($taskModel, $siteModel, $userModel);
        break;
    default:
        http_response_code(404);
        echo 'Not Found';
}
