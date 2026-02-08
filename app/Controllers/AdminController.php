<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Validator;
use App\Models\Site;
use App\Models\Task;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard(Task $taskModel, Site $siteModel, User $userModel, ?string $message = null): void
    {
        $this->view('admin/dashboard', [
            'tasks' => $taskModel->allWithUsers(),
            'sites' => $siteModel->all(),
            'workers' => $userModel->allWorkers(),
            'message' => $message,
        ]);
    }

    public function createWorker(User $userModel, Task $taskModel, Site $siteModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'CSRF validation failed.');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!Validator::required($username) || !Validator::required($fullName) || strlen($password) < 8) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'Username, full name, and a password of at least 8 chars are required.');
            return;
        }

        if ($userModel->findByUsername($username)) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'Username already exists.');
            return;
        }

        $userModel->create($username, $fullName, $password, 'worker');
        $this->redirect('/admin/dashboard');
    }

    public function createSite(Site $siteModel, Task $taskModel, User $userModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'CSRF validation failed.');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        if (!Validator::required($name) || !Validator::required($location)) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'Site name and location are required.');
            return;
        }

        $siteModel->create($name, $location);
        $this->redirect('/admin/dashboard');
    }

    public function createTask(Task $taskModel, Site $siteModel, User $userModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'CSRF validation failed.');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $userId = (int) ($_POST['user_id'] ?? 0);
        $siteId = (int) ($_POST['site_id'] ?? 0);

        if (!Validator::required($title) || $userId < 1 || $siteId < 1) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'Task title, worker, and site are required.');
            return;
        }

        if (!$userModel->findById($userId) || !$siteModel->findById($siteId)) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'Invalid worker or site selected.');
            return;
        }

        $taskModel->create($title, $userId, $siteId, $description);
        $this->redirect('/admin/dashboard');
    }

    public function deleteTask(Task $taskModel, Site $siteModel, User $userModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'CSRF validation failed.');
            return;
        }

        $taskId = (int) ($_POST['task_id'] ?? 0);
        if ($taskId < 1 || !$taskModel->deleteById($taskId)) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'Task not found.');
            return;
        }

        $this->redirect('/admin/dashboard');
    }

    public function exportTasks(Task $taskModel): void
    {
        $tasks = $taskModel->allWithUsers();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="tasks_export.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Title', 'Site', 'Worker', 'Status', 'Started At', 'Ended At', 'Progress']);
        foreach ($tasks as $task) {
            fputcsv($output, [
                $task['id'],
                $task['title'],
                $task['site_name'],
                $task['full_name'],
                $task['status'],
                $task['started_at'],
                $task['ended_at'],
                $task['progress_note'],
            ]);
        }
        fclose($output);
        exit;
    }

    public function importTasks(Task $taskModel, Site $siteModel, User $userModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'CSRF validation failed.');
            return;
        }

        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'Import file upload failed.');
            return;
        }

        $file = $_FILES['import_file'];
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'Import file is too large.');
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']) ?: '';
        finfo_close($finfo);
        $allowed = ['text/plain', 'text/csv', 'application/vnd.ms-excel'];
        if (!in_array($mime, $allowed, true)) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'Invalid import file type. Please upload CSV.');
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            $this->dashboard($taskModel, $siteModel, $userModel, 'Unable to read import file.');
            return;
        }

        fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) {
                continue;
            }
            $title = trim($row[0]);
            $description = trim($row[1]);
            $importUserId = (int) $row[2];
            $importSiteId = (int) $row[3];
            if ($title !== '' && $importUserId > 0 && $importSiteId > 0 && $userModel->findById($importUserId) && $siteModel->findById($importSiteId)) {
                $taskModel->create($title, $importUserId, $importSiteId, $description);
            }
        }
        fclose($handle);

        $this->redirect('/admin/dashboard');
    }
}
