<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class User
{
    public function __construct(private PDO $db)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower($email)]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, full_name, email, role FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT id, full_name, email, role FROM users ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function allByRole(string $role): array
    {
        $stmt = $this->db->prepare('SELECT id, full_name, email, role FROM users WHERE role = :role ORDER BY id DESC');
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll();
    }

    public function create(string $fullName, string $email, string $password, string $role): bool
    {
        $stmt = $this->db->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (:full_name, :email, :password_hash, :role)');
        return $stmt->execute([
            'full_name' => $fullName,
            'email' => strtolower($email),
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role' => $role,
        ]);
    }

    public function update(int $id, string $fullName, string $email, string $role): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET full_name = :full_name, email = :email, role = :role WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'full_name' => $fullName,
            'email' => strtolower($email),
            'role' => $role,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
