<div>
  <!-- Header -->
  <div class="row mb-4">
    <div class="col-12">
      <h2 class="mb-0">{{ __t('permission_management') }}</h2>
    </div>
  </div>

  <!-- Tabs Navigation -->
  <div class="content-card mb-4">
    <div class="nav nav-tabs border-bottom" role="tablist">
      <button 
        wire:click="setTab('roles')" 
        class="nav-link {{ $activeTab === 'roles' ? 'active' : '' }}" 
        type="button">
        <i class="bi bi-shield-check me-2"></i>{{ __t('roles_management') }}
      </button>
      <button 
        wire:click="setTab('permissions')" 
        class="nav-link {{ $activeTab === 'permissions' ? 'active' : '' }}" 
        type="button">
        <i class="bi bi-lock me-2"></i>{{ __t('permissions_management') }}
      </button>
      <button 
        wire:click="setTab('users')" 
        class="nav-link {{ $activeTab === 'users' ? 'active' : '' }}" 
        type="button">
        <i class="bi bi-people me-2"></i>{{ __t('assign_roles_users') }}
      </button>
    </div>
  </div>

  <!-- TAB 1: ROLES MANAGEMENT -->
  @if($activeTab === 'roles')
  <div class="content-card">
    <div class="content-card-body">
      <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
          <h5>{{ __t('roles_list') }}</h5>
          <button wire:click="showCreateRoleModal()" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>{{ __t('create_role') }}
          </button>
        </div>
      </div>

      <!-- Search -->
      <div class="row mb-3">
        <div class="col-md-6">
          <input 
            type="text" 
            class="form-control" 
            placeholder="{{ __t('search') }}..."
            wire:model.live="searchRoles">
        </div>
      </div>

      <!-- Roles List -->
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th class="px-3 py-3">{{ __t('role_name_en') }}</th>
              <th class="px-3 py-3">{{ __t('display_name') }}</th>
              <th class="px-3 py-3">{{ __t('role_description') }}</th>
              <th class="px-3 py-3 text-center">{{ __t('status_label') }}</th>
              <th class="px-3 py-3 text-center">{{ __t('actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($filteredRoles as $role)
            <tr>
              <td class="px-3 py-3">
                <code>{{ $role->name }}</code>
                @if($role->is_system_role)
                <span class="badge bg-secondary ms-2">{{ __t('system_label') }}</span>
                @endif
              </td>
              <td class="px-3 py-3">{{ $role->display_name }}</td>
              <td class="px-3 py-3">
                <small class="text-muted">{{ Str::limit($role->description, 50) }}</small>
              </td>
              <td class="px-3 py-3 text-center">
                @if($role->visible)
                <span class="badge bg-success">{{ __t('active_label') }}</span>
                @else
                <span class="badge bg-danger">{{ __t('inactive_label') }}</span>
                @endif
              </td>
              <td class="px-3 py-3 text-center">
                @if(!$role->is_system_role)
                <button wire:click="showEditRoleModal({{ $role->id }})" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-pencil"></i>
                </button>
                <button wire:click="deleteRole({{ $role->id }})" class="btn btn-sm btn-outline-danger" 
                  onclick="return confirm('{{ __t('confirm_delete_role') }}')">
                  <i class="bi bi-trash"></i>
                </button>
                @else
                <span class="text-muted">{{ __t('system_label') }}</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">{{ __t('no_permissions') }}</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif

  <!-- TAB 2: PERMISSIONS MANAGEMENT -->
  @if($activeTab === 'permissions')
  <div class="content-card">
    <div class="content-card-body">
      <div class="row mb-3">
        <div class="col-12">
          <h5>{{ __t('permissions_matrix') }}</h5>
        </div>
      </div>

      <!-- Filters -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">{{ __t('filter_by_role') }}</label>
          <select class="form-select" wire:model.live="filterRole">
            <option value="">{{ __t('all_roles') }}</option>
            @foreach($roles as $role)
            <option value="{{ $role->id }}">{{ $role->display_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">{{ __t('filter_by_feature') }}</label>
          <select class="form-select" wire:model.live="filterModule">
            <option value="">{{ __t('all_features') }}</option>
            @foreach($modules as $module)
            <option value="{{ $module->id }}">{{ $module->display_name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <!-- Permissions Table -->
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th class="px-3 py-2">{{ __t('role_label') }}</th>
              <th class="px-3 py-2">{{ __t('feature_label') }}</th>
              <th class="px-3 py-2 text-center">{{ __t('view_label') }}</th>
              <th class="px-3 py-2 text-center">{{ __t('create_label') }}</th>
              <th class="px-3 py-2 text-center">{{ __t('edit_label') }}</th>
              <th class="px-3 py-2 text-center">{{ __t('delete_label') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($filteredPermissions as $perm)
            <tr>
              <td class="px-3 py-2">
                <small class="fw-semibold">{{ $perm->role_display_name }}</small>
              </td>
              <td class="px-3 py-2">
                <small>{{ $perm->module_display_name }}</small>
              </td>
              <td class="px-3 py-2 text-center">
                <div class="form-check">
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    {{ $perm->can_view ? 'checked' : '' }}
                    wire:click="updatePermissionCheckbox({{ $perm->role_id }}, {{ $perm->module_id }}, 'can_view')">
                </div>
              </td>
              <td class="px-3 py-2 text-center">
                <div class="form-check">
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    {{ $perm->can_create ? 'checked' : '' }}
                    wire:click="updatePermissionCheckbox({{ $perm->role_id }}, {{ $perm->module_id }}, 'can_create')">
                </div>
              </td>
              <td class="px-3 py-2 text-center">
                <div class="form-check">
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    {{ $perm->can_edit ? 'checked' : '' }}
                    wire:click="updatePermissionCheckbox({{ $perm->role_id }}, {{ $perm->module_id }}, 'can_edit')">
                </div>
              </td>
              <td class="px-3 py-2 text-center">
                <div class="form-check">
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    {{ $perm->can_delete ? 'checked' : '' }}
                    wire:click="updatePermissionCheckbox({{ $perm->role_id }}, {{ $perm->module_id }}, 'can_delete')">
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">{{ __t('no_permissions_configured') }}</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif

  <!-- TAB 3: USER ROLES MANAGEMENT -->
  @if($activeTab === 'users')
  <div class="content-card">
    <div class="content-card-body">
      <div class="row mb-3">
        <div class="col-12">
          <h5>{{ __t('assign_roles_to_users') }}</h5>
        </div>
      </div>

      <!-- Search Users -->
      <div class="row mb-3">
        <div class="col-md-6">
          <input 
            type="text" 
            class="form-control" 
            placeholder="{{ __t('search_users_placeholder') }}"
            wire:model.live="searchUsers">
        </div>
      </div>

      <!-- Users Table -->
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th class="px-3 py-3">{{ __t('user') }}</th>
              <th class="px-3 py-3">{{ __t('role_label') }} / {{ __t('manage_department_scope') }}</th>
              <th class="px-3 py-3">{{ __t('add_role') }}</th>
              <th class="px-3 py-3 text-center">{{ __t('actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($filteredUsers as $user)
            <tr>
              <td class="px-3 py-3">
                <div>
                  <strong>{{ $user->firstname }} {{ $user->lastname }}</strong>
                  <small class="d-block text-muted">{{ $user->email }}</small>
                </div>
              </td>
<td class="px-3 py-3">
                @if($user->roles && count($user->roles) > 0)
                  @foreach($user->roles as $role)
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary">{{ $role->display_name ?? $role->name }}</span>
                    @if(($role->scope ?? 'all') === 'all')
                    <span class="badge bg-success">{{ __t('all_departments') }}</span>
                    @else
                    <span class="badge bg-info">{{ count($role->selected_depts ?? []) }} {{ __t('departments') }}</span>
                    @endif
                    <button 
                      wire:click="openDeptScopeModal({{ $user->id }}, {{ $role->id }})" 
                      class="btn btn-sm btn-outline-secondary"
                      title="{{ __t('manage_department_scope') }}">
                      <i class="bi bi-geo-alt"></i>
                    </button>
                    <button 
                      wire:click="removeRoleFromUser({{ $user->id }}, {{ $role->id }})"
                      class="btn btn-sm btn-close"
                      type="button"
                      title="{{ __t('remove') }}"
                      onclick="if(!confirm(&quot;{{ __t('confirm_delete') }}&quot;)){event.stopImmediatePropagation();event.preventDefault();}">
                    </button>
                  </div>
                  @endforeach
                @else
                <span class="badge bg-secondary">{{ __t('no_role_assigned') }}</span>
                @endif
              </td>
              <td class="px-3 py-3">
                <div class="d-flex gap-2">
                  @if($selectingForUserId === $user->id)
                  <div class="input-group input-group-sm">
                    <select 
                      class="form-select form-select-sm" 
                      wire:model.live="selectedRoleIds"
                      multiple
                      autofocus>
                      @foreach($roles as $role)
                        @php
                          $alreadyHasRole = $user->roles && collect($user->roles)->contains('id', $role->id);
                        @endphp
                        @if(!$alreadyHasRole)
                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                        @endif
                      @endforeach
                    </select>
                    <button 
                      wire:click="assignRoleToUser({{ $user->id }})"
                      class="btn btn-success btn-sm"
                      type="button"
                      {{ count($selectedRoleIds) === 0 ? 'disabled' : '' }}>
                      <i class="bi bi-check-circle"></i>
                    </button>
                    <button 
                      wire:click="closeRoleSelector()"
                      class="btn btn-secondary btn-sm"
                      type="button">
                      <i class="bi bi-x-circle"></i>
                    </button>
                  </div>
                  @else
                  <button 
                    wire:click="openRoleSelector({{ $user->id }})"
                    class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> {{ __t('add_role') }}
                  </button>
                  @endif
                </div>
              </td>
              <td class="px-3 py-3 text-center">
                @if($user->roles && count($user->roles) > 0)
                <button 
                  wire:click="removePermission({{ $user->id }})" 
                  class="btn btn-outline-danger btn-sm"
                  title="{{ __t('remove_all_roles') }}"
                  onclick="if(!confirm(&quot;{{ __t('confirm_delete') }}&quot;)){event.stopImmediatePropagation();event.preventDefault();}">
                  <i class="bi bi-trash"></i> {{ __t('remove_all') }}
                </button>
                @else
                <span class="text-muted small">-</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center text-muted py-4">{{ __t('no_users') }}</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif

  <!-- Department Scope Modal -->
  @include('livewire.permission-management.dept-modal')

  <!-- Role Modal -->
  @if($showRoleModal)
  <div class="modal-backdrop fade show" style="display: block;"></div>
  <div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            {{ $editingRole ? __t('edit_role') : __t('create_role') }}
          </h5>
          <button wire:click="resetRoleForm()" type="button" class="btn-close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">{{ __t('role_name_en') }}</label>
            <input 
              type="text" 
              class="form-control" 
              placeholder="e.g., admin"
              wire:model="roleForm.name"
              {{ $editingRole ? 'disabled' : '' }}>
            @error('roleForm.name')
            <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">{{ __t('display_name') }}</label>
            <input 
              type="text" 
              class="form-control"
              placeholder="e.g., Administrator"
              wire:model="roleForm.display_name">
            @error('roleForm.display_name')
            <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">{{ __t('role_description') }}</label>
            <textarea 
              class="form-control" 
              rows="3"
              placeholder="Detailed description..."
              wire:model="roleForm.description"></textarea>
          </div>

          <div class="form-check">
            <input 
              type="checkbox" 
              class="form-check-input" 
              id="roleVisible"
              wire:model="roleForm.visible">
            <label class="form-check-label" for="roleVisible">
              {{ __t('active_label') }}
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button wire:click="resetRoleForm()" type="button" class="btn btn-secondary">{{ __t('cancel') }}</button>
          <button 
            wire:click="{{ $editingRole ? 'updateRole' : 'createRole' }}()" 
            type="button" 
            class="btn btn-primary"
            wire:loading.attr="disabled">
            <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
            {{ $editingRole ? __t('update') : __t('create') }}
          </button>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Flash Messages -->
  @if(session()->has('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  @if(session()->has('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
</div>
