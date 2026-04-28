<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class UserManagementTest extends TestCase
{
    public function test_can_view_user_management_page(): void
    {
        $response = $this->get('/tms/users');
        $response->assertStatus(200);
    }

    public function test_can_search_users(): void
    {
        $response = $this->get('/tms/users?search=admin');
        $response->assertStatus(200);
    }

    public function test_can_filter_by_status(): void
    {
        $response = $this->get('/tms/users?status=active');
        $response->assertStatus(200);
    }

    public function test_can_paginate_users(): void
    {
        $response = $this->get('/tms/users?page=1');
        $response->assertStatus(200);
    }

    public function test_excludes_deleted_users(): void
    {
        $moodle = app('moodle');
        $users = $moodle->getUsers();
        
        foreach ($users as $user) {
            $this->assertEquals(0, $user->deleted);
        }
    }

    public function test_excludes_guest_user(): void
    {
        $moodle = app('moodle');
        $users = $moodle->getUsers();
        
        foreach ($users as $user) {
            $this->assertNotEquals('guest', $user->username);
        }
    }

    public function test_can_sort_users(): void
    {
        $response = $this->get('/tms/users?sort=firstname');
        $response->assertStatus(200);
    }
}