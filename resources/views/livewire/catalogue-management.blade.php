<div>
<div class="container py-4">
      <h1 class="mb-4">{{ __t('catalogue_management') }}</h1>
  <div class="row g-4">
    <!-- Left: Categories Tree -->
    <div class="col-md-4">
      <div class="card">
         <div class="card-header d-flex justify-content-between align-items-center">
           <span>{{ __t('categories') }}</span>
          <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#categoryModal" wire:click="$set('editingCategory', null)" wire:loading.attr="disabled">
            <i class="bi bi-plus-lg" wire:loading.remove></i>
            <span wire:loading><i class="spinner-border spinner-border-sm me-1"></i></span>
          </button>
        </div>
        <div class="card-body p-2" id="categoryTreeRoot" style="min-height:320px; max-height:60vh; overflow:auto;">
          <ul id="categoryRootList" style="list-style:none; padding-left:0; margin:0;">
            @foreach ($categoryTree as $node)
              @include('livewire.catalogue-node', ['node'=>$node, 'level'=>0, 'selectedCategoryId' => $selectedCategoryId, 'expandedNodes' => $expandedNodes])
            @endforeach
          </ul>
        </div>
      </div>
    </div>

    <!-- Right: Courses -->
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
           <span>
             {{ __t('courses_in_category') }}: 
             @if($selectedCategoryId)
               @foreach($categoryTree as $node)
                 @if($node->id == $selectedCategoryId)
                   {{ $node->name }}
                   @break
                 @endif
               @endforeach
             @else
               {{ __t('all_categories') }}
             @endif
           </span>
          <button class="btn btn-sm btn-success" 
            {{ !$selectedCategoryId ? 'disabled' : '' }}
            data-bs-toggle="modal" 
             data-bs-target="#courseModal" 
             wire:click="$set('editingCourse', null)"
             title="{{ !$selectedCategoryId ? __t('select_category_first') : __t('add_new_course') }}"
             wire:loading.attr="disabled">
            <i class="bi bi-plus-lg" wire:loading.remove></i>
             <span wire:loading><i class="spinner-border spinner-border-sm me-1"></i></span> {{ __t('add_course_label') }}
          </button>
        </div>
        <div class="card-body p-2">
          <ul class="list-group">
            @if($selectedCategoryId)
              @foreach($courses as $course)
                @if($course->category_id == $selectedCategoryId)
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>{{ $course->name ?? '' }}</span>
                    <div class="btn-group btn-group-sm" role="group">
                       <button class="btn btn-outline-info" title="{{ __t('toggle_visibility') }}" wire:click="toggleCourseVisibility({{ $course->id }})" wire:loading.attr="disabled">
                        @if($course->visible ?? true)
                          <i class="bi bi-eye" wire:loading.remove></i>
                        @else
                          <i class="bi bi-eye-slash" wire:loading.remove></i>
                        @endif
                        <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                      </button>
                       <button class="btn btn-outline-primary" title="{{ __t('edit') }}" wire:click="editCourse({{ $course->id }})" data-bs-toggle="modal" data-bs-target="#courseModal" wire:loading.attr="disabled">
                        <i class="bi bi-pencil" wire:loading.remove></i>
                        <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                      </button>
                       <button class="btn btn-outline-danger" title="{{ __t('delete') }}" wire:click="deleteCourse({{ $course->id }})" wire:loading.attr="disabled">
                        <i class="bi bi-trash" wire:loading.remove></i>
                        <span wire:loading><i class="spinner-border spinner-border-sm"></i></span>
                      </button>
                    </div>
                  </li>
                @endif
              @endforeach
             @else
               <li class="list-group-item text-muted text-center">{{ __t('select_category_to_view') }}</li>
             @endif
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-sm">
       <div class="modal-header bg-light border-bottom">
         <h5 class="modal-title fw-bold">{{ $editingCategory ? __t('edit_category') : __t('new_category') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form wire:submit.prevent="{{ $editingCategory ? 'updateCategory' : 'createCategory' }}">
        <div class="modal-body">
           <!-- Category Name (Required) -->
           <div class="mb-4">
             <label class="form-label fw-semibold">
               <span class="text-dark">{{ __t('category_name') }}</span>
               <span class="badge bg-danger ms-2">{{ __t('required') }}</span>
             </label>
             <input type="text" 
               class="form-control form-control-lg border-2" 
               wire:model="categoryForm.name" 
               placeholder="{{ __t('enter_category_name') }}"
               style="border-color: #dee2e6; transition: all 0.3s ease;"
               required>
             <small class="text-muted d-block mt-2">{{ __t('field_mandatory') }}</small>
           </div>
           <!-- Description (Optional) -->
           <div class="mb-4">
             <label class="form-label fw-semibold">
               <span class="text-dark">{{ __t('description') }}</span>
               <span class="badge bg-secondary ms-2">{{ __t('optional') }}</span>
             </label>
             <textarea class="form-control form-control-lg border-2" 
               wire:model="categoryForm.description" 
               placeholder="{{ __t('enter_category_description') }}"
               rows="4"
               style="border-color: #dee2e6; transition: all 0.3s ease;"></textarea>
             <small class="text-muted d-block mt-2">{{ __t('brief_description_category') }}</small>
           </div>
           <!-- Visibility -->
           <div class="mb-3">
             <label class="form-label fw-semibold">
               <span class="text-dark">{{ __t('visibility') }}</span>
             </label>
             <select class="form-select form-select-lg border-2" 
               wire:model="categoryForm.visible"
               style="border-color: #dee2e6; transition: all 0.3s ease;">
               <option value="1">{{ __t('visible') }}</option>
               <option value="0">{{ __t('hidden') }}</option>
             </select>
           </div>
          <!-- Description (Optional) -->
          <div class="mb-4">
            <label class="form-label fw-semibold">
              <span class="text-dark">Description</span>
              <span class="badge bg-secondary ms-2">Optional</span>
            </label>
            <textarea class="form-control form-control-lg border-2" 
              wire:model="categoryForm.description" 
              placeholder="Enter category description"
              rows="4"
              style="border-color: #dee2e6; transition: all 0.3s ease;"></textarea>
            <small class="text-muted d-block mt-2">Provide a brief description of this category</small>
          </div>
          <!-- Visibility -->
          <div class="mb-3">
            <label class="form-label fw-semibold">
              <span class="text-dark">Visibility</span>
            </label>
            <select class="form-select form-select-lg border-2" 
              wire:model="categoryForm.visible"
              style="border-color: #dee2e6; transition: all 0.3s ease;">
              <option value="1">✓ Visible</option>
              <option value="0">✗ Hidden</option>
            </select>
          </div>
        </div>
         <div class="modal-footer bg-light border-top">
           <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
             <i class="bi bi-x-circle me-2"></i>Cancel
           </button>
           <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
             <i class="bi bi-{{ $editingCategory ? 'pencil' : 'plus' }}-circle me-2" wire:loading.remove></i>
             <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
             {{ $editingCategory ? 'Update' : 'Create' }}
           </button>
         </div>
      </form>
    </div>
  </div>
</div>

<!-- Course Modal -->
<div class="modal fade" id="courseModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-light border-bottom">
        <h5 class="modal-title fw-bold">{{ $editingCourse ? 'Edit Course' : 'New Course' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form wire:submit.prevent="{{ $editingCourse ? 'updateCourse' : 'createCourse' }}">
        <div class="modal-body">
          <!-- Course Name (Required) -->
          <div class="mb-4">
            <label class="form-label fw-semibold">
              <span class="text-dark">Course Name</span>
              <span class="badge bg-danger ms-2">Required</span>
            </label>
            <input type="text" 
              class="form-control form-control-lg border-2" 
              wire:model="courseForm.name" 
              placeholder="Enter course name"
              style="border-color: #dee2e6; transition: all 0.3s ease;"
              required>
            <small class="text-muted d-block mt-2">Provide a clear and descriptive course name</small>
          </div>
          
          <!-- Course Code -->
          <div class="mb-4">
            <label class="form-label fw-semibold">
              <span class="text-dark">Course Code</span>
              <span class="badge bg-secondary ms-2">Optional</span>
            </label>
            <input type="text" 
              class="form-control form-control-lg border-2" 
              wire:model="courseForm.code" 
              placeholder="e.g., CS-101"
              style="border-color: #dee2e6; transition: all 0.3s ease;">
            <small class="text-muted d-block mt-2">Course code/identifier</small>
          </div>
          
          <!-- Description (Optional) -->
          <div class="mb-4">
            <label class="form-label fw-semibold">
              <span class="text-dark">Description</span>
              <span class="badge bg-secondary ms-2">Optional</span>
            </label>
            <textarea class="form-control form-control-lg border-2" 
              wire:model="courseForm.description" 
              placeholder="Enter course description"
              rows="4"
              style="border-color: #dee2e6; transition: all 0.3s ease;"></textarea>
            <small class="text-muted d-block mt-2">Describe the course content and objectives</small>
          </div>
          
          <!-- Duration & Type -->
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="text-dark">Duration</span>
                <span class="badge bg-secondary ms-2">Optional</span>
              </label>
              <input type="text" 
                class="form-control form-control-lg border-2" 
                wire:model="courseForm.duration" 
                placeholder="e.g., 8 hours"
                style="border-color: #dee2e6; transition: all 0.3s ease;">
              <small class="text-muted d-block mt-2">e.g., 8 hours, 2 days</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="text-dark">Type</span>
                <span class="badge bg-secondary ms-2">Optional</span>
              </label>
              <input type="text" 
                class="form-control form-control-lg border-2" 
                wire:model="courseForm.type" 
                placeholder="e.g., Online"
                style="border-color: #dee2e6; transition: all 0.3s ease;">
              <small class="text-muted d-block mt-2">e.g., Online, Offline, Hybrid</small>
            </div>
          </div>
          
          <!-- Visibility -->
          <div class="mb-3">
            <label class="form-label fw-semibold">
              <span class="text-dark">Visibility</span>
            </label>
            <select class="form-select form-select-lg border-2" 
              wire:model="courseForm.visible"
              style="border-color: #dee2e6; transition: all 0.3s ease;">
              <option value="1">✓ Visible</option>
              <option value="0">✗ Hidden</option>
            </select>
          </div>
          
        </div>
         <div class="modal-footer bg-light border-top">
           <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
             <i class="bi bi-x-circle me-2"></i>Cancel
           </button>
           <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
             <i class="bi bi-{{ $editingCourse ? 'pencil' : 'plus' }}-circle me-2" wire:loading.remove></i>
             <span wire:loading><i class="spinner-border spinner-border-sm me-2"></i></span>
             {{ $editingCourse ? 'Update' : 'Create' }}
           </button>
         </div>
      </form>
    </div>
  </div>
</div>

<style>
  /* Enhanced form input styling */
  .form-control-lg, .form-select-lg {
    font-size: 0.95rem;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    background-color: #f8f9fa;
  }
  
  .form-control:hover, .form-select:hover {
    border-color: #0d6efd;
    background-color: #fff;
  }
  
  .form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    background-color: #fff;
    font-weight: 500;
  }
  
  .badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.35rem 0.65rem;
    letter-spacing: 0.5px;
  }
  
  .badge.bg-danger {
    background-color: #dc3545 !important;
    text-transform: uppercase;
  }
  
  .badge.bg-secondary {
    background-color: #6c757d !important;
    text-transform: uppercase;
  }
  
  .form-label {
    margin-bottom: 0.75rem;
    color: #2c3e50;
  }
  
  .form-label .text-dark {
    font-weight: 600;
    color: #1a1a1a;
  }
  
  small.text-muted {
    font-size: 0.85rem;
    line-height: 1.4;
    color: #6c757d !important;
  }
  
  .modal-content {
    border-radius: 0.75rem;
  }
  
  .modal-header {
    background-color: #f8f9fa;
    padding: 1.5rem;
  }
  
  .modal-title {
    color: #1a1a1a;
    letter-spacing: 0.5px;
  }
  
  .btn-primary {
    background-color: #0d6efd;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
  }
  
  .btn-primary:hover {
    background-color: #0b5ed7;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
  }
  
  .btn-outline-secondary {
    font-weight: 600;
    transition: all 0.3s ease;
  }
  
  .btn-outline-secondary:hover {
    transform: translateY(-1px);
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', initializeTreeToggle);
  document.addEventListener('livewire:updated', initializeTreeToggle);

  function initializeTreeToggle() {
    // Handle expand/collapse - use event delegation
    const categoryTree = document.getElementById('categoryTreeRoot');
    if (categoryTree) {
      categoryTree.removeEventListener('click', handleTreeToggle);
      categoryTree.addEventListener('click', handleTreeToggle);
    }
    
    initializeDragDrop();
  }

  function handleTreeToggle(e) {
    if (e.target.closest('.tree-toggle')) {
      e.preventDefault();
      e.stopPropagation();
      
      const button = e.target.closest('.tree-toggle');
      const targetId = button.dataset.target;
      const el = document.getElementById(targetId);
      const icon = button.querySelector('i');
      
      if (el) {
        const isHidden = el.style.display === 'none' || el.style.display === '';
        el.style.display = isHidden ? 'block' : 'none';
        icon.style.transition = 'transform 0.2s ease';
        icon.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
      }
    }
  }

  function initializeDragDrop() {
    const list = document.getElementById('categoryRootList');
    if (!list) return;
    
    let draggedEl = null;
    
    document.querySelectorAll('.category-item').forEach(item => {
      // Remove old listeners to prevent duplicates
      item.ondragstart = null;
      item.ondragover = null;
      item.ondragleave = null;
      item.ondrop = null;
      item.ondragend = null;
      
      item.ondragstart = function(e) {
        draggedEl = this;
        this.style.opacity = '0.5';
        e.dataTransfer.effectAllowed = 'move';
      };
      
      item.ondragover = function(e) {
        e.preventDefault();
        this.style.borderTop = '2px solid #0d6efd';
      };
      
      item.ondragleave = function(e) {
        this.style.borderTop = 'none';
      };
      
      item.ondrop = function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderTop = 'none';
        if (draggedEl && draggedEl !== this && list.contains(draggedEl)) {
          list.insertBefore(draggedEl, this);
        }
      };
      
      item.ondragend = function(e) {
        this.style.opacity = '1';
        document.querySelectorAll('.category-item').forEach(el => {
          el.style.borderTop = 'none';
        });
      };
    });
  }
</script>
</div>
