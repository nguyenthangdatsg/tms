<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class QuestionBank extends Component
{
    use WithPagination;

    protected $rules = [
        'formData.name' => 'required|string|max:255',
        'formData.questiontext' => 'required|string',
        'formData.defaultmark' => 'required|numeric|min:1',
        'formData.qtype' => 'required',
    ];

    public $questions = [];
    public $search = '';
    public $perPage = 20;
    public $currentPage = 1;
    public $showModal = false;
    public $editingQuestion = null;
    public $formData = [];
    public $qtypeOptions = [];
    public $courses = [];

    public function mount()
    {
        $this->loadQuestions();
        $this->loadQtypes();
        $this->loadCourses();
    }

    public function loadQuestions()
    {
        $moodle = app('moodle');
        $this->questions = $moodle->getQuestions();
    }

    public function loadQtypes()
    {
        $moodle = app('moodle');
        $this->qtypeOptions = $moodle->getQuestionTypes();
    }

    public function loadCourses()
    {
        $moodle = app('moodle');
        $this->courses = $moodle->getCourses();
    }

    public function updatedSearch()
    {
        $this->currentPage = 1;
    }

    public function setPage($page)
    {
        $this->currentPage = $page;
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal($questionId)
    {
        $moodle = app('moodle');
        $question = $moodle->getQuestion($questionId);

        if ($question) {
            $this->editingQuestion = $question;
            $this->formData = [
                'name' => $question->name ?? '',
                'questiontext' => $question->questiontext ?? '',
                'qtype' => $question->qtype ?? 'essay',
                'defaultmark' => $question->defaultmark ?? 1,
            ];
            $this->showModal = true;
        }
    }

    public function resetForm()
    {
        $this->editingQuestion = null;
        $this->formData = [
            'name' => '',
            'questiontext' => '',
            'qtype' => 'essay',
            'defaultmark' => 1,
        ];
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function saveQuestion()
    {
        $this->validate();
        
        $moodle = app('moodle');

        if ($this->editingQuestion) {
            $moodle->updateQuestion($this->editingQuestion->id, $this->formData);
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Question updated successfully']]);
        } else {
            $moodle->createQuestion($this->formData);
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Question created successfully']]);
        }

        $this->loadQuestions();
        $this->closeModal();
    }

    public function deleteQuestion($questionId)
    {
        if (empty($questionId)) {
            return;
        }

        $moodle = app('moodle');
        $moodle->deleteQuestion($questionId);
        $this->loadQuestions();
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Question deleted successfully']]);
    }

    public function getFilteredQuestionsProperty()
    {
        if (empty($this->search)) {
            return $this->questions;
        }

        $search = strtolower($this->search);
        return array_filter($this->questions, function($question) use ($search) {
            return strpos(strtolower($question->name), $search) !== false
                || strpos(strtolower($question->questiontext ?? ''), $search) !== false
                || (isset($question->qtype_name) && strpos(strtolower($question->qtype_name), $search) !== false);
        });
    }

    public function render()
    {
        $filtered = $this->filteredQuestions;
        $total = count($filtered);
        $page = $this->currentPage;
        $perPage = $this->perPage;
        $lastPage = max(1, ceil($total / $perPage));

        if ($page > $lastPage) {
            $page = $lastPage;
            $this->currentPage = $page;
        }

        $start = ($page - 1) * $perPage;
        $sliced = array_slice($filtered, $start, $perPage);

        $paginatedQuestions = [
            'questions' => $sliced,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => $lastPage,
        ];

        return view('livewire.question-bank', [
            'paginatedQuestions' => $paginatedQuestions,
            'qtypeOptions' => $this->qtypeOptions,
            'courses' => $this->courses,
        ]);
    }
}
