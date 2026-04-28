<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\PermissionService;
use Illuminate\Support\Facades\DB;

class PermissionManagement extends Component
{
    // Data properties
    public $modules = [];
    public $roles = [];
    public $users = [];
    public $usersWithRoles = []; // Users enriched with role data
    public $permissions = [];
    public $userRolesCache = [];
    
    // Organization units
    public $organizationUnits = [];
    public $flatOrganizationUnits = [];
    
    // UI state
    public $activeTab = 'roles'; // roles, permissions, users
    public $editingRole = null;
    public $selectedRole = null;
    public $selectedModule = null;
    public $showRoleModal = false;
    public $showDeptModal = false;
    public $filterRole = null;
    public $filterModule = null;
    
    // User role editing state
    public $editingUserId = null;
    public $editingRoleId = null;
    public $selectedDepartments = [];
    public $deptScope = 'all';
    
    // Form data
    public $roleForm = [
        'name' => '',
        'display_name' => '',
        'description' => '',
        'visible' => 1,
    ];
    
    public $searchUsers = '';
    public $searchRoles = '';
    public $permissionMatrix = [];
    public $selectedRoleIds = []; // Array of selected roleIds for current user being assigned
    public $selectingForUserId = null; // Which user is being assigned roles

    private $permissionService;

    public function mount()
    {
        $this->loadData();
        $this->loadOrganizationUnits();
    }

    /**
     * Get PermissionService instance (lazy initialization)
     */
    private function getPermissionService()
    {
        if (!$this->permissionService) {
            $this->permissionService = new PermissionService();
        }
        return $this->permissionService;
    }
    
    /**
     * Load organization units
     */
    private function loadOrganizationUnits()
    {
        $moodle = app('moodle');
        $this->flatOrganizationUnits = $moodle->getOrganizationUnits();
        $this->organizationUnits = $moodle->getOrganizationTree();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        // Reset minor UI state when switching tabs
        if ($tab === 'roles') {
            $this->selectedRole = null;
        }
    }

    public function loadData()
    {
        $service = $this->getPermissionService();
        $this->modules = $service->getModules(true);
        $this->roles = $service->getRoles(true);
        $this->permissions = $service->getAllPermissions();
        
        // Get all users with their roles info
        $this->users = DB::select(
            'SELECT u.* FROM mdl_user u WHERE u.deleted = 0 AND u.username != ? ORDER BY u.firstname ASC, u.lastname ASC',
            ['guest']
        );
        
        // Enrich users with roles data
        $this->usersWithRoles = [];
        foreach ($this->users as $user) {
            $userWithRole = (array)$user;
            $roles = $service->getUserRoles($user->id);

            // Enrich each role with scope info (managed_departments)
            $enrichedRoles = [];
            foreach ($roles as $role) {
                $r = (array)$role;
                $userRole = $service->getUserRoleWithScope($user->id, $role->id);
                $r['scope'] = $userRole->scope ?? 'all';
                $r['managed_departments'] = $userRole->managed_departments ?? null;
                if ($r['managed_departments']) {
                    $r['selected_depts'] = json_decode($r['managed_departments'], true) ?? [];
                } else {
                    $r['selected_depts'] = [];
                }
                $enrichedRoles[] = (object)$r;
            }

            // Store all roles for this user (enriched)
            $userWithRole['roles'] = $enrichedRoles;
            $userWithRole['role_count'] = count($enrichedRoles);
            
            $this->usersWithRoles[] = (object)$userWithRole;
        }
    }

    /**
     * Get filtered users based on search
     */
    public function getFilteredUsers()
    {
        // If no search term, only show users that already have at least one role assigned
        if (trim($this->searchUsers) === '') {
            return array_values(array_filter($this->usersWithRoles, function($user) {
                return !empty($user->roles) && count($user->roles) > 0;
            }));
        }

        // When searching, query users by name/email/username so admin can find users to assign
        $searchTerm = '%' . trim($this->searchUsers) . '%';
        $rows = DB::select(
            'SELECT u.* FROM mdl_user u WHERE u.deleted = 0 AND u.username != ? AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ? OR u.username LIKE ?) ORDER BY u.firstname ASC, u.lastname ASC LIMIT 50',
            ['guest', $searchTerm, $searchTerm, $searchTerm, $searchTerm]
        );

        $service = $this->getPermissionService();
        $results = [];
        foreach ($rows as $user) {
            $userArr = (array)$user;
            $roles = $service->getUserRoles($user->id);
            
            // Enrich each role with scope info
            $enrichedRoles = [];
            foreach ($roles as $role) {
                $r = (array)$role;
                $userRole = $service->getUserRoleWithScope($user->id, $role->id);
                $r['scope'] = $userRole->scope ?? 'all';
                $r['managed_departments'] = $userRole->managed_departments ?? null;
                if ($r['managed_departments']) {
                    $r['selected_depts'] = json_decode($r['managed_departments'], true) ?? [];
                } else {
                    $r['selected_depts'] = [];
                }
                $enrichedRoles[] = (object)$r;
            }
            
            $userArr['roles'] = $enrichedRoles;
            $userArr['role_count'] = count($enrichedRoles);
            $results[] = (object)$userArr;
        }

        return $results;
    }

    /**
     * Get filtered roles based on search
     */
    public function getFilteredRoles()
    {
        if (!$this->searchRoles) {
            return $this->roles;
        }

        $search = strtolower($this->searchRoles);
        return array_filter($this->roles, function($role) use ($search) {
            return stripos($role->name, $search) !== false ||
                   stripos($role->display_name, $search) !== false;
        });
    }

    /**
     * Get filtered permissions based on role and module filters
     */
    public function getFilteredPermissions()
    {
        $filtered = $this->permissions;

        if ($this->filterRole) {
            $filtered = array_filter($filtered, function($perm) {
                return $perm->role_id == $this->filterRole;
            });
        }

        if ($this->filterModule) {
            $filtered = array_filter($filtered, function($perm) {
                return $perm->module_id == $this->filterModule;
            });
        }

        return $filtered;
    }

    public function render()
    {
        return view('livewire.permission-management.index', [
            'filteredUsers' => $this->getFilteredUsers(),
            'filteredRoles' => $this->getFilteredRoles(),
            'filteredPermissions' => $this->getFilteredPermissions(),
        ]);
    }

    // ============= ROLE MANAGEMENT =============
    public function selectRole($roleId)
    {
        $this->selectedRole = $roleId;
        $service = $this->getPermissionService();
        $this->permissionMatrix = $service->getRolePermissionMatrix($roleId);
    }

    public function showCreateRoleModal()
    {
        $this->resetRoleForm();
        $this->editingRole = null;
        $this->showRoleModal = true;
    }

    public function showEditRoleModal($roleId)
    {
        $service = $this->getPermissionService();
        $role = $service->getRole($roleId);
        
        if ($role->is_system_role) {
            session()->flash('error', __t('cannot_edit_system_roles'));
            return;
        }

        $this->roleForm = [
            'name' => $role->name,
            'display_name' => $role->display_name,
            'description' => $role->description,
            'visible' => $role->visible,
        ];
        $this->editingRole = $roleId;
        $this->showRoleModal = true;
    }

     public function createRole()
     {
         $this->validate([
             'roleForm.name' => 'required|min:2|max:255',
             'roleForm.display_name' => 'required|min:2|max:255',
         ]);

         try {
             $service = $this->getPermissionService();
$service->createRole($this->roleForm);
              session()->flash('success', __t('role_created'));
              $this->resetRoleForm();
             $this->showRoleModal = false;
             $this->loadData();
             $this->activeTab = 'permissions'; // Auto-switch to permissions tab
             $this->dispatch('close-role-modal');
} catch (\Exception $e) {
              session()->flash('error', __t('error_creating_role') . ': ' . $e->getMessage());
          }
      }

      public function updateRole()
     {
         $this->validate([
             'roleForm.display_name' => 'required|min:2|max:255',
         ]);

         try {
             $service = $this->getPermissionService();
$service->updateRole($this->editingRole, $this->roleForm);
              session()->flash('success', __t('role_updated'));
              $this->resetRoleForm();
             $this->showRoleModal = false;
             $this->loadData();
             $this->activeTab = 'permissions'; // Auto-switch to permissions tab
             $this->dispatch('close-role-modal');
} catch (\Exception $e) {
              session()->flash('error', __t('error_updating_role') . ': ' . $e->getMessage());
          }
      }

      public function deleteRole($roleId)
     {
         try {
             $service = $this->getPermissionService();
$service->deleteRole($roleId);
              session()->flash('success', __t('role_deleted'));
              $this->loadData();
             $this->selectedRole = null;
             $this->activeTab = 'permissions'; // Auto-switch to permissions tab
} catch (\Exception $e) {
              session()->flash('error', __t('error_deleting_role') . ': ' . $e->getMessage());
          }
      }

    public function resetRoleForm()
    {
        $this->roleForm = [
            'name' => '',
            'display_name' => '',
            'description' => '',
            'visible' => 1,
        ];
        $this->editingRole = null;
    }

    // ============= PERMISSION MANAGEMENT =============
    public function updatePermissionCheckbox($roleId, $moduleId, $permissionType)
    {
        try {
            $service = $this->getPermissionService();
            
            // Get current permissions
            $current = $service->getPermission($roleId, $moduleId);
            
            // Build new permissions with toggle
            $permissions = [
                'can_view' => $current && $permissionType === 'can_view' ? !$current->can_view : ($current->can_view ?? false),
                'can_create' => $current && $permissionType === 'can_create' ? !$current->can_create : ($current->can_create ?? false),
                'can_edit' => $current && $permissionType === 'can_edit' ? !$current->can_edit : ($current->can_edit ?? false),
                'can_delete' => $current && $permissionType === 'can_delete' ? !$current->can_delete : ($current->can_delete ?? false),
            ];
            
            // Save updated permissions
            $service->assignPermission($roleId, $moduleId, $permissions);
            
            // Reload permissions data
            $this->loadData();
            session()->flash('success', __t('permission_updated'));
        } catch (\Exception $e) {
            session()->flash('error', __t('error_updating_permission') . ': ' . $e->getMessage());
        }
    }

    public function updatePermission($moduleId, $permission)
    {
        if (!$this->selectedRole) return;

        $current = $this->permissionMatrix[$moduleId] ?? null;
        if (!$current) return;

        // Toggle permission
        $permissions = [
            'can_view' => $permission === 'can_view' ? !$current->can_view : $current->can_view,
            'can_create' => $permission === 'can_create' ? !$current->can_create : $current->can_create,
            'can_edit' => $permission === 'can_edit' ? !$current->can_edit : $current->can_edit,
            'can_delete' => $permission === 'can_delete' ? !$current->can_delete : $current->can_delete,
        ];

        try {
            $service = $this->getPermissionService();
            $service->assignPermission($this->selectedRole, $moduleId, $permissions);
            $this->permissionMatrix[$moduleId] = (object)array_merge((array)$current, $permissions);
            session()->flash('success', __t('permission_updated'));
        } catch (\Exception $e) {
            session()->flash('error', __t('error_updating_permission') . ': ' . $e->getMessage());
        }
    }

    // ============= USER ROLE MANAGEMENT =============
    
    public function openDeptScopeModal($userId, $roleId)
    {
        $this->editingUserId = $userId;
        $this->editingRoleId = $roleId;
        
        $service = $this->getPermissionService();
        
        // Load existing department scope for this specific role
        $this->selectedDepartments = [];
        $this->deptScope = 'all';
        
        $userRole = $service->getUserRoleWithScope($userId, $roleId);
        
        if ($userRole) {
            $this->deptScope = $userRole->scope ?? 'all';
            if ($userRole->managed_departments) {
                $this->selectedDepartments = json_decode($userRole->managed_departments, true) ?? [];
            }
        }
        
        $this->showDeptModal = true;
    }
    
    public function closeDeptScopeModal()
    {
        $this->showDeptModal = false;
        $this->editingUserId = null;
        $this->editingRoleId = null;
        $this->selectedDepartments = [];
    }
    
     public function saveDeptScope()
     {
         if (!$this->editingUserId || !$this->editingRoleId) {
             return;
         }
         
         $service = $this->getPermissionService();
         
         // Convert department IDs to integers
         $deptIds = array_map('intval', $this->selectedDepartments);
         
// Save with department scope
          $service->assignRoleToUser($this->editingUserId, $this->editingRoleId, $deptIds, $this->deptScope);
          
          session()->flash('success', __t('dept_scope_updated'));
          $this->closeDeptScopeModal();
          $this->loadData();
      }
     
     public function assignRoleToUser($userId)
     {
         try {
             // Validate inputs
             if (!$userId || empty($this->selectedRoleIds)) {
                 session()->flash('error', __t('select_at_least_one_role'));
                 return;
             }
             
             $service = $this->getPermissionService();
             
             // Assign each selected role to the user
             foreach ($this->selectedRoleIds as $roleId) {
                 $service->assignRoleToUser((int)$userId, (int)$roleId);
             }
             
             session()->flash('success', __t('role_assigned'));
             
             // Clear the temporary state
             $this->selectedRoleIds = [];
             $this->selectingForUserId = null;
             
             // Reload data
             $this->loadData();
         } catch (\Exception $e) {
             session()->flash('error', __t('error_assigning_role') . ': ' . $e->getMessage());
         }
     }
     
     public function openRoleSelector($userId)
     {
         $this->selectingForUserId = $userId;
         $this->selectedRoleIds = [];
     }
     
     public function closeRoleSelector()
     {
         $this->selectingForUserId = null;
         $this->selectedRoleIds = [];
     }

     public function setUserRole($userId)
     {
         if (!$this->selectedRole) {
             // No role selected yet; default to first role if available
             if (!empty($this->roles)) {
                 $this->selectedRole = $this->roles[0]->id ?? null;
             } else {
                 return;
             }
         }
         $this->assignRoleToUser((int)$userId, (int)$this->selectedRole);
     }

public function removeRoleFromUser($userId, $roleId)
      {
          try {
              $service = $this->getPermissionService();
              $service->removeRoleFromUser($userId, $roleId);
              session()->flash('success', __t('role_removed'));
              $this->loadData();
          } catch (\Exception $e) {
              session()->flash('error', __t('error_removing_role') . ': ' . $e->getMessage());
          }
      }
      
      /**
       * Remove all permissions (roles) assigned to a user.
       */
      public function removePermission($userId)
      {
          try {
              $service = $this->getPermissionService();
              // Fetch all roles assigned to the user and remove them one by one
              $roles = $service->getUserRoles((int)$userId);
              foreach ($roles as $role) {
                  if (isset($role->id)) {
                      $service->removeRoleFromUser((int)$userId, (int)$role->id);
                  }
              }
              session()->flash('success', __t('all_roles_removed'));
              $this->loadData();
          } catch (\Exception $e) {
              session()->flash('error', __t('error_removing_roles') . ': ' . $e->getMessage());
         }
     }
    
    public function getDeptScopeLabel($scope, $managedDepartments)
    {
        if ($scope === 'all' || $scope === null) {
            return 'Tất cả phòng ban';
        }
        
        if (empty($managedDepartments)) {
            return 'Tất cả phòng ban';
        }
        
        // Get department names
        $names = [];
        foreach ($this->flatOrganizationUnits as $unit) {
            if (in_array($unit->id, $managedDepartments)) {
                $names[] = $unit->name;
            }
        }
        
        return implode(', ', array_slice($names, 0, 3)) . (count($names) > 3 ? '...' : '');
    }
    
    public function toggleDepartment($deptId, $checked)
    {
        // Build department hierarchy map
        $deptMap = [];
        foreach ($this->flatOrganizationUnits as $unit) {
            $deptMap[$unit->id] = [
                'parent_id' => $unit->parent_id,
                'id' => $unit->id
            ];
        }
        
        // Get all child department IDs (recursive)
        $childIds = $this->getChildDepartmentIds($deptId, $deptMap);
        
        if ($checked) {
            // Check parent + ALL children
            foreach ([$deptId, ...$childIds] as $id) {
                $id = (int)$id;
                if (!in_array($id, $this->selectedDepartments)) {
                    $this->selectedDepartments[] = $id;
                }
            }
        } else {
            // Uncheck parent + ALL children
            $newSelected = [];
            foreach ($this->selectedDepartments as $id) {
                $id = (int)$id;
                // Keep ONLY items NOT in parent + children list
                if ($id !== (int)$deptId && !in_array($id, $childIds)) {
                    $newSelected[] = $id;
                }
            }
            $this->selectedDepartments = array_values($newSelected);
        }
    }
    
    private function getChildDepartmentIds($parentId, $deptMap): array
    {
        $childIds = [];
        foreach ($deptMap as $id => $dept) {
            if ($dept['parent_id'] == $parentId) {
                $childIds[] = (int)$id;
                $childIds = array_merge($childIds, $this->getChildDepartmentIds($id, $deptMap));
            }
        }
        return $childIds;
    }
    
    public function isDepartmentChecked($deptId): bool
    {
        return in_array($deptId, $this->selectedDepartments);
    }
}
