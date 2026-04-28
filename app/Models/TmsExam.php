<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TmsExam extends Model
{
    protected $connection = 'mysql_tms';
    protected $table = 'exams';
    
    protected $fillable = [
        'moodle_course_id',
        'moodle_quiz_id',
        'status',
    ];

    public function course()
    {
        return $this->belongsTo(TmsCourse::class, 'moodle_course_id', 'moodle_course_id');
    }
}