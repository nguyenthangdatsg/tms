<div class="modal-backdrop fade show" style="display: block;"></div>
<div class="modal fade show d-block" tabindex="-1" style="display: block !important; z-index: 1056;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __t('manage_courses') }}</h5>
                <button wire:click="closeCoursesModal()" type="button" class="btn-close"></button>
            </div>
            <div class="modal-body">
                <div class="col-12 mb-3">
                    <ul class="nav nav-pills" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeCourseTab == 'special' ? 'active' : '' }}" wire:click="setActiveCourseTab('special')" type="button" role="tab">
                                <i class="bi bi-book me-1"></i> {{ __t('special_courses') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeCourseTab == 'catalogue' ? 'active' : '' }}" wire:click="setActiveCourseTab('catalogue')" type="button" role="tab">
                                <i class="bi bi-collection me-1"></i> {{ __t('catalogue_courses') }}
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="row">
                    <div class="col-md-5">
                        <h6>
                            @if($activeCourseTab == 'special')
                                {{ __t('available_special_courses') }}
                            @else
                                {{ __t('available_catalogue_courses') }}
                            @endif
                        </h6>
                        @if($activeCourseTab == 'special')
                        <div class="mb-2">
                            <input type="text" wire:model.live="specialSearch" placeholder="{{ __t('search_by_name') }}" class="form-control form-control-sm">
                        </div>
                        @else
                        <div class="mb-2">
                            <input type="text" wire:model.live="catalogueSearch" placeholder="{{ __t('search_by_name_or_code') }}" class="form-control form-control-sm">
                        </div>
                        @endif
                        <div class="border rounded p-2" style="max-height: 350px; overflow-y: auto;">
                            @if($activeCourseTab == 'special')
                                @forelse($this->filteredSpecialCourses as $course)
                                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                    <a href="{{ config('app.moodle_url') }}/course/view.php?id={{ $course->id }}" target="_blank" class="text-truncate text-decoration-none" style="max-width: 200px;">{{ $course->fullname }}</a>
                                     <button wire:click="addMoodleCourseToPath({{ $course->id }})" class="btn btn-sm btn-outline-primary" wire:loading.attr="disabled">
                                         <i class="bi bi-plus" wire:loading.remove></i>
                                         <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                                     </button>
                                </div>
                                @empty
                                <p class="text-muted">{{ __t('no_available_courses') }}</p>
                                @endforelse
                            @else
                                @forelse($this->filteredCatalogueCourses as $course)
                                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                    <div>
                                        @if(isset($course->id))
                                            <a href="{{ config('app.moodle_url') }}/course/view.php?id={{ $course->id }}" target="_blank" class="text-decoration-none">
                                                <span class="text-truncate d-block" style="max-width: 150px;">{{ $course->fullname ?: $course->code }}</span>
                                            </a>
                                        @else
                                            <span class="text-truncate d-block" style="max-width: 150px;">{{ $course->fullname ?: $course->code }}</span>
                                        @endif
                                        <small class="text-muted">{{ $course->code }}</small>
                                    </div>
                                     <button wire:click="addCatalogueCourseToPath('{{ $course->code }}')" class="btn btn-sm btn-outline-primary" wire:loading.attr="disabled">
                                         <i class="bi bi-plus" wire:loading.remove></i>
                                         <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                                     </button>
                                </div>
                                @empty
                                <p class="text-muted">{{ __t('no_available_courses') }}</p>
                                @endforelse
                            @endif
                        </div>
                    </div>
                      <div class="col-md-7">
                          <h6>{{ __t('assigned_courses') }}</h6>
                          <div class="border rounded" style="max-height: 350px; overflow-y: auto;">
                              <div class="row g-0 p-2 border-bottom bg-light fw-bold d-none d-md-flex">
                                  <div class="col-md-6">{{ __t('course') }}</div>
                                  <div class="col-md-3 text-center">{{ __t('required') }}</div>
                                  <div class="col-md-2 text-center">{{ __t('credit') }}</div>
                                  <div class="col-md-1 text-center">{{ __t('actions') }}</div>
                              </div>
                              @forelse($pathCourses as $index => $course)
                              <div class="row g-0 p-2 border-bottom align-items-center">
                                  <div class="col-md-6">
                                      @if($course->course_type == 'moodle')
                                          <a href="{{ config('app.moodle_url') }}/course/view.php?id={{ $course->course_id }}" target="_blank" class="text-decoration-none d-block text-truncate">
                                              {{ $course->fullname ?: __t('course_fallback') . ' (' . $course->course_id . ')' }}
                                          </a>
                                          <small><span class="badge bg-primary">{{ __t('special') }}</span></small>
                                      @else
                                          @if(isset($course->moodle_course_id))
                                              <a href="{{ config('app.moodle_url') }}/course/view.php?id={{ $course->moodle_course_id }}" target="_blank" class="text-decoration-none d-block text-truncate">
                                                  {{ $course->catalogue_name ?: $course->catalogue_code }}
                                              </a>
                                          @else
                                              <span class="d-block text-truncate">{{ $course->catalogue_name ?: $course->catalogue_code }}</span>
                                          @endif
                                          <small><span class="badge bg-info">{{ $course->catalogue_code }}</span></small>
                                      @endif
                                  </div>
                                   <div class="col-md-3 text-center">
                                       <div class="form-check d-flex justify-content-center">
                                           <input type="checkbox" class="form-check-input" id="required-{{ $course->id }}" 
                                               {{ $course->required ? 'checked' : '' }} 
                                               wire:click="updateCourseRequired({{ $course->id }}, {{ $course->required ? 0 : 1 }})">
                                           <label class="form-check-label ms-1" for="required-{{ $course->id }}">
                                               <span class="d-md-none">{{ __t('required') }}: </span>
                                           </label>
                                       </div>
                                   </div>
                                   <div class="col-md-2">
                                       <select wire:model="pathCourses.{{ $index }}.credit" class="form-select form-select-sm" wire:change="updateCourseCredit({{ $course->id }}, $event.target.value)">
                                           <option value="0">0</option>
                                           @for($i = 1; $i <= 10; $i++)
                                               <option value="{{ $i }}" {{ ($course->credit ?? 0) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                           @endfor
                                       </select>
                                   </div>
                                  <div class="col-md-1 text-center">
                                      <button wire:click="removeCourseFromPath({{ $course->id }})" class="btn btn-sm btn-outline-danger" wire:loading.attr="disabled">
                                          <i class="bi bi-x" wire:loading.remove></i>
                                          <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                                      </button>
                                  </div>
                              </div>
                              @empty
                              <p class="text-muted p-3 mb-0">{{ __t('no_assigned_courses') }}</p>
                              @endforelse
                          </div>
                      </div>
                </div>
            </div>
            <div class="modal-footer">
                <button wire:click="closeCoursesModal()" type="button" class="btn btn-secondary">{{ __t('close') }}</button>
                <button wire:click="saveCourses()" type="button" class="btn btn-primary" wire:loading.attr="disabled">
                    <i class="bi bi-check-circle" wire:loading.remove></i>
                    <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                    {{ __t('save') }}
                </button>
            </div>
        </div>
    </div>
</div>