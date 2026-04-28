<?php

namespace Tests\Feature;

use Tests\TestCase;

class CourseManagementFeatureTest extends TestCase
{
    public function test_can_view_online_courses(): void
    {
        $response = $this->get('/tms/online-course');
        $response->assertStatus(200);
    }

    public function test_can_view_offline_courses(): void
    {
        $response = $this->get('/tms/offline-course');
        $response->assertStatus(200);
    }

    public function test_can_view_blended_courses(): void
    {
        $response = $this->get('/tms/blended-course');
        $response->assertStatus(200);
    }

    public function test_can_filter_online_courses(): void
    {
        $response = $this->get('/tms/online-course?status=active');
        $response->assertStatus(200);
    }

    public function test_can_filter_offline_courses(): void
    {
        $response = $this->get('/tms/offline-course?status=active');
        $response->assertStatus(200);
    }

    public function test_can_search_online_courses(): void
    {
        $response = $this->get('/tms/online-course?search=test');
        $response->assertStatus(200);
    }

    public function test_can_paginate_courses(): void
    {
        $response = $this->get('/tms/online-course?page=1');
        $response->assertStatus(200);
    }
}