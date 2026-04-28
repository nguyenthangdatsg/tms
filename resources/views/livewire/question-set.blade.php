<div>
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">{{ __t('question_sets') }}</h2>
            <button wire:click="openAddModal()" class="btn btn-primary" wire:loading.attr="disabled">
                <i class="bi bi-plus-circle" wire:loading.remove></i>
                <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                {{ __t('add_question_set') }}
            </button>
        </div>
    </div>

    <div class="content-card mb-4">
        <div class="content-card-body p-0">
            <div class="d-flex align-items-center justify-content-between p-2">
                <div class="flex-grow-1">
                    <input type="text" class="form-control" placeholder="{{ __t('search_question_set_placeholder') }}" wire:model.live="search">
                </div>
                <div class="ms-3">
                    <select class="form-select" wire:model.live="perPage">
                        <option value="10">{{ __t('per_page_10') }}</option>
                        <option value="20">{{ __t('per_page_20') }}</option>
                        <option value="50">{{ __t('per_page_50') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="content-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">{{ __t('id') }}</th>
                            <th class="px-4 py-3">{{ __t('question_set_name') }}</th>
                            <th class="px-4 py-3">{{ __t('description') }}</th>
                            <th class="px-4 py-3">{{ __t('question_count') }}</th>
                            <th class="px-4 py-3">{{ __t('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedSets['sets'] ?? [] as $set)
                        <tr>
                            <td class="px-4 py-3">{{ $set->id }}</td>
                            <td class="px-4 py-3 fw-medium">{{ $set->name }}</td>
                            <td class="px-4 py-3 text-muted" style="max-width: 300px;">
                                {{ $set->description ?? '' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge bg-primary">{{ $set->question_count ?? 0 }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-warning" title="{{ __t('edit') }}" wire:click="openEditModal({{ $set->id }})"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-outline-danger" title="{{ __t('delete') }}" wire:click="deleteSet({{ $set->id }})"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if(empty($paginatedSets['sets'] ?? []))
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __t('no_question_sets') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(isset($paginatedSets) && $paginatedSets['lastPage'] > 1)
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
            {{ __t('page') }} {{ $paginatedSets['page'] ?? 1 }} {{ __t('of') }} {{ $paginatedSets['lastPage'] ?? 1 }}
            ({{ $paginatedSets['total'] ?? 0 }} {{ __t('sets') }})
        </small>
    </div>
    @endif

    @if($showModal)
    <div class="modal-backdrop fade show" style="display: block;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingSet ? __t('edit_question_set') : __t('add_question_set') }}</h5>
                    <button wire:click="closeModal()" type="button" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __t('question_set_name') }}</label>
                            <input type="text" class="form-control @error('formData.name') is-invalid @enderror" wire:model="formData.name" placeholder="{{ __t('question_set_name') }}">
                            @error('formData.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __t('description') }}</label>
                            <textarea class="form-control" wire:model="formData.description" rows="2" placeholder="{{ __t('description') }}"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __t('select_questions') }}</label>
                            @error('selectedQuestions') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                @if(count($availableQuestions) > 0)
                                    @foreach($availableQuestions as $question)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               wire:model="selectedQuestions" 
                                               value="{{ $question->id }}" 
                                               id="sq_{{ $question->id }}">
                                        <label class="form-check-label" for="sq_{{ $question->id }}">
                                            {{ $question->name }} ({{ $question->qtype_name }})
                                        </label>
                                    </div>
                                    @endforeach
                                @else
                                <p class="text-muted">{{ __t('no_questions_in_bank') }}</p>
                                @endif
                            </div>
                            <small class="text-muted">{{ __t('selected_count') }}: {{ count($selectedQuestions) }}</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="closeModal()">{{ __t('cancel') }}</button>
                    <button type="button" class="btn btn-primary" wire:click="saveSet()" wire:loading.attr="disabled">
                        <span wire:loading.remove><i class="bi bi-check-circle me-2"></i>{{ __t('save') }}</span>
                        <span wire:loading><i class="bi bi-hourglass-split me-2"></i>{{ __t('processing') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>