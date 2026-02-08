<?php

declare(strict_types=1);

require __DIR__ . '/../app/Core/Autoloader.php';
$config = require __DIR__ . '/../config/config.php';

$dbPath = __DIR__ . '/../storage/database.sqlite';
if (!is_file($dbPath)) {
    touch($dbPath);
}

$pdo = App\Core\Database::connection($config['db']);
$pdo->exec('PRAGMA foreign_keys = ON');

$uploadDirs = [__DIR__ . '/../public/uploads/start', __DIR__ . '/../public/uploads/end'];
foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    full_name TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ("admin", "worker"))
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS sites (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    location TEXT NOT NULL
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    user_id INTEGER NOT NULL,
    site_id INTEGER NOT NULL,
    status TEXT NOT NULL DEFAULT "assigned" CHECK(status IN ("assigned", "in_progress", "completed")),
    start_image TEXT,
    end_image TEXT,
    progress_note TEXT,
    started_at TEXT,
    ended_at TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (site_id) REFERENCES sites(id)
)');

$userModel = new App\Models\User($pdo);
$siteModel = new App\Models\Site($pdo);
$taskModel = new App\Models\Task($pdo);

if (!$userModel->findByUsername('admin')) {
    $userModel->create('admin', 'System Admin', 'admin123', 'admin');
}
if (!$userModel->findByUsername('worker1')) {
    $userModel->create('worker1', 'Worker One', 'worker123', 'worker');
}

$sites = $siteModel->all();
if (count($sites) === 0) {
    $siteModel->create('Site Alpha', 'Downtown');
    $siteModel->create('Site Beta', 'Uptown');
}

$workers = $userModel->allWorkers();
$sites = $siteModel->all();
$tasks = $taskModel->allWithUsers();
if (count($tasks) === 0 && isset($workers[0], $sites[0])) {
    $taskModel->create('Foundation Inspection', (int) $workers[0]['id'], (int) $sites[0]['id'], 'Inspect and record foundation progress.');
}

echo "Setup complete.\nAdmin login: admin / admin123\nWorker login: worker1 / worker123\n";
