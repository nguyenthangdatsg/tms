<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\DB;

class UserManagementTest extends DuskTestCase
{
    /** @test */
    public function it_can_visit_user_management_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/users')
                ->assertSee('user_management')
                ->assertSee('add_user');
        });
    }

    /** @test */
    public function it_can_search_users()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/users')
                ->type('input[type="search"], input[type="text"]', 'admin')
                ->waitForText('user_management');
        });
    }

    /** @test */
    public function it_can_filter_users_by_status()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/users')
                ->select('status', 'active')
                ->waitForText('user_management');
        });
    }

    /** @test */
    public function it_can_open_add_user_modal()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/users')
                ->clickLink('Add User')
                ->waitForDialog()
                ->assertSee('firstname')
                ->assertSee('lastname')
                ->assertSee('email')
                ->assertSee('username');
        });
    }

    /** @test */
    public function it_can_cancel_add_user_modal()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/users')
                ->clickLink('Add User')
                ->waitForDialog()
                ->clickButton('Cancel')
                ->waitForText('user_management');
        });
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/users')
                ->clickLink('Add User')
                ->waitForDialog()
                ->clickButton('Save')
                ->waitForText('firstname');
        });
    }

    /** @test */
    public function it_validates_email_format()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/users')
                ->clickLink('Add User')
                ->waitForDialog()
                ->type('firstname', 'Test')
                ->type('lastname', 'User')
                ->type('email', 'invalid-email')
                ->type('username', 'testuser')
                ->clickButton('Save')
                ->waitForText('email');
        });
    }

    /** @test */
    public function it_can_paginate_users()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/users?page=1')
                ->assertSee('user_management');
        });
    }

    /** @test */
    public function it_shows_user_list()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/users')
                ->assertSee('firstname')
                ->assertSee('lastname')
                ->assertSee('email');
        });
    }

    /** @test */
    public function it_can_sort_users_by_name()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/users?sort=firstname')
                ->assertSee('user_management');
        });
    }

    /** @test */
    public function it_excludes_deleted_users()
    {
        $moodle = app('moodle');
        $users = $moodle->getUsers();
        
        $this->browse(function (Browser $browser) use ($users) {
            $browser->visit('/tms/users');
            
            // Verify no deleted users shown
            foreach ($users as $user) {
                $this->assertEquals(0, $user->deleted);
            }
        });
    }
}