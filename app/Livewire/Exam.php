<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Exam extends Component
{
    use WithPagination;
    
    public $quizzes = [];
    public $search = '';
    public $perPage = 20;
    public $currentPage = 1;
    public $showModal = false;
    public $showViewModal = false;
    public $showConfigModal = false;
    public $activeViewTab = 1;
    public $editingQuiz = null;
    public $viewingQuiz = null;
    public $configuringQuiz = null;
    public $formData = [];
    public $courses = [];
    public $selectedQuestions = [];
    public $activeConfigTab = 1;
    public $availableQuestions = [];
    public $questionSets = [];
    public $selectedSetId = null;
    public $enrolledUserIds = [];
    public $allUsers = [];
    public $cohorts = [];
    public $showEnrollModal = false;
    public $enrollingQuiz = null;
    public $activeEnrollTab = 1;
    public $selectedCohortId = null;
    public $selectedUserIds = [];
    public $unenrolUserIds = [];
    public $enrollSearch = '';
    public $enrollMethodFilter = '';
    public $enrolledUsersWithMethod = [];
    public $enrolledCohorts = [];
    public $importFile = null;
    public $importErrors = [];
    public $importSuccess = '';

    public function mount()
    {
        $this->loadQuizzes();
        $this->loadCourses();
        $this->loadAvailableQuestions();
    }

    public function loadQuizzes()
    {
        $moodle = app('moodle');
        $this->quizzes = $moodle->getTmsExams();
    }

    public function loadCourses()
    {
        $moodle = app('moodle');
        $this->courses = $moodle->getTmsCourses();
    }

    public function getCourseNameById($courseId)
    {
        $moodle = app('moodle');
        $courses = $moodle->getTmsCourses();
        foreach ($courses as $course) {
            if ($course->id == $courseId) {
                return $course->fullname;
            }
        }
        return 'Unknown';
    }

    public function loadAvailableQuestions()
    {
        $moodle = app('moodle');
        $this->availableQuestions = $moodle->getQuestions();
        $this->questionSets = $moodle->getQuestionSets();
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
        $this->reset();
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
                'timeopen' => $quiz->timeopen ? date('Y-m-d H:i:s', $quiz->timeopen) : '',
                'timeclose' => $quiz->timeclose ? date('Y-m-d H:i:s', $quiz->timeclose) : '',
                'timelimit' => $quiz->timelimit ?? '',
                'attempts' => $quiz->attempts ?? 1,
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

    public function openConfigModal($quizId)
    {
        $moodle = app('moodle');
        $quiz = $moodle->getQuiz($quizId);
        
        if ($quiz) {
            $this->configuringQuiz = $quiz;
            $this->activeConfigTab = 1;
            $this->selectedQuestions = $moodle->getQuestionsInQuiz($quiz->id);
            $this->questionSets = $moodle->getQuestionSets();
            
            $this->allUsers = $moodle->getAllUsers();
            $this->cohorts = $moodle->getCohorts();
            
            $courseId = $quiz->course_id ?? $quiz->course;
            $enrolledUsers = $moodle->getEnrolledUsers($courseId);
            $this->enrolledUserIds = array_column($enrolledUsers, 'id');
            
            $this->showConfigModal = true;
        }
    }

    public function closeConfigModal()
    {
        $this->showConfigModal = false;
        $this->configuringQuiz = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingQuiz = null;
        $this->formData = [];
        $this->selectedQuestions = [];
    }

    public function openEnrollModal($quizId)
    {
        $moodle = app('moodle');
        $quiz = $moodle->getQuiz($quizId);
        
        if ($quiz) {
            $this->enrollingQuiz = $quiz;
            $this->activeEnrollTab = 1;
            $this->allUsers = $moodle->getAllUsers();
            $this->cohorts = $moodle->getCohorts();
            $this->selectedUserIds = [];
            $this->unenrolUserIds = [];
            
            $courseId = $quiz->course_id ?? $quiz->course;
            $enrolledUsers = $moodle->getEnrolledUsers($courseId);
            $ids = array_column($enrolledUsers, 'id');
            $this->enrolledUserIds = array_map('intval', $ids);
            $this->enrolledUsersWithMethod = $enrolledUsers;
            
            $this->enrolledCohorts = $moodle->getEnrolledCohorts($courseId);
            $this->importFile = null;
            $this->importErrors = [];
            $this->importSuccess = '';
            
            $this->showEnrollModal = true;
        }
    }

    public function setActiveEnrollTab(int $tab)
    {
        $this->activeEnrollTab = $tab;
        
        if ($tab == 3 && $this->enrollingQuiz) {
            $moodle = app('moodle');
            $this->cohorts = $moodle->getCohorts();
            $courseId = $this->enrollingQuiz->course_id ?? $this->enrollingQuiz->course;
            $this->enrolledCohorts = $moodle->getEnrolledCohorts($courseId);
        }
        
        if ($tab == 4) {
            $this->importFile = null;
            $this->importErrors = [];
            $this->importSuccess = '';
        }
        
        $this->refreshEnrolledUsers();
    }

    public function refreshEnrolledUsers()
    {
        if (!$this->enrollingQuiz) {
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingQuiz->course_id ?? $this->enrollingQuiz->course;
        $enrolledUsers = $moodle->getEnrolledUsers($courseId);
        $ids = array_column($enrolledUsers, 'id');
        $this->enrolledUserIds = array_map('intval', $ids);
        $this->enrolledUsersWithMethod = $enrolledUsers;
    }

    public function openEnrollSelectModal($quizId)
    {
        $moodle = app('moodle');
        $quiz = $moodle->getQuiz($quizId);
        
        if ($quiz) {
            $this->enrollingQuiz = $quiz;
            $this->activeEnrollTab = 2;
            $this->allUsers = $moodle->getAllUsers();
            $this->selectedUserIds = [];
            
            $courseId = $quiz->course_id ?? $quiz->course;
            $enrolledUsers = $moodle->getEnrolledUsers($courseId);
            $this->enrolledUserIds = array_values(array_map('intval', array_column($enrolledUsers, 'id')));
            $this->enrolledUsersWithMethod = $enrolledUsers;
            
            $this->showEnrollModal = true;
        }
    }

    public function openEnrollCohortModal($quizId)
    {
        $moodle = app('moodle');
        $quiz = $moodle->getQuiz($quizId);
        
        if ($quiz) {
            $this->enrollingQuiz = $quiz;
            $this->cohorts = $moodle->getCohorts();
            $this->activeEnrollTab = 3;
            
            $courseId = $quiz->course_id ?? $quiz->course;
            $enrolledUsers = $moodle->getEnrolledUsers($courseId);
            $this->enrolledUserIds = array_values(array_map('intval', array_column($enrolledUsers, 'id')));
            $this->enrolledUsersWithMethod = $enrolledUsers;
            
            $this->showEnrollModal = true;
        }
    }

    public function enrolCohort()
    {
        if (!$this->enrollingQuiz || !$this->selectedCohortId) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Vui lòng chọn nhóm']]);
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingQuiz->course_id ?? $this->enrollingQuiz->course;
        $cohortId = (int)$this->selectedCohortId;
        
        $existingCohorts = $moodle->getEnrolledCohorts($courseId);
        $existingIds = array_column($existingCohorts, 'id');
        if (in_array($cohortId, $existingIds)) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Cohort này đã được ghi danh']]);
            return;
        }
        
        $result = $moodle->enrolUsersToCohorts([$cohortId], $courseId);
        
        if ($result) {
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Ghi danh nhóm thành công']]);
            $enrolledUsers = $moodle->getEnrolledUsers($courseId);
            $this->enrolledUserIds = array_column($enrolledUsers, 'id');
            $this->enrolledUsersWithMethod = $enrolledUsers;
            $this->enrolledCohorts = $moodle->getEnrolledCohorts($courseId);
            $this->selectedCohortId = null;
            
            $this->loadQuizzes();
        } else {
            $this->dispatch('browser', ['alert' => ['type' => 'error', 'message' => 'Ghi danh nhóm thất bại']]);
        }
    }

    public function unenrolCohort($cohortId)
    {
        if (!$this->enrollingQuiz) {
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingQuiz->course_id ?? $this->enrollingQuiz->course;
        
        $result = $moodle->unenrolCohort((int)$cohortId, $courseId);
        
        if ($result) {
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Đã bỏ ghi danh nhóm']]);
            $enrolledUsers = $moodle->getEnrolledUsers($courseId);
            $this->enrolledUserIds = array_column($enrolledUsers, 'id');
            $this->enrolledUsersWithMethod = $enrolledUsers;
            $this->enrolledCohorts = $moodle->getEnrolledCohorts($courseId);
            
            $this->loadQuizzes();
        } else {
            $this->dispatch('browser', ['alert' => ['type' => 'error', 'message' => 'Bỏ ghi danh thất bại']]);
        }
    }

    public function closeEnrollModal()
    {
        $this->showEnrollModal = false;
        $this->enrollingQuiz = null;
    }

    public function enrollUsers()
    {
        if (!$this->enrollingQuiz || empty($this->selectedUserIds)) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Vui lòng chọn người dùng']]);
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingQuiz->course_id ?? $this->enrollingQuiz->course;
        
        $existingEnrolled = $moodle->getEnrolledUsers($courseId);
        $existingIds = array_column($existingEnrolled, 'id');
        
        $successCount = 0;
        $alreadyEnrolledCount = 0;
        $selectedToEnroll = [];
        
        foreach ($this->selectedUserIds as $userId) {
            if (in_array((int)$userId, $existingIds)) {
                $alreadyEnrolledCount++;
            } else {
                $selectedToEnroll[] = $userId;
            }
        }
        
        foreach ($selectedToEnroll as $userId) {
            $result = $moodle->enrolUser((int)$userId, $courseId);
            if ($result) {
                $successCount++;
            }
        }
        
        if ($successCount > 0) {
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => "Đã ghi danh {$successCount} người dùng"]]);
        }
        if ($alreadyEnrolledCount > 0) {
            $this->dispatch('browser', ['alert' => ['type' => 'info', 'message' => "{$alreadyEnrolledCount} người dùng đã được ghi danh trước đó"]]);
        }
        
        $enrolledUsers = $moodle->getEnrolledUsers($courseId);
        $ids = array_column($enrolledUsers, 'id');
        $this->enrolledUserIds = array_map('intval', $ids);
        $this->enrolledUsersWithMethod = $enrolledUsers;
        $this->selectedUserIds = [];
        
        $this->loadQuizzes();
    }

    public function unenrolSelectedUsers()
    {
        if (!$this->enrollingQuiz || empty($this->unenrolUserIds)) {
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingQuiz->course_id ?? $this->enrollingQuiz->course;
        
        $removedCount = 0;
        foreach ($this->unenrolUserIds as $userId) {
            $moodle->unenrolUser((int)$userId, $courseId);
            $removedCount++;
        }
        
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => "Đã bỏ ghi danh {$removedCount} người dùng"]]);
        
        $enrolledUsers = $moodle->getEnrolledUsers($courseId);
        $ids = array_column($enrolledUsers, 'id');
        $this->enrolledUserIds = array_map('intval', $ids);
        $this->enrolledUsersWithMethod = $enrolledUsers;
        $this->unenrolUserIds = [];
        
        $this->loadQuizzes();
    }

    public function saveEnrolledUsers()
    {
        if (!$this->enrollingQuiz) {
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingQuiz->course_id ?? $this->enrollingQuiz->course;
        $enrolledUsers = $moodle->getEnrolledUsers($courseId);
        $currentEnrolledIds = array_map('intval', array_column($enrolledUsers, 'id'));
        
        $removedCount = 0;
        foreach ($currentEnrolledIds as $userId) {
            if (!in_array($userId, $this->enrolledUserIds)) {
                $moodle->unenrolUser($userId, $courseId);
                $removedCount++;
            }
        }
        
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => "Đã cập nhật. Đã gỡ {$removedCount} người dùng"]]);
        
        $this->loadQuizzes();
    }

    public function unenrolUser($userId)
    {
        if (!$this->enrollingQuiz) {
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingQuiz->course_id ?? $this->enrollingQuiz->course;
        
        $result = $moodle->unenrolUser((int)$userId, $courseId);
        
        if ($result) {
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Đã bỏ ghi danh người dùng']]);
            $enrolledUsers = $moodle->getEnrolledUsers($courseId);
            $ids = array_column($enrolledUsers, 'id');
            $this->enrolledUserIds = array_map('intval', $ids);
            $this->enrolledUsersWithMethod = $enrolledUsers;
            
            $this->loadQuizzes();
        } else {
            $this->dispatch('browser', ['alert' => ['type' => 'error', 'message' => 'Bỏ ghi danh thất bại']]);
        }
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingQuiz = null;
    }

    public function setConfigActive(int $tab)
    {
        $this->activeConfigTab = $tab;
    }

    public function setViewTab(int $tab)
    {
        $this->activeViewTab = $tab;
    }

    public function saveQuestionSet()
    {
        if (!$this->configuringQuiz || !$this->selectedSetId) {
            return;
        }

        $moodle = app('moodle');
        $setQuestions = $moodle->getQuestionsInSet($this->selectedSetId);
        
        $addedCount = 0;
        foreach ($setQuestions as $sq) {
            $moodle->addQuestionToQuiz($this->configuringQuiz->id, $sq->question_id);
            $addedCount++;
        }
        
        $this->selectedSetId = null;
        
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Added ' . $addedCount . ' questions from set']]);
    }

    public function refreshQuestions()
    {
        if (!$this->configuringQuiz) {
            return;
        }
        
        $moodle = app('moodle');
        $this->selectedQuestions = $moodle->getQuestionsInQuiz($this->configuringQuiz->id);
    }

    public function updatedSelectedSetId($value)
    {
        if (!$value) {
            return;
        }
        
        $moodle = app('moodle');
        $setQuestions = $moodle->getQuestionsInSet($value);
        
        $addedCount = 0;
        foreach ($setQuestions as $sq) {
            $moodle->addQuestionToQuiz($this->configuringQuiz->id, $sq->question_id);
            $addedCount++;
        }
        
        $this->selectedQuestions = $moodle->getQuestionsInQuiz($this->configuringQuiz->id);
        $this->selectedSetId = null;
        
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Added ' . $addedCount . ' questions from set']]);
    }

    public function saveQuiz()
    {
        $moodle = app('moodle');
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
        
        $nums = ['timelimit', 'attempts'];
        foreach ($nums as $k) {
            $val = $tf[$k] ?? 0;
            $tf[$k] = is_numeric($val) ? (int)$val : 0;
        }
        
        $this->formData = $tf;
        
        $this->validate([
            'formData.course' => 'required',
            'formData.name' => 'required',
            'formData.timeopen' => 'nullable|date',
            'formData.timeclose' => 'nullable|date|after_or_equal:formData.timeopen',
            'formData.timelimit' => 'nullable|numeric|min:0',
            'formData.attempts' => 'nullable|numeric|min:1',
        ]);
        
        if ($this->editingQuiz) {
            $moodle->updateQuiz($this->editingQuiz->id, $this->formData);
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Cập nhật kỳ thi thành công']]);
        } else {
            $moodle->createQuiz($this->formData);
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Tạo kỳ thi thành công']]);
        }
        
        $this->closeModal();
        $this->loadQuizzes();
    }
    
    public function deleteQuiz($quizId)
    {
        $moodle = app('moodle');
        $result = $moodle->deleteQuiz($quizId);
        
        if ($result) {
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Đã xóa kỳ thi']]);
        } else {
            $this->dispatch('browser', ['alert' => ['type' => 'error', 'message' => 'Xóa kỳ thi thất bại']]);
        }
        
        $this->loadQuizzes();
    }

    public function getFilteredQuizzesProperty()
    {
        if (empty($this->search)) {
            return $this->quizzes;
        }

        $search = strtolower($this->search);
        return array_filter($this->quizzes, function($quiz) use ($search) {
            return strpos(strtolower($quiz->name ?? ''), $search) !== false
                || strpos(strtolower($quiz->course_name ?? ''), $search) !== false;
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

        return view('livewire.exam', [
            'paginatedQuizzes' => $paginatedQuizzes,
            'courses' => $this->courses,
            'availableQuestions' => $this->availableQuestions,
            'selectedQuestions' => $this->selectedQuestions,
            'viewingQuiz' => $this->viewingQuiz,
            'showViewModal' => $this->showViewModal,
            'showConfigModal' => $this->showConfigModal,
            'configuringQuiz' => $this->configuringQuiz,
            'activeConfigTab' => $this->activeConfigTab,
            'activeViewTab' => $this->activeViewTab,
            'questionSets' => $this->questionSets,
            'selectedSetId' => $this->selectedSetId,
            'allUsers' => $this->allUsers,
            'enrolledUserIds' => $this->enrolledUserIds,
            'enrolledUsersWithMethod' => $this->enrolledUsersWithMethod,
            'enrolledCohorts' => $this->enrolledCohorts,
            'cohorts' => $this->cohorts,
            'showEnrollModal' => $this->showEnrollModal,
            'enrollingQuiz' => $this->enrollingQuiz,
            'activeEnrollTab' => $this->activeEnrollTab,
            'selectedCohortId' => $this->selectedCohortId,
            'selectedUserIds' => $this->selectedUserIds,
            'unenrolUserIds' => $this->unenrolUserIds,
            'enrollSearch' => $this->enrollSearch,
            'enrollMethodFilter' => $this->enrollMethodFilter,
            'importErrors' => $this->importErrors,
            'importSuccess' => $this->importSuccess,
        ]);
    }

    public function importFromExcel()
    {
        if (!$this->enrollingQuiz || !$this->importFile) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Vui lòng chọn file']]);
            return;
        }

        $this->importErrors = [];
        $this->importSuccess = '';

        try {
            $moodle = app('moodle');
            $courseId = $this->enrollingQuiz->course_id ?? $this->enrollingQuiz->course;
            
            $existingEnrolled = $moodle->getEnrolledUsers($courseId);
            $existingIds = array_column($existingEnrolled, 'id');
            
            $path = $this->importFile->getRealPath();
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            array_shift($rows);
            
            $successCount = 0;
            $skipCount = 0;
            
            foreach ($rows as $index => $row) {
                $email = isset($row[0]) ? trim($row[0]) : '';
                $username = isset($row[1]) ? trim($row[1]) : '';
                $timeStart = isset($row[2]) ? trim($row[2]) : '';
                $timeEnd = isset($row[3]) ? trim($row[3]) : '';
                
                if (empty($email) && empty($username)) {
                    $this->importErrors[] = "Dòng " . ($index + 2) . ": Email hoặc Username là bắt buộc";
                    continue;
                }
                
                if (!empty($timeStart) && !empty($timeEnd)) {
                    $startTs = strtotime($timeStart);
                    $endTs = strtotime($timeEnd);
                    
                    if ($startTs !== false && $endTs !== false && $endTs < $startTs) {
                        $this->importErrors[] = "Dòng " . ($index + 2) . ": Thời gian kết thúc phải lớn hơn hoặc bằng thời gian bắt đầu";
                        continue;
                    }
                }
                
                $user = null;
                if (!empty($email)) {
                    $user = $moodle->getUserByEmail($email);
                }
                
                if (!$user && !empty($username)) {
                    $user = $moodle->getUserByUsername($username);
                }
                
                if (!$user) {
                    $identifier = !empty($email) ? $email : $username;
                    $this->importErrors[] = "Dòng " . ($index + 2) . ": Không tìm thấy user ({$identifier})";
                    continue;
                }
                
                if (in_array($user->id, $existingIds)) {
                    $skipCount++;
                    continue;
                }
                
                $timestart = 0;
                $timeend = 0;
                
                if (!empty($timeStart)) {
                    $timestart = strtotime($timeStart);
                    if ($timestart === false) {
                        $timestart = 0;
                    }
                }
                
                if (!empty($timeEnd)) {
                    $timeend = strtotime($timeEnd);
                    if ($timeend === false) {
                        $timeend = 0;
                    }
                }
                
                $result = $moodle->enrolUserWithTime($user->id, $courseId, $timestart, $timeend);
                if ($result) {
                    $successCount++;
                    $existingIds[] = $user->id;
                }
            }
            
            $enrolledUsers = $moodle->getEnrolledUsers($courseId);
            $ids = array_column($enrolledUsers, 'id');
            $this->enrolledUserIds = array_map('intval', $ids);
            $this->enrolledUsersWithMethod = $enrolledUsers;
            
            $this->loadQuizzes();
            
            if ($successCount > 0) {
                $this->importSuccess = "Đã import {$successCount} người dùng";
            }
            if ($skipCount > 0) {
                $this->importSuccess .= ($this->importSuccess ? ', ' : '') . "Bỏ qua {$skipCount} người đã ghi danh";
            }
            if (empty($this->importErrors) && $successCount == 0 && $skipCount == 0) {
                $this->importErrors[] = 'Không có dữ liệu hợp lệ';
            }
            
            $this->importFile = null;
            
        } catch (\Exception $e) {
            $this->importErrors[] = "Lỗi đọc file: " . $e->getMessage();
        }
    }

    public function getFilteredEnrollUsersProperty()
    {
        $users = $this->allUsers;
        
        if (!empty($this->enrollSearch)) {
            $search = strtolower($this->enrollSearch);
            $users = array_filter($users, function($user) use ($search) {
                $fullname = strtolower(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
                $email = strtolower($user->email ?? '');
                $username = strtolower($user->username ?? '');
                return strpos($fullname, $search) !== false 
                    || strpos($email, $search) !== false
                    || strpos($username, $search) !== false;
            });
        }
        
        return $users;
    }

    public function getFilteredEnrolledUsersProperty()
    {
        $users = $this->enrolledUsersWithMethod;
        
        if (!empty($this->enrollSearch)) {
            $search = strtolower($this->enrollSearch);
            $users = array_filter($users, function($user) use ($search) {
                $fullname = strtolower(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
                $email = strtolower($user->email ?? '');
                $username = strtolower($user->username ?? '');
                return strpos($fullname, $search) !== false 
                    || strpos($email, $search) !== false
                    || strpos($username, $search) !== false;
            });
        }
        
        if (!empty($this->enrollMethodFilter)) {
            $users = array_filter($users, function($user) {
                return ($user->enrol_method ?? 'manual') === $this->enrollMethodFilter;
            });
        }
        
        return $users;
    }

    private function getEnrolMethod($userId)
    {
        if (!$this->enrollingQuiz) {
            return 'manual';
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingQuiz->course_id ?? $this->enrollingQuiz->course;
        
        $result = DB::selectOne("
            SELECT e.enrol as enrol_method
            FROM mdl_user_enrolments ue
            JOIN mdl_enrol e ON ue.enrolid = e.id
            WHERE ue.userid = ? AND e.courseid = ?
        ", [$userId, $courseId]);
        
        return $result ? $result->enrol_method : 'manual';
    }
}
