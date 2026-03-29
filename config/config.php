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
        'name' => 'School Exam Paper Generation System',
        'base_url' => '/',
        'role_permissions' => [
            'admin' => [
                'admin.dashboard',
                'users.manage',
                'academic.manage',
                'questions.manage',
                'papers.view_all',
                'papers.print',
            ],
            'user' => [
                'papers.create',
                'papers.edit_own',
                'papers.print_own',
            ],
        ],
    ],
];
