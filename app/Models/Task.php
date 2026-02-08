<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Task
{
    public function __construct(private PDO $db)
    {
    }

    public function assignedToUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT t.*, s.name AS site_name FROM tasks t LEFT JOIN sites s ON s.id=t.site_id WHERE t.user_id = :user_id ORDER BY t.id DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function allWithUsers(): array
    {
        $sql = 'SELECT t.*, u.full_name, s.name AS site_name FROM tasks t LEFT JOIN users u ON u.id=t.user_id LEFT JOIN sites s ON s.id=t.site_id ORDER BY t.id DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function findByIdAndUser(int $taskId, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tasks WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['id' => $taskId, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function startWork(int $taskId, int $userId, string $imagePath): bool
    {
        $stmt = $this->db->prepare("UPDATE tasks SET status='in_progress', started_at=CURRENT_TIMESTAMP, start_image=:start_image WHERE id=:id AND user_id=:user_id AND status='assigned'");
        $stmt->execute(['start_image' => $imagePath, 'id' => $taskId, 'user_id' => $userId]);
        return $stmt->rowCount() === 1;
    }

    public function endWork(int $taskId, int $userId, string $imagePath, string $progress): bool
    {
        $stmt = $this->db->prepare("UPDATE tasks SET status='completed', ended_at=CURRENT_TIMESTAMP, end_image=:end_image, progress_note=:progress_note WHERE id=:id AND user_id=:user_id AND status='in_progress'");
        $stmt->execute([
            'end_image' => $imagePath,
            'progress_note' => $progress,
            'id' => $taskId,
            'user_id' => $userId,
        ]);
        return $stmt->rowCount() === 1;
    }

    public function create(string $title, int $userId, int $siteId, string $description): bool
    {
        $stmt = $this->db->prepare("INSERT INTO tasks (title, user_id, site_id, description, status) VALUES (:title, :user_id, :site_id, :description, 'assigned')");
        return $stmt->execute([
            'title' => $title,
            'user_id' => $userId,
            'site_id' => $siteId,
            'description' => $description,
        ]);
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }
}
