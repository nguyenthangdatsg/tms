<?php

namespace App\Livewire;

use Livewire\Component;

class UserManagement extends Component
{
    protected $rules = [
        'formData.firstname' => 'required|string|max:100',
        'formData.lastname' => 'required|string|max:100',
        'formData.email' => 'required|email',
        'formData.username' => 'required|string|max:100',
        'formData.password' => 'nullable|string|min:8',
    ];
    
    public $users = [];
    public $search = '';
    public $showModal = false;
    public $editingUser = null;
    public $formData = [];
    public $perPage = 20;
    public $currentPage = 1;

    public function mount()
    {
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $moodle = app('moodle');
        $this->users = $moodle->getUsers();
    }

    public function openModal($userId = null)
    {
        if ($userId) {
            $moodle = app('moodle');
            $user = $moodle->getUser($userId);
            $this->editingUser = $user;
            $this->formData = [
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'username' => $user->username,
            ];
        } else {
            $this->editingUser = null;
            $this->formData = [
                'firstname' => '',
                'lastname' => '',
                'email' => '',
                'username' => '',
                'password' => '',
            ];
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingUser = null;
        $this->formData = [];
    }

    public function saveUser()
    {
        $this->validate();
        
        $moodle = app('moodle');
        
        if ($this->editingUser) {
            $moodle->updateUser($this->editingUser->id, $this->formData);
        } else {
            $moodle->createUser($this->formData);
        }
        
        $this->closeModal();
        $this->loadUsers();
    }

    public function deleteUser($id)
    {
        $moodle = app('moodle');
        $moodle->deleteUser($id);
        $this->loadUsers();
    }

    public function updatedSearch()
    {
        $this->currentPage = 1;
    }

    public function setPage($page)
    {
        $this->currentPage = $page;
    }

    public function getFilteredUsersProperty()
    {
        if (empty($this->search)) {
            return $this->users;
        }
        
        $search = strtolower($this->search);
        return array_filter($this->users, function($user) use ($search) {
            return strpos(strtolower($user->firstname . ' ' . $user->lastname), $search) !== false
                || strpos(strtolower($user->email), $search) !== false
                || strpos(strtolower($user->username), $search) !== false;
        });
    }

    public function render()
    {
        $filtered = $this->filteredUsers;
        $total = count($filtered);
        $page = $this->currentPage;
        $perPage = $this->perPage;
        $lastPage = max(1, ceil($total / $perPage));
        
        if ($page > $lastPage) {
            $page = $lastPage;
            $this->currentPage = $page;
        }
        
        $start = ($page - 1) * $perPage;
        $sliced = array_slice($filtered, $start, $perPage);
        
        $paginatedUsers = [
            'users' => $sliced,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => $lastPage,
        ];
        
        return view('livewire.user-management', ['paginatedUsers' => $paginatedUsers])
            ->layout('layouts.app');
    }
}