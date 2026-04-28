<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TmsCourse extends Model
{
    protected $connection = 'mysql_tms';
    protected $table = 'courses';
    
    protected $fillable = [
        'moodle_course_id',
        'type',
        'status',
    ];

    public function exams()
    {
        return $this->hasMany(TmsExam::class, 'moodle_course_id', 'moodle_course_id');
    }
}