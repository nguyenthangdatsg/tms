<div>
    <div wire:loading.remove>
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h2 class="mb-0">{{ __t('exam_management') }}</h2>
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
                    <table class="table table-hover table-responsive-md mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 py-3">{{ __t('quiz_name') }}</th>
                                <th class="px-3 py-3 hide-mobile">{{ __t('course_name') }}</th>
                                <th class="px-3 py-3 hide-mobile">{{ __t('start_date') }}</th>
                                <th class="px-3 py-3 hide-mobile">{{ __t('end_date') }}</th>
                                <th class="px-3 py-3 text-center">{{ __t('participants') }}</th>
                                <th class="px-3 py-3 text-center hide-mobile">{{ __t('completed') }}</th>
                                <th class="px-3 py-3 text-center hide-mobile">{{ __t('status') }}</th>
                                <th class="px-3 py-3 text-center">{{ __t('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        
                        @foreach($paginatedQuizzes['quizzes'] ?? [] as $quiz)
                        <tr>
                            <td class="px-3 py-3 fw-medium">
                                <a href="#" wire:click.prevent="openConfigModal({{ $quiz->id }})" class="text-decoration-none">
                                    {{ $quiz->name }} <i class="bi bi-gear ms-1"></i>
                                </a>
                            </td>
                            <td class="px-3 py-3 hide-mobile">
                                <a href="{{ config('app.moodle_url') }}/course/view.php?id={{ $quiz->course }}" target="_blank" class="text-decoration-none">
                                    {{ $quiz->course_name }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </a>
                            </td>
                            <td class="px-3 py-3 hide-mobile">
                                {{ $quiz->timeopen ? date('d/m/Y H:i', $quiz->timeopen) : '-' }}
                            </td>
                            <td class="px-3 py-3 hide-mobile">
                                {{ $quiz->timeclose ? date('d/m/Y H:i', $quiz->timeclose) : '-' }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="badge bg-primary">{{ $quiz->participants ?? 0 }}</span>
                            </td>
                            <td class="px-3 py-3 text-center hide-mobile">
                                <span class="badge bg-success">{{ $quiz->completed ?? 0 }}</span>
                            </td>
                            <td class="px-3 py-3 text-center hide-mobile">
                                @if($quiz->status == 'active')
                                    <span class="badge bg-success">{{ __t('active') }}</span>
                                @elseif($quiz->status == 'upcoming')
                                    <span class="badge bg-warning">{{ __t('upcoming') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __t('closed') }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-info btn-xs" title="{{ __t('view') }}" wire:click="openViewModal({{ $quiz->id }})"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-outline-warning btn-xs" title="{{ __t('edit') }}" wire:click="openEditModal({{ $quiz->id }})"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-outline-success btn-xs" title="{{ __t('enroll') }}" wire:click="openEnrollModal({{ $quiz->id }})">
                                        <i class="bi bi-person-plus"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-xs" title="{{ __t('delete') }}" wire:click="deleteQuiz({{ $quiz->id }})"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                            @if(empty($paginatedQuizzes['quizzes'] ?? []))
                            <tr><td colspan="8" class="text-center text-muted py-4">{{ __t('no_quizzes') ?? 'No quizzes' }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if(isset($paginatedQuizzes) && $paginatedQuizzes['lastPage'] > 1)
        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
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
    </div>

    @include('livewire.exam.view-modal')
    @include('livewire.exam.form-modal')
    @include('livewire.exam.config-modal')
    @include('livewire.exam.enroll-modal')

    @livewire('enrolment', key('enrolment-exam'))
</div>