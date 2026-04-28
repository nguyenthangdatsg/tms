<div>
    @if($course)
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">{{ $course->fullname }}</h2>
            <button wire:click="openModal()" class="btn btn-primary" wire:loading.attr="disabled">
                <i class="bi bi-pencil" wire:loading.remove></i>
                <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                {{ __t('edit') }}
            </button>
        </div>
    </div>

    <div class="row">
        @if($courseImage)
        <div class="col-md-4 mb-4">
            <div class="content-card h-100">
                <div class="content-card-body p-0">
                     <img src="/tms/course/{{ $course->id }}/image" alt="{{ __t('current_image') }}" class="img-fluid rounded" style="width: 100%; height: 200px; object-fit: cover;" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-{{ $courseImage ? '8' : '12' }} mb-4">
            <div class="content-card">
                <div class="content-card-header">
                    <h5 class="mb-0">{{ __t('course_info') }}</h5>
                </div>
                <div class="content-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>{{ __t('id') }}:</strong> {{ $course->id }}</p>
                            <p><strong>{{ __t('course_code') }}:</strong> 
                                @if(!empty($course->catalogue_code))
                                <span class="badge bg-info">{{ $course->catalogue_code }}</span>
                                @else
                                {{ $course->shortname ?? '-' }}
                                @endif
                            </p>
                            <p><strong>{{ __t('course_type') }}:</strong> 
                                <span class="badge bg-{{ $courseType == 'Online' ? 'primary' : ($courseType == 'Offline' ? 'secondary' : 'warning') }}">
                                    {{ $courseType }}
                                </span>
                            </p>
                            <p><strong>{{ __t('start_date') }}:</strong> {{ $course->startdate ? date('d/m/Y', $course->startdate) : '-' }}</p>
                            <p><strong>{{ __t('end_date') }}:</strong> {{ $course->enddate ? date('d/m/Y', $course->enddate) : '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>{{ __t('status') }}:</strong> 
                                <span class="badge bg-{{ $course->visible ? 'success' : 'danger' }}">
                                    {{ $course->visible ? __t('visible') : __t('hidden') }}
                                </span>
                            </p>
                            <p><strong>{{ __t('created_at') }}:</strong> {{ date('d/m/Y', $course->timecreated) }}</p>
                            <p><strong>{{ __t('updated_at') }}:</strong> {{ date('d/m/Y', $course->timemodified) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card mb-4">
        <div class="content-card-header">
            <h5 class="mb-0">{{ __t('description') }}</h5>
        </div>
        <div class="content-card-body">
            {!! $course->summary ?? __t('no_description') !!}
        </div>
    </div>

    @if($showModal)
    <div class="modal-backdrop fade show" style="display: block;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __t('edit_course') }}</h5>
                    <button wire:click="closeModal()" type="button" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __t('course_name') }}</label>
                        <input type="text" wire:model="formData.fullname" class="form-control @error('formData.fullname') is-invalid @enderror">
                        @error('formData.fullname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __t('course_code') }}</label>
                        <input type="text" wire:model="formData.shortname" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __t('start_date') }}</label>
                                <input type="date" wire:model="formData.startdate" class="form-control @error('formData.startdate') is-invalid @enderror">
                                @error('formData.startdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __t('end_date') }}</label>
                                <input type="date" wire:model="formData.enddate" class="form-control @error('formData.enddate') is-invalid @enderror">
                                @error('formData.enddate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __t('course_type') }}</label>
                        <select wire:model="courseType" class="form-select">
                            <option value="Online">{{ __t('type_online') }}</option>
                            <option value="Offline">{{ __t('type_offline') }}</option>
                            <option value="Blended">{{ __t('type_blended') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __t('course_image') }}</label>
                        @if($courseImage)
                        <div class="mb-2">
                            <img src="/tms/course/{{ $course->id }}/image" alt="Current image" class="img-thumbnail" style="max-height: 100px;" onerror="this.style.display='none'">
                        </div>
                        @endif
                        <input type="file" wire:model="newImage" class="form-control" accept="image/*">
                         @if($newImage)
                         <div class="mt-2">
                             <img src="{{ $newImage->temporaryUrl() }}" alt="{{ __t('new_image') }}" class="img-thumbnail" style="max-height: 100px;">
                         </div>
                         @endif
                    </div>
                      <div class="mb-3">
                          <label class="form-label">{{ __t('description') }}</label>
                          <textarea id="description" name="formData.summary" wire:model.live="formData.summary" class="form-control" rows="6"></textarea>
                      </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __t('status') }}</label>
                        <select wire:model="formData.visible" class="form-select">
                            <option value="1">{{ __t('visible') }}</option>
                            <option value="0">{{ __t('hidden') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeModal()" type="button" class="btn btn-secondary">{{ __t('cancel') }}</button>
                    <button wire:click="saveCourse()" type="button" class="btn btn-primary" wire:loading.attr="disabled">
                         <i class="bi bi-check-circle" wire:loading.remove></i>
                         <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                         {{ __t('save') }}
                     </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @else
    <div class="content-card">
        <div class="content-card-body text-center text-muted py-5">
            <i class="bi bi-exclamation-circle d-block mb-2" style="font-size: 2rem;"></i>
            {{ __t('course_not_found') }}
        </div>
    </div>
    @endif
</div>
