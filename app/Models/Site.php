<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Site
{
    public function __construct(private PDO $db)
    {
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM sites ORDER BY id DESC')->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM sites WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(string $name, string $location): bool
    {
        $stmt = $this->db->prepare('INSERT INTO sites (name, location) VALUES (:name, :location)');
        return $stmt->execute(['name' => $name, 'location' => $location]);
    }
}
