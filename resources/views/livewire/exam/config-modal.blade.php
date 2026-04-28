@if($showConfigModal && $configuringQuiz)
<div class="modal-backdrop fade show" style="display: block;"></div>
<div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __t('configure_quiz') }}: {{ $configuringQuiz->name }}</h5>
                <button wire:click="closeConfigModal()" type="button" class="btn-close"></button>
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
                        <p class="form-control-plaintext">{{ $configuringQuiz->course_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('quiz_name') }}</label>
                        <p class="form-control-plaintext">{{ $configuringQuiz->name }}</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __t('description') }}</label>
                        <p class="form-control-plaintext">{!! $configuringQuiz->intro ?? 'None' !!}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('start_date') }}</label>
                        <p class="form-control-plaintext">{{ $configuringQuiz->timeopen ? date('d/m/Y H:i', $configuringQuiz->timeopen) : 'Not set' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('end_date') }}</label>
                        <p class="form-control-plaintext">{{ $configuringQuiz->timeclose ? date('d/m/Y H:i', $configuringQuiz->timeclose) : 'Not set' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('timelimit') }}</label>
                        <p class="form-control-plaintext">{{ $configuringQuiz->timelimit ? $configuringQuiz->timelimit . ' seconds' : 'No limit' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('attempts') }}</label>
                        <p class="form-control-plaintext">{{ $configuringQuiz->attempts ?? 1 }}</p>
                    </div>
                    @elseif($activeConfigTab == 2)
                    <div class="col-12">
                        <label class="form-label">{{ __t('questions_in_quiz') }}</label>
                        @if(count($selectedQuestions) > 0)
                        <div class="border rounded p-3 mb-3" style="max-height: 300px; overflow-y: auto;">
                            <ul class="list-group">
                                @foreach($selectedQuestions as $sq)
                                <li class="list-group-item">{{ $sq->name ?? 'Question #' . $sq->id }} ({{ $sq->qtype_name ?? $sq->qtype }})</li>
                                @endforeach
                            </ul>
                        </div>
                        @else
                        <p class="text-muted mb-3">No questions in this quiz</p>
                        @endif
                        
                        @if(($configuringQuiz->participants ?? 0) == 0)
                        <div class="p-3 bg-light rounded">
                            <label class="form-label">{{ __t('import_from_set') }}</label>
                            <select class="form-select" wire:model="selectedSetId" wire:change="refreshQuestions()">
                                <option value="">{{ __t('select_question_set') }}</option>
                                @foreach($questionSets as $set)
                                <option value="{{ $set->id }}">{{ $set->name }} ({{ $set->question_count }} câu)</option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-1 d-block">{{ __t('select_question_set_hint') ?? 'Chọn bộ đề để thêm câu hỏi vào kỳ thi' }}</small>
                        </div>
                        @else
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ __t('cannot_change_questions') }}
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" wire:click="closeConfigModal()">{{ __t('close') }}</button>
            </div>
        </div>
    </div>
</div>
@endif