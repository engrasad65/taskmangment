<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Academic
{
    public function __construct(private PDO $db)
    {
    }

    public function allClasses(): array
    {
        return $this->db->query('SELECT * FROM classes ORDER BY name')->fetchAll();
    }

    public function createClass(string $name): bool
    {
        return $this->db->prepare('INSERT INTO classes (name) VALUES (:name)')->execute(['name' => $name]);
    }

    public function allSubjects(): array
    {
        $sql = 'SELECT subjects.*, classes.name AS class_name FROM subjects INNER JOIN classes ON classes.id = subjects.class_id ORDER BY classes.name, subjects.name';
        return $this->db->query($sql)->fetchAll();
    }

    public function subjectsByClass(int $classId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM subjects WHERE class_id = :class_id ORDER BY name');
        $stmt->execute(['class_id' => $classId]);
        return $stmt->fetchAll();
    }

    public function createSubject(int $classId, string $name): bool
    {
        $stmt = $this->db->prepare('INSERT INTO subjects (class_id, name) VALUES (:class_id, :name)');
        return $stmt->execute(['class_id' => $classId, 'name' => $name]);
    }

    public function allChapters(): array
    {
        $sql = 'SELECT chapters.*, subjects.name AS subject_name, classes.name AS class_name
                FROM chapters
                INNER JOIN subjects ON subjects.id = chapters.subject_id
                INNER JOIN classes ON classes.id = subjects.class_id
                ORDER BY classes.name, subjects.name, chapters.name';
        return $this->db->query($sql)->fetchAll();
    }

    public function chaptersBySubject(int $subjectId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM chapters WHERE subject_id = :subject_id ORDER BY name');
        $stmt->execute(['subject_id' => $subjectId]);
        return $stmt->fetchAll();
    }

    public function createChapter(int $subjectId, string $name): bool
    {
        $stmt = $this->db->prepare('INSERT INTO chapters (subject_id, name) VALUES (:subject_id, :name)');
        return $stmt->execute(['subject_id' => $subjectId, 'name' => $name]);
    }
}
