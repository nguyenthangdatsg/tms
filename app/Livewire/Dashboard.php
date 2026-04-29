<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public $totalUsers = 0;
    public $onlineUsers = 0;
    public $totalCourses = 0;
    public $specialCourses = 0;
    public $catalogueCourses = 0;
    public $blendedCourses = 0;
    public $totalLearningPaths = 0;
    
    public function mount()
    {
        $this->loadStatistics();
    }
    
    public function loadStatistics()
    {
        $moodle = app('moodle');
        
        // Total users (not deleted/suspended)
        $result = \Illuminate\Support\Facades\DB::selectOne("
            SELECT COUNT(*) as cnt FROM mdl_user 
            WHERE deleted = 0 AND suspended = 0 AND id > 1
        ");
        $this->totalUsers = $result->cnt ?? 0;
        
        // Online users (last access within 5 minutes)
        $result = \Illuminate\Support\Facades\DB::selectOne("
            SELECT COUNT(DISTINCT userid) as cnt FROM mdl_user_lastaccess 
            WHERE timeaccess > ?
        ", [time() - 300]);
        $this->onlineUsers = $result->cnt ?? 0;
        
        // Total courses
        $result = \Illuminate\Support\Facades\DB::selectOne("
            SELECT COUNT(*) as cnt FROM mdl_course WHERE visible = 1 AND id > 1
        ");
        $this->totalCourses = $result->cnt ?? 0;
        
        // Special courses (from mdl_course)
        $result = \Illuminate\Support\Facades\DB::selectOne("
            SELECT COUNT(*) as cnt FROM mdl_course WHERE visible = 1 AND id > 1
        ");
        $this->specialCourses = $result->cnt ?? 0;
        
        // Catalogue courses
        $result = \Illuminate\Support\Facades\DB::selectOne("
            SELECT COUNT(*) as cnt FROM mdl_local_catalogue_courses WHERE visible = 1
        ");
        $this->catalogueCourses = $result->cnt ?? 0;
        
        // Blended courses
        $result = \Illuminate\Support\Facades\DB::selectOne("
            SELECT COUNT(*) as cnt FROM mdl_local_blended_courses WHERE visible = 1
        ");
        $this->blendedCourses = $result->cnt ?? 0;
        
        // Learning paths
        $result = \Illuminate\Support\Facades\DB::selectOne("
            SELECT COUNT(*) as cnt FROM mdl_local_learningpath WHERE visible = 1
        ");
        $this->totalLearningPaths = $result->cnt ?? 0;
    }
    
    public function getLoginStatsProperty()
    {
        $moodle = app('moodle');
        
        // Get login stats for last 7 days
        $stats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $start = strtotime($date . ' 00:00:00');
            $end = strtotime($date . ' 23:59:59');
            
            $result = \Illuminate\Support\Facades\DB::selectOne("
                SELECT COUNT(DISTINCT userid) as cnt 
                FROM mdl_user_lastaccess 
                WHERE timeaccess BETWEEN ? AND ?
            ", [$start, $end]);
            
            $stats[] = [
                'date' => date('d/m', $start),
                'count' => $result->cnt ?? 0
            ];
        }
        
        return $stats;
    }
    
    public function getCourseStatsProperty()
    {
        $moodle = app('moodle');
        
        // Course enrollment stats
        $special = \Illuminate\Support\Facades\DB::selectOne("
            SELECT COUNT(*) as cnt FROM mdl_enrol WHERE status = 0 AND courseid > 1
        ");
        
        $catalogue = \Illuminate\Support\Facades\DB::selectOne("
            SELECT COUNT(*) as cnt FROM mdl_local_catalogue_enrolments le
            JOIN mdl_local_catalogue_courses cc ON le.catalogue_code = cc.code
            WHERE cc.visible = 1
        ");
        
        $blended = \Illuminate\Support\Facades\DB::selectOne("
            SELECT COUNT(*) as cnt FROM mdl_local_blended_enrolments
        ");
        
        return [
            'labels' => ['Khoa hoc dac biet', 'Khoa hoc catalogue', 'Khoa hoc blended'],
            'data' => [
                $special->cnt ?? 0,
                $catalogue->cnt ?? 0,
                $blended->cnt ?? 0
            ]
        ];
    }
    
    public function render()
    {
        return view('livewire.dashboard')
            ->layout('layouts.app');
    }
}
