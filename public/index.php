<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\NotificationController;
use App\Controllers\WorkerController;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Permission;
use App\Core\Session;
use App\Models\Notification;
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
$notificationController = new NotificationController($config);

$userModel = new User($db);
$taskModel = new Task($db);
$siteModel = new Site($db);
$notificationModel = new Notification($db);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];
$user = Session::get('user');

if ($user) {
    Session::set('unread_notifications', $notificationModel->unreadCount((int) $user['id']));
}

$needsAuth = static function () use ($user): void {
    if (!$user) {
        header('Location: /');
        exit;
    }
};

$requiresPermission = static function (string $permission) use ($user, $config): void {
    $role = $user['role'] ?? null;
    if (!Permission::can($role, $permission, $config)) {
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
        $requiresPermission('worker.dashboard');
        $workerController->dashboard($taskModel, $notificationModel);
        break;
    case ['POST', '/worker/start']:
        $needsAuth();
        $requiresPermission('tasks.start');
        $workerController->startWork($taskModel, $userModel, $notificationModel);
        break;
    case ['POST', '/worker/end']:
        $needsAuth();
        $requiresPermission('tasks.end');
        $workerController->endWork($taskModel, $userModel, $notificationModel);
        break;

    case ['GET', '/admin/dashboard']:
        $needsAuth();
        $requiresPermission('admin.dashboard');
        $adminController->dashboard($taskModel, $siteModel, $userModel, $notificationModel);
        break;
    case ['POST', '/admin/users/create']:
        $needsAuth();
        $requiresPermission('users.manage');
        $adminController->createWorker($userModel, $taskModel, $siteModel, $notificationModel);
        break;
    case ['POST', '/admin/sites/create']:
        $needsAuth();
        $requiresPermission('sites.manage');
        $adminController->createSite($siteModel, $taskModel, $userModel, $notificationModel);
        break;
    case ['POST', '/admin/tasks/create']:
        $needsAuth();
        $requiresPermission('tasks.manage');
        $adminController->createTask($taskModel, $siteModel, $userModel, $notificationModel);
        break;
    case ['POST', '/admin/tasks/delete']:
        $needsAuth();
        $requiresPermission('tasks.manage');
        $adminController->deleteTask($taskModel, $siteModel, $userModel, $notificationModel);
        break;
    case ['GET', '/admin/reports/tasks-detailed']:
        $needsAuth();
        $requiresPermission('reports.export');
        $adminController->exportTasksDetailed($taskModel);
        break;
    case ['GET', '/admin/reports/site-progress']:
        $needsAuth();
        $requiresPermission('reports.export');
        $adminController->exportReportBySite($taskModel);
        break;
    case ['GET', '/admin/reports/worker-performance']:
        $needsAuth();
        $requiresPermission('reports.export');
        $adminController->exportReportByWorker($taskModel);
        break;
    case ['GET', '/admin/reports/daily-summary']:
        $needsAuth();
        $requiresPermission('reports.export');
        $adminController->exportDailySummary($taskModel);
        break;
    case ['POST', '/admin/tasks/import']:
        $needsAuth();
        $requiresPermission('reports.import');
        $adminController->importTasks($taskModel, $siteModel, $userModel, $notificationModel);
        break;

    case ['GET', '/notifications']:
        $needsAuth();
        $requiresPermission('notifications.view');
        $notificationController->index($notificationModel);
        break;
    case ['POST', '/notifications/read-all']:
        $needsAuth();
        $requiresPermission('notifications.view');
        $notificationController->markAllRead($notificationModel);
        break;

    default:
        http_response_code(404);
        echo 'Not Found';
}
