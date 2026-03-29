<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class QuestionBank
{
    public function __construct(private PDO $db)
    {
    }

    public function create(array $data): bool
    {
        $sql = 'INSERT INTO questions (class_id, subject_id, chapter_id, question_type, question_text, marks, difficulty_level, slo_reference)
                VALUES (:class_id, :subject_id, :chapter_id, :question_type, :question_text, :marks, :difficulty_level, :slo_reference)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $sql = 'UPDATE questions SET class_id = :class_id, subject_id = :subject_id, chapter_id = :chapter_id, question_type = :question_type,
                question_text = :question_text, marks = :marks, difficulty_level = :difficulty_level, slo_reference = :slo_reference WHERE id = :id';
        return $this->db->prepare($sql)->execute($data);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM questions WHERE id = :id')->execute(['id' => $id]);
    }

    public function byId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM questions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function paginated(array $filters, int $limit, int $offset): array
    {
        $conditions = [];
        $params = [];
        foreach (['class_id', 'subject_id', 'chapter_id', 'question_type'] as $f) {
            if (!empty($filters[$f])) {
                $conditions[] = 'q.' . $f . ' = :' . $f;
                $params[$f] = $filters[$f];
            }
        }
        if (!empty($filters['q'])) {
            $conditions[] = 'q.question_text LIKE :q';
            $params['q'] = '%' . $filters['q'] . '%';
        }
        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

        $sql = 'SELECT q.*, c.name AS class_name, s.name AS subject_name, ch.name AS chapter_name
                FROM questions q
                INNER JOIN classes c ON c.id = q.class_id
                INNER JOIN subjects s ON s.id = q.subject_id
                INNER JOIN chapters ch ON ch.id = q.chapter_id
                ' . $where . '
                ORDER BY q.id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(array $filters): int
    {
        $conditions = [];
        $params = [];
        foreach (['class_id', 'subject_id', 'chapter_id', 'question_type'] as $f) {
            if (!empty($filters[$f])) {
                $conditions[] = $f . ' = :' . $f;
                $params[$f] = $filters[$f];
            }
        }
        if (!empty($filters['q'])) {
            $conditions[] = 'question_text LIKE :q';
            $params['q'] = '%' . $filters['q'] . '%';
        }
        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM questions ' . $where);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function randomByCriteria(int $subjectId, array $chapterIds, string $type, int $limit, array $excludeIds = []): array
    {
        if ($chapterIds === [] || $limit <= 0) {
            return [];
        }
        $chapterPlaceholders = implode(',', array_fill(0, count($chapterIds), '?'));
        $excludeSql = $excludeIds ? 'AND id NOT IN (' . implode(',', array_fill(0, count($excludeIds), '?')) . ')' : '';
        $sql = 'SELECT * FROM questions WHERE subject_id = ? AND question_type = ? AND chapter_id IN (' . $chapterPlaceholders . ') ' . $excludeSql . ' ORDER BY RANDOM() LIMIT ?';
        $stmt = $this->db->prepare($sql);
        $binds = [$subjectId, $type, ...$chapterIds, ...$excludeIds, $limit];
        foreach ($binds as $index => $value) {
            $stmt->bindValue($index + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
