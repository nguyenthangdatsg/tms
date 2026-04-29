<div>
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">{{ __t('organization_structure') }}</h2>
            <button wire:click="openAddModal(0)" class="btn btn-primary" wire:loading.attr="disabled">
                <i class="bi bi-plus-circle" wire:loading.remove></i>
                <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                {{ __t('add_unit') }}
            </button>
        </div>
    </div>

    <div class="content-card mb-4">
        <div class="content-card-body">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" wire:model.live="search" placeholder="{{ __t('search_unit') }}" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="content-card-body">
            @if(count($this->filteredTree) > 0)
                <div class="org-tree">
                    @foreach($this->filteredTree as $unit)
                        @include('livewire.partials.org-unit', ['unit' => $unit, 'level' => 0, 'orgTypes' => $orgTypes])
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">{{ __t('no_organization_units') }}</p>
                    <button class="btn btn-primary" wire:click="openAddModal(0)">
                        <i class="bi bi-plus-circle me-2"></i>{{ __t('add_first_unit') }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if($showModal)
    <div class="modal-backdrop fade show" style="display: block;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingUnit ? __t('edit_unit') : __t('add_unit') }}</h5>
                    <button wire:click="closeModal()" type="button" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __t('unit_name') }} *</label>
                        <input type="text" wire:model="formData.name" class="form-control @error('formData.name') is-invalid @enderror" required>
                        @error('formData.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __t('unit_code') }}</label>
                                <input type="text" wire:model="formData.code" class="form-control @error('formData.code') is-invalid @enderror" placeholder="Mã đơn vị">
                                @error('formData.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __t('unit_type') }}</label>
                                <select wire:model="formData.type" class="form-select @error('formData.type') is-invalid @enderror">
                                    @foreach($orgTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('formData.type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __t('parent_unit') }}</label>
                        <select wire:model="formData.parent_id" class="form-select">
                            <option value="0">{{ __t('root_unit') }}</option>
                            @foreach($flatUnits as $unit)
                                @if(!$editingUnit || $unit->id != $editingUnit->id)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $orgTypes[$unit->type] ?? $unit->type }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __t('description') }}</label>
                        <textarea wire:model="formData.description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" wire:model="formData.visible" class="form-check-input" id="visibleCheck">
                        <label class="form-check-label" for="visibleCheck">{{ __t('active') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeModal()" type="button" class="btn btn-secondary">{{ __t('cancel') }}</button>
                    <button wire:click="saveUnit()" type="button" class="btn btn-primary" wire:loading.attr="disabled">
                        <i class="bi bi-check-circle" wire:loading.remove></i>
                        <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                        {{ __t('save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showUsersModal && $selectedOrgId)
    <div class="modal-backdrop fade show" style="display: block;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __t('unit_members') }}: {{ $selectedOrgName }}</h5>
                    <button wire:click="closeUsersModal()" type="button" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>{{ __t('current_members') }}</h6>
                            <input type="text" class="form-control mb-2" placeholder="{{ __t('search') }}..." wire:model.live="userSearch">
                            <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                @if(count($this->filteredOrgUsers) > 0)
                                    @foreach($this->filteredOrgUsers as $user)
                                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                            <div>
                                                <strong>{{ $user->firstname }} {{ $user->lastname }}</strong>
                                                <br><small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                            <button class="btn btn-sm btn-outline-danger" wire:click="removeUserFromOrg({{ $user->id }})" title="{{ __t('remove') }}">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted text-center py-3">{{ __t('no_members') }}</p>
                                @endif
                            </div>
                            <small class="text-muted d-block mt-2">{{ count($orgUsers) }} {{ __t('members') }}</small>
                        </div>
                        <div class="col-md-6">
                            <h6>{{ __t('add_members') }}</h6>
                            <input type="text" class="form-control mb-2" placeholder="{{ __t('search') }}..." wire:model.live="userSearch">
                            <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                @if(count($this->availableUsers) > 0)
                                    <table class="table table-sm table-hover mb-0">
                                        <tbody>
                                            @foreach($this->availableUsers as $user)
                                                <tr class="{{ in_array($user->id, $selectedUserIds) ? 'table-primary' : '' }}"
                                                    style="cursor: pointer;"
                                                    wire:click="toggleUserSelection({{ $user->id }})">
                                                    <td style="width: 40px;">
                                                        <input type="checkbox" 
                                                               class="form-check-input" 
                                                               id="user-{{ $user->id }}"
                                                               {{ in_array($user->id, $selectedUserIds) ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $user->firstname }} {{ $user->lastname }}</strong>
                                                        <br><small class="text-muted">{{ $user->email }}</small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted text-center py-3">{{ __t('no_available_users') }}</p>
                                @endif
                            </div>
                            <small class="text-muted d-block mt-2">{{ __t('selected') }}: {{ count($selectedUserIds) }}</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeUsersModal()" type="button" class="btn btn-secondary">{{ __t('close') }}</button>
                    <button wire:click="addSelectedUsers()" type="button" class="btn btn-primary" wire:loading.attr="disabled" {{ empty($selectedUserIds) ? 'disabled' : '' }}>
                        <i class="bi bi-plus-circle me-2"></i>{{ __t('add_selected') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($confirmDeleteId)
    <div class="modal-backdrop fade show" style="display: block;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __t('confirm_delete') }}</h5>
                </div>
                <div class="modal-body">
                    <p>{{ __t('confirm_delete_unit_message') }}</p>
                </div>
                <div class="modal-footer">
                    <button wire:click="cancelDelete()" type="button" class="btn btn-secondary">{{ __t('cancel') }}</button>
                    <button wire:click="deleteUnit()" type="button" class="btn btn-danger" wire:loading.attr="disabled">
                        <i class="bi bi-trash" wire:loading.remove></i>
                        <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                        {{ __t('delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
