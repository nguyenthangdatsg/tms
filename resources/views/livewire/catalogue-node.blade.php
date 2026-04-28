@php
  $isSelected = $selectedCategoryId && $selectedCategoryId == $node->id;
  $hasChildren = !empty($node->children);
  $isExpanded = in_array($node->id, $expandedNodes);
@endphp
<div style="margin-left: {{ $level * 20 }}px; padding: 0;">
  <div class="category-node category-item d-flex align-items-center gap-2 p-2 rounded" 
    data-id="{{ $node->id }}" draggable="true"
    @if($isSelected)
      style="cursor: move; background-color: #f0f7ff; border-left: 3px solid #0d6efd;"
    @else
      style="cursor: move;"
    @endif>
    
    @if($hasChildren)
      <button 
        class="btn btn-sm btn-outline-secondary tree-toggle" 
        data-target="node-{{ $node->id }}" 
        type="button" 
        style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: none;"
        wire:click="toggleNode({{ $node->id }})">
        <i class="bi bi-chevron-right" style="transition: transform 0.2s ease; transform: rotate({{ $isExpanded ? '90deg' : '0deg' }});"></i>
      </button>
    @else
      <div style="width: 32px; flex-shrink: 0;"></div>
    @endif
    
    <span class="fw-bold flex-grow-1" 
      @if($isSelected)
        style="cursor: pointer; padding: 4px 8px; display: inline-block; border-radius: 3px; background-color: #0d6efd; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
      @else
        style="cursor: pointer; padding: 4px 8px; display: inline-block; border-radius: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
      @endif
      wire:click="selectCategory({{ $node->id }})">
      {{ $node->name }}
    </span>
    
     <button class="btn btn-sm btn-outline-success" title="{{ __t('add_new_course') }}" data-bs-toggle="modal" data-bs-target="#categoryModal" wire:click="openAddUnder({{ $node->id }})">
       <i class="bi bi-plus-lg"></i>
     </button>
     <button class="btn btn-sm btn-outline-info" title="{{ __t('toggle_visibility') }}" wire:click="toggleCategoryVisibility({{ $node->id }})">
       @if($node->visible ?? true)
         <i class="bi bi-eye"></i>
       @else
         <i class="bi bi-eye-slash"></i>
       @endif
     </button>
     <button class="btn btn-sm btn-outline-primary" title="{{ __t('edit') }}" wire:click="editCategory({{ $node->id }})" data-bs-toggle="modal" data-bs-target="#categoryModal">
       <i class="bi bi-pencil"></i>
     </button>
     <button class="btn btn-sm btn-outline-danger" title="{{ __t('delete') }}" wire:click="deleteCategory({{ $node->id }})">
       <i class="bi bi-trash"></i>
     </button>
  </div>
  
  @if(!empty($node->children))
    <div id="node-{{ $node->id }}" style="display: {{ $isExpanded ? 'block' : 'none' }}; padding: 0; margin: 0;">
      @foreach ($node->children as $child)
        @include('livewire.catalogue-node', ['node'=>$child, 'level'=>$level+1, 'selectedCategoryId' => $selectedCategoryId, 'expandedNodes' => $expandedNodes])
      @endforeach
    </div>
  @endif
</div>