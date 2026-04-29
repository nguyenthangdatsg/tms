<?php

namespace App\Livewire;

use Livewire\Component;

class Organization extends Component
{
    protected $rules = [
        'formData.name' => 'required|string|max:255',
        'formData.code' => 'required|string|max:50',
        'formData.type' => 'required|in:company,division,department,team',
    ];
    
    public $orgTree = [];
    public $flatUnits = [];
    public $search = '';
    public $showModal = false;
    public $editingUnit = null;
    public $formData = [];
    public $confirmDeleteId = null;
    
    public $showUsersModal = false;
    public $selectedOrgId = null;
    public $selectedOrgName = '';
    public $orgUsers = [];
    public $allUsers = [];
    public $selectedUserIds = [];
    public $userSearch = '';
    
    public $orgTypes = [
        'company' => 'Cong ty',
        'division' => 'Khoi/Bo phan',
        'department' => 'Phong ban',
        'team' => 'Nhom/Team',
    ];

    public function mount()
    {
        $this->loadOrganization();
    }

    public function loadOrganization()
    {
        $moodle = app('moodle');
        $this->flatUnits = $moodle->getOrganizationUnits();
        $this->orgTree = $this->buildTreeWithCounts($moodle, $moodle->getOrganizationUnits(), 0);
    }
    
    private function buildTreeWithCounts($moodle, array $units, int $parentId): array
    {
        $tree = [];
        foreach ($units as $unit) {
            if ($unit->parent_id == $parentId) {
                $unit->member_count = $moodle->getOrganizationUserCount($unit->id);
                $unit->children = $this->buildTreeWithCounts($moodle, $units, $unit->id);
                $tree[] = $unit;
            }
        }
        return $tree;
    }
    
    public function openAddModal($parentId = 0)
    {
        $this->editingUnit = null;
        $this->formData = [
            'name' => '',
            'code' => '',
            'type' => 'department',
            'parent_id' => $parentId,
            'description' => '',
            'visible' => 1,
        ];
        $this->showModal = true;
    }

    public function openEditModal($unitId)
    {
        $moodle = app('moodle');
        $unit = $moodle->getOrganizationUnit($unitId);
        
        if ($unit) {
            $this->editingUnit = $unit;
            $this->formData = [
                'name' => $unit->name ?? '',
                'code' => $unit->code ?? '',
                'type' => $unit->type ?? 'department',
                'parent_id' => $unit->parent_id ?? 0,
                'description' => $unit->description ?? '',
                'visible' => $unit->visible ?? 1,
            ];
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingUnit = null;
        $this->formData = [];
    }

    public function saveUnit()
    {
        $this->validate();
        
        $moodle = app('moodle');
        
        if ($this->editingUnit) {
            $moodle->updateOrganizationUnit($this->editingUnit->id, $this->formData);
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Da cap nhat don vi to chuc']]);
        } else {
            $moodle->createOrganizationUnit($this->formData);
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Da them don vi to chuc']]);
        }
        
        $this->closeModal();
        $this->loadOrganization();
    }

    public function confirmDelete($unitId)
    {
        $this->confirmDeleteId = $unitId;
    }

    public function cancelDelete()
    {
        $this->confirmDeleteId = null;
    }

    public function deleteUnit()
    {
        if ($this->confirmDeleteId) {
            $moodle = app('moodle');
            $moodle->deleteOrganizationUnit($this->confirmDeleteId);
            $this->confirmDeleteId = null;
            $this->loadOrganization();
            $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Da xoa don vi to chuc']]);
        }
    }

    public function openUsersModal($orgId, $orgName)
    {
        $this->selectedOrgId = $orgId;
        $this->selectedOrgName = $orgName;
        $this->selectedUserIds = [];
        $this->userSearch = '';
        
        $moodle = app('moodle');
        $this->orgUsers = $moodle->getAllOrganizationUsers($orgId);
        $this->allUsers = $moodle->getAvailableOrganizationUsers($orgId);
        
        $this->showUsersModal = true;
    }

    public function closeUsersModal()
    {
        $this->showUsersModal = false;
        $this->selectedOrgId = null;
        $this->selectedOrgName = '';
        $this->orgUsers = [];
        $this->allUsers = [];
        
        // Refresh org tree to update member counts
        $this->loadOrganization();
    }

    public function toggleUserSelection($userId)
    {
        $userId = (int)$userId;
        $key = array_search($userId, $this->selectedUserIds);
        if ($key !== false) {
            unset($this->selectedUserIds[$key]);
            $this->selectedUserIds = array_values($this->selectedUserIds);
        } else {
            $this->selectedUserIds[] = $userId;
        }
    }

    public function addSelectedUsers()
    {
        if (!$this->selectedOrgId || empty($this->selectedUserIds)) {
            $this->dispatch('browser', ['alert' => ['type' => 'warning', 'message' => 'Vui long chon nguoi dung']]);
            return;
        }
        
        $moodle = app('moodle');
        $addedCount = 0;
        
        foreach ($this->selectedUserIds as $userId) {
            $moodle->addUserToOrganization($this->selectedOrgId, (int)$userId);
            $addedCount++;
        }
        
        $this->orgUsers = $moodle->getAllOrganizationUsers($this->selectedOrgId);
        $this->allUsers = $moodle->getAvailableOrganizationUsers($this->selectedOrgId);
        $this->selectedUserIds = [];
        
        // Update org tree with new member counts
        $this->loadOrganization();
        
        $msg = 'Da them ' . $addedCount . ' nguoi dung';
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => $msg]]);
    }

    public function removeUserFromOrg($userId)
    {
        if (!$this->selectedOrgId) {
            return;
        }
        
        $moodle = app('moodle');
        $moodle->removeUserFromOrganization($this->selectedOrgId, $userId);
        
        $this->orgUsers = $moodle->getAllOrganizationUsers($this->selectedOrgId);
        $this->allUsers = $moodle->getAvailableOrganizationUsers($this->selectedOrgId);
        
        // Update org tree with new member counts
        $this->loadOrganization();
        
        $this->dispatch('browser', ['alert' => ['type' => 'success', 'message' => 'Da xoa nguoi dung khoi don vi']]);
    }

    public function getFilteredOrgUsersProperty()
    {
        if (empty($this->userSearch)) {
            return $this->orgUsers;
        }
        
        $search = strtolower($this->userSearch);
        return array_filter($this->orgUsers, function($user) use ($search) {
            $fullname = strtolower(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
            $email = strtolower($user->email ?? '');
            return strpos($fullname, $search) !== false || strpos($email, $search) !== false;
        });
    }

    public function getAvailableUsersProperty()
    {
        $enrolledUserIds = array_column($this->orgUsers, 'id');
        
        $users = array_filter($this->allUsers, function($user) use ($enrolledUserIds) {
            return !in_array($user->id, $enrolledUserIds);
        });
        
        if (!empty($this->userSearch)) {
            $search = strtolower($this->userSearch);
            $users = array_filter($users, function($user) use ($search) {
                $fullname = strtolower(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
                $email = strtolower($user->email ?? '');
                return strpos($fullname, $search) !== false || strpos($email, $search) !== false;
            });
        }
        
        return $users;
    }

    public function getFilteredTreeProperty()
    {
        if (empty($this->search)) {
            return $this->orgTree;
        }
        
        $search = strtolower($this->search);
        return $this->filterTree($this->orgTree, $search);
    }

    private function filterTree($tree, $search)
    {
        $result = [];
        foreach ($tree as $node) {
            $matches = strpos(strtolower($node->name), $search) !== false;
            $children = $this->filterTree($node->children ?? [], $search);
            
            if ($matches || !empty($children)) {
                $node->children = $children;
                $result[] = $node;
            }
        }
        return $result;
    }

    public function render()
    {
        return view('livewire.organization')
            ->layout('layouts.app');
    }
}
