<?php

declare(strict_types=1);

require __DIR__ . '/../app/Core/Autoloader.php';
$config = require __DIR__ . '/../config/config.php';

$storageDir = __DIR__ . '/../storage';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}
$dbPath = __DIR__ . '/../storage/database.sqlite';
if (!is_file($dbPath)) {
    touch($dbPath);
}

$pdo = App\Core\Database::connection($config['db']);
$pdo->exec('PRAGMA foreign_keys = ON');

$pdo->exec('DROP TABLE IF EXISTS exam_paper_items');
$pdo->exec('DROP TABLE IF EXISTS exam_papers');
$pdo->exec('DROP TABLE IF EXISTS questions');
$pdo->exec('DROP TABLE IF EXISTS chapters');
$pdo->exec('DROP TABLE IF EXISTS subjects');
$pdo->exec('DROP TABLE IF EXISTS classes');
$pdo->exec('DROP TABLE IF EXISTS notifications');
$pdo->exec('DROP TABLE IF EXISTS tasks');
$pdo->exec('DROP TABLE IF EXISTS sites');
$pdo->exec('DROP TABLE IF EXISTS users');

$pdo->exec('CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ("admin", "user"))
)');

$pdo->exec('CREATE TABLE classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL
)');

$pdo->exec('CREATE TABLE subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
)');

$pdo->exec('CREATE TABLE chapters (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
)');

$pdo->exec('CREATE TABLE questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    chapter_id INTEGER NOT NULL,
    question_type TEXT NOT NULL,
    question_text TEXT NOT NULL,
    marks INTEGER NOT NULL,
    difficulty_level TEXT,
    slo_reference TEXT,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
)');

$pdo->exec('CREATE TABLE exam_papers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    class_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    created_by INTEGER NOT NULL,
    status TEXT NOT NULL CHECK(status IN ("draft", "finalized")),
    created_at TEXT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
)');

$pdo->exec('CREATE TABLE exam_paper_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paper_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    position INTEGER NOT NULL,
    FOREIGN KEY (paper_id) REFERENCES exam_papers(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id)
)');

$userModel = new App\Models\User($pdo);
$userModel->create('System Admin', 'admin@school.local', 'admin12345', 'admin');
$userModel->create('School Head', 'head@school.local', 'head12345', 'user');

$academicModel = new App\Models\Academic($pdo);
$academicModel->createClass('Grade 9');
$academicModel->createClass('Grade 10');
$academicModel->createSubject(1, 'Mathematics');
$academicModel->createSubject(1, 'Physics');
$academicModel->createChapter(1, 'Algebra');
$academicModel->createChapter(1, 'Geometry');
$academicModel->createChapter(2, 'Motion');

$questionModel = new App\Models\QuestionBank($pdo);
$samples = [
    ['chapter_id' => 1, 'question_type' => 'MCQ', 'question_text' => 'What is 2x + 3 = 11? Solve for x.', 'marks' => 2],
    ['chapter_id' => 1, 'question_type' => 'Short Question', 'question_text' => 'Define linear equation.', 'marks' => 3],
    ['chapter_id' => 2, 'question_type' => 'True/False', 'question_text' => 'All squares are rectangles.', 'marks' => 1],
    ['chapter_id' => 2, 'question_type' => 'Long Question', 'question_text' => 'Prove Pythagoras theorem with a diagram.', 'marks' => 8],
    ['chapter_id' => 3, 'question_type' => 'Fill in the Blanks', 'question_text' => 'Velocity is displacement divided by ____.', 'marks' => 1],
    ['chapter_id' => 3, 'question_type' => 'SLO-Based', 'question_text' => 'Apply Newton\'s second law to calculate force.', 'marks' => 5],
];
foreach ($samples as $sample) {
    $questionModel->create([
        'class_id' => 1,
        'subject_id' => $sample['chapter_id'] === 3 ? 2 : 1,
        'chapter_id' => $sample['chapter_id'],
        'question_type' => $sample['question_type'],
        'question_text' => $sample['question_text'],
        'marks' => $sample['marks'],
        'difficulty_level' => 'medium',
        'slo_reference' => 'SLO-' . $sample['chapter_id'],
    ]);
}

echo "Setup complete.\nAdmin: admin@school.local / admin12345\nSchool Head: head@school.local / head12345\n";
