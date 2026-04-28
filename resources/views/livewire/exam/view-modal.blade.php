@if($showViewModal && $viewingQuiz)
<div class="modal-backdrop fade show" style="display: block;"></div>
<div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __t('quiz_details') }}: {{ $viewingQuiz->name }}</h5>
                <button wire:click="closeViewModal()" type="button" class="btn-close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeViewTab == 1 ? 'active' : '' }}" wire:click="setViewTab(1)" type="button" role="tab">{{ __t('quiz_settings') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeViewTab == 2 ? 'active' : '' }}" wire:click="setViewTab(2)" type="button" role="tab">{{ __t('question_bank') }}</button>
                            </li>
                        </ul>
                    </div>

                    @if($activeViewTab == 1)
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('course') }}</label>
                        <p class="form-control-plaintext">
                            <a href="{{ config('app.moodle_url') }}/course/view.php?id={{ $viewingQuiz->course }}" target="_blank" class="text-decoration-none">
                                {{ $viewingQuiz->course_name }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('course_type') }}</label>
                        <p class="form-control-plaintext">
                            @if($viewingQuiz->course_type == 'exam')
                                <span class="badge bg-primary">Kỳ thi</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($viewingQuiz->course_type ?? 'N/A') }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('timelimit') }}</label>
                        <p class="form-control-plaintext">{{ $viewingQuiz->timelimit ? $viewingQuiz->timelimit . ' seconds' : 'No limit' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('start_date') }}</label>
                        <p class="form-control-plaintext">{{ $viewingQuiz->timeopen ? date('d/m/Y H:i', $viewingQuiz->timeopen) : 'Not set' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('end_date') }}</label>
                        <p class="form-control-plaintext">{{ $viewingQuiz->timeclose ? date('d/m/Y H:i', $viewingQuiz->timeclose) : 'Not set' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __t('attempts') }}</label>
                        <p class="form-control-plaintext">{{ $viewingQuiz->attempts ?? 1 }}</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __t('description') }}</label>
                        <p class="form-control-plaintext">{!! $viewingQuiz->intro ?? 'None' !!}</p>
                    </div>
                    @elseif($activeViewTab == 2)
                    <div class="col-12">
                        <label class="form-label">{{ __t('questions_in_quiz') }}</label>
                        @if(count($selectedQuestions) > 0)
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <ul class="list-group">
                                @foreach($selectedQuestions as $sq)
                                <li class="list-group-item">{{ $sq->name ?? 'Question #' . $sq->id }} ({{ $sq->qtype_name ?? $sq->qtype }})</li>
                                @endforeach
                            </ul>
                        </div>
                        @else
                        <p class="text-muted">No questions in this quiz</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" wire:click="closeViewModal()">{{ __t('close') }}</button>
                <button type="button" class="btn btn-primary" wire:click="openEditModal({{ $viewingQuiz->id }}); closeViewModal();">{{ __t('edit') }}</button>
            </div>
        </div>
    </div>
</div>
@endif