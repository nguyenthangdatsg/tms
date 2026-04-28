<div class="org-unit-item mb-1">
    <div class="d-flex align-items-center p-2 rounded bg-light">
        <span style="width: {{ $level * 24 }}px;"></span>
        <span class="me-2">
            @if($unit->type == 'company')
                <i class="bi bi-building text-primary"></i>
            @elseif($unit->type == 'division')
                <i class="bi bi-diagram-3 text-info"></i>
            @elseif($unit->type == 'department')
                <i class="bi bi-people text-success"></i>
            @else
                <i class="bi bi-person-badge text-warning"></i>
            @endif
        </span>
        <div class="flex-grow-1">
            <strong>{{ $unit->name }}</strong>
            @if($unit->code)
                <span class="badge bg-secondary ms-2">{{ $unit->code }}</span>
            @endif
            <span class="badge bg-{{ $unit->type == 'company' ? 'primary' : ($unit->type == 'division' ? 'info' : ($unit->type == 'department' ? 'success' : 'warning')) }} ms-2">
                {{ $orgTypes[$unit->type] ?? $unit->type }}
            </span>
            <span class="badge bg-light text-dark border ms-2" title="{{ __t('members') }}">
                <i class="bi bi-people me-1"></i>{{ $unit->member_count ?? 0 }}
            </span>
        </div>
        <div class="btn-group btn-group-sm">
            <button wire:click="openUsersModal({{ $unit->id }}, '{{ addslashes($unit->name) }}')" class="btn btn-outline-secondary btn-xs" title="{{ __t('members') }}">
                <i class="bi bi-people"></i>
            </button>
            <button wire:click="openAddModal({{ $unit->id }})" class="btn btn-outline-primary btn-xs" title="{{ __t('add_child_unit') }}">
                <i class="bi bi-plus"></i>
            </button>
            <button wire:click="openEditModal({{ $unit->id }})" class="btn btn-outline-warning btn-xs" title="{{ __t('edit') }}">
                <i class="bi bi-pencil"></i>
            </button>
            <button wire:click="confirmDelete({{ $unit->id }})" class="btn btn-outline-danger btn-xs" title="{{ __t('delete') }}">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
    @if(!empty($unit->children))
        <div class="org-children ms-4">
            @foreach($unit->children as $child)
                @include('livewire.partials.org-unit', ['unit' => $child, 'level' => $level + 1, 'orgTypes' => $orgTypes])
            @endforeach
        </div>
    @endif
</div>
