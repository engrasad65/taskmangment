<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Task;

class WorkerController extends Controller
{
    public function dashboard(Task $taskModel, ?string $message = null): void
    {
        $user = Session::get('user');
        $tasks = $taskModel->assignedToUser((int) $user['id']);
        $this->view('worker/dashboard', ['tasks' => $tasks, 'message' => $message]);
    }

    public function startWork(Task $taskModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($taskModel, 'CSRF validation failed.');
            return;
        }

        $user = Session::get('user');
        $taskId = (int) ($_POST['task_id'] ?? 0);

        if ($taskId < 1 || !$taskModel->findByIdAndUser($taskId, (int) $user['id'])) {
            $this->dashboard($taskModel, 'Task not found or not assigned to you.');
            return;
        }

        $uploadedPath = $this->handleUpload('start_image', 'start');
        if (!$uploadedPath) {
            $this->dashboard($taskModel, 'Invalid start image upload.');
            return;
        }

        if (!$taskModel->startWork($taskId, (int) $user['id'], $uploadedPath)) {
            $this->dashboard($taskModel, 'Only assigned tasks can be started.');
            return;
        }

        $this->redirect('/worker/dashboard');
    }

    public function endWork(Task $taskModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($taskModel, 'CSRF validation failed.');
            return;
        }

        $user = Session::get('user');
        $taskId = (int) ($_POST['task_id'] ?? 0);
        $progress = trim($_POST['progress_note'] ?? '');

        if (!Validator::required($progress)) {
            $this->dashboard($taskModel, 'Progress note is required.');
            return;
        }

        if ($taskId < 1 || !$taskModel->findByIdAndUser($taskId, (int) $user['id'])) {
            $this->dashboard($taskModel, 'Task not found or not assigned to you.');
            return;
        }

        $uploadedPath = $this->handleUpload('end_image', 'end');
        if (!$uploadedPath) {
            $this->dashboard($taskModel, 'Invalid end image upload.');
            return;
        }

        if (!$taskModel->endWork($taskId, (int) $user['id'], $uploadedPath, $progress)) {
            $this->dashboard($taskModel, 'Only in-progress tasks can be completed.');
            return;
        }

        $this->redirect('/worker/dashboard');
    }

    private function handleUpload(string $fieldName, string $folder): ?string
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$fieldName];
        if ($file['size'] > $this->config['app']['max_upload_size']) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']) ?: '';
        finfo_close($finfo);

        if (!in_array($mime, $this->config['app']['allowed_mime_types'], true)) {
            return null;
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };

        if ($extension === null) {
            return null;
        }

        $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
        $destinationDir = __DIR__ . '/../../public/uploads/' . $folder;
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $destinationPath = $destinationDir . '/' . $safeName;
        if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
            return null;
        }

        return '/uploads/' . $folder . '/' . $safeName;
    }
}
