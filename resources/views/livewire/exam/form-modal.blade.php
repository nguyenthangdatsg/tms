@if($showModal)
<div class="modal-backdrop fade show" style="display: block;"></div>
<div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editingQuiz ? __t('edit_quiz') : __t('add_quiz') }}</h5>
                <button wire:click="closeModal()" type="button" class="btn-close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeConfigTab == 1 ? 'active' : '' }}" wire:click="setConfigActive(1)" type="button" role="tab">{{ __t('quiz_settings') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeConfigTab == 2 ? 'active' : '' }}" wire:click="setConfigActive(2)" type="button" role="tab">{{ __t('question_bank') }}</button>
                            </li>
                        </ul>
                    </div>
                    
                    @if($activeConfigTab == 1)
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('course') }}</label>
                        <select class="form-select" wire:model="formData.course" required>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->fullname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('quiz_name') }}</label>
                        <input type="text" class="form-control" wire:model="formData.name" placeholder="{{ __t('quiz_name') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __t('description') }}</label>
                        <textarea class="form-control" wire:model="formData.intro" rows="3" placeholder="{{ __t('description') }}"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('start_date') }}</label>
                        <input type="datetime-local" class="form-control @error('formData.timeopen') is-invalid @enderror" wire:model="formData.timeopen">
                        @error('formData.timeopen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('end_date') }}</label>
                        <input type="datetime-local" class="form-control @error('formData.timeclose') is-invalid @enderror" wire:model="formData.timeclose">
                        @error('formData.timeclose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('timelimit') }} (giây)</label>
                        <input type="number" class="form-control @error('formData.timelimit') is-invalid @enderror" wire:model="formData.timelimit" min="0" placeholder="0">
                        @error('formData.timelimit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('attempts') }}</label>
                        <input type="number" class="form-control @error('formData.attempts') is-invalid @enderror" wire:model="formData.attempts" min="1">
                        @error('formData.attempts') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    @elseif($activeConfigTab == 2)
                    <div class="col-12">
                        <label class="form-label">{{ __t('select_questions') }}</label>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            @if(count($availableQuestions) > 0)
                                @foreach($availableQuestions as $question)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           wire:model="selectedQuestions" 
                                           value="{{ $question->id }}" 
                                           id="q_{{ $question->id }}">
                                    <label class="form-check-label" for="q_{{ $question->id }}">
                                        {{ $question->name }} ({{ $question->qtype_name ?? $question->qtype }})
                                    </label>
                                </div>
                                @endforeach
                            @else
                            <p class="text-muted">{{ __t('no_questions_in_bank') }}</p>
                            @endif
                        </div>
                        <small class="text-muted">{{ __t('selected_count') }}: {{ count($selectedQuestions) }}</small>
                        <div class="mt-2 p-3 bg-light rounded">
                            <label class="form-label">{{ __t('import_from_set') }}</label>
                            <div class="d-flex gap-2">
                                <select class="form-select" wire:model="selectedSetId">
                                    <option value="">{{ __t('select_question_set') }}</option>
                                    @foreach($questionSets as $set)
                                        <option value="{{ $set->id }}">{{ $set->name }} ({{ $set->question_count }} câu)</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-primary" wire:click="importFromQuestionSet()" wire:loading.attr="disabled">
                                    {{ __t('import') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" wire:click="closeModal()">{{ __t('cancel') }}</button>
                <button type="button" class="btn btn-primary" wire:click="saveQuiz()" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="bi bi-check-circle me-2"></i>{{ __t('save') }}</span>
                    <span wire:loading><i class="bi bi-hourglass-split me-2"></i>{{ __t('processing') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif