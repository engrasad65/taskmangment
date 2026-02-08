<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Notification;
use App\Models\Site;
use App\Models\Task;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard(Task $taskModel, Site $siteModel, User $userModel, Notification $notificationModel, ?string $message = null): void
    {
        $user = Session::get('user');
        $this->view('admin/dashboard', [
            'tasks' => $taskModel->allWithUsers(),
            'sites' => $siteModel->all(),
            'workers' => $userModel->allWorkers(),
            'notifications' => $notificationModel->forUser((int) $user['id']),
            'message' => $message,
        ]);
    }

    public function createWorker(User $userModel, Task $taskModel, Site $siteModel, Notification $notificationModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'CSRF validation failed.');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!Validator::required($username) || !Validator::required($fullName) || strlen($password) < 8) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'Username, full name, and a password of at least 8 chars are required.');
            return;
        }

        if ($userModel->findByUsername($username)) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'Username already exists.');
            return;
        }

        $userModel->create($username, $fullName, $password, 'worker');
        $createdUser = $userModel->findByUsername($username);
        if ($createdUser) {
            $notificationModel->create((int) $createdUser['id'], 'Welcome', 'Your worker account was created by admin.');
        }

        $this->redirect('/admin/dashboard');
    }

    public function createSite(Site $siteModel, Task $taskModel, User $userModel, Notification $notificationModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'CSRF validation failed.');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        if (!Validator::required($name) || !Validator::required($location)) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'Site name and location are required.');
            return;
        }

        $siteModel->create($name, $location);
        $this->redirect('/admin/dashboard');
    }

    public function createTask(Task $taskModel, Site $siteModel, User $userModel, Notification $notificationModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'CSRF validation failed.');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $userId = (int) ($_POST['user_id'] ?? 0);
        $siteId = (int) ($_POST['site_id'] ?? 0);

        if (!Validator::required($title) || $userId < 1 || $siteId < 1) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'Task title, worker, and site are required.');
            return;
        }

        if (!$userModel->findById($userId) || !$siteModel->findById($siteId)) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'Invalid worker or site selected.');
            return;
        }

        $taskModel->create($title, $userId, $siteId, $description);
        $notificationModel->create($userId, 'New Task Assigned', 'A new task "' . $title . '" has been assigned to you.');
        $this->redirect('/admin/dashboard');
    }

    public function deleteTask(Task $taskModel, Site $siteModel, User $userModel, Notification $notificationModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'CSRF validation failed.');
            return;
        }

        $taskId = (int) ($_POST['task_id'] ?? 0);
        if ($taskId < 1 || !$taskModel->deleteById($taskId)) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'Task not found.');
            return;
        }

        $this->redirect('/admin/dashboard');
    }

    public function exportTasksDetailed(Task $taskModel): void
    {
        $tasks = $taskModel->allWithUsers();
        $this->exportCsv('tasks_detailed.csv', ['ID', 'Title', 'Site', 'Worker', 'Status', 'Started At', 'Ended At', 'Progress'], array_map(
            static fn(array $task): array => [
                $task['id'],
                $task['title'],
                $task['site_name'],
                $task['full_name'],
                $task['status'],
                $task['started_at'],
                $task['ended_at'],
                $task['progress_note'],
            ],
            $tasks
        ));
    }

    public function exportReportBySite(Task $taskModel): void
    {
        $rows = $taskModel->reportBySite();
        $this->exportCsv('report_by_site.csv', ['Site', 'Total Tasks', 'Completed', 'In Progress', 'Assigned'], array_map(
            static fn(array $row): array => [$row['site_name'], $row['total_tasks'], $row['completed_tasks'], $row['in_progress_tasks'], $row['assigned_tasks']],
            $rows
        ));
    }

    public function exportReportByWorker(Task $taskModel): void
    {
        $rows = $taskModel->reportByWorker();
        $this->exportCsv('report_by_worker.csv', ['Worker', 'Total Tasks', 'Completed', 'In Progress', 'Assigned'], array_map(
            static fn(array $row): array => [$row['worker_name'], $row['total_tasks'], $row['completed_tasks'], $row['in_progress_tasks'], $row['assigned_tasks']],
            $rows
        ));
    }

    public function exportDailySummary(Task $taskModel): void
    {
        $rows = $taskModel->reportDailySummary();
        $this->exportCsv('report_daily_summary.csv', ['Date', 'Total Events', 'Completed', 'In Progress'], array_map(
            static fn(array $row): array => [$row['report_date'], $row['total_events'], $row['completed_count'], $row['in_progress_count']],
            $rows
        ));
    }

    public function importTasks(Task $taskModel, Site $siteModel, User $userModel, Notification $notificationModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'CSRF validation failed.');
            return;
        }

        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'Import file upload failed.');
            return;
        }

        $file = $_FILES['import_file'];
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'Import file is too large.');
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']) ?: '';
        finfo_close($finfo);
        $allowed = ['text/plain', 'text/csv', 'application/vnd.ms-excel'];
        if (!in_array($mime, $allowed, true)) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'Invalid import file type. Please upload CSV.');
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            $this->dashboard($taskModel, $siteModel, $userModel, $notificationModel, 'Unable to read import file.');
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
                $notificationModel->create($importUserId, 'Task Imported', 'Imported task "' . $title . '" has been assigned to you.');
            }
        }
        fclose($handle);

        $this->redirect('/admin/dashboard');
    }

    private function exportCsv(string $fileName, array $headers, array $rows): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}
