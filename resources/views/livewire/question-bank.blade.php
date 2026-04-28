<div>
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">{{ __t('question_bank') }}</h2>
            <button wire:click="openAddModal()" class="btn btn-primary" wire:loading.attr="disabled">
                <i class="bi bi-plus-circle" wire:loading.remove></i>
                <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                {{ __t('add_question') }}
            </button>
        </div>
    </div>

    <div class="content-card mb-4">
        <div class="content-card-body p-0">
            <div class="d-flex align-items-center justify-content-between p-2">
                <div class="flex-grow-1">
                    <input type="text" class="form-control" placeholder="{{ __t('search_question_placeholder') }}" wire:model.live="search">
                </div>
                <div class="ms-3">
                    <select class="form-select" wire:model.live="perPage">
                        <option value="10">{{ __t('per_page_10') }}</option>
                        <option value="20">{{ __t('per_page_20') }}</option>
                        <option value="50">{{ __t('per_page_50') }}</option>
                        <option value="100">{{ __t('per_page_100') }}</option>
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
                            <th class="px-4 py-3">{{ __t('question_name') }}</th>
                            <th class="px-4 py-3">{{ __t('question_type') }}</th>
                            <th class="px-4 py-3">{{ __t('question_text') }}</th>
                            <th class="px-4 py-3">{{ __t('default_mark') }}</th>
                            <th class="px-4 py-3">{{ __t('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedQuestions['questions'] ?? [] as $question)
                        <tr>
                            <td class="px-4 py-3">{{ $question->id ?? '' }}</td>
                            <td class="px-4 py-3 fw-medium">{{ $question->name ?? '' }}</td>
                            <td class="px-4 py-3">{{ $question->qtype_name ?? ($question->qtype ?? '') }}</td>
                            <td class="px-4 py-3 text-muted" style="max-width: 300px;">
                                {{ isset($question->questiontext) ? strip_tags(substr($question->questiontext, 0, 100)) : '' }}
                                @if(strlen($question->questiontext ?? '') > 100)...@endif
                            </td>
                            <td class="px-4 py-3">{{ $question->defaultmark ?? 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Question actions">
                                    <button class="btn btn-outline-warning" title="{{ __t('edit') }}" wire:click="openEditModal({{ $question->id ?? 0 }})"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-outline-danger" title="{{ __t('delete') }}" wire:click="deleteQuestion({{ $question->id ?? 0 }})"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if(empty($paginatedQuestions['questions'] ?? []))
                        <tr><td colspan="6" class="text-center text-muted py-4">{{ __t('no_questions') ?? 'No questions' }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(isset($paginatedQuestions) && $paginatedQuestions['lastPage'] > 1)
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
            {{ __t('page') }} {{ $paginatedQuestions['page'] ?? 1 }} {{ __t('of') }} {{ $paginatedQuestions['lastPage'] ?? 1 }}
            ({{ $paginatedQuestions['total'] ?? 0 }} {{ __t('questions') ?? 'questions' }})
        </small>
        <nav>
            <ul class="pagination mb-0">
                <li class="page-item {{ ( $paginatedQuestions['page'] ?? 1) <= 1 ? 'disabled' : '' }}">
                    <button class="page-link" wire:click="setPage({{ ($paginatedQuestions['page'] ?? 1) - 1 }})" wire:loading.attr="disabled">{{ __t('previous') }}</button>
                </li>
                <li class="page-item active"><span class="page-link">{{ ($paginatedQuestions['page'] ?? 1) }}</span></li>
                <li class="page-item {{ ( $paginatedQuestions['page'] ?? 1) >= ($paginatedQuestions['lastPage'] ?? 1) ? 'disabled' : '' }}">
                    <button class="page-link" wire:click="setPage({{ ($paginatedQuestions['page'] ?? 1) + 1 }})"  wire:loading.attr="disabled">{{ __t('next') }}</button>
                </li>
            </ul>
        </nav>
    </div>
    @endif

    @if($showModal)
    <div class="modal-backdrop fade show" style="display: block;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingQuestion ? __t('edit_question') : __t('add_question') }}</h5>
                    <button wire:click="closeModal()" type="button" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __t('question_name') }}</label>
                            <input type="text" class="form-control @error('formData.name') is-invalid @enderror" wire:model="formData.name" placeholder="{{ __t('question_name') }}">
                            @error('formData.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __t('question_type') }}</label>
                            <select class="form-select @error('formData.qtype') is-invalid @enderror" wire:model="formData.qtype">
                                @foreach($qtypeOptions as $qtype)
                                    <option value="{{ $qtype->qtype }}">{{ $qtype->name }}</option>
                                @endforeach
                            </select>
                            @error('formData.qtype') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __t('default_mark') }}</label>
                            <input type="number" class="form-control @error('formData.defaultmark') is-invalid @enderror" wire:model="formData.defaultmark" min="0.1" step="0.1">
                            @error('formData.defaultmark') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __t('question_text') }}</label>
                            <textarea class="form-control @error('formData.questiontext') is-invalid @enderror" wire:model="formData.questiontext" rows="6" placeholder="{{ __t('question_text') }}"></textarea>
                            @error('formData.questiontext') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="closeModal()">{{ __t('cancel') }}</button>
                    <button type="button" class="btn btn-primary" wire:click="saveQuestion()" wire:loading.attr="disabled">
                        <span wire:loading.remove><i class="bi bi-check-circle me-2"></i>{{ __t('save') }}</span>
                        <span wire:loading><i class="bi bi-hourglass-split me-2"></i>{{ __t('processing') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
