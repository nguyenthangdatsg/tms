<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Quiz extends Component
{
    use WithFileUploads, WithPagination;
    
    public $quizzes = [];
    public $search = '';
    public $perPage = 20;
    public $currentPage = 1;
    public $showModal = false;
    public $showViewModal = false;
    public $editingQuiz = null;
    public $viewingQuiz = null;
    public $formData = [];
    public $selectedQuestions = [];
    public $availableQuestions = [];
    public $newImage = null;
    public $deleteId = null;
    public $courses = [];
    public $allUsers = [];
    public $enrolledUserIds = [];
    public $groupIds = '';
    public $cohortIds = '';
    public $activeConfigTab = true;
    public $enrollmentSubTab = 'personal';
    public $questionSets = [];
    public $selectedSetId = null;
    
    // Quiz form fields
    public $course = '';
    public $name = '';
    public $intro = '';
    public $introformat = 0;
    public $timeopen = '';
    public $timeclose = '';
    public $timelimit = '';
    public $overduehandling = 'autoabandon';
    public $graceperiod = '';
    public $preferredbehaviour = 'deferredfeedback';
    public $canredoquestions = 0;
    public $attempts = 1;
    public $attemptonlast = 0;
    public $grademethod = 1;
    public $decimalpoints = 2;
    public $questiondecimalpoints = -1;
    public $reviewattempt = 1023;
    public $reviewcorrectness = 511;
    public $reviewmaxmarks = 7;
    public $reviewmarks = 5;
    public $reviewspecificfeedback = 3;
    public $reviewgeneralfeedback = 3;
    public $reviewrightanswer = 3;
    public $reviewoverallfeedback = 3;
    public $questionsperpage = 0;
    public $navmethod = 'free';
    public $shuffleanswers = 0;
    public $password = '';
    public $subnet = '';
    public $browsersecurity = '';
    public $delay1 = 0;
    public $delay2 = 0;
    public $showuserpicture = 0;
    public $showblocks = 0;
    public $completionattemptsexhausted = 0;
    public $completionminattempts = 0;
    public $allowofflineattempts = 0;
    
    public function mount()
    {
        $this->loadQuizzes();
        $this->loadQuestions();
        $this->loadCourses();
        $this->loadAllUsers();
        $this->loadQuestionSets();
    }

    public function loadQuestionSets()
    {
        $moodle = app('moodle');
        $this->questionSets = $moodle->getQuestionSets();
    }
    
    public function loadQuizzes()
    {
        $moodle = app('moodle');
        $this->quizzes = $moodle->getQuizzes();
    }

    public function loadCourses()
    {
        $moodle = app('moodle');
        $this->courses = $moodle->getCourses();
    }

    public function loadAllUsers()
    {
        $moodle = app('moodle');
        $this->allUsers = $moodle->getUsers();
    }

    public function setConfigActive(bool $flag)
    {
        $this->activeConfigTab = (bool)$flag;
    }

    public function setEnrollmentSubTab(string $tab)
    {
        $this->enrollmentSubTab = $tab;
    }
    public function appendGroupId()
    {
        if (!empty($this->groupIds)) {
            $this->groupIds .= ',';
        }
        $this->groupIds .= '';
    }
    public function appendCohortId()
    {
        if (!empty($this->cohortIds)) {
            $this->cohortIds .= ',';
        }
        $this->cohortIds .= '';
    }
    
    public function loadQuestions()
    {
        $moodle = app('moodle');
        // Get all questions from question bank
        $this->availableQuestions = $moodle->getQuestions(); // We'll need to add this method
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
    
    public function openEditModal($quizId)
    {
        $moodle = app('moodle');
        $quiz = $moodle->getQuiz($quizId);
        
        if ($quiz) {
            $this->editingQuiz = $quiz;
            $this->formData = [
                'course' => $quiz->course ?? '',
                'name' => $quiz->name ?? '',
                'intro' => $quiz->intro ?? '',
                'introformat' => $quiz->introformat ?? 0,
                'timeopen' => $quiz->timeopen ? date('Y-m-d H:i:s', $quiz->timeopen) : '',
                'timeclose' => $quiz->timeclose ? date('Y-m-d H:i:s', $quiz->timeclose) : '',
                'timelimit' => $quiz->timelimit ?? '',
                'overduehandling' => $quiz->overduehandling ?? 'autoabandon',
                'graceperiod' => $quiz->graceperiod ?? '',
                'preferredbehaviour' => $quiz->preferredbehaviour ?? 'deferredfeedback',
                'canredoquestions' => $quiz->canredoquestions ?? 0,
                'attempts' => $quiz->attempts ?? 1,
                'attemptonlast' => $quiz->attemptonlast ?? 0,
                'grademethod' => $quiz->grademethod ?? 1,
                'decimalpoints' => $quiz->decimalpoints ?? 2,
                'questiondecimalpoints' => $quiz->questiondecimalpoints ?? -1,
                'reviewattempt' => $quiz->reviewattempt ?? 1023,
                'reviewcorrectness' => $quiz->reviewcorrectness ?? 511,
                'reviewmaxmarks' => $quiz->reviewmaxmarks ?? 7,
                'reviewmarks' => $quiz->reviewmarks ?? 5,
                'reviewspecificfeedback' => $quiz->reviewspecificfeedback ?? 3,
                'reviewgeneralfeedback' => $quiz->reviewgeneralfeedback ?? 3,
                'reviewrightanswer' => $quiz->reviewrightanswer ?? 3,
                'reviewoverallfeedback' => $quiz->reviewoverallfeedback ?? 3,
                'questionsperpage' => $quiz->questionsperpage ?? 0,
                'navmethod' => $quiz->navmethod ?? 'free',
                'shuffleanswers' => $quiz->shuffleanswers ?? 0,
                'password' => $quiz->password ?? '',
                'subnet' => $quiz->subnet ?? '',
                'browsersecurity' => $quiz->browsersecurity ?? '',
                'delay1' => $quiz->delay1 ?? 0,
                'delay2' => $quiz->delay2 ?? 0,
                'showuserpicture' => $quiz->showuserpicture ?? 0,
                'showblocks' => $quiz->showblocks ?? 0,
                'completionattemptsexhausted' => $quiz->completionattemptsexhausted ?? 0,
                'completionminattempts' => $quiz->completionminattempts ?? 0,
                'allowofflineattempts' => $quiz->allowofflineattempts ?? 0,
            ];
            
            $this->selectedQuestions = $moodle->getQuestionsInQuiz($quiz->id);
            $this->showModal = true;
        }
    }
    
    public function openViewModal($quizId)
    {
        $moodle = app('moodle');
        $quiz = $moodle->getQuiz($quizId);
        
        if ($quiz) {
            $this->viewingQuiz = $quiz;
            $this->selectedQuestions = $moodle->getQuestionsInQuiz($quiz->id);
            $this->showViewModal = true;
        }
    }
    
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingQuiz = null;
    }
    
    public function resetForm()
    {
        $this->editingQuiz = null;
        $this->formData = [
            'course' => '',
            'name' => '',
            'intro' => '',
            'introformat' => 0,
            'timeopen' => '',
            'timeclose' => '',
            'timelimit' => '',
            'overduehandling' => 'autoabandon',
            'graceperiod' => '',
            'preferredbehaviour' => 'deferredfeedback',
            'canredoquestions' => 0,
            'attempts' => 1,
            'attemptonlast' => 0,
            'grademethod' => 1,
            'decimalpoints' => 2,
            'questiondecimalpoints' => -1,
            'reviewattempt' => 1023,
            'reviewcorrectness' => 511,
            'reviewmaxmarks' => 7,
            'reviewmarks' => 5,
            'reviewspecificfeedback' => 3,
            'reviewgeneralfeedback' => 3,
            'reviewrightanswer' => 3,
            'reviewoverallfeedback' => 3,
            'questionsperpage' => 0,
            'navmethod' => 'free',
            'shuffleanswers' => 0,
            'password' => '',
            'subnet' => '',
            'browsersecurity' => '',
            'delay1' => 0,
            'delay2' => 0,
            'showuserpicture' => 0,
            'showblocks' => 0,
            'completionattemptsexhausted' => 0,
            'completionminattempts' => 0,
            'allowofflineattempts' => 0,
        ];
        $this->selectedQuestions = [];
        $this->newImage = null;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }
    
    public function saveQuiz()
    {
        $moodle = app('moodle');
        // Normalize time fields from datetime-local to unix timestamps
        $tf = $this->formData;
        if (isset($tf['timeopen']) && $tf['timeopen'] !== '') {
            $tf['timeopen'] = is_numeric($tf['timeopen']) ? (int)$tf['timeopen'] : strtotime($tf['timeopen']);
        } else {
            $tf['timeopen'] = 0;
        }
        if (isset($tf['timeclose']) && $tf['timeclose'] !== '') {
            $tf['timeclose'] = is_numeric($tf['timeclose']) ? (int)$tf['timeclose'] : strtotime($tf['timeclose']);
        } else {
            $tf['timeclose'] = 0;
        }
        // Basic numeric conversions
        $nums = ['timelimit','attempts','graceperiod','decimalpoints','questiondecimalpoints','reviewattempt','reviewcorrectness','reviewmaxmarks','reviewmarks','reviewspecificfeedback','reviewgeneralfeedback','reviewrightanswer','reviewoverallfeedback','questionsperpage','delay1','delay2'];
        foreach ($nums as $k) {
            $val = $tf[$k] ?? 0;
            $tf[$k] = is_numeric($val) ? (int)$val : 0;
        }
        // Booleans-like fields to ints
        foreach (['canredoquestions','attemptonlast','shuffleanswers','showuserpicture','showblocks','completionattemptsexhausted','completionminattempts','allowofflineattempts'] as $flag) {
            $tf[$flag] = !empty($tf[$flag]) ? 1 : 0;
        }
        // Merge back
        $this->formData = $tf;
        
        // Validate required fields
        $this->validate([
            'formData.course' => 'required',
            'formData.name' => 'required',
            'formData.timeopen' => 'nullable|date',
            'formData.timeclose' => 'nullable|date|after_or_equal:formData.timeopen',
            'formData.timelimit' => 'nullable|numeric|min:0',
            'formData.attempts' => 'nullable|numeric|min:1',
            'formData.graceperiod' => 'nullable|numeric|min:0',
        ]);
        
        if ($this->editingQuiz) {
            $moodle->updateQuiz($this->editingQuiz->id, $this->formData);
            
            // Handle image upload if needed
            // if ($this->newImage) {
            //     $moodle->uploadQuizImage($this->editingQuiz->id, $this->newImage);
            // }
        } else {
            $quizId = $moodle->createQuiz($this->formData);
            
            // Handle image upload if needed
            // if ($this->newImage) {
            //     $moodle->uploadQuizImage($quizId, $this->newImage);
            // }
        }
        
        $this->closeModal();
        $this->loadQuizzes();
    }
    
    public function confirmDelete($quizId)
    {
        $this->deleteId = $quizId;
        $this->dispatch('showDeleteAlert');
    }
    
    public function deleteQuiz()
    {
        if ($this->deleteId) {
            $moodle = app('moodle');
            $moodle->deleteQuiz($this->deleteId);
            $this->deleteId = null;
            $this->loadQuizzes();
        }
    }
    
    public function addQuestion($questionId)
    {
        if (!$this->editingQuiz) {
            $this->dispatch('browser', ['alert' => ['type' => 'error', 'message' => 'Please save the quiz first before adding questions']]);
            return;
        }
        
        $moodle = app('moodle');
        $moodle->addQuestionToQuiz($this->editingQuiz->id, $questionId);
        
        // Refresh selected questions
        $this->selectedQuestions = $moodle->getQuestionsInQuiz($this->editingQuiz->id);
    }

    public function importFromQuestionSet()
    {
        if (!$this->selectedSetId) {
            return;
        }
        
        $moodle = app('moodle');
        $setQuestions = $moodle->getQuestionsInSet($this->selectedSetId);
        
        foreach ($setQuestions as $sq) {
            if (!in_array($sq->question_id, $this->selectedQuestions)) {
                $this->selectedQuestions[] = $sq->question_id;
            }
        }
        
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Imported ' . count($setQuestions) . ' questions from set']]);
    }

    public function enrollStudents()
    {
        if (!$this->editingQuiz) {
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->formData['course'] ?? 1;
        
        // Enroll selected users
        if (!empty($this->enrolledUserIds)) {
            foreach ($this->enrolledUserIds as $userId) {
                $moodle->enrolUser($userId, $courseId, 5);
            }
        }
        
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Enrolled ' . count($this->enrolledUserIds) . ' students']]);
    }
    
    public function removeQuestion($slotId)
    {
        if (!$this->editingQuiz) {
            $this->dispatch('browser', ['alert' => ['type' => 'error', 'message' => 'Please save the quiz first before removing questions']]);
            return;
        }
        
        $moodle = app('moodle');
        $moodle->removeQuestionFromQuiz($slotId);
        
        // Refresh selected questions
        $this->selectedQuestions = $moodle->getQuestionsInQuiz($this->editingQuiz->id);
    }
    
    public function getFilteredQuizzesProperty()
    {
        if (empty($this->search)) {
            return $this->quizzes;
        }
        
        $search = strtolower($this->search);
        return array_filter($this->quizzes, function($quiz) use ($search) {
            return strpos(strtolower($quiz->name), $search) !== false
                || (isset($quiz->course_name) && strpos(strtolower($quiz->course_name), $search) !== false);
        });
    }
    
    public function render()
    {
        $filtered = $this->filteredQuizzes;
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
        
        $paginatedQuizzes = [
            'quizzes' => $sliced,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => $lastPage,
        ];
        
        return view('livewire.quiz', [
            'paginatedQuizzes' => $paginatedQuizzes,
            'selectedQuestions' => $this->selectedQuestions,
            'availableQuestions' => $this->availableQuestions,
            'courses' => $this->courses,
            'allUsers' => $this->allUsers,
            'activeConfigTab' => $this->activeConfigTab,
            'viewingQuiz' => $this->viewingQuiz,
            'showViewModal' => $this->showViewModal,
            'questionSets' => $this->questionSets,
        ]);
    }
}
