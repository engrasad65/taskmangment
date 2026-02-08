<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Notification
{
    public function __construct(private PDO $db)
    {
    }

    public function create(int $userId, string $title, string $message): bool
    {
        $stmt = $this->db->prepare('INSERT INTO notifications (user_id, title, message, is_read, created_at) VALUES (:user_id, :title, :message, 0, CURRENT_TIMESTAMP)');
        return $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
        ]);
    }

    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE user_id = :user_id ORDER BY id DESC LIMIT 50');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0');
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0');
        return $stmt->execute(['user_id' => $userId]);
    }
}
