<div>
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">{{ __t('online_course') }}</h2>
            <button wire:click="openAddModal()" class="btn btn-primary" wire:loading.attr="disabled">
                <i class="bi bi-plus-circle" wire:loading.remove></i>
                <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                {{ __t('add_course') }}
            </button>
        </div>
    </div>

    <div class="content-card mb-4">
        <div class="content-card-body">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" wire:model.live="search" placeholder="{{ __t('search_course') }}" class="form-control">
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
        </div>
    </div>

    <div class="content-card">
        <div class="content-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-responsive-md mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-3">{{ __t('id') }}</th>
                            <th class="px-3 py-3 hide-mobile">{{ __t('shortname') }}</th>
                            <th class="px-3 py-3">{{ __t('course_code') }}</th>
                            <th class="px-3 py-3">{{ __t('course_name') }}</th>
                            <th class="px-3 py-3 hide-mobile">{{ __t('start_date') }}</th>
                            <th class="px-3 py-3 hide-mobile">{{ __t('end_date') }}</th>
                            <th class="px-3 py-3 text-center">{{ __t('participants') }}</th>
                            <th class="px-3 py-3 text-center hide-mobile">{{ __t('completed') }}</th>
                            <th class="px-3 py-3 text-center">{{ __t('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        @forelse($paginatedCourses['courses'] as $course)
                        <tr>
                            <td class="px-3 py-3">{{ $course->id }}</td>
                            <td class="px-3 py-3 hide-mobile">{{ $course->shortname ?? '-' }}</td>
                            <td class="px-3 py-3">
                                <span class="badge bg-info">{{ $course->catalogue_code ?? '-' }}</span>
                            </td>
                            <td class="px-3 py-3 fw-medium">
                                <a href="{{ config('app.moodle_url') }}/course/view.php?id={{ $course->id }}" target="_blank" class="text-decoration-none">
                                    {{ $course->fullname }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </a>
                            </td>
                            <td class="px-3 py-3 hide-mobile">{{ $course->startdate ? date('d/m/Y', $course->startdate) : '-' }}</td>
                            <td class="px-3 py-3 hide-mobile">{{ $course->enddate ? date('d/m/Y', $course->enddate) : '-' }}</td>
                            <td class="px-3 py-3 text-center">
                                <span class="badge bg-primary">{{ $course->participants ?? 0 }}</span>
                            </td>
                            <td class="px-3 py-3 text-center hide-mobile">
                                <span class="badge bg-success">{{ $course->completed ?? 0 }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="btn-group btn-group-sm mobile-btn-group">
                                    <a href="/tms/course/{{ $course->id }}" class="btn btn-outline-primary btn-xs" title="{{ __t('detail') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button wire:click="openEnrollModal({{ $course->id }})" class="btn btn-outline-success btn-xs" title="{{ __t('enroll') }}">
                                        <i class="bi bi-person-plus"></i>
                                    </button>
                                    <button wire:click="openEditModal({{ $course->id }})" class="btn btn-outline-warning btn-xs" title="{{ __t('edit') }}" wire:loading.attr="disabled">
                                        <i class="bi bi-pencil" wire:loading.remove></i>
                                        <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                                    </button>
                                    <button wire:click="confirmDelete({{ $course->id }})" class="btn btn-outline-danger btn-xs" title="{{ __t('delete') }}" wire:loading.attr="disabled">
                                        <i class="bi bi-trash" wire:loading.remove></i>
                                        <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-4 text-center text-muted">{{ __t('no_online_courses') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($paginatedCourses['lastPage'] > 1)
    @php
        $maxPages = min($paginatedCourses['lastPage'], 5);
    @endphp
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
            {{ __t('page') }} {{ $paginatedCourses['page'] }} {{ __t('of') }} {{ $paginatedCourses['lastPage'] }}
            ({{ $paginatedCourses['total'] }} {{ __t('courses_found') }})
        </small>
        <nav>
            <ul class="pagination mb-0">
                <li class="page-item {{ $paginatedCourses['page'] <= 1 ? 'disabled' : '' }}">
                    <button type="button" class="page-link" wire:click="setPage({{ $paginatedCourses['page'] - 1 }})" wire:loading.attr="disabled">{{ __t('previous') }}</button>
                </li>
                @for($i = 1; $i <= $maxPages; $i++)
                    <li class="page-item {{ $paginatedCourses['page'] == $i ? 'active' : '' }}">
                        <button type="button" class="page-link" wire:click="setPage({{ $i }})" wire:loading.attr="disabled">{{ $i }}</button>
                    </li>
                @endfor
                <li class="page-item {{ $paginatedCourses['page'] >= $paginatedCourses['lastPage'] ? 'disabled' : '' }}">
                    <button type="button" class="page-link" wire:click="setPage({{ $paginatedCourses['page'] + 1 }})" wire:loading.attr="disabled">{{ __t('next') }}</button>
                </li>
            </ul>
        </nav>
    </div>
    @endif

    @include('livewire.online-course.form-modal')
    @include('livewire.online-course.delete-modal')
    @include('livewire.online-course.enroll-modal')
</div>