<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use stdClass;

class PermissionService
{
    /**
     * Get all modules with optional filtering
     */
    public function getModules($visible = true)
    {
        $sql = 'SELECT * FROM mdl_local_permission_modules';
        $params = [];

        if ($visible !== null) {
            $sql .= ' WHERE visible = ?';
            $params[] = $visible ? 1 : 0;
        }

        $sql .= ' ORDER BY sort_order ASC';

        return DB::select($sql, $params);
    }

    /**
     * Get all roles
     */
    public function getRoles($visible = true)
    {
        $sql = 'SELECT * FROM mdl_local_permission_roles';
        $params = [];

        if ($visible !== null) {
            $sql .= ' WHERE visible = ?';
            $params[] = $visible ? 1 : 0;
        }

        $sql .= ' ORDER BY name ASC';

        return DB::select($sql, $params);
    }

    /**
     * Get single role by ID
     */
    public function getRole($roleId)
    {
        $results = DB::select('SELECT * FROM mdl_local_permission_roles WHERE id = ?', [$roleId]);
        return $results[0] ?? null;
    }

    /**
     * Create a new role
     */
    public function createRole(array $data)
    {
        $time = time();
        $userId = $this->getCurrentUserId();

        DB::insert('
            INSERT INTO mdl_local_permission_roles 
            (name, display_name, description, is_system_role, visible, timecreated, timemodified, createdby, modifiedby)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ', [
            $data['name'],
            $data['display_name'],
            $data['description'] ?? null,
            $data['is_system_role'] ?? 0,
            $data['visible'] ?? 1,
            $time,
            $time,
            $userId,
            $userId
        ]);

        $result = DB::select('SELECT LAST_INSERT_ID() as id');
        return $this->getRole($result[0]->id);
    }

    /**
     * Update a role
     */
    public function updateRole($roleId, array $data)
    {
        $role = $this->getRole($roleId);
        if (!$role) {
            throw new \Exception('Role not found');
        }

        // Prevent modification of system roles
        if ($role->is_system_role) {
            throw new \Exception('Cannot modify system roles');
        }

        $sets = [];
        $values = [];
        
        if (isset($data['display_name'])) {
            $sets[] = 'display_name = ?';
            $values[] = $data['display_name'];
        }
        if (isset($data['description'])) {
            $sets[] = 'description = ?';
            $values[] = $data['description'];
        }
        if (isset($data['visible'])) {
            $sets[] = 'visible = ?';
            $values[] = $data['visible'];
        }
        
        $sets[] = 'timemodified = ?';
        $values[] = time();
        $sets[] = 'modifiedby = ?';
        $values[] = $this->getCurrentUserId();
        $values[] = $roleId;

        DB::update(
            'UPDATE mdl_local_permission_roles SET ' . implode(', ', $sets) . ' WHERE id = ?',
            $values
        );

        return $this->getRole($roleId);
    }

    /**
     * Delete a role
     */
    public function deleteRole($roleId)
    {
        $role = $this->getRole($roleId);
        if (!$role) {
            throw new \Exception('Role not found');
        }

        if ($role->is_system_role) {
            throw new \Exception('Cannot delete system roles');
        }

        // Delete associated permissions and user roles
        DB::delete('DELETE FROM mdl_local_permission_role_permissions WHERE role_id = ?', [$roleId]);
        DB::delete('DELETE FROM mdl_local_permission_user_roles WHERE role_id = ?', [$roleId]);

        return DB::delete('DELETE FROM mdl_local_permission_roles WHERE id = ?', [$roleId]);
    }

    /**
     * Get role permissions (which modules this role can access)
     */
    public function getRolePermissions($roleId)
    {
        $sql = 'SELECT rp.*, m.name as module_name, m.display_name, m.icon
                FROM mdl_local_permission_role_permissions rp
                JOIN mdl_local_permission_modules m ON rp.module_id = m.id
                WHERE rp.role_id = ?
                ORDER BY m.sort_order ASC';

        return DB::select($sql, [$roleId]);
    }

    /**
     * Assign permission to a role for a module
     */
    public function assignPermission($roleId, $moduleId, array $permissions)
    {
        // Check if assignment already exists
        $existing = DB::selectOne(
            'SELECT * FROM mdl_local_permission_role_permissions WHERE role_id = ? AND module_id = ?',
            [$roleId, $moduleId]
        );

        $time = time();

        if ($existing) {
            DB::update(
                'UPDATE mdl_local_permission_role_permissions SET can_view = ?, can_create = ?, can_edit = ?, can_delete = ?, timemodified = ? WHERE id = ?',
                [
                    $permissions['can_view'] ?? 0,
                    $permissions['can_create'] ?? 0,
                    $permissions['can_edit'] ?? 0,
                    $permissions['can_delete'] ?? 0,
                    $time,
                    $existing->id
                ]
            );
            return $this->getPermission($roleId, $moduleId);
        } else {
            DB::insert(
                'INSERT INTO mdl_local_permission_role_permissions (role_id, module_id, can_view, can_create, can_edit, can_delete, timecreated, timemodified) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $roleId,
                    $moduleId,
                    $permissions['can_view'] ?? 0,
                    $permissions['can_create'] ?? 0,
                    $permissions['can_edit'] ?? 0,
                    $permissions['can_delete'] ?? 0,
                    $time,
                    $time
                ]
            );
            return $this->getPermission($roleId, $moduleId);
        }
    }

    /**
     * Get all permissions for a role-module combination
     */
    public function getPermission($roleId, $moduleId)
    {
        $results = DB::select(
            'SELECT * FROM mdl_local_permission_role_permissions WHERE role_id = ? AND module_id = ?',
            [$roleId, $moduleId]
        );
        return $results[0] ?? null;
    }

    /**
     * Remove permission
     */
    public function removePermission($roleId, $moduleId)
    {
        return DB::delete(
            'DELETE FROM mdl_local_permission_role_permissions WHERE role_id = ? AND module_id = ?',
            [$roleId, $moduleId]
        );
    }

    /**
     * Assign role to user with optional department scope
     * @param int $userId
     * @param int $roleId
     * @param array $managedDepartments - Array of department IDs this user manages (null for all)
     * @param string $scope - 'all' or 'specific'
     */
    public function assignRoleToUser($userId, $roleId, $managedDepartments = null, $scope = 'all')
    {
        // Check if already assigned
        $existing = DB::selectOne(
            'SELECT * FROM mdl_local_permission_user_roles WHERE user_id = ? AND role_id = ?',
            [$userId, $roleId]
        );

        if ($existing) {
            // Update existing with new department scope
            $departmentsJson = $managedDepartments ? json_encode($managedDepartments) : null;
            DB::update(
                'UPDATE mdl_local_permission_user_roles SET managed_departments = ?, scope = ?, timemodified = ? WHERE user_id = ? AND role_id = ?',
                [$departmentsJson, $scope, time(), $userId, $roleId]
            );
            return $existing;
        }

        $time = time();
        $departmentsJson = $managedDepartments ? json_encode($managedDepartments) : null;
        
        DB::insert(
            'INSERT INTO mdl_local_permission_user_roles (user_id, role_id, assigned_by, timecreated, timemodified, managed_departments, scope) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$userId, $roleId, $this->getCurrentUserId(), $time, $time, $departmentsJson, $scope]
        );

        $result = DB::select('SELECT LAST_INSERT_ID() as id');
        $id = $result[0]->id;

        $results = DB::select('SELECT * FROM mdl_local_permission_user_roles WHERE id = ?', [$id]);
        return $results[0] ?? null;
    }

    /**
     * Remove role from user
     */
    public function removeRoleFromUser($userId, $roleId)
    {
        return DB::delete(
            'DELETE FROM mdl_local_permission_user_roles WHERE user_id = ? AND role_id = ?',
            [$userId, $roleId]
        );
    }
    
    /**
     * Update managed departments for a user's role
     */
    public function updateUserRoleDepartments($userId, $roleId, $managedDepartments = null, $scope = 'all')
    {
        $departmentsJson = $managedDepartments ? json_encode($managedDepartments) : null;
        return DB::update(
            'UPDATE mdl_local_permission_user_roles SET managed_departments = ?, scope = ?, timemodified = ? WHERE user_id = ? AND role_id = ?',
            [$departmentsJson, $scope, time(), $userId, $roleId]
        );
    }
    
    /**
     * Get user's role with department scope
     */
    public function getUserRoleWithScope($userId, $roleId)
    {
        return DB::selectOne(
            'SELECT * FROM mdl_local_permission_user_roles WHERE user_id = ? AND role_id = ?',
            [$userId, $roleId]
        );
    }
    
    /**
     * Check if user has permission in specific department
     * $departmentId is the department user is trying to access
     */
    public function userCanAccessDepartment($userId, $roleId, $departmentId)
    {
        $userRole = $this->getUserRoleWithScope($userId, $roleId);
        
        // If no scope or 'all', user can access everything
        if (!$userRole || $userRole->scope === 'all' || $userRole->scope === null) {
            return true;
        }
        
        // Check if managed_departments contains the department or its parent
        if ($userRole->managed_departments) {
            $managed = json_decode($userRole->managed_departments, true);
            if (is_array($managed)) {
                return in_array($departmentId, $managed);
            }
        }
        
        return false;
    }
    
    /**
     * Get all department IDs user can manage (including child departments)
     */
    public function getManagedDepartmentIds($userId, $roleId)
    {
        $userRole = $this->getUserRoleWithScope($userId, $roleId);
        
        if (!$userRole || $userRole->scope === 'all' || $userRole->scope === null) {
            return []; // Means all departments
        }
        
        if ($userRole->managed_departments) {
            $managed = json_decode($userRole->managed_departments, true);
            if (is_array($managed)) {
                // Return directly managed departments
                return $managed;
            }
        }
        
        return [];
    }

    /**
     * Get all roles assigned to a user
     */
    public function getUserRoles($userId)
    {
        $sql = 'SELECT r.*
                FROM mdl_local_permission_user_roles ur
                JOIN mdl_local_permission_roles r ON ur.role_id = r.id
                WHERE ur.user_id = ?
                ORDER BY r.name ASC';

        return DB::select($sql, [$userId]);
    }

    /**
     * Get users with a specific role
     */
    public function getUsersWithRole($roleId)
    {
        $sql = 'SELECT DISTINCT u.id, u.firstname, u.lastname, u.email, u.username
                FROM mdl_local_permission_user_roles ur
                JOIN mdl_user u ON ur.user_id = u.id
                WHERE ur.role_id = ?
                ORDER BY u.firstname ASC, u.lastname ASC';

        return DB::select($sql, [$roleId]);
    }

    /**
     * Get permissions matrix for a role (all modules with their permission status)
     */
    public function getRolePermissionMatrix($roleId)
    {
        $modules = $this->getModules(true);
        $permissions = $this->getRolePermissions($roleId);

        // Create indexed array for quick lookup
        $permIndex = [];
        foreach ($permissions as $perm) {
            $permIndex[$perm->module_id] = $perm;
        }

        // Build matrix
        $matrix = [];
        foreach ($modules as $module) {
            $perm = $permIndex[$module->id] ?? null;
            $matrix[$module->id] = (object)[
                'module' => $module,
                'can_view' => $perm ? (bool)$perm->can_view : false,
                'can_create' => $perm ? (bool)$perm->can_create : false,
                'can_edit' => $perm ? (bool)$perm->can_edit : false,
                'can_delete' => $perm ? (bool)$perm->can_delete : false,
            ];
        }

        return $matrix;
    }

    /**
     * Check if user has permission to access a module
     */
    public function userCanViewModule($userId, $moduleName)
    {
        $userRoles = $this->getUserRoles($userId);
        
        foreach ($userRoles as $role) {
            $sql = 'SELECT rrp.can_view
                    FROM mdl_local_permission_role_permissions rrp
                    JOIN mdl_local_permission_modules m ON rrp.module_id = m.id
                    WHERE rrp.role_id = ? AND m.name = ? AND rrp.can_view = 1
                    LIMIT 1';
            
            $permission = DB::selectOne($sql, [$role->id, $moduleName]);
            if ($permission) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get current user ID
     */
    private function getCurrentUserId()
    {
        global $USER;
        return $USER->id ?? 0;
    }

    /**
     * Get all permission records with role and module info
     * Shows full matrix: all roles × all modules
     */
    public function getAllPermissions($roleId = null)
    {
        $sql = 'SELECT r.id as role_id, r.name as role_name, r.display_name as role_display_name,
                       m.id as module_id, m.name as module_name, m.display_name as module_display_name, m.icon,
                       COALESCE(rrp.can_view, 0) as can_view,
                       COALESCE(rrp.can_create, 0) as can_create,
                       COALESCE(rrp.can_edit, 0) as can_edit,
                       COALESCE(rrp.can_delete, 0) as can_delete
                FROM mdl_local_permission_roles r
                CROSS JOIN mdl_local_permission_modules m
                LEFT JOIN mdl_local_permission_role_permissions rrp 
                    ON r.id = rrp.role_id AND m.id = rrp.module_id';
        
        $params = [];
        if ($roleId) {
            $sql .= ' WHERE r.id = ?';
            $params[] = $roleId;
        }

        $sql .= ' ORDER BY r.name ASC, m.sort_order ASC';

        return DB::select($sql, $params);
    }
}

