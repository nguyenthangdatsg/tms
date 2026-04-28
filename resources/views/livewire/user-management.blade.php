<div>
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">{{ __t('user_management') }}</h2>
            <button wire:click="openModal()" class="btn btn-primary" wire:loading.attr="disabled">
                <i class="bi bi-plus-lg" wire:loading.remove></i>
                <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                {{ __t('add_user') }}
            </button>
        </div>
    </div>

    <div class="content-card mb-4">
        <div class="content-card-body">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" wire:model.live="search" placeholder="{{ __t('search_placeholder') }}" 
                        class="form-control">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">{{ __t('per_page_10') }}</option>
                        <option value="20">{{ __t('per_page_20') }}</option>
                        <option value="50">{{ __t('per_page_50') }}</option>
                        <option value="100">{{ __t('per_page_100') }}</option>
                    </select>
                </div>
            </div>
            @if($search)
                <small class="text-muted mt-2 d-block">
                    {{ __t('search') }}: {{ count($filteredUsers) }} {{ __t('users_found') }}
                </small>
            @endif
        </div>
    </div>

    <div class="content-card">
        <div class="content-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">{{ __t('id') }}</th>
                            <th class="px-4 py-3">{{ __t('fullname') }}</th>
                            <th class="px-4 py-3">{{ __t('email') }}</th>
                            <th class="px-4 py-3">{{ __t('username') }}</th>
                            <th class="px-4 py-3">{{ __t('created_at') }}</th>
                            <th class="px-4 py-3">{{ __t('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedUsers['users'] as $user)
                        <tr>
                            <td class="px-4 py-3">{{ $user->id }}</td>
                            <td class="px-4 py-3 fw-medium">{{ $user->firstname }} {{ $user->lastname }}</td>
                            <td class="px-4 py-3">{{ $user->email }}</td>
                            <td class="px-4 py-3">{{ $user->username }}</td>
                            <td class="px-4 py-3 text-muted">
                                @if($user->timecreated > 0)
                                    {{ date('d/m/Y', $user->timecreated) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <button wire:click="openModal({{ $user->id }})" class="btn btn-sm btn-outline-primary me-2" wire:loading.attr="disabled">
                                    <i class="bi bi-pencil" wire:loading.remove></i>
                                    <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                                    {{ __t('edit') }}
                                </button>
                                <button wire:click="deleteUser({{ $user->id }})" class="btn btn-sm btn-outline-danger" wire:loading.attr="disabled">
                                    <i class="bi bi-trash" wire:loading.remove></i>
                                    <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                                    {{ __t('delete') }}
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-muted">{{ __t('no_users') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($paginatedUsers['lastPage'] > 1)
    @php
        $maxPages = min($paginatedUsers['lastPage'], 5);
    @endphp
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
            {{ __t('page') }} {{ $paginatedUsers['page'] }} {{ __t('of') }} {{ $paginatedUsers['lastPage'] }}
            ({{ $paginatedUsers['total'] }} {{ __t('users_found') }})
        </small>
        <nav>
            <ul class="pagination mb-0">
                <li class="page-item {{ $paginatedUsers['page'] <= 1 ? 'disabled' : '' }}">
                    <button type="button" class="page-link" wire:click="setPage({{ $paginatedUsers['page'] - 1 }})" wire:loading.attr="disabled">{{ __t('previous') }}</button>
                </li>
                @for($i = 1; $i <= $maxPages; $i++)
                    <li class="page-item {{ $paginatedUsers['page'] == $i ? 'active' : '' }}">
                        <button type="button" class="page-link" wire:click="setPage({{ $i }})" wire:loading.attr="disabled">{{ $i }}</button>
                    </li>
                @endfor
                <li class="page-item {{ $paginatedUsers['page'] >= $paginatedUsers['lastPage'] ? 'disabled' : '' }}">
                    <button type="button" class="page-link" wire:click="setPage({{ $paginatedUsers['page'] + 1 }})" wire:loading.attr="disabled">{{ __t('next') }}</button>
                </li>
            </ul>
        </nav>
    </div>
    @endif

    @if($showModal)
    <div class="modal-backdrop fade show" style="display: block;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingUser ? __t('edit_user') : __t('add_user') }}</h5>
                    <button wire:click="closeModal()" type="button" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __t('firstname') }}</label>
                        <input type="text" wire:model="formData.firstname" class="form-control @error('formData.firstname') is-invalid @enderror">
                        @error('formData.firstname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __t('lastname') }}</label>
                        <input type="text" wire:model="formData.lastname" class="form-control @error('formData.lastname') is-invalid @enderror">
                        @error('formData.lastname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __t('email') }}</label>
                        <input type="email" wire:model="formData.email" class="form-control @error('formData.email') is-invalid @enderror">
                        @error('formData.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __t('username') }}</label>
                        <input type="text" wire:model="formData.username" class="form-control @error('formData.username') is-invalid @enderror">
                        @error('formData.username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    @if(!$editingUser)
                    <div class="mb-3">
                        <label class="form-label">{{ __t('password') }}</label>
                        <input type="password" wire:model="formData.password" class="form-control @error('formData.password') is-invalid @enderror">
                        @error('formData.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    @else
                    <div class="mb-3">
                        <label class="form-label">{{ __t('password') }} ({{ __t('password_hint') }})</label>
                        <input type="password" wire:model="formData.password" class="form-control @error('formData.password') is-invalid @enderror">
                        @error('formData.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button wire:click="closeModal()" type="button" class="btn btn-secondary">{{ __t('cancel') }}</button>
                    <button wire:click="saveUser()" type="button" class="btn btn-primary" wire:loading.attr="disabled">
                        <i class="bi bi-check-circle" wire:loading.remove></i>
                        <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                        {{ __t('save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>