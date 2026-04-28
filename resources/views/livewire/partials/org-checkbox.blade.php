@php
    $orgTypes = [
        'company' => 'Công ty',
        'division' => 'Khối',
        'department' => 'Phòng',
        'team' => 'Nhóm',
    ];
    $typeColors = [
        'company' => 'primary',
        'division' => 'info',
        'department' => 'success',
        'team' => 'warning',
    ];
    $icon = $unit->type == 'company' ? 'bi-building' : ($unit->type == 'division' ? 'bi-diagram-3' : ($unit->type == 'department' ? 'bi-people' : 'bi-person-badge'));
    $isChecked = in_array($unit->id, $selectedDepartments);
@endphp

<div class="mb-1">
    <div class="d-flex align-items-center py-1" 
         style="padding-left: {{ $level * 20 }}px; cursor: pointer;"
         wire:click="toggleDepartment({{ $unit->id }}, {{ $isChecked ? 'false' : 'true' }})">
        <i class="bi {{ $icon }} me-2 text-{{ $typeColors[$unit->type] ?? 'secondary' }}"></i>
        <input class="form-check-input me-2" 
               type="checkbox" 
               @if($isChecked) checked @endif
               id="dept_{{ $unit->id }}">
        <label class="form-check-label mb-0" for="dept_{{ $unit->id }}">
            <strong>{{ $unit->name }}</strong>
            <span class="badge bg-{{ $typeColors[$unit->type] ?? 'secondary' }} ms-1">{{ $orgTypes[$unit->type] ?? $unit->type }}</span>
        </label>
    </div>
    @if(!empty($unit->children))
        @foreach($unit->children as $child)
            @include('livewire.partials.org-checkbox', ['unit' => $child, 'level' => $level + 1, 'selectedDepartments' => $selectedDepartments])
        @endforeach
    @endif
</div>
