<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\DB;

class OrganizationTest extends DuskTestCase
{
    /** @test */
    public function it_can_visit_organization_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/organization')
                ->assertSee('manage_depts')
                ->assertSee('add_org_unit');
        });
    }

    /** @test */
    public function it_can_expand_org_tree()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/organization')
                ->click('.org-tree-toggle')
                ->waitForText('manage_depts');
        });
    }

    /** @test */
    public function it_can_add_new_org_unit()
    {
        $this->browse(function (Browser $browser) {
            $orgName = 'Test Org ' . time();
            
            $browser->visit('/tms/organization')
                ->clickLink('Add')
                ->waitForDialog()
                ->type('name', $orgName)
                ->type('code', 'ORG' . time())
                ->select('type', 'department')
                ->clickButton('Save')
                ->waitForText($orgName);
        });
    }

    /** @test */
    public function it_validates_required_org_fields()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/organization')
                ->clickLink('Add')
                ->waitForDialog()
                ->clickButton('Save')
                ->waitForText('name');
        });
    }

    /** @test */
    public function it_can_edit_org_unit()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/organization')
                ->click('.edit-org-btn')
                ->waitForDialog()
                ->type('name', 'Updated Org Name')
                ->clickButton('Save')
                ->waitForText('Updated Org Name');
        });
    }

    /** @test */
    public function it_can_delete_org_unit()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/organization')
                ->click('.delete-org-btn')
                ->waitForDialog()
                ->clickButton('Confirm')
                ->waitForText('manage_depts');
        });
    }

    /** @test */
    public function it_can_manage_org_users()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/organization')
                ->click('.manage-users-btn')
                ->waitForDialog()
                ->assertSee('user');
        });
    }

    /** @test */
    public function it_can_search_org_users()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/organization')
                ->click('.manage-users-btn')
                ->waitForDialog()
                ->type('input[type="text"]', 'admin')
                ->waitForText('user');
        });
    }

    /** @test */
    public function it_shows_member_count()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/organization')
                ->assertSee('member_count');
        });
    }

    /** @test */
    public function it_validates_unique_org_code()
    {
        // Get existing org code
        $moodle = app('moodle');
        $units = $moodle->getOrganizationUnits();
        
        if (empty($units)) {
            $this->markTestSkipped('No org units');
        }
        
        $existingCode = $units[0]->code;
        
        $this->browse(function (Browser $browser) use ($existingCode) {
            $browser->visit('/tms/organization')
                ->clickLink('Add')
                ->waitForDialog()
                ->type('name', 'Test Org')
                ->type('code', $existingCode)
                ->select('type', 'department')
                ->clickButton('Save')
                ->waitForText('code');
        });
    }
}