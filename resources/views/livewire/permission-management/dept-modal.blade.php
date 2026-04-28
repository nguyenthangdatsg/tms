<!-- Department Scope Modal -->
@if($showDeptModal)
<div class="modal-backdrop fade show" style="display: block;"></div>
<div class="modal fade show d-block" tabindex="-1" style="display: block !important;">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __t('manage_department_scope') }}</h5>
        <button wire:click="closeDeptScopeModal()" type="button" class="btn-close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">{{ __t('scope') }}</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" wire:model.live="deptScope" id="scopeAll" value="all">
              <label class="form-check-label" for="scopeAll">
                {{ __t('all_departments') }}
              </label>
              <small class="d-block text-muted">{{ __t('all_departments_hint') }}</small>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" wire:model.live="deptScope" id="scopeSpecific" value="specific">
              <label class="form-check-label" for="scopeSpecific">
                {{ __t('specific_departments') }}
              </label>
              <small class="d-block text-muted">{{ __t('specific_departments_hint') }}</small>
            </div>
          </div>
        </div>
        
        @if($deptScope === 'specific')
        <div class="mb-3">
          <label class="form-label fw-semibold">{{ __t('select_departments') }}</label>
          <div class="border rounded p-3" style="max-height: 350px; overflow-y: auto;">
            @if(count($organizationUnits) > 0)
              @foreach($organizationUnits as $unit)
                @include('livewire.partials.org-checkbox', ['unit' => $unit, 'level' => 0, 'selectedDepartments' => $selectedDepartments])
              @endforeach
            @else
              <p class="text-muted">{{ __t('no_organization_units') }}</p>
            @endif
          </div>
          <small class="text-muted">{{ __t('selected_count') }}: {{ count($selectedDepartments) }}</small>
        </div>
        @endif
      </div>
      <div class="modal-footer">
        <button wire:click="closeDeptScopeModal()" type="button" class="btn btn-secondary">{{ __t('cancel') }}</button>
        <button wire:click="saveDeptScope()" type="button" class="btn btn-primary" wire:loading.attr="disabled">
          <i class="bi bi-check-circle" wire:loading.remove></i>
          <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
          {{ __t('save') }}
        </button>
      </div>
    </div>
  </div>
</div>
@endif