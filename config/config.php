<?php

declare(strict_types=1);

return [
    'db' => [
        'dsn' => 'sqlite:' . __DIR__ . '/../storage/database.sqlite',
        'username' => null,
        'password' => null,
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
    'app' => [
        'name' => 'Work Progress Management',
        'base_url' => '/',
        'max_upload_size' => 2 * 1024 * 1024,
        'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ],
];
