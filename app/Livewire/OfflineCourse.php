<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

class OfflineCourse extends Component
{
    use WithFileUploads;
    
    protected $rules = [
        'formData.fullname' => 'required|string|max:255',
        'formData.shortname' => 'nullable|string|max:100',
        'formData.startdate' => 'nullable|date',
        'formData.enddate' => 'nullable|date|after_or_equal:formData.startdate',
        'formData.summary' => 'nullable|string',
    ];
    
    public $courses = [];
    public $search = '';
    public $availableCodes = [];
    public $perPage = 20;
    public $currentPage = 1;
    public $showModal = false;
    public $editingCourse = null;
    public $formData = [];
    public $courseType = 'Offline';
    public $newImage = null;
    public $deleteId = null;
    public $showEnrollModal = false;
    public $enrollingCourse = null;
    public $activeEnrollTab = 1;
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

    public function mount()
    {
        $this->loadCourses();
        $this->loadAvailableCodes();
    }

    public function loadCourses()
    {
        $moodle = app('moodle');
        $this->courses = $moodle->getTmsCourses('offline');
    }
    
    protected function loadAvailableCodes()
    {
        $rows = DB::select("SELECT code, name FROM mdl_local_catalogue_courses WHERE code != '' GROUP BY code, name ORDER BY code");
        $this->availableCodes = array_map(function($r) {
            return ['code' => $r->code, 'name' => $r->name];
        }, $rows);
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
        $this->editingCourse = null;
        $this->formData = [
            'fullname' => '',
            'shortname' => '',
            'summary' => '',
            'startdate' => '',
            'enddate' => '',
            'visible' => 1,
        ];
        $this->courseType = 'Offline';
        $this->newImage = null;
        $this->showModal = true;
    }

    public function openEditModal($courseId)
    {
        $moodle = app('moodle');
        $course = $moodle->getCourse($courseId);
        
        if ($course) {
            $this->editingCourse = $course;
            $this->formData = [
                'fullname' => $course->fullname ?? '',
                'shortname' => $course->shortname ?? '',
                'summary' => strip_tags($course->summary ?? ''),
                'startdate' => $course->startdate ? date('Y-m-d', $course->startdate) : '',
                'enddate' => $course->enddate ? date('Y-m-d', $course->enddate) : '',
                'visible' => $course->visible ?? 1,
            ];
            $this->courseType = $course->course_type ?? 'Offline';
            $this->newImage = null;
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingCourse = null;
        $this->formData = [];
        $this->newImage = null;
    }

    public function saveCourse()
    {
        $this->validate();
        
        $moodle = app('moodle');
        
        $formData = $this->formData;
        $courseCode = $formData['shortname'] ?? '';
        unset($formData['shortname']);
        
        if ($this->editingCourse) {
            $moodle->updateCourse($this->editingCourse->id, $formData);
            $moodle->updateCourseCustomField($this->editingCourse->id, $this->courseType, 8);
            if (!empty($courseCode)) {
                $moodle->updateCourseCustomField($this->editingCourse->id, $courseCode, 4);
            }
            
            if ($this->newImage) {
                $moodle->uploadCourseImage($this->editingCourse->id, $this->newImage);
            }
        } else {
            $courseId = $moodle->createCourse($formData);
            $moodle->updateCourseCustomField($courseId, $this->courseType, 8);
            if (!empty($courseCode)) {
                $moodle->updateCourseCustomField($courseId, $courseCode, 4);
            }
            
            if ($this->newImage) {
                $moodle->uploadCourseImage($courseId, $this->newImage);
            }
        }
        
        $this->closeModal();
        $this->loadCourses();
    }

    public function confirmDelete($courseId)
    {
        $this->deleteId = $courseId;
    }

    public function deleteCourse()
    {
        if ($this->deleteId) {
            $moodle = app('moodle');
            $moodle->deleteCourse($this->deleteId);
            $this->deleteId = null;
            $this->loadCourses();
        }
    }

    public function getFilteredCoursesProperty()
    {
        if (empty($this->search)) {
            return $this->courses;
        }
        
        $search = strtolower($this->search);
        return array_filter($this->courses, function($course) use ($search) {
            return strpos(strtolower($course->fullname), $search) !== false
                || (isset($course->shortname) && strpos(strtolower($course->shortname), $search) !== false);
        });
    }

    public function render()
    {
        $filtered = $this->filteredCourses;
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
        
        $paginatedCourses = [
            'courses' => $sliced,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => $lastPage,
        ];
        
        return view('livewire.offline-course', ['paginatedCourses' => $paginatedCourses]);
    }
    
    public function openEnrollModal($courseId)
    {
        $moodle = app('moodle');
        $course = $moodle->getCourse($courseId);
        
        if ($course) {
            $this->enrollingCourse = $course;
            $this->activeEnrollTab = 1;
            $this->allUsers = $moodle->getAllUsers();
            $this->cohorts = $moodle->getCohorts();
            $this->selectedUserIds = [];
            $this->unenrolUserIds = [];
            
            $enrolledUsers = $moodle->getEnrolledUsers($courseId);
            $this->enrolledUserIds = array_map('intval', array_column($enrolledUsers, 'id'));
            $this->enrolledUsersWithMethod = $enrolledUsers;
            $this->enrolledCohorts = $moodle->getEnrolledCohorts($courseId);
            $this->importFile = null;
            $this->importErrors = [];
            $this->importSuccess = '';
            
            $this->showEnrollModal = true;
        }
    }
    
    public function closeEnrollModal()
    {
        $this->showEnrollModal = false;
        $this->enrollingCourse = null;
    }
    
    public function setActiveEnrollTab($tab)
    {
        $this->activeEnrollTab = $tab;
        
        if ($tab == 3) {
            $moodle = app('moodle');
            $this->cohorts = $moodle->getCohorts();
            if ($this->enrollingCourse) {
                $this->enrolledCohorts = $moodle->getEnrolledCohorts($this->enrollingCourse->id);
            }
        }
        
        if ($tab == 4) {
            $this->importFile = null;
            $this->importErrors = [];
            $this->importSuccess = '';
        }
    }
    
    public function enrollUsers()
    {
        if (!$this->enrollingCourse || empty($this->selectedUserIds)) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Vui lòng chọn người dùng']]);
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingCourse->id;
        
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
        $this->enrolledUserIds = array_map('intval', array_column($enrolledUsers, 'id'));
        $this->enrolledUsersWithMethod = $enrolledUsers;
        $this->selectedUserIds = [];
    }
    
    public function enrolCohort()
    {
        if (!$this->enrollingCourse || !$this->selectedCohortId) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Vui lòng chọn nhóm']]);
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingCourse->id;
        
        $existingCohorts = $moodle->getEnrolledCohorts($courseId);
        $existingIds = array_column($existingCohorts, 'id');
        if (in_array((int)$this->selectedCohortId, $existingIds)) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Cohort này đã được ghi danh']]);
            return;
        }
        
        $result = $moodle->enrolUsersToCohorts([(int)$this->selectedCohortId], $courseId);
        
        if ($result) {
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Ghi danh nhóm thành công']]);
            $enrolledUsers = $moodle->getEnrolledUsers($courseId);
            $this->enrolledUserIds = array_map('intval', array_column($enrolledUsers, 'id'));
            $this->enrolledUsersWithMethod = $enrolledUsers;
            $this->enrolledCohorts = $moodle->getEnrolledCohorts($courseId);
            $this->selectedCohortId = null;
        } else {
            $this->dispatch('browser', ['alert' => ['type' => 'error', 'message' => 'Ghi danh nhóm thất bại']]);
        }
    }
    
    public function unenrolCohort($cohortId)
    {
        if (!$this->enrollingCourse) {
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingCourse->id;
        
        $result = $moodle->unenrolCohort((int)$cohortId, $courseId);
        
        if ($result) {
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Đã bỏ ghi danh nhóm']]);
            $enrolledUsers = $moodle->getEnrolledUsers($courseId);
            $this->enrolledUserIds = array_map('intval', array_column($enrolledUsers, 'id'));
            $this->enrolledUsersWithMethod = $enrolledUsers;
            $this->enrolledCohorts = $moodle->getEnrolledCohorts($courseId);
        } else {
            $this->dispatch('browser', ['alert' => ['type' => 'error', 'message' => 'Bỏ ghi danh thất bại']]);
        }
    }
    
    public function unenrolSelectedUsers()
    {
        if (!$this->enrollingCourse || empty($this->unenrolUserIds)) {
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingCourse->id;
        
        $removedCount = 0;
        foreach ($this->unenrolUserIds as $userId) {
            $moodle->unenrolUser((int)$userId, $courseId);
            $removedCount++;
        }
        
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => "Đã bỏ ghi danh {$removedCount} người dùng"]]);
        
        $enrolledUsers = $moodle->getEnrolledUsers($courseId);
        $this->enrolledUserIds = array_map('intval', array_column($enrolledUsers, 'id'));
        $this->enrolledUsersWithMethod = $enrolledUsers;
        $this->unenrolUserIds = [];
    }
    
    public function unenrolUser($userId)
    {
        if (!$this->enrollingCourse) {
            return;
        }
        
        $moodle = app('moodle');
        $courseId = $this->enrollingCourse->id;
        
        $result = $moodle->unenrolUser((int)$userId, $courseId);
        
        if ($result) {
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Đã bỏ ghi danh người dùng']]);
            $enrolledUsers = $moodle->getEnrolledUsers($courseId);
            $this->enrolledUserIds = array_map('intval', array_column($enrolledUsers, 'id'));
            $this->enrolledUsersWithMethod = $enrolledUsers;
        } else {
            $this->dispatch('browser', ['alert' => ['type' => 'error', 'message' => 'Bỏ ghi danh thất bại']]);
        }
    }
    
    public function importFromExcel()
    {
        if (!$this->enrollingCourse || !$this->importFile) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Vui lòng chọn file']]);
            return;
        }
        
        $this->importErrors = [];
        $this->importSuccess = '';
        
        try {
            $moodle = app('moodle');
            $courseId = $this->enrollingCourse->id;
            
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
            $this->enrolledUserIds = array_map('intval', array_column($enrolledUsers, 'id'));
            $this->enrolledUsersWithMethod = $enrolledUsers;
            
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
}
