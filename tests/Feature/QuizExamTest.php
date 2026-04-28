<?php

namespace Tests\Feature;

use Tests\TestCase;

class QuizExamTest extends TestCase
{
    public function test_can_view_quiz_list(): void
    {
        $response = $this->get('/tms/quiz');
        $response->assertStatus(200);
    }

    public function test_can_filter_quiz_by_status(): void
    {
        $response = $this->get('/tms/quiz?status=active');
        $response->assertStatus(200);
    }

    public function test_can_search_quiz(): void
    {
        $response = $this->get('/tms/quiz?search=test');
        $response->assertStatus(200);
    }

    public function test_can_view_exam_list(): void
    {
        $response = $this->get('/tms/exam');
        $response->assertStatus(200);
    }

    public function test_can_filter_exam_by_status(): void
    {
        $response = $this->get('/tms/exam?status=active');
        $response->assertStatus(200);
    }
}