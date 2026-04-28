<div>
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">{{ __t('quiz_management') }}</h2>
            <button wire:click="openAddModal()" class="btn btn-primary" wire:loading.attr="disabled">
                <i class="bi bi-plus-circle" wire:loading.remove></i>
                <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                {{ __t('add_quiz') }}
            </button>
        </div>
    </div>

    <div class="content-card mb-4">
        <div class="content-card-body p-0">
            <div class="d-flex align-items-center justify-content-between p-2">
                <div class="flex-grow-1">
                    <input type="text" class="form-control" placeholder="{{ __t('search_quiz_placeholder') }}" wire:model.live="search">
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
                            <th class="px-4 py-3">{{ __t('course_name') }}</th>
                            <th class="px-4 py-3">{{ __t('quiz_name') ?? __t('name') }}</th>
                            <th class="px-4 py-3">{{ __t('description') }}</th>
                            <th class="px-4 py-3">{{ __t('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedQuizzes['quizzes'] ?? [] as $quiz)
                        <tr>
                            <td class="px-4 py-3">{{ $quiz->id }}</td>
                            <td class="px-4 py-3">{{ $quiz->course_name }}</td>
                            <td class="px-4 py-3 fw-medium">{{ $quiz->name }}</td>
                            <td class="px-4 py-3 text-muted" style="max-width: 300px;">
                                {{ strip_tags($quiz->intro ?? '') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Quiz actions">
                                    <button class="btn btn-outline-info" title="{{ __t('view') }}" wire:click="openViewModal({{ $quiz->id }})"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-outline-warning" title="{{ __t('edit') }}" wire:click="openEditModal({{ $quiz->id }})"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-outline-danger" title="{{ __t('delete') }}" wire:click="deleteQuiz({{ $quiz->id }})"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if(empty($paginatedQuizzes['quizzes'] ?? []))
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __t('no_quizzes') ?? 'No quizzes' }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(isset($paginatedQuizzes) && $paginatedQuizzes['lastPage'] > 1)
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
            {{ __t('page') }} {{ $paginatedQuizzes['page'] ?? 1 }} {{ __t('of') }} {{ $paginatedQuizzes['lastPage'] ?? 1 }}
            ({{ $paginatedQuizzes['total'] ?? 0 }} {{ __t('quizzes') ?? 'quizzes' }})
        </small>
        <nav>
            <ul class="pagination mb-0">
                @php $currentPage = $paginatedQuizzes['page'] ?? 1; @endphp
                <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                    <button class="page-link" wire:click="setPage({{ $currentPage - 1 }})" wire:loading.attr="disabled">{{ __t('previous') }}</button>
                </li>
                <li class="page-item active"><span class="page-link">{{ $currentPage }}</span></li>
                <li class="page-item {{ $currentPage >= ($paginatedQuizzes['lastPage'] ?? 1) ? 'disabled' : '' }}">
                    <button class="page-link" wire:click="setPage({{ $currentPage + 1 }})" wire:loading.attr="disabled">{{ __t('next') }}</button>
                </li>
            </ul>
        </nav>
    </div>
    @endif

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
                        <div class="col-md-6">
                            <label class="form-label">{{ __t('course') }}</label>
                            <p class="form-control-plaintext">{{ $viewingQuiz->course_name }}</p>
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
                        <div class="col-md-6">
                            <label class="form-label">{{ __t('navmethod') }}</label>
                            <p class="form-control-plaintext">{{ $viewingQuiz->navmethod == 'free' ? 'Free' : 'Sequential' }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __t('description') }}</label>
                            <p class="form-control-plaintext">{!! $viewingQuiz->intro ?? 'None' !!}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __t('questions_in_quiz') }}</label>
                            @if(count($selectedQuestions) > 0)
                            <ul class="list-group">
                                @foreach($selectedQuestions as $sq)
                                <li class="list-group-item">{{ $sq->name ?? 'Question #' . $sq->id }} ({{ $sq->qtype_name ?? $sq->qtype }})</li>
                                @endforeach
                            </ul>
                            @else
                            <p class="text-muted">No questions in this quiz</p>
                            @endif
                        </div>
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
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $activeConfigTab == 3 ? 'active' : '' }}" wire:click="setConfigActive(3)" type="button" role="tab">{{ __t('enroll_students') }}</button>
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
                            <textarea class="form-control" wire:model.live="formData.intro" rows="4" placeholder="{{ __t('description') }}"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __t('start_date') }}</label>
                            <input type="datetime-local" class="form-control @error('formData.timeopen') is-invalid @enderror" wire:model.live="formData.timeopen">
                            @error('formData.timeopen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __t('end_date') }}</label>
                            <input type="datetime-local" class="form-control @error('formData.timeclose') is-invalid @enderror" wire:model.live="formData.timeclose">
                            @error('formData.timeclose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __t('timelimit') }}</label>
                            <input type="number" class="form-control @error('formData.timelimit') is-invalid @enderror" wire:model.live="formData.timelimit" min="0">
                            @error('formData.timelimit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __t('attempts') }}</label>
                            <input type="number" class="form-control @error('formData.attempts') is-invalid @enderror" wire:model.live="formData.attempts" min="1">
                            @error('formData.attempts') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __t('navmethod') }}</label>
                            <select class="form-select" wire:model.live="formData.navmethod">
                                <option value="free">{{ __('Free') }}</option>
                                <option value="seq">{{ __('Sequential') }}</option>
                            </select>
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
                        @elseif($activeConfigTab == 3)
                        <div class="col-12">
                            <label class="form-label">{{ __t('enroll_students') }}</label>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                @if(count($allUsers) > 0)
                                    @foreach($allUsers as $user)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               wire:model="enrolledUserIds" 
                                               value="{{ $user->id }}" 
                                               id="u_{{ $user->id }}">
                                        <label class="form-check-label" for="u_{{ $user->id }}">
                                            {{ $user->firstname }} {{ $user->lastname }} ({{ $user->username }})
                                        </label>
                                    </div>
                                    @endforeach
                                @else
                                <p class="text-muted">No users available</p>
                                @endif
                            </div>
                            <small class="text-muted">{{ __t('selected_count') }}: {{ count($enrolledUserIds) }}</small>
                            <button class="btn btn-success mt-2" wire:click="enrollStudents()" wire:loading.attr="disabled">
                                {{ __t('enroll_selected') }}
                            </button>
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
</div>
