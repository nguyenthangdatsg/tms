<div>
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">{{ __t('learning_path') }}</h2>
            <button wire:click="openAddModal()" class="btn btn-primary" wire:loading.attr="disabled">
                <i class="bi bi-plus-circle" wire:loading.remove></i>
                <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                {{ __t('add_learning_path') }}
            </button>
        </div>
    </div>

    <div class="content-card mb-4">
        <div class="content-card-body">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" wire:model.live="search" placeholder="{{ __t('search_learning_path') }}" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="content-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-responsive-md mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-3">{{ __t('id') }}</th>
                            <th class="px-3 py-3">{{ __t('learning_path_name') }}</th>
                            <th class="px-3 py-3 hide-mobile">{{ __t('start_date') }}</th>
                            <th class="px-3 py-3 hide-mobile">{{ __t('end_date') }}</th>
                            <!--th class="px-3 py-3 hide-mobile">{{ __t('credit') }}</th-->
                            <th class="px-3 py-3">{{ __t('participants') }}</th>
                            <th class="px-3 py-3">{{ __t('status') }}</th>
                            <th class="px-3 py-3 text-center">{{ __t('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($learningPaths as $path)
                        <tr>
                            <td class="px-3 py-3">{{ $path->id }}</td>
                            <td class="px-3 py-3 fw-medium">{{ $path->name }}</td>
                            <td class="px-3 py-3 hide-mobile">{{ $path->startdate ? date('d/m/Y', $path->startdate) : '-' }}</td>
                            <td class="px-3 py-3 hide-mobile">{{ $path->enddate ? date('d/m/Y', $path->enddate) : '-' }}</td>
                            <!--td class="px-3 py-3 hide-mobile">{{ $path->credit ?? 0 }}</td-->
                            <td class="px-3 py-3">
                                <span class="badge bg-info">{{ $path->enrolled_count ?? 0 }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="badge bg-{{ $path->published ? 'success' : 'secondary' }}">
                                    {{ $path->published ? __t('published') : __t('draft') }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <div class="btn-group btn-group-sm mobile-btn-group">
                                    <button wire:click="openCoursesModal({{ $path->id }})" class="btn btn-outline-primary btn-xs" title="{{ __t('courses') }}" wire:loading.attr="disabled">
                                        <i class="bi bi-book" wire:loading.remove></i>
                                        <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                                    </button>
                                    <button wire:click="openEnrollModal({{ $path->id }})" class="btn btn-outline-success btn-xs" title="{{ __t('enroll_students') }}" wire:loading.attr="disabled">
                                        <i class="bi bi-person-plus"></i>
                                    </button>
                                    <button wire:click="openEditModal({{ $path->id }})" class="btn btn-outline-warning btn-xs" title="{{ __t('edit') }}" wire:loading.attr="disabled">
                                        <i class="bi bi-pencil" wire:loading.remove></i>
                                        <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                                    </button>
                                    <button wire:click="openNotificationModal({{ $path->id }})" class="btn btn-outline-secondary btn-xs" title="{{ __t('notification_settings') }}" wire:loading.attr="disabled">
                                        <i class="bi bi-bell" wire:loading.remove></i>
                                        <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                                    </button>
                                    <button wire:click="openProgressModal({{ $path->id }})" class="btn btn-outline-info btn-xs" title="{{ __t('progress') }}" wire:loading.attr="disabled">
                                        <i class="bi bi-graph-up" wire:loading.remove></i>
                                        <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                                    </button>
                                    <button wire:click="confirmDelete({{ $path->id }})" class="btn btn-outline-danger btn-xs" title="{{ __t('delete') }}" wire:loading.attr="disabled">
                                        <i class="bi bi-trash" wire:loading.remove></i>
                                        <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-4 text-center text-muted">{{ __t('no_learning_paths') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showModal)
    <div class="modal-backdrop fade show" style="display: block;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingPath ? __t('edit_learning_path') : __t('add_learning_path') }}</h5>
                    <button wire:click="closeModal()" type="button" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __t('learning_path_name') }}</label>
                        <input type="text" wire:model="formData.name" class="form-control @error('formData.name') is-invalid @enderror">
                        @error('formData.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                     <div class="mb-3">
                         <label class="form-label">{{ __t('description') }}</label>
                         <textarea id="description" name="formData.description" wire:model.live="formData.description" class="form-control" rows="4"></textarea>
                     </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __t('start_date') }}</label>
                                <input type="date" wire:model="formData.startdate" class="form-control @error('formData.startdate') is-invalid @enderror">
                                @error('formData.startdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __t('end_date') }}</label>
                                <input type="date" wire:model="formData.enddate" class="form-control @error('formData.enddate') is-invalid @enderror">
                                @error('formData.enddate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __t('credit') }}</label>
                                <input type="number" wire:model="formData.credit" class="form-control @error('formData.credit') is-invalid @enderror">
                                @error('formData.credit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __t('status') }}</label>
                                <select wire:model="formData.published" class="form-select">
                                    <option value="0">{{ __t('draft') }}</option>
                                    <option value="1">{{ __t('published') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeModal()" type="button" class="btn btn-secondary">{{ __t('cancel') }}</button>
                    <button wire:click="saveLearningPath()" type="button" class="btn btn-primary" wire:loading.attr="disabled">
                         <i class="bi bi-check-circle" wire:loading.remove></i>
                         <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                         {{ __t('save') }}
                     </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showReportModal && $selectedPathId)
    @include('livewire.learning-path.report-modal')
    @endif

    @if($showCoursesModal)
    @include('livewire.learning-path.courses-modal')
    @endif

    @if($deletingPathId)
    <div class="modal-backdrop fade show" style="display: block;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __t('confirm_delete_learning_path') }}</h5>
                </div>
                <div class="modal-body">
                    <p>{{ __t('confirm_delete_learning_path_message') }}</p>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeDeleteModal()" type="button" class="btn btn-secondary">{{ __t('cancel') }}</button>
                    <button wire:click="deleteLearningPath()" type="button" class="btn btn-danger" wire:loading.attr="disabled">
                         <i class="bi bi-trash" wire:loading.remove></i>
                         <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                         {{ __t('delete') }}
                     </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showEnrollModal && $selectedPathId)
    @include('livewire.learning-path.enroll-modal')
    @endif

    @if($showNotificationModal)
    @include('livewire.learning-path.notification-modal')
    @endif
</div>