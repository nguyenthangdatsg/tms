<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LocalCatalogueService
{
    // Categories (local catalogue)
    public function getCategories(): array
    {
        return DB::select("SELECT id, parent, name, description, status, visible, sortorder, timecreated, timemodified, createdby, modifiedby FROM mdl_local_catalogue_categories ORDER BY sortorder ASC");
    }

    public function getCategoryTree(): array
    {
        $categories = $this->getCategories();
        $tree = [];
        foreach ($categories as $cat) {
            if (($cat->parent ?? 0) == 0) {
                $node = clone $cat;
                $node->children = $this->buildTreeObj($categories, $cat->id);
                $tree[] = $node;
            }
        }
        usort($tree, function($a, $b){ return ($a->sortorder ?? 0) <=> ($b->sortorder ?? 0); });
        return $tree;
    }

    protected function buildTreeObj(array $elements, int $parentId): array
    {
        $branch = [];
        foreach ($elements as $element) {
            if (($element->parent ?? 0) == $parentId) {
                $child = clone $element;
                $child->children = $this->buildTreeObj($elements, $element->id);
                $branch[] = $child;
            }
        }
        usort($branch, function($a, $b){ return ($a->sortorder ?? 0) <=> ($b->sortorder ?? 0); });
        return $branch;
    }

    // Categories CRUD (local catalogue)
    public function createCategory(array $data): int
    {
        $time = time();
        $parent = $data['parent'] ?? 0;
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $sortorder = $data['sortorder'] ?? 0;
        $visible = $data['visible'] ?? 1;
        DB::insert("INSERT INTO mdl_local_catalogue_categories (parent, name, description, status, visible, sortorder, timecreated, timemodified, createdby, modifiedby) VALUES (?, ?, ?, 1, ?, ?, ?, ?, ?, ?)", [$parent, $name, $description, $visible, $sortorder, $time, $time, 1, 1]);
        $r = DB::select('SELECT LAST_INSERT_ID() as id');
        return $r[0]->id;
    }

    public function updateCategory(int $id, array $data): bool
    {
        if (empty($data)) return false;
        $sets = [];
        $vals = [];
        foreach ($data as $k => $v) {
            $sets[] = "$k = ?";
            $vals[] = $v;
        }
        $vals[] = time();
        $vals[] = $id;
        DB::update("UPDATE mdl_local_catalogue_categories SET " . implode(', ', $sets) . ", timemodified = ? WHERE id = ?", $vals);
        return true;
    }

    public function deleteCategory(int $id): bool
    {
        DB::update("UPDATE mdl_local_catalogue_categories SET visible = 0, timemodified = ? WHERE id = ?", [time(), $id]);
        return true;
    }

    // Courses (local catalogue)
    public function getCourses(): array
    {
        return DB::select("SELECT id, category_id as category_id, name, description, duration, code, visible, type, status, timecreated, timemodified, createdby, modifiedby FROM mdl_local_catalogue_courses ORDER BY name ASC");
    }

public function createCourse(array $data): int
    {
        $category = $data['category_id'] ?? 0;
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $duration = $data['duration'] ?? '';
        $code = $data['code'] ?? '';
        $visible = $data['visible'] ?? 1;
        $type = $data['type'] ?? 0;
        $time = time();
        DB::insert("INSERT INTO mdl_local_catalogue_courses (category_id, name, description, duration, code, visible, type, status, timecreated, timemodified, createdby, modifiedby) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, 1, 1)", [$category, $name, $description, $duration, $code, $visible, $type, $time, $time]);
        $r = DB::select('SELECT LAST_INSERT_ID() as id');
        return $r[0]->id;
    }

    public function updateCourse(int $id, array $data): bool
    {
        if (empty($data)) return false;
        $sets = [];
        $vals = [];
        foreach ($data as $k => $v) {
            $sets[] = "$k = ?";
            $vals[] = $v;
        }
        $vals[] = $id;
        DB::update("UPDATE mdl_local_catalogue_courses SET " . implode(', ', $sets) . ", timemodified = ? WHERE id = ?", array_merge($vals, [time()]));
        return true;
    }

    public function deleteCourse(int $id): bool
    {
        DB::update("UPDATE mdl_local_catalogue_courses SET visible = 0, timemodified = ? WHERE id = ?", [time(), $id]);
        return true;
    }

    // Get single course by id (for editing)
    public function getCourse(int $id): ?object
    {
        $row = DB::selectOne("SELECT id, name as fullname, code as shortname, description as summary, '' as startdate, '' as enddate, visible FROM mdl_local_catalogue_courses WHERE id = ?", [$id]);
        return $row ?? null;
    }
}
