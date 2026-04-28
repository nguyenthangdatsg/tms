<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Enrolment extends Component
{
    use WithPagination;
    
    protected $listeners = ['openEnrolmentModal' => 'open'];
    
    public $showModal = false;
    public $targetType = 'quiz';
    public $targetId = null;
    public $targetName = '';
    public $courseId = null;

    public $activeTab = 1;
    public $allUsers = [];
    public $cohorts = [];
    public $enrolledUserIds = [];
    public $enrolledUsersWithMethod = [];
    public $enrolledCohorts = [];
    
    public $selectedUserIds = [];
    public $unenrolUserIds = [];
    public $selectedCohortId = null;
    
    public $enrollSearch = '';
    public $enrollMethodFilter = '';
    
    public $importFile = null;
    public $importErrors = [];
    public $importSuccess = '';

    public function open($data = null)
    {
        if (is_array($data)) {
            $this->targetType = $data['type'] ?? 'quiz';
            $this->targetId = $data['id'] ?? null;
            $this->targetName = $data['name'] ?? '';
            $this->courseId = $data['courseId'] ?? null;
        }
        
        $this->loadData();
        $this->showModal = true;
    }

    public function loadData()
    {
        $moodle = app('moodle');
        
        $this->allUsers = $moodle->getAllUsers();
        $this->cohorts = $moodle->getCohorts();
        
        if (!$this->courseId) {
            $this->courseId = $this->getCourseId();
        }
        
        if ($this->courseId) {
            $enrolledUsers = $moodle->getEnrolledUsers($this->courseId);
            $this->enrolledUserIds = array_map('intval', array_column($enrolledUsers, 'id'));
            $this->enrolledUsersWithMethod = $enrolledUsers;
            $this->enrolledCohorts = $moodle->getEnrolledCohorts($this->courseId);
        }
        
        $this->selectedUserIds = [];
        $this->unenrolUserIds = [];
        $this->selectedCohortId = null;
        $this->importFile = null;
        $this->importErrors = [];
        $this->importSuccess = '';
    }

    private function getCourseId()
    {
        if ($this->targetType === 'quiz') {
            $moodle = app('moodle');
            $quiz = $moodle->getQuiz($this->targetId);
            return $quiz ? ($quiz->course_id ?? $quiz->course) : null;
        }
        return $this->targetId;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        
        if ($tab == 3) {
            $moodle = app('moodle');
            $this->cohorts = $moodle->getCohorts();
            if ($this->courseId) {
                $this->enrolledCohorts = $moodle->getEnrolledCohorts($this->courseId);
            }
        }
        
        if ($tab == 4) {
            $this->importFile = null;
            $this->importErrors = [];
            $this->importSuccess = '';
        }
    }

    public function enrolUser()
    {
        if (!$this->courseId || empty($this->selectedUserIds)) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Vui lòng chọn người dùng']]);
            return;
        }
        
        $moodle = app('moodle');
        
        $existingEnrolled = $moodle->getEnrolledUsers($this->courseId);
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
            $result = $moodle->enrolUser((int)$userId, $this->courseId);
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
        
        $this->refreshEnrolled();
        $this->selectedUserIds = [];
    }

    public function enrolCohort()
    {
        if (!$this->courseId || !$this->selectedCohortId) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Vui lòng chọn nhóm']]);
            return;
        }
        
        $moodle = app('moodle');
        
        $existingCohorts = $moodle->getEnrolledCohorts($this->courseId);
        $existingIds = array_column($existingCohorts, 'id');
        
        if (in_array((int)$this->selectedCohortId, $existingIds)) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Cohort này đã được ghi danh']]);
            return;
        }
        
        $result = $moodle->enrolUsersToCohorts([(int)$this->selectedCohortId], $this->courseId);
        
        if ($result) {
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Ghi danh nhóm thành công']]);
            $this->refreshEnrolled();
            $this->selectedCohortId = null;
        } else {
            $this->dispatch('browser', ['alert' => ['type' => 'error', 'message' => 'Ghi danh nhóm thất bại']]);
        }
    }

    public function unenrolUser($userId)
    {
        if (!$this->courseId) {
            return;
        }
        
        $moodle = app('moodle');
        $result = $moodle->unenrolUser((int)$userId, $this->courseId);
        
        if ($result) {
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Đã bỏ ghi danh người dùng']]);
            $this->refreshEnrolled();
        } else {
            $this->dispatch('browser', ['alert' => ['type' => 'error', 'message' => 'Bỏ ghi danh thất bại']]);
        }
    }

    public function unenrolSelectedUsers()
    {
        if (!$this->courseId || empty($this->unenrolUserIds)) {
            return;
        }
        
        $moodle = app('moodle');
        
        $removedCount = 0;
        foreach ($this->unenrolUserIds as $userId) {
            $moodle->unenrolUser((int)$userId, $this->courseId);
            $removedCount++;
        }
        
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => "Đã bỏ ghi danh {$removedCount} người dùng"]]);
        $this->refreshEnrolled();
        $this->unenrolUserIds = [];
    }

    public function unenrolCohort($cohortId)
    {
        if (!$this->courseId) {
            return;
        }
        
        $moodle = app('moodle');
        $result = $moodle->unenrolCohort((int)$cohortId, $this->courseId);
        
        if ($result) {
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Đã bỏ ghi danh nhóm']]);
            $this->refreshEnrolled();
        } else {
            $this->dispatch('browser', ['alert' => ['type' => 'error', 'message' => 'Bỏ ghi danh thất bại']]);
        }
    }

    public function importFromExcel()
    {
        if (!$this->courseId || !$this->importFile) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Vui lòng chọn file']]);
            return;
        }

        $this->importErrors = [];
        $this->importSuccess = '';

        try {
            $moodle = app('moodle');
            
            $existingEnrolled = $moodle->getEnrolledUsers($this->courseId);
            $existingIds = array_column($existingEnrolled, 'id');
            
            $path = $this->importFile->getRealPath();
            $spreadsheet = IOFactory::load($path);
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
                
                $result = $moodle->enrolUserWithTime($user->id, $this->courseId, $timestart, $timeend);
                if ($result) {
                    $successCount++;
                    $existingIds[] = $user->id;
                }
            }
            
            $this->refreshEnrolled();
            
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

    private function refreshEnrolled()
    {
        if (!$this->courseId) {
            return;
        }
        
        $moodle = app('moodle');
        $enrolledUsers = $moodle->getEnrolledUsers($this->courseId);
        $this->enrolledUserIds = array_map('intval', array_column($enrolledUsers, 'id'));
        $this->enrolledUsersWithMethod = $enrolledUsers;
        $this->enrolledCohorts = $moodle->getEnrolledCohorts($this->courseId);
    }

    public function closeModal()
    {
        $this->showModal = false;
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

    public function render()
    {
        return view('livewire.enrolment', [
            'filteredEnrolledUsers' => $this->filteredEnrolledUsers,
            'filteredEnrollUsers' => $this->filteredEnrollUsers,
        ]);
    }
}