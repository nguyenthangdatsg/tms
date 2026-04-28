@if($deleteId)
<div class="modal-backdrop fade show" style="display: block;"></div>
<div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __t('confirm_delete_course') }}</h5>
            </div>
            <div class="modal-body">
                <p>{{ __t('confirm_delete_course_message') }}</p>
            </div>
            <div class="modal-footer">
                <button wire:click="deleteId = null" type="button" class="btn btn-secondary">{{ __t('cancel') }}</button>
                <button wire:click="deleteCourse()" type="button" class="btn btn-danger" wire:loading.attr="disabled">
                    <i class="bi bi-trash" wire:loading.remove></i>
                    <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
                    {{ __t('delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif