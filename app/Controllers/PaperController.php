<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Academic;
use App\Models\ExamPaper;
use App\Models\QuestionBank;

class PaperController extends Controller
{
    public function dashboard(Academic $academicModel, ExamPaper $paperModel, ?string $error = null): void
    {
        $user = Session::get('user');
        $this->view('user/dashboard', [
            'classes' => $academicModel->allClasses(),
            'subjects' => $academicModel->allSubjects(),
            'chapters' => $academicModel->allChapters(),
            'papers' => $paperModel->listForUser((int) $user['id']),
            'questionTypeOptions' => ['MCQ', 'True/False', 'Fill in the Blanks', 'Short Question', 'Long Question', 'SLO-Based'],
            'error' => $error,
        ]);
    }

    public function generate(Academic $academicModel, ExamPaper $paperModel, QuestionBank $questionBankModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->dashboard($academicModel, $paperModel, 'CSRF validation failed.');
            return;
        }
        $user = Session::get('user');
        $title = trim($_POST['title'] ?? '');
        $classId = (int) ($_POST['class_id'] ?? 0);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $chapterIds = array_map('intval', $_POST['chapter_ids'] ?? []);
        $typeConfig = $_POST['type_config'] ?? [];

        if (!Validator::required($title) || $classId < 1 || $subjectId < 1 || $chapterIds === [] || !is_array($typeConfig)) {
            $this->dashboard($academicModel, $paperModel, 'Please fill title, class, subject, chapters, and question quantities.');
            return;
        }

        $paperId = $paperModel->create((int) $user['id'], $title, $classId, $subjectId);
        $position = 1;
        $selectedIds = [];

        foreach ($typeConfig as $type => $qtyRaw) {
            $qty = (int) $qtyRaw;
            if ($qty <= 0) {
                continue;
            }
            $batch = $questionBankModel->randomByCriteria($subjectId, $chapterIds, (string) $type, $qty, $selectedIds);
            foreach ($batch as $question) {
                $selectedIds[] = (int) $question['id'];
                $paperModel->addItem($paperId, (int) $question['id'], $position++);
            }
        }

        $this->redirect('/paper/review?id=' . $paperId);
    }

    public function review(ExamPaper $paperModel, QuestionBank $questionBankModel): void
    {
        $paperId = (int) ($_GET['id'] ?? 0);
        $paper = $paperModel->find($paperId);
        if (!$paper) {
            http_response_code(404);
            echo 'Paper not found';
            return;
        }
        $items = $paperModel->items($paperId);
        $this->view('user/review', [
            'paper' => $paper,
            'items' => $items,
            'questionBankModel' => $questionBankModel,
        ]);
    }

    public function updateItems(ExamPaper $paperModel, QuestionBank $questionBankModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->redirect('/user/dashboard');
        }
        $paperId = (int) ($_POST['paper_id'] ?? 0);

        foreach (($_POST['positions'] ?? []) as $itemId => $position) {
            $paperModel->updateItemPosition((int) $itemId, (int) $position);
        }

        if (!empty($_POST['remove_item_id'])) {
            $paperModel->removeItem((int) $_POST['remove_item_id']);
        }

        if (!empty($_POST['replace_item_id'])) {
            $itemId = (int) $_POST['replace_item_id'];
            $currentItems = $paperModel->items($paperId);
            $currentQuestionIds = array_map(static fn(array $it): int => (int) $it['question_id'], $currentItems);
            $targetItem = null;
            foreach ($currentItems as $item) {
                if ((int) $item['item_id'] === $itemId) {
                    $targetItem = $item;
                    break;
                }
            }
            if ($targetItem) {
                $alternatives = $questionBankModel->randomByCriteria((int) $targetItem['subject_id'], [(int) $targetItem['chapter_id']], (string) $targetItem['question_type'], 1, $currentQuestionIds);
                if ($alternatives !== []) {
                    $paperModel->replaceItemQuestion($itemId, (int) $alternatives[0]['id']);
                }
            }
        }

        $this->redirect('/paper/review?id=' . $paperId);
    }

    public function finalize(ExamPaper $paperModel): void
    {
        if (Csrf::verify($_POST['_csrf'] ?? null)) {
            $paperModel->finalize((int) ($_POST['paper_id'] ?? 0));
        }
        $this->redirect('/user/dashboard');
    }

    public function print(ExamPaper $paperModel): void
    {
        $paperId = (int) ($_GET['id'] ?? 0);
        $paper = $paperModel->find($paperId);
        if (!$paper) {
            http_response_code(404);
            echo 'Paper not found';
            return;
        }
        $this->view('paper/print', ['paper' => $paper, 'items' => $paperModel->items($paperId)]);
    }
}
