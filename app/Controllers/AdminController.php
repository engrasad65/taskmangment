<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Validator;
use App\Models\Academic;
use App\Models\ExamPaper;
use App\Models\QuestionBank;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard(User $userModel, Academic $academicModel, QuestionBank $questionBankModel, ExamPaper $paperModel, ?string $message = null): void
    {
        $filters = [
            'class_id' => $_GET['class_id'] ?? '',
            'subject_id' => $_GET['subject_id'] ?? '',
            'chapter_id' => $_GET['chapter_id'] ?? '',
            'question_type' => $_GET['question_type'] ?? '',
            'q' => trim($_GET['q'] ?? ''),
        ];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $totalQuestions = $questionBankModel->count($filters);

        $this->view('admin/dashboard', [
            'users' => $userModel->all(),
            'classes' => $academicModel->allClasses(),
            'subjects' => $academicModel->allSubjects(),
            'chapters' => $academicModel->allChapters(),
            'questions' => $questionBankModel->paginated($filters, $limit, $offset),
            'filters' => $filters,
            'questionTypeOptions' => ['MCQ', 'True/False', 'Fill in the Blanks', 'Short Question', 'Long Question', 'SLO-Based'],
            'page' => $page,
            'totalPages' => max(1, (int) ceil($totalQuestions / $limit)),
            'papers' => $paperModel->listAll(),
            'message' => $message,
        ]);
    }

    public function createUser(User $userModel, Academic $academicModel, QuestionBank $questionBankModel, ExamPaper $paperModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($userModel, $academicModel, $questionBankModel, $paperModel, 'CSRF validation failed.');
            return;
        }
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        if (!Validator::required($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || !in_array($role, ['admin', 'user'], true)) {
            $this->dashboard($userModel, $academicModel, $questionBankModel, $paperModel, 'Valid full name, email, role, and password (min 8) are required.');
            return;
        }

        if ($userModel->findByEmail($email)) {
            $this->dashboard($userModel, $academicModel, $questionBankModel, $paperModel, 'Email already exists.');
            return;
        }

        $userModel->create($name, $email, $password, $role);
        $this->redirect('/admin/dashboard');
    }

    public function updateUser(User $userModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->redirect('/admin/dashboard');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        if ($id > 0 && Validator::required($name) && filter_var($email, FILTER_VALIDATE_EMAIL) && in_array($role, ['admin', 'user'], true)) {
            $userModel->update($id, $name, $email, $role);
        }
        $this->redirect('/admin/dashboard');
    }

    public function deleteUser(User $userModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->redirect('/admin/dashboard');
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $userModel->delete($id);
        }
        $this->redirect('/admin/dashboard');
    }

    public function createClass(Academic $academicModel): void
    {
        if (Csrf::verify($_POST['_csrf'] ?? null) && Validator::required($_POST['name'] ?? '')) {
            $academicModel->createClass(trim((string) $_POST['name']));
        }
        $this->redirect('/admin/dashboard');
    }

    public function createSubject(Academic $academicModel): void
    {
        if (Csrf::verify($_POST['_csrf'] ?? null)) {
            $classId = (int) ($_POST['class_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            if ($classId > 0 && Validator::required($name)) {
                $academicModel->createSubject($classId, $name);
            }
        }
        $this->redirect('/admin/dashboard');
    }

    public function createChapter(Academic $academicModel): void
    {
        if (Csrf::verify($_POST['_csrf'] ?? null)) {
            $subjectId = (int) ($_POST['subject_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            if ($subjectId > 0 && Validator::required($name)) {
                $academicModel->createChapter($subjectId, $name);
            }
        }
        $this->redirect('/admin/dashboard');
    }

    public function createQuestion(QuestionBank $questionBankModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->redirect('/admin/dashboard');
        }
        $data = [
            'class_id' => (int) ($_POST['class_id'] ?? 0),
            'subject_id' => (int) ($_POST['subject_id'] ?? 0),
            'chapter_id' => (int) ($_POST['chapter_id'] ?? 0),
            'question_type' => trim($_POST['question_type'] ?? ''),
            'question_text' => trim($_POST['question_text'] ?? ''),
            'marks' => (int) ($_POST['marks'] ?? 0),
            'difficulty_level' => trim($_POST['difficulty_level'] ?? ''),
            'slo_reference' => trim($_POST['slo_reference'] ?? ''),
        ];
        if ($data['class_id'] > 0 && $data['subject_id'] > 0 && $data['chapter_id'] > 0 && Validator::required($data['question_type']) && Validator::required($data['question_text']) && $data['marks'] > 0) {
            $questionBankModel->create($data);
        }
        $this->redirect('/admin/dashboard');
    }

    public function updateQuestion(QuestionBank $questionBankModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->redirect('/admin/dashboard');
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $questionBankModel->update($id, [
                'class_id' => (int) ($_POST['class_id'] ?? 0),
                'subject_id' => (int) ($_POST['subject_id'] ?? 0),
                'chapter_id' => (int) ($_POST['chapter_id'] ?? 0),
                'question_type' => trim($_POST['question_type'] ?? ''),
                'question_text' => trim($_POST['question_text'] ?? ''),
                'marks' => (int) ($_POST['marks'] ?? 0),
                'difficulty_level' => trim($_POST['difficulty_level'] ?? ''),
                'slo_reference' => trim($_POST['slo_reference'] ?? ''),
            ]);
        }
        $this->redirect('/admin/dashboard');
    }

    public function deleteQuestion(QuestionBank $questionBankModel): void
    {
        if (Csrf::verify($_POST['_csrf'] ?? null)) {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $questionBankModel->delete($id);
            }
        }
        $this->redirect('/admin/dashboard');
    }
}
