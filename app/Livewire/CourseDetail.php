<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class CourseDetail extends Component
{
    use WithFileUploads;

    protected $rules = [
        'formData.fullname' => 'required|string|max:255',
        'formData.startdate' => 'nullable|date',
        'formData.enddate' => 'nullable|date|after_or_equal:formData.startdate',
        'formData.summary' => 'nullable|string',
    ];
    
    public $courseId = null;
    public $course = null;
    public $showModal = false;
    public $formData = [];
    public $courseType = 'Online';
    public $courseImage = null;
    public $newImage = null;

    protected $queryString = ['courseId'];

    public function mount($courseId = null)
    {
        $this->courseId = $courseId ?? request()->segment(3);
        $this->loadCourse();
    }

    public function loadCourse()
    {
        if (!$this->courseId) {
            return;
        }
        
        $moodle = app('moodle');
        $this->course = $moodle->getCourse((int)$this->courseId);
        
        if ($this->course) {
            $this->formData = [
                'fullname' => $this->course->fullname ?? '',
                'shortname' => $this->course->shortname ?? '',
                'summary' => strip_tags($this->course->summary ?? ''),
                'startdate' => $this->course->startdate ? date('Y-m-d', $this->course->startdate) : '',
                'enddate' => $this->course->enddate ? date('Y-m-d', $this->course->enddate) : '',
                'visible' => $this->course->visible ?? 1,
            ];
            $this->courseType = $this->course->course_type ?? 'Online';
            $this->courseImage = $this->course->course_image ?? null;
        }
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->newImage = null;
    }

    public function saveCourse()
    {
        $this->validate();
        
        $moodle = app('moodle');
        
        $moodle->updateCourse($this->courseId, $this->formData);
        $moodle->updateCourseCustomField($this->courseId, $this->courseType);
        
        if ($this->newImage) {
            $moodle->uploadCourseImage($this->courseId, $this->newImage);
        }
        
        $this->closeModal();
        $this->loadCourse();
        
        $this->dispatch('courseUpdated');
    }

    public function render()
    {
        return view('livewire.course-detail')
            ->layout('layouts.app');
    }
}
