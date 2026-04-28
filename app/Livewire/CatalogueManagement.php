<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;

use Livewire\Component;

class CatalogueManagement extends Component
{
    public $tab = 'courses';
    public $courses = [];
    public $categories = [];
    public $selectedCategoryId = null;
    public $categoryTree = [];
    public $availableCodes = [];
    public $expandedNodes = []; // Track expanded nodes
    public $showNewCategoryForm = false;
    public $categoryNew = [
        'parent' => 0,
        'name' => '',
        'description' => '',
        'sortorder' => 0,
        'visible' => 1,
    ];

    // Course form fields (local catalogue fields)
    public $courseForm = [
        'category_id' => 0,
        'name' => '',
        'code' => '',
        'description' => '',
        'duration' => '',
        'type' => '',
        'visible' => 1,
    ];
    public $editingCourse = null;

    // Category form fields (basic)
    public $categoryForm = [
        'name' => '',
        'description' => '',
        'parent' => 0,
        'sortorder' => 0,
        'visible' => 1,
    ];
    public $editingCategory = null;
    public $categoryParentId = 0; // for creating subcategories

    public function mount()
    {
        $this->loadData();
    }

protected function loadData()
    {
        $catalogue = app('localCatalogue');
        $this->categories = $catalogue->getCategories();
        $this->courses = $catalogue->getCourses();
        $this->categoryTree = $catalogue->getCategoryTree();
        
        $codes = DB::select("SELECT DISTINCT code FROM mdl_local_catalogue_courses WHERE code != '' ORDER BY code");
        $this->availableCodes = array_column($codes, 'code');
    }

    private function buildTree(array $categories, int $parentId = 0): array
    {
        $tree = [];
        foreach ($categories as $cat) {
            if (($cat->parent ?? 0) == $parentId) {
                $node = [
                    'id' => $cat->id,
                    'name' => $cat->name ?? '',
                    'parent' => $cat->parent ?? 0,
                    'sortorder' => $cat->sortorder ?? 0,
                    'children' => $this->buildTree($categories, $cat->id),
                ];
                $tree[] = $node;
            }
        }
        usort($tree, function($a, $b){ return $a['sortorder'] <=> $b['sortorder']; });
        return $tree;
    }

    public function selectCategory($id)
    {
        $this->selectedCategoryId = (int)$id;
    }

    public function toggleNode($nodeId)
    {
        $nodeId = (int)$nodeId;
        if (in_array($nodeId, $this->expandedNodes)) {
            $this->expandedNodes = array_values(array_filter($this->expandedNodes, fn($id) => $id !== $nodeId));
        } else {
            $this->expandedNodes[] = $nodeId;
        }
    }

    public function isNodeExpanded($nodeId): bool
    {
        return in_array((int)$nodeId, $this->expandedNodes);
    }

    public function openAddUnder($parentId)
    {
        $this->categoryParentId = (int)$parentId;
        $this->editingCategory = null;
        $this->categoryForm = [
            'name' => '',
            'description' => '',
            'parent' => (int)$parentId,
            'sortorder' => 0,
            'visible' => 1,
        ];
    }

    protected $listeners = ['updateCategoryOrder' => 'updateCategoryOrder'];

    public function updateCategoryOrder(array $orderedIds)
    {
        $catalogue = app('localCatalogue');
        foreach ($orderedIds as $index => $id) {
            if (method_exists($catalogue, 'updateCategory')) {
                $catalogue->updateCategory((int)$id, ['sortorder' => $index + 1]);
            }
        }
        $this->loadData();
    }

    public function editCategory($id)
    {
        $cat = collect($this->categories)->firstWhere('id', (int)$id);
        if ($cat) {
            $this->categoryForm = [
                'name' => $cat->name ?? '',
                'description' => $cat->description ?? '',
                'parent' => $cat->parent ?? 0,
                'sortorder' => $cat->sortorder ?? 0,
                'visible' => $cat->visible ?? 1,
            ];
            $this->editingCategory = $cat;
        }
    }

    public function updateCategory()
    {
        if (!$this->editingCategory) return;
        $catalogue = app('localCatalogue');
        if (method_exists($catalogue, 'updateCategory')) {
            $catalogue->updateCategory((int)$this->editingCategory->id, $this->categoryForm);
        }
        $this->editingCategory = null;
        $this->categoryParentId = 0;
        $this->categoryForm = [
            'name' => '',
            'description' => '',
            'parent' => 0,
            'sortorder' => 0,
            'visible' => 1,
        ];
        $this->loadData();
    }

    public function deleteCategory(int $id)
    {
        $catalogue = app('localCatalogue');
        if (method_exists($catalogue, 'deleteCategory')) {
            $catalogue->deleteCategory($id);
        }
        $this->loadData();
    }

    public function toggleCategoryVisibility(int $id)
    {
        $cat = collect($this->categories)->firstWhere('id', (int)$id);
        if ($cat) {
            $newVisibility = $cat->visible ? 0 : 1;
            $catalogue = app('localCatalogue');
            if (method_exists($catalogue, 'updateCategory')) {
                $catalogue->updateCategory($id, ['visible' => $newVisibility]);
            }
            $this->loadData();
        }
    }

    public function setTab(string $tab)
    {
        $this->tab = $tab;
    }

    // Course CRUD (simplified: create/update/delete)
    public function createCourse()
    {
        $catalogue = app('localCatalogue');
        $this->courseForm['category_id'] = $this->selectedCategoryId ?? 0;
        $id = $catalogue->createCourse($this->courseForm);
        $this->resetCourseForm();
        $this->loadData();
        return $id;
    }

    public function editCourse($courseOrId)
    {
        if (is_int($courseOrId) || is_numeric($courseOrId)) {
            $catalogue = app('localCatalogue');
            $course = $catalogue->getCourse((int)$courseOrId);
        } else {
            $course = $courseOrId;
        }
        $this->editingCourse = $course;
        $this->courseForm = [
            'category_id' => $course->category_id ?? 0,
            'name' => $course->name ?? '',
            'code' => $course->code ?? '',
            'description' => $course->description ?? '',
            'duration' => $course->duration ?? '',
            'type' => $course->type ?? '',
            'visible' => $course->visible ?? 1,
        ];
    }

    public function updateCourse()
    {
        if (!$this->editingCourse) {
            return;
        }
        $catalogue = app('localCatalogue');
        $this->courseForm['category_id'] = $this->selectedCategoryId ?? 0;
        $catalogue->updateCourse($this->editingCourse->id ?? 0, $this->courseForm);
        $this->editingCourse = null;
        $this->resetCourseForm();
        $this->loadData();
    }

    public function deleteCourse(int $id)
    {
        $catalogue = app('localCatalogue');
        $catalogue->deleteCourse($id);
        $this->loadData();
    }

    public function toggleCourseVisibility(int $id)
    {
        $catalogue = app('localCatalogue');
        $course = $catalogue->getCourse($id);
        if ($course) {
            $newVisibility = $course->visible ? 0 : 1;
            $catalogue->updateCourse($id, ['visible' => $newVisibility]);
            $this->loadData();
        }
    }

    protected function resetCourseForm()
    {
        $this->courseForm = [
            'category_id' => 0,
            'name' => '',
            'code' => '',
            'description' => '',
            'duration' => '',
            'type' => '',
            'visible' => 1,
        ];
    }

    // Category CRUD (basic) - rely on Moodle DB via moodle service if available
    public function createCategory()
    {
        // Create via local catalogue service
        $catalogue = app('localCatalogue');
        $this->categoryForm['parent'] = $this->categoryParentId;
        $catalogue->createCategory($this->categoryForm);
        
        $this->categoryForm = [
            'name' => '',
            'description' => '',
            'parent' => 0,
            'sortorder' => 0,
            'visible' => 1,
        ];
        $this->categoryParentId = 0;
        $this->loadData();
    }

    public function getSelectedCategoryNameProperty(): ?string
    {
        if (!$this->selectedCategoryId) {
            return null;
        }
        foreach ($this->categories as $c) {
            if ((int)($c->id ?? 0) === (int)$this->selectedCategoryId) {
                return $c->name ?? '';
            }
        }
        return null;
    }

    public function getFilteredCoursesProperty(): array
    {
        if (!$this->selectedCategoryId) {
            return $this->courses;
        }
        $cat = (int)$this->selectedCategoryId;
        return array_values(array_filter($this->courses, function($course) use ($cat){
            return isset($course->category_id) && (int)$course->category_id === $cat;
        }));
    }

    public function render()
    {
        return view('livewire.catalogue-management');
    }
}
