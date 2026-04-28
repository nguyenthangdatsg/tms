<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class QuestionSet extends Component
{
    use WithPagination;

    protected $rules = [
        'formData.name' => 'required|string|max:255',
        'selectedQuestions' => 'required|array|min:1',
    ];

    public $sets = [];
    public $search = '';
    public $perPage = 20;
    public $currentPage = 1;
    public $showModal = false;
    public $editingSet = null;
    public $formData = [];
    public $availableQuestions = [];
    public $selectedQuestions = [];

    public function mount()
    {
        $this->loadSets();
        $this->loadAvailableQuestions();
    }

    public function loadSets()
    {
        $moodle = app('moodle');
        $this->sets = $moodle->getQuestionSets();
    }

    public function loadAvailableQuestions()
    {
        $moodle = app('moodle');
        $this->availableQuestions = $moodle->getQuestions();
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

    public function openEditModal($setId)
    {
        $moodle = app('moodle');
        $set = $moodle->getQuestionSet($setId);
        
        if ($set) {
            $this->editingSet = $set;
            $this->formData = [
                'name' => $set->name ?? '',
                'description' => $set->description ?? '',
            ];
            
            // Load questions in this set
            $setQuestions = $moodle->getQuestionsInSet($setId);
            $this->selectedQuestions = array_map(function($q) { return $q->question_id; }, $setQuestions);
            
            $this->showModal = true;
        }
    }

    public function resetForm()
    {
        $this->editingSet = null;
        $this->formData = [
            'name' => '',
            'description' => '',
        ];
        $this->selectedQuestions = [];
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function saveSet()
    {
        $this->validate();
        
        $moodle = app('moodle');
        
        $data = [
            'name' => $this->formData['name'] ?? 'New Question Set',
            'description' => $this->formData['description'] ?? '',
            'question_ids' => $this->selectedQuestions,
        ];

        if ($this->editingSet) {
            $moodle->updateQuestionSet($this->editingSet->id, $data);
        } else {
            $moodle->createQuestionSet($data);
        }

        $this->loadSets();
        $this->closeModal();
    }

    public function deleteSet($setId)
    {
        $moodle = app('moodle');
        $moodle->deleteQuestionSet($setId);
        $this->loadSets();
    }

    public function getFilteredSetsProperty()
    {
        if (empty($this->search)) {
            return $this->sets;
        }

        $search = strtolower($this->search);
        return array_filter($this->sets, function($set) use ($search) {
            return strpos(strtolower($set->name ?? ''), $search) !== false
                || strpos(strtolower($set->description ?? ''), $search) !== false;
        });
    }

    public function render()
    {
        $filtered = $this->filteredSets;
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

        $paginatedSets = [
            'sets' => $sliced,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => $lastPage,
        ];

        return view('livewire.question-set', [
            'paginatedSets' => $paginatedSets,
            'availableQuestions' => $this->availableQuestions,
        ]);
    }
}