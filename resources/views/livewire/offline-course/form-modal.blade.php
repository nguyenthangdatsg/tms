@if($showModal)
<div class="modal-backdrop fade show" style="display: block;"></div>
<div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editingCourse ? __t('edit_course') : __t('add_course') }}</h5>
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
                    <select wire:model="formData.shortname" class="form-select">
                        <option value="">-- Select Code --</option>
                        @foreach($availableCodes as $c)
                        <option value="{{ is_array($c) ? $c['code'] : $c->code }}">{{ is_array($c) ? $c['code'] : $c->code }} - {{ is_array($c) ? $c['name'] : $c->name }}</option>
                        @endforeach
                    </select>
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
                <div class="mb-3">
                    <label class="form-label">{{ __t('course_image') }}</label>
                    <input type="file" wire:model="newImage" class="form-control" accept="image/*">
                    @if($newImage)
                    <div class="mt-2">
                            <img src="{{ $newImage->temporaryUrl() }}" alt="{{ __t('image_preview') }}" class="img-thumbnail" style="max-height: 100px;">
                    </div>
                    @endif
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