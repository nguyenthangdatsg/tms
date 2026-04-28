@if($showEnrollModal && $enrollingQuiz)
<div class="modal-backdrop fade show" style="display: block;"></div>
<div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __t('enroll') }}: {{ $enrollingQuiz->name }}</h5>
                <button wire:click="closeEnrollModal()" type="button" class="btn-close"></button>
            </div>
            <div class="modal-body">
                <div class="col-12 mb-3">
                    <ul class="nav nav-pills" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeEnrollTab == 1 ? 'active' : '' }}" wire:click="setActiveEnrollTab(1)" type="button" role="tab">{{ __t('enrolled_users') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeEnrollTab == 2 ? 'active' : '' }}" wire:click="setActiveEnrollTab(2)" type="button" role="tab">{{ __t('enroll_user') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeEnrollTab == 3 ? 'active' : '' }}" wire:click="setActiveEnrollTab(3)" type="button" role="tab">{{ __t('enroll_cohort') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeEnrollTab == 4 ? 'active' : '' }}" wire:click="setActiveEnrollTab(4)" type="button" role="tab">{{ __t('import_excel') }}</button>
                        </li>
                    </ul>
                </div>
                
                @if($activeEnrollTab == 1)
                <div class="mb-3">
                    <label class="form-label">{{ __t('enrolled_users') }}</label>
                    <div class="d-flex gap-2 mb-2">
                        <input type="text" class="form-control" placeholder="{{ __t('search') }}..." wire:model.live="enrollSearch">
                        <select class="form-select" style="width: 180px;" wire:model.live="enrollMethodFilter">
                            <option value="">{{ __t('all_methods') }}</option>
                            <option value="manual">{{ __t('manual') }}</option>
                            <option value="cohort">{{ __t('cohort') }}</option>
                        </select>
                    </div>
                    @if(!empty($enrolledUserIds) && is_array($enrolledUserIds) && count($enrolledUserIds) > 0)
                    <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>{{ __t('fullname') }}</th>
                                    <th>Email</th>
                                    <th>{{ __t('enrol_method') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->filteredEnrolledUsers as $user)
                                <tr>
                                    <td>
                                        <input class="form-check-input" type="checkbox" 
                                               wire:model="unenrolUserIds" 
                                               value="{{ $user->id }}">
                                    </td>
                                    <td>{{ $user->firstname }} {{ $user->lastname }}</td>
                                    <td><small class="text-muted">{{ $user->email }}</small></td>
                                    <td>
                                        @if(($user->enrol_method ?? 'manual') === 'cohort')
                                        <span class="badge bg-info">{{ $user->cohort_name ?? __t('cohort') }}</span>
                                        @else
                                        <span class="badge bg-secondary">{{ __t('manual') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">{{ __t('enrolled_count') }}: {{ count($enrolledUserIds) }}</small>
                        <button class="btn btn-sm btn-danger" wire:click="unenrolSelectedUsers()">
                            {{ __t('unenrol_selected') }}
                        </button>
                    </div>
                    @else
                    <p class="text-muted p-3">No users enrolled yet</p>
                    @endif
                </div>
                @elseif($activeEnrollTab == 2)
                <div class="mb-3">
                    <label class="form-label">{{ __t('select_users') }}</label>
                    <input type="text" class="form-control mb-2" placeholder="{{ __t('search') }}..." wire:model.live="enrollSearch">
                    <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                        @if(count($this->filteredEnrollUsers) > 0)
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>{{ __t('fullname') }}</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->filteredEnrollUsers as $user)
                                <tr>
                                    <td>
                                        <input class="form-check-input" type="checkbox" 
                                               wire:model="selectedUserIds" 
                                               value="{{ $user->id }}" 
                                               id="select_u_{{ $user->id }}">
                                    </td>
                                    <td>{{ $user->firstname }} {{ $user->lastname }}</td>
                                    <td><small class="text-muted">{{ $user->email }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <p class="text-muted">No users found</p>
                        @endif
                    </div>
                    <small class="text-muted d-block mt-2">{{ __t('selected_count') }}: {{ count($selectedUserIds) }}</small>
                </div>
                @elseif($activeEnrollTab == 3)
                <div class="mb-3">
                    <label class="form-label">{{ __t('select_cohort') }}</label>
                    <div class="mb-3">
                        <select class="form-select" wire:model="selectedCohortId">
                            <option value="">-- {{ __t('select_cohort') }} --</option>
                            @foreach($cohorts as $cohort)
                            <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    @if(count($enrolledCohorts) > 0)
                    <label class="form-label">{{ __t('enrolled_cohorts') }}</label>
                    <div class="border rounded p-3">
                        @foreach($enrolledCohorts as $ec)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                {{ $ec->name }}
                                <span class="badge bg-info">{{ __t('cohort') }}</span>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" wire:click="unenrolCohort({{ $ec->id }})" title="{{ __t('unenrol') }}">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    <small class="text-muted d-block mt-2">{{ __t('enrolled_cohort_count') }}: {{ count($enrolledCohorts) }}</small>
                    @endif
                </div>
                @elseif($activeEnrollTab == 4)
                <div class="mb-3">
                    <label class="form-label">{{ __t('import_excel') }}</label>
                    <div class="border rounded p-3">
                        <p class="text-muted small mb-2">{{ __t('import_excel_desc') }}</p>
                        <div class="mb-2">
                            <a href="/tms/download-sample-enrolment" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-download me-1"></i>{{ __t('download_sample') }}
                            </a>
                        </div>
                        <input type="file" class="form-control" wire:model="importFile" accept=".xlsx,.xls,.csv">
                        @if($importFile)
                        <div class="mt-2">
                            <small class="text-success">{{ __t('file_selected') }}: {{ $importFile->getClientOriginalName() }}</small>
                        </div>
                        @endif
                    </div>
                    @if(!empty($importErrors))
                    <div class="alert alert-danger mt-2">
                        <strong>{{ __t('import_errors') }}:</strong>
                        <ul class="mb-0">
                            @foreach($importErrors as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @if(!empty($importSuccess))
                    <div class="alert alert-success mt-2">
                        {{ $importSuccess }}
                    </div>
                    @endif
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" wire:click="closeEnrollModal()">{{ __t('cancel') }}</button>
                @if($activeEnrollTab == 1)
                <button type="button" class="btn btn-primary" wire:click="saveEnrolledUsers()" wire:loading.attr="disabled">
                    <i class="bi bi-check2 me-2"></i>{{ __t('save_changes') }}
                </button>
                @elseif($activeEnrollTab == 2)
                <button type="button" class="btn btn-success" wire:click="enrollUsers()" wire:loading.attr="disabled">
                    <i class="bi bi-person-plus me-2"></i>{{ __t('enroll') }}
                </button>
                @elseif($activeEnrollTab == 4)
                <button type="button" class="btn btn-primary" wire:click="importFromExcel()" wire:loading.attr="disabled">
                    <i class="bi bi-upload me-2"></i>{{ __t('import') }}
                </button>
                @else
                <button type="button" class="btn btn-success" wire:click="enrolCohort()" wire:loading.attr="disabled">
                    <i class="bi bi-people me-2"></i>{{ __t('enroll_cohort') }}
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif