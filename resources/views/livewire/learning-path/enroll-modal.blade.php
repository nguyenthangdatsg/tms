<div class="modal-backdrop fade show" style="display: block;"></div>
<div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __t('enroll_students') }}</h5>
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
                    </ul>
                </div>
                
                @if($activeEnrollTab == 1)
                <div class="mb-3">
                    <label class="form-label">{{ __t('enrolled_users') }}</label>
                    <input type="text" class="form-control mb-2" placeholder="{{ __t('search') }}..." wire:model.live="enrollSearch">
                    @if(count($enrolledUsers) > 0)
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
                                               value="{{ $user->u_id }}">
                                    </td>
                                    <td>{{ $user->firstname ?? '' }} {{ $user->lastname ?? '' }}</td>
                                    <td><small class="text-muted">{{ $user->email ?? '' }}</small></td>
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
                        <small class="text-muted">{{ __t('enrolled_count') }}: {{ count($enrolledUsers) }}</small>
                        <button class="btn btn-sm btn-danger" wire:click="unenrolSelectedUsers()">
                            {{ __t('unenrol_selected') }}
                        </button>
                    </div>
                    @else
                    <p class="text-muted p-3">{{ __t('no_enrolled_users') }}</p>
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
                        <p class="text-muted">{{ __t('no_users_found') }}</p>
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
                    <button class="btn btn-success btn-sm mb-3" wire:click="enrolCohort()" wire:loading.attr="disabled">
                        <i class="bi bi-plus-circle me-1"></i>{{ __t('enroll') }}
                    </button>
                    
                    @if(count($enrolledCohorts) > 0)
                    <label class="form-label">{{ __t('enrolled_cohorts') }}</label>
                    <div class="border rounded p-3">
                        @foreach($enrolledCohorts as $ec)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                {{ $ec->name }}
                                <span class="badge bg-info">{{ __t('cohort') }}</span>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" wire:click="unenrolCohort({{ $ec->cohort_id }})" title="{{ __t('unenrol') }}">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    <small class="text-muted d-block mt-2">{{ __t('enrolled_cohort_count') }}: {{ count($enrolledCohorts) }}</small>
                    @endif
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" wire:click="closeEnrollModal()">{{ __t('cancel') }}</button>
                @if($activeEnrollTab == 2)
                <button type="button" class="btn btn-success" wire:click="enrollUsers()" wire:loading.attr="disabled">
                    <i class="bi bi-person-plus me-2"></i>{{ __t('enroll') }}
                </button>
                @endif
            </div>
        </div>
    </div>
</div>