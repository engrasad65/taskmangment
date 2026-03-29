<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class ExamPaper
{
    public function __construct(private PDO $db)
    {
    }

    public function create(int $createdBy, string $title, int $classId, int $subjectId): int
    {
        $stmt = $this->db->prepare('INSERT INTO exam_papers (title, class_id, subject_id, created_by, status, created_at) VALUES (:title, :class_id, :subject_id, :created_by, :status, :created_at)');
        $stmt->execute([
            'title' => $title,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'created_by' => $createdBy,
            'status' => 'draft',
            'created_at' => gmdate('c'),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function addItem(int $paperId, int $questionId, int $position): bool
    {
        $stmt = $this->db->prepare('INSERT INTO exam_paper_items (paper_id, question_id, position) VALUES (:paper_id, :question_id, :position)');
        return $stmt->execute(['paper_id' => $paperId, 'question_id' => $questionId, 'position' => $position]);
    }

    public function items(int $paperId): array
    {
        $sql = 'SELECT epi.id AS item_id, epi.position, q.id AS question_id, q.* FROM exam_paper_items epi INNER JOIN questions q ON q.id = epi.question_id WHERE epi.paper_id = :paper_id ORDER BY epi.position, epi.id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['paper_id' => $paperId]);
        return $stmt->fetchAll();
    }

    public function find(int $paperId): ?array
    {
        $sql = 'SELECT ep.*, c.name AS class_name, s.name AS subject_name, u.full_name AS created_by_name
                FROM exam_papers ep
                INNER JOIN classes c ON c.id = ep.class_id
                INNER JOIN subjects s ON s.id = ep.subject_id
                INNER JOIN users u ON u.id = ep.created_by
                WHERE ep.id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $paperId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function listForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT id, title, status, created_at FROM exam_papers WHERE created_by = :created_by ORDER BY id DESC');
        $stmt->execute(['created_by' => $userId]);
        return $stmt->fetchAll();
    }

    public function listAll(): array
    {
        $sql = 'SELECT ep.id, ep.title, ep.status, ep.created_at, u.full_name AS created_by_name, c.name AS class_name, s.name AS subject_name
                FROM exam_papers ep
                INNER JOIN users u ON u.id = ep.created_by
                INNER JOIN classes c ON c.id = ep.class_id
                INNER JOIN subjects s ON s.id = ep.subject_id
                ORDER BY ep.id DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function finalize(int $paperId): bool
    {
        return $this->db->prepare('UPDATE exam_papers SET status = :status WHERE id = :id')->execute(['status' => 'finalized', 'id' => $paperId]);
    }

    public function removeItem(int $itemId): bool
    {
        return $this->db->prepare('DELETE FROM exam_paper_items WHERE id = :id')->execute(['id' => $itemId]);
    }

    public function updateItemPosition(int $itemId, int $position): bool
    {
        return $this->db->prepare('UPDATE exam_paper_items SET position = :position WHERE id = :id')->execute(['id' => $itemId, 'position' => $position]);
    }

    public function replaceItemQuestion(int $itemId, int $questionId): bool
    {
        return $this->db->prepare('UPDATE exam_paper_items SET question_id = :question_id WHERE id = :id')->execute(['id' => $itemId, 'question_id' => $questionId]);
    }
}
