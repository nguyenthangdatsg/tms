<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_tms';

    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('moodle_course_id');
            $table->unsignedBigInteger('moodle_quiz_id')->unique();
            $table->string('status')->default('active');
            $table->timestamps();
            
            $table->foreign('moodle_course_id')->references('moodle_course_id')->on('courses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};