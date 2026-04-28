<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\DB;

class PermissionManagementTest extends DuskTestCase
{
    /** @test */
    public function it_can_visit_permission_management_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/permissions')
                ->assertSee('permission')
                ->assertSee('role')
                ->assertSee('manage');
        });
    }

    /** @test */
    public function it_can_switch_to_roles_tab()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/permissions')
                ->clickLink('Roles')
                ->waitForText('role_name')
                ->assertSee('role_name');
        });
    }

    /** @test */
    public function it_can_switch_to_permissions_tab()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/permissions')
                ->clickLink('Permissions')
                ->waitForText('feature_label')
                ->assertSee('feature_label');
        });
    }

    /** @test */
    public function it_can_switch_to_users_tab()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/permissions')
                ->clickLink('Assign Roles')
                ->waitForText('user')
                ->assertSee('user');
        });
    }

    /** @test */
    public function it_can_create_new_role()
    {
        $this->browse(function (Browser $browser) {
            $roleName = 'test_role_' . time();
            
            $browser->visit('/tms/permissions')
                ->clickLink('Roles')
                ->waitForText('role_name')
                ->clickButton('Add Role')
                ->waitForDialog()
                ->assertSee('role_name')
                ->type('name', $roleName)
                ->type('display_name', 'Test Role')
                ->type('description', 'Test Description')
                ->clickButton('Save')
                ->waitForText('Role created');
        });
    }

    /** @test */
    public function it_can_edit_existing_role()
    {
        $this->browse(function (Browser $browser) {
            // First create a role to edit
            $roleId = $this->createTestRole();
            
            $browser->visit('/tms/permissions')
                ->clickLink('Roles')
                ->waitForText('role_name')
                ->click('.edit-role-btn')
                ->waitForDialog()
                ->type('display_name', 'Updated Role Name')
                ->clickButton('Save')
                ->waitForText('Role updated');
            
            // Cleanup
            $this->deleteTestRole($roleId);
        });
    }

    /** @test */
    public function it_can_toggle_permission_checkbox()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/permissions')
                ->clickLink('Permissions')
                ->waitForText('feature_label')
                ->click('.permission-checkbox')
                ->assertVue('permissionMatrix', null, '');
        });
    }

    /** @test */
    public function it_can_search_users_in_assign_tab()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/permissions')
                ->clickLink('Assign Roles')
                ->waitForText('user')
                ->type('input[type="text"]', 'admin')
                ->waitForText('user');
        });
    }

    /** @test */
    public function it_can_filter_permissions_by_role()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/permissions')
                ->clickLink('Permissions')
                ->waitForText('feature_label')
                ->select('filterRole', 1)
                ->waitForText('feature_label');
        });
    }

    /** @test */
    public function it_can_filter_permissions_by_module()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/permissions')
                ->clickLink('Permissions')
                ->waitForText('feature_label')
                ->select('filterModule', 1)
                ->waitForText('feature_label');
        });
    }

    /** @test */
    public function it_shows_success_notification()
    {
        $this->browse(function (Browser $browser) {
            $roleName = 'test_role_notification_' . time();
            
            $browser->visit('/tms/permissions')
                ->clickLink('Roles')
                ->waitForText('role_name')
                ->clickButton('Add Role')
                ->waitForDialog()
                ->type('name', $roleName)
                ->type('display_name', 'Test Role')
                ->type('description', 'Test Description')
                ->clickButton('Save')
                ->waitForText('Role created successfully');
        });
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/permissions')
                ->clickLink('Roles')
                ->waitForText('role_name')
                ->clickButton('Add Role')
                ->waitForDialog()
                ->clickButton('Save')
                ->waitForText('name field is required');
        });
    }

    /** @test */
    public function it_cannot_delete_system_role()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/tms/permissions')
                ->clickLink('Roles')
                ->waitForText('role_name');
            
            // System roles should have delete button disabled
            $browser->assertMissing('.delete-system-role-btn');
        });
    }

    // Helper methods
    private function createTestRole(): int
    {
        $service = new \App\Services\PermissionService();
        return $service->createRole([
            'name' => 'test_role_' . time(),
            'display_name' => 'Test Role',
            'description' => 'Test',
            'visible' => 1,
        ]);
    }

    private function deleteTestRole(int $roleId)
    {
        $service = new \App\Services\PermissionService();
        $service->deleteRole($roleId);
    }
}