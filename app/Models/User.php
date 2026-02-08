<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class User
{
    public function __construct(private PDO $db)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, username, full_name, role FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function allWorkers(): array
    {
        $stmt = $this->db->query("SELECT id, username, full_name, role FROM users WHERE role = 'worker' ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function create(string $username, string $fullName, string $password, string $role = 'worker'): bool
    {
        $stmt = $this->db->prepare('INSERT INTO users (username, full_name, password_hash, role) VALUES (:username, :full_name, :password_hash, :role)');
        return $stmt->execute([
            'username' => $username,
            'full_name' => $fullName,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role' => $role,
        ]);
    }
}
