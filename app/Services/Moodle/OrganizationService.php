<?php

namespace App\Services\Moodle;

use Illuminate\Support\Facades\DB;

class OrganizationService
{
    public static function getOrganizationUnits(): array
    {
        return DB::select("
            SELECT * FROM mdl_org_unit
            WHERE visible = 1
            ORDER BY parent_id ASC, sortorder ASC, name ASC
        ");
    }

    public static function getOrganizationTree(): array
    {
        $units = self::getOrganizationUnits();
        return self::buildOrgTree($units, 0);
    }

    private static function buildOrgTree(array $units, int $parentId): array
    {
        $tree = [];
        foreach ($units as $unit) {
            if ($unit->parent_id == $parentId) {
                $children = self::buildOrgTree($units, $unit->id);
                $unit->children = $children;
                $unit->level = $parentId == 0 ? 0 : 1;
                $tree[] = $unit;
            }
        }
        return $tree;
    }

    public static function getOrganizationUnit(int $id): ?object
    {
        return DB::selectOne('SELECT * FROM mdl_org_unit WHERE id = ?', [$id]);
    }

    public static function createOrganizationUnit(array $data): int
    {
        DB::insert("
            INSERT INTO mdl_org_unit (name, code, type, parent_id, sortorder, description, visible, timecreated, timemodified)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $data['name'] ?? '',
            $data['code'] ?? null,
            $data['type'] ?? 'department',
            $data['parent_id'] ?? 0,
            $data['sortorder'] ?? 0,
            $data['description'] ?? null,
            $data['visible'] ?? 1,
            time(),
            time()
        ]);
        return DB::getPdo()->lastInsertId();
    }

    public static function updateOrganizationUnit(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $values[] = $data['name'];
        }
        if (isset($data['code'])) {
            $fields[] = 'code = ?';
            $values[] = $data['code'];
        }
        if (isset($data['type'])) {
            $fields[] = 'type = ?';
            $values[] = $data['type'];
        }
        if (isset($data['parent_id'])) {
            $fields[] = 'parent_id = ?';
            $values[] = $data['parent_id'];
        }
        if (isset($data['sortorder'])) {
            $fields[] = 'sortorder = ?';
            $values[] = $data['sortorder'];
        }
        if (isset($data['description'])) {
            $fields[] = 'description = ?';
            $values[] = $data['description'];
        }
        if (isset($data['visible'])) {
            $fields[] = 'visible = ?';
            $values[] = $data['visible'];
        }

        $fields[] = 'timemodified = ?';
        $values[] = time();
        $values[] = $id;

        DB::update('UPDATE mdl_org_unit SET ' . implode(', ', $fields) . ' WHERE id = ?', $values);
        return true;
    }

    public static function deleteOrganizationUnit(int $id): bool
    {
        $unit = self::getOrganizationUnit($id);
        if ($unit) {
            DB::update('UPDATE mdl_org_unit SET parent_id = ? WHERE parent_id = ?', [$unit->parent_id, $id]);
            DB::delete('DELETE FROM mdl_org_unit_user WHERE org_id = ?', [$id]);
            DB::delete('DELETE FROM mdl_org_unit WHERE id = ?', [$id]);
        }
        return true;
    }

    public static function getOrganizationUsers(int $orgId): array
    {
        return DB::select("
            SELECT ou.*, u.firstname, u.lastname, u.email
            FROM mdl_org_unit_user ou
            JOIN mdl_user u ON ou.user_id = u.id
            WHERE ou.org_id = ?
            ORDER BY u.firstname ASC
        ", [$orgId]);
    }

    public static function getUserOrganizations(int $userId): array
    {
        return DB::select("
            SELECT ou.*, o.name, o.type, o.parent_id
            FROM mdl_org_unit_user ou
            JOIN mdl_org_unit o ON ou.org_id = o.id
            WHERE ou.user_id = ?
            ORDER BY o.type ASC, o.name ASC
        ", [$userId]);
    }

    public static function addUserToOrganization(int $orgId, int $userId, string $role = 'member'): bool
    {
        DB::delete('DELETE FROM mdl_org_unit_user WHERE user_id = ?', [$userId]);

        $orgIds = self::getOrgAndChildIds($orgId);
        foreach ($orgIds as $oid) {
            DB::insert(
                'INSERT INTO mdl_org_unit_user (org_id, user_id, role, timecreated) VALUES (?, ?, ?, ?)',
                [$oid, $userId, $role, time()]
            );
        }
        return true;
    }

    public static function removeUserFromOrganization(int $orgId, int $userId): bool
    {
        $orgIds = self::getOrgAndChildIds($orgId);
        foreach ($orgIds as $oid) {
            DB::delete('DELETE FROM mdl_org_unit_user WHERE org_id = ? AND user_id = ?', [$oid, $userId]);
        }
        return true;
    }

    public static function getAllOrganizationUsers(int $orgId): array
    {
        $orgIds = self::getOrgAndChildIds($orgId);
        if (empty($orgIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($orgIds), '?'));
        return DB::select("
            SELECT DISTINCT u.id, u.firstname, u.lastname, u.email, ou.org_id
            FROM mdl_user u
            JOIN mdl_org_unit_user ou ON u.id = ou.user_id
            WHERE ou.org_id IN ($placeholders) AND u.deleted = 0 AND u.suspended = 0
            ORDER BY u.firstname ASC
        ", $orgIds);
    }

    public static function getAvailableOrganizationUsers(int $orgId): array
    {
        $orgIds = self::getOrgAndChildIds($orgId);

        if (empty($orgIds)) {
            return DB::select("
                SELECT id, firstname, lastname, email
                FROM mdl_user
                WHERE deleted = 0 AND suspended = 0
                ORDER BY firstname ASC
            ");
        }

        $placeholders = implode(',', array_fill(0, count($orgIds), '?'));
        return DB::select("
            SELECT id, firstname, lastname, email
            FROM mdl_user
            WHERE deleted = 0 AND suspended = 0
            AND id NOT IN (
                SELECT DISTINCT user_id FROM mdl_org_unit_user
                WHERE org_id IN ($placeholders)
            )
            ORDER BY firstname ASC
        ", $orgIds);
    }

    private static function getOrgAndChildIds(int $parentId): array
    {
        $ids = [$parentId];
        $children = DB::select('SELECT id FROM mdl_org_unit WHERE parent_id = ?', [$parentId]);
        foreach ($children as $child) {
            $ids = array_merge($ids, self::getOrgAndChildIds($child->id));
        }
        return $ids;
    }

    public static function getOrganizationUserCount(int $orgId): int
    {
        $orgIds = self::getOrgAndChildIds($orgId);

        if (empty($orgIds)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($orgIds), '?'));
        $result = DB::selectOne("
            SELECT COUNT(DISTINCT ou.user_id) as cnt
            FROM mdl_org_unit_user ou
            JOIN mdl_user u ON ou.user_id = u.id
            WHERE ou.org_id IN ($placeholders) AND u.deleted = 0 AND u.suspended = 0
        ", $orgIds);
        return $result->cnt ?? 0;
    }
}
