<?php

namespace App\Livewire;

use Livewire\Component;

class LearningPath extends Component
{
    protected function rules()
    {
        $baseRules = [
            'formData.name' => 'required|string|max:255',
            'formData.startdate' => 'nullable|date',
            'formData.enddate' => 'nullable|date|after_or_equal:formData.startdate',
        ];
        
        if (!empty($this->notificationData['day_frequency_enable'])) {
            $baseRules['notificationData.day_frequency'] = 'nullable|numeric|min:1';
        }
        
        return $baseRules;
    }
    
    public $learningPaths = [];
    public $search = '';
    public $showModal = false;
    public $editingPath = null;
    public $formData = [];
    public $selectedPathId = null;
    public $deletingPathId = null;
    public $pathCourses = [];
    public $availableMoodleCourses = [];
    public $availableCatalogueCourses = [];
    public $showCoursesModal = false;
    public $activeCourseTab = 'special';
    public $addingCourseData = [];
    
    public $specialSearch = '';
    public $catalogueSearch = '';
    
    public $showEnrollModal = false;
    public $activeEnrollTab = 1;
    public $allUsers = [];
    public $cohorts = [];
    public $enrolledUsers = [];
    public $enrolledCohorts = [];
    public $selectedUserIds = [];
    public $unenrolUserIds = [];
    public $selectedCohortId = null;
    public $enrollSearch = '';
    
    public $showReportModal = false;
    public $learningPathProgress = [];
    
    public $showNotificationModal = false;
    public $notificationData = [];
    
    public function mount()
    {
        $this->loadLearningPaths();
    }

    public function loadLearningPaths()
    {
        $moodle = app('moodle');
        $this->learningPaths = $moodle->getLearningPaths();
    }

    public function openAddModal()
    {
        $this->editingPath = null;
        $this->formData = [
            'name' => '',
            'description' => '',
            'startdate' => '',
            'enddate' => '',
            'credit' => 0,
        ];
        $this->showModal = true;
    }

    public function openEditModal($pathId)
    {
        $moodle = app('moodle');
        $path = $moodle->getLearningPath($pathId);
        
        if ($path) {
            $this->editingPath = $path;
            $this->formData = [
                'name' => $path->name ?? '',
                'description' => $path->description ?? '',
                'startdate' => $path->startdate ? date('Y-m-d', $path->startdate) : '',
                'enddate' => $path->enddate ? date('Y-m-d', $path->enddate) : '',
                'credit' => $path->credit ?? 0,
                'published' => $path->published ?? 0,
            ];
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingPath = null;
        $this->formData = [];
    }

    public function saveLearningPath()
    {
        $this->validate();
        
        $moodle = app('moodle');
        
        if ($this->editingPath) {
            $moodle->updateLearningPath($this->editingPath->id, $this->formData);
        } else {
            $moodle->createLearningPath($this->formData);
        }
        
        $this->closeModal();
        $this->loadLearningPaths();
    }

    public function confirmDelete($pathId)
    {
        $this->deletingPathId = $pathId;
    }

    public function closeDeleteModal()
    {
        $this->deletingPathId = null;
    }

    public function deleteLearningPath()
    {
        if ($this->deletingPathId) {
            $moodle = app('moodle');
            $moodle->deleteLearningPath($this->deletingPathId);
            $this->deletingPathId = null;
            $this->loadLearningPaths();
        }
    }

    public function openCoursesModal($pathId)
    {
        $this->selectedPathId = $pathId;
        $this->specialSearch = '';
        $this->catalogueSearch = '';
        
        $moodle = app('moodle');
        $this->pathCourses = $moodle->getLearningPathCourses($pathId);
        
        $this->availableMoodleCourses = $moodle->getLearningPathAvailableCourses();
        $this->availableCatalogueCourses = $moodle->getLearningPathAvailableCatalogueCourses();
        
        $assignedCourseIds = array_filter(array_column($this->pathCourses, 'course_id'));
        $assignedCatalogueCodes = array_filter(array_column($this->pathCourses, 'catalogue_code'));
        
        $this->availableMoodleCourses = array_filter($this->availableMoodleCourses, function($c) use ($assignedCourseIds) {
            return !in_array($c->id, $assignedCourseIds);
        });
        
        $this->availableCatalogueCourses = array_filter($this->availableCatalogueCourses, function($c) use ($assignedCatalogueCodes) {
            return !in_array($c->code, $assignedCatalogueCodes);
        });
        
        $this->showCoursesModal = true;
    }

    public function closeCoursesModal()
    {
        $this->showCoursesModal = false;
        $this->selectedPathId = null;
        $this->pathCourses = [];
        $this->activeCourseTab = 'special';
        $this->specialSearch = '';
        $this->catalogueSearch = '';
        $this->js('window.location.reload()');
    }
    
    public function setActiveCourseTab($tab)
    {
        $this->activeCourseTab = $tab;
    }

    public function getFilteredSpecialCoursesProperty()
    {
        if (empty($this->specialSearch)) {
            return $this->availableMoodleCourses;
        }
        
        $search = strtolower($this->specialSearch);
        return array_filter($this->availableMoodleCourses, function($c) use ($search) {
            return strpos(strtolower($c->fullname ?? ''), $search) !== false;
        });
    }

    public function getFilteredCatalogueCoursesProperty()
    {
        if (empty($this->catalogueSearch)) {
            return $this->availableCatalogueCourses;
        }
        
        $search = strtolower($this->catalogueSearch);
        return array_filter($this->availableCatalogueCourses, function($c) use ($search) {
            $nameMatch = strpos(strtolower($c->fullname ?? ''), $search) !== false;
            $codeMatch = strpos(strtolower($c->code ?? ''), $search) !== false;
            return $nameMatch || $codeMatch;
        });
    }

    public function addMoodleCourseToPath($courseId)
    {
        $moodle = app('moodle');
        $sortorder = count($this->pathCourses);
        $moodle->addMoodleCourseToLearningPath($this->selectedPathId, $courseId, $sortorder);
        
        $this->pathCourses = $moodle->getLearningPathCourses($this->selectedPathId);
        $this->availableMoodleCourses = array_filter($this->availableMoodleCourses, function($c) use ($courseId) {
            return $c->id != $courseId;
        });
    }

    public function addCatalogueCourseToPath($catalogueCode)
    {
        $moodle = app('moodle');
        $sortorder = count($this->pathCourses);
        $moodle->addCatalogueCourseToLearningPath($this->selectedPathId, $catalogueCode, $sortorder);
        
        $this->pathCourses = $moodle->getLearningPathCourses($this->selectedPathId);
        $this->availableCatalogueCourses = array_filter($this->availableCatalogueCourses, function($c) use ($catalogueCode) {
            return $c->code != $catalogueCode;
        });
    }

    public function removeCourseFromPath($lineId)
    {
        $moodle = app('moodle');
        $moodle->removeCourseFromLearningPath($lineId);
        
        $this->pathCourses = $moodle->getLearningPathCourses($this->selectedPathId);
        
        $this->availableMoodleCourses = $moodle->getLearningPathAvailableCourses();
        $this->availableCatalogueCourses = $moodle->getLearningPathAvailableCatalogueCourses();
        
        $assignedCourseIds = array_filter(array_column($this->pathCourses, 'course_id'));
        $assignedCatalogueCodes = array_filter(array_column($this->pathCourses, 'catalogue_code'));
        
        $this->availableMoodleCourses = array_filter($this->availableMoodleCourses, function($c) use ($assignedCourseIds) {
            return !in_array($c->id, $assignedCourseIds);
        });
        
        $this->availableCatalogueCourses = array_filter($this->availableCatalogueCourses, function($c) use ($assignedCatalogueCodes) {
            return !in_array($c->code, $assignedCatalogueCodes);
        });
    }

    public function updateCourseRequired($lineId, $required)
    {
        $moodle = app('moodle');
        $moodle->updateLearningPathLineRequired($lineId, $required);
        
        $this->pathCourses = $moodle->getLearningPathCourses($this->selectedPathId);
    }
    
    public function updateCourseCredit($lineId, $credit)
    {
        $moodle = app('moodle');
        $moodle->updateLearningPathLineCredit($lineId, $credit);
        
        $this->pathCourses = $moodle->getLearningPathCourses($this->selectedPathId);
    }
    
    public function saveCourses()
    {
        $this->showCoursesModal = false;
        $this->selectedPathId = null;
        $this->pathCourses = [];
        $this->activeCourseTab = 'special';
        $this->specialSearch = '';
        $this->catalogueSearch = '';
        $this->loadLearningPaths();
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Đã cập nhật khóa học']]);
    }
    
    public function openEnrollModal($pathId)
    {
        $this->selectedPathId = $pathId;
        $this->activeEnrollTab = 1;
        $this->enrollSearch = '';
        
        $moodle = app('moodle');
        if (method_exists($moodle, 'getAllUsersForLearningPath')) {
            $this->allUsers = $moodle->getAllUsersForLearningPath();
        } else {
            $this->allUsers = [];
        }
        if (method_exists($moodle, 'getCohortsForLearningPath')) {
            $this->cohorts = $moodle->getCohortsForLearningPath();
        } else {
            $this->cohorts = [];
        }
        
        $this->enrolledUsers = $moodle->getLearningPathUsers($pathId);
        $this->enrolledCohorts = $moodle->getLearningPathEnrolledCohorts($pathId);
        
        $this->selectedUserIds = [];
        $this->unenrolUserIds = [];
        
        $this->showEnrollModal = true;
    }
    
    public function closeEnrollModal()
    {
        $this->showEnrollModal = false;
        $this->selectedPathId = null;
    }

public function openNotificationModal($pathId = null)
    {
        if (!$pathId || !is_numeric($pathId)) {
            $pathId = 1;
        }
        
        $this->selectedPathId = (int)$pathId;
        $this->showNotificationModal = true;
        
        $this->dispatch('init-tinymce');
        
        $moodle = app('moodle');
        $existing = $moodle->getLearningPathNotifications($pathId);
        
        $defaultTags = '{user_fullname}, {learning_path_name}, {learning_path_startdate}, {learning_path_enddate}, {learning_path_coursesrequired}';
        
        $this->notificationData = [
            'enrollment_enable' => $existing->enrollment_enable ?? 0,
            'enrollment_mail_templates' => $existing->enrollment_mail_templates ?? '',
            'expiration_enable' => $existing->expiration_enable ?? 0,
            'expiration_mail_templates' => $existing->expiration_mail_templates ?? '',
            'enrollment_reminder_enable' => $existing->enrollment_reminder_enable ?? 0,
            'day_after_enrollment' => $existing->day_after_enrollment ?? 7,
            'enrollment_reminder_mail_templates' => $existing->enrollment_reminder_mail_templates ?? '',
            'expiration_reminder_enable' => $existing->expiration_reminder_enable ?? 0,
            'day_before_expiration' => $existing->day_before_expiration ?? 7,
            'expiration_reminder_mail_templates' => $existing->expiration_reminder_mail_templates ?? '',
            'day_frequency_enable' => $existing->day_frequency_enable ?? 0,
            'day_frequency' => $existing->day_frequency ?? 7,
            'day_frequency_mail_templates' => $existing->day_frequency_mail_templates ?? $this->getDefaultReminderTemplate(),
            'completion_path_enable' => $existing->completion_path_enable ?? 0,
            'completion_path_mail_templates' => $existing->completion_path_mail_templates ?? $this->getDefaultCompletionTemplate(),
        ];
    }
    
    public function closeNotificationModal()
    {
        $this->showNotificationModal = false;
        $this->selectedPathId = null;
        $this->notificationData = [];
    }
    
    public function getDefaultReminderTemplate(): string
    {
        return "Xin chào {{firstname}} {{lastname}},

Đây là email nhắc nhở về Lộ trình học: {{learning_path_name}}

Tình trạng hiện tại: {{completion_percentage}}% hoàn thành
Số khóa học đã hoàn thành: {{completed_courses}}/{{total_courses}}

Vui lòng tiếp tục hoàn thành các khóa học còn lại để đạt được chứng chỉ.

Trân trọng,
Phòng đào tạo";
    }
    
    public function getDefaultCompletionTemplate(): string
    {
        return "Xin chào {{firstname}} {{lastname}},

Chúc mừng bạn đã hoàn thành Lộ trình học: {{learning_path_name}}!

Tổng số khóa học: {{total_courses}}
Ngày hoàn thành: {{completion_date}}

Cảm ơn bạn đã tham gia chương trình đào tạo.

Trân trọng,
Phòng đào tạo";
    }
    
    public function saveNotificationSettings()
    {
        $moodle = app('moodle');
        $moodle->saveLearningPathNotifications($this->selectedPathId, $this->notificationData);
        
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Đã lưu cài đặt thông báo']]);
        $this->closeNotificationModal();
    }
    
    public function refreshForm()
    {
        // Trigger re-render to update conditional display
    }

    // Open progress report modal for a given learning path
    public function openProgressModal($pathId)
    {
        $this->selectedPathId = $pathId;
        $moodle = app('moodle');
        // Ensure progress data is loaded (Phase 2 integration planned)
        if (method_exists($moodle, 'getLearningPathProgress')) {
            $this->learningPathProgress = $moodle->getLearningPathProgress($pathId);
        } else {
            $this->learningPathProgress = [];
        }
        $this->showReportModal = true;
    }

    
    
    public function setActiveEnrollTab($tab)
    {
        $this->activeEnrollTab = $tab;
        
        if ($tab == 3) {
            $moodle = app('moodle');
            $this->cohorts = $moodle->getCohortsForLearningPath();
            if ($this->selectedPathId) {
                $this->enrolledCohorts = $moodle->getLearningPathEnrolledCohorts($this->selectedPathId);
            }
        }
    }
    
    public function enrollUsers()
    {
        if (!$this->selectedPathId || empty($this->selectedUserIds)) {
            return;
        }
        
        $moodle = app('moodle');
        $existingUserIds = array_column($this->enrolledUsers, 'u_id');
        
        $successCount = 0;
        $assigneeId = $_SESSION['USER']->id ?? 2;
        foreach ($this->selectedUserIds as $userId) {
            if (!in_array((int)$userId, $existingUserIds)) {
                $moodle->assignUserToLearningPath($this->selectedPathId, (int)$userId, $assigneeId);
                $successCount++;
            }
        }
        
        $this->enrolledUsers = $moodle->getLearningPathUsers($this->selectedPathId);
        $this->selectedUserIds = [];
        $this->loadLearningPaths();
        
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => "Đã ghi danh {$successCount} người dùng"]]);
    }
    
    public function enrolCohort()
    {
        if (!$this->selectedPathId || !$this->selectedCohortId) {
            return;
        }
        
        $moodle = app('moodle');
        
        $existingCohortIds = array_column($this->enrolledCohorts, 'cohort_id');
        if (in_array((int)$this->selectedCohortId, $existingCohortIds)) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Cohort này đã được ghi danh']]);
            return;
        }
        
        $result = $moodle->assignCohortToLearningPath($this->selectedPathId, (int)$this->selectedCohortId);
        
        if ($result) {
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Ghi danh nhóm thành công']]);
            $this->enrolledUsers = $moodle->getLearningPathUsers($this->selectedPathId);
            $this->enrolledCohorts = $moodle->getLearningPathEnrolledCohorts($this->selectedPathId);
            $this->selectedCohortId = null;
            $this->loadLearningPaths();
        }
    }
    
    public function unenrolCohort($cohortId)
    {
        if (!$this->selectedPathId) {
            return;
        }
        
        $moodle = app('moodle');
        $moodle->unassignCohortFromLearningPath($this->selectedPathId, $cohortId);
        
        $this->enrolledUsers = $moodle->getLearningPathUsers($this->selectedPathId);
        $this->enrolledCohorts = $moodle->getLearningPathEnrolledCohorts($this->selectedPathId);
        $this->loadLearningPaths();
    }
    
    public function unenrolSelectedUsers()
    {
        if (!$this->selectedPathId || empty($this->unenrolUserIds)) {
            return;
        }
        
        $moodle = app('moodle');
        
        foreach ($this->unenrolUserIds as $userId) {
            $moodle->unassignUserFromLearningPath($this->selectedPathId, (int)$userId);
        }
        
        $this->enrolledUsers = $moodle->getLearningPathUsers($this->selectedPathId);
        $this->unenrolUserIds = [];
        $this->loadLearningPaths();
        
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Đã bỏ ghi danh người dùng']]);
    }
    
    public function getFilteredEnrolledUsersProperty()
    {
        $users = $this->enrolledUsers;
        
        if (!empty($this->enrollSearch)) {
            $search = strtolower($this->enrollSearch);
            $users = array_filter($users, function($user) use ($search) {
                $fullname = strtolower(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
                $email = strtolower($user->email ?? '');
                return strpos($fullname, $search) !== false || strpos($email, $search) !== false;
            });
        }
        
        return $users;
    }
    
    public function getFilteredEnrollUsersProperty()
    {
        $users = $this->allUsers;
        $enrolledUserIds = array_column($this->enrolledUsers, 'u_id');
        
        $users = array_filter($users, function($user) use ($enrolledUserIds) {
            return !in_array($user->id, $enrolledUserIds);
        });
        
        if (!empty($this->enrollSearch)) {
            $search = strtolower($this->enrollSearch);
            $users = array_filter($users, function($user) use ($search) {
                $fullname = strtolower(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
                $email = strtolower($user->email ?? '');
                return strpos($fullname, $search) !== false || strpos($email, $search) !== false;
            });
        }
        
        return $users;
    }
    
    public function openReportModal($pathId)
    {
        $this->selectedPathId = $pathId;
        $moodle = app('moodle');
        $this->learningPathProgress = $moodle->getLearningPathProgress($pathId);
        $this->showReportModal = true;
    }
    
    public function closeReportModal()
    {
        $this->showReportModal = false;
        $this->selectedPathId = null;
        $this->learningPathProgress = [];
    }

    public function getFilteredPathsProperty()
    {
        if (empty($this->search)) {
            return $this->learningPaths;
        }
        
        $search = strtolower($this->search);
        return array_filter($this->learningPaths, function($path) use ($search) {
            return strpos(strtolower($path->name), $search) !== false;
        });
    }

    public function render()
    {
        return view('livewire.learning-path')
            ->layout('layouts.app');
    }
}
