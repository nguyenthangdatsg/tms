<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TMS - Quản lý Đào tạo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/loading-indicators.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
    @livewireStyles()
<style>
        :root {
            --primary-color: #2563eb;
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-x: hidden;
        }
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        .sidebar.collapsed .sidebar-brand h1 span,
        .sidebar.collapsed .sidebar-menu-item span {
            display: none;
        }
        .sidebar.collapsed .sidebar-brand {
            padding: 1.5rem 0.75rem;
            text-align: center;
        }
        .sidebar.collapsed .sidebar-menu-item {
            padding: 0.75rem;
            text-align: center;
        }
        .sidebar.collapsed .sidebar-menu-item i {
            margin-right: 0;
        }
        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-brand h1 {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
        }
        .sidebar-menu {
            padding: 1rem 0;
        }
        .sidebar-menu-item {
            display: block;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            white-space: nowrap;
            overflow: hidden;
        }
        .sidebar-menu-item:hover, .sidebar-menu-item.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-left-color: var(--primary-color);
        }
        .sidebar-menu-item i {
            margin-right: 0.75rem;
            width: 1.25rem;
            min-width: 1.25rem;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }
        .top-header {
            background: #fff;
            padding: 1rem 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sidebar-toggle {
            width: 36px;
            height: 36px;
            border: none;
            background: #f1f5f9;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .sidebar-toggle:hover {
            background: #e2e8f0;
        }
        .page-content {
            padding: 2rem;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
            }
            .sidebar.show-mobile {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
            }
            .sidebar-toggle {
                display: flex;
            }
        }
    </style>
</head>
<body>
    <!-- Global Loading Indicator for all components -->
    @livewire('loading-indicator')

    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h1><i class="bi bi-mortarboard-fill"></i> <span>TMS - Education</span></h1>
        </div>
        <nav class="sidebar-menu">
            <a href="/tms" class="sidebar-menu-item {{ request()->is('tms') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> <span>{{ __t('dashboard') }}</span>
            </a>
            <a href="/tms/exam" class="sidebar-menu-item {{ request()->is('tms/exam*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text-fill"></i> <span>{{ __t('exam_management') }}</span>
            </a>
            <a href="/tms/online-course" class="sidebar-menu-item {{ request()->is('tms/online-course*') ? 'active' : '' }}">
                <i class="bi bi-laptop-fill"></i> <span>{{ __t('online_course') }}</span>
            </a>
            <a href="/tms/offline-course" class="sidebar-menu-item {{ request()->is('tms/offline-course*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt-fill"></i> <span>{{ __t('offline_course') }}</span>
            </a>
            <a href="/tms/blended-course" class="sidebar-menu-item {{ request()->is('tms/blended-course*') ? 'active' : '' }}">
                <i class="bi bi-person-badge-fill"></i> <span>{{ __t('blended_course') }}</span>
            </a>
            <a href="/tms/users" class="sidebar-menu-item {{ request()->is('tms/users*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> <span>{{ __t('user_management') }}</span>
            </a>
            <a href="/tms/organization" class="sidebar-menu-item {{ request()->is('tms/organization*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3-fill"></i> <span>{{ __t('organization_structure') }}</span>
            </a>
            <a href="/tms/catalogue" class="sidebar-menu-item {{ request()->is('tms/catalogue*') ? 'active' : '' }}">
                <i class="bi bi-kanban"></i> <span>{{ __t('catalogue_management') }}</span>
            </a>
            <a href="/tms/learning-path" class="sidebar-menu-item {{ request()->is('tms/learning-path*') ? 'active' : '' }}">
                <i class="bi bi-signpost-2-fill"></i> <span>{{ __t('learning_path') }}</span>
            </a>
            <a href="/tms/permissions" class="sidebar-menu-item {{ request()->is('tms/permissions*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i> <span>{{ __t('permission_management') }}</span>
            </a>
            <a href="/tms/question-bank" class="sidebar-menu-item {{ request()->is('tms/question-bank*') ? 'active' : '' }}">
                <i class="bi bi-chat-square-quote"></i> <span>{{ __t('question_bank') }}</span>
            </a>
            <a href="/tms/question-set" class="sidebar-menu-item {{ request()->is('tms/question-set*') ? 'active' : '' }}">
                <i class="bi bi-collection"></i> <span>{{ __t('question_sets') }}</span>
            </a>
        </nav>
    </div>

    <div class="main-content" id="mainContent">
        <header class="top-header">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle hide-desktop" id="sidebarToggleMobile" onclick="toggleSidebarMobile()">
                    <i class="bi bi-list"></i>
                </button>
                <button class="sidebar-toggle hide-mobile" id="sidebarToggle" onclick="toggleSidebar()">
                    <i class="bi bi-chevron-left" id="toggleIcon"></i>
                </button>
                <h4 class="m-0">{{ __t('lms') }}</h4>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">{{ date('d/m/Y') }}</span>
                @livewire(LanguageSwitcher::class)
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> Admin
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/login/logout.php"><i class="bi bi-box-arrow-right"></i> {{ __t('logout') }}</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="page-content">
            {{ $slot }}
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts()
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
    <script>
        // Toast notification function
        function showToast(type, message) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            container.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            setTimeout(() => toast.remove(), 5000);
        }

        // Listen to Livewire browser events
        window.addEventListener('browser', (event) => {
            console.log('Browser event received:', event.detail);
            const alert = event.detail[0]?.alert || event.detail?.alert;
            if (alert) {
                showToast(alert.type || 'info', alert.message || '');
            }
        });

        // Also support Livewire 3 event format
        document.addEventListener('livewire:emit', (event) => {
            console.log('Livewire emit:', event.detail);
        });

        // Initialize TinyMCE for description textareas
        function initTinyMCE() {
            if (typeof tinymce === 'undefined') {
                console.log('TinyMCE not loaded yet');
                return;
            }
            
            // Get current locale
            const locale = document.documentElement.lang || 'vi';
            const langUrl = locale === 'vi' 
                ? 'https://cdn.jsdelivr.net/npm/tinymce@6/langs/vi.js'
                : 'https://cdn.jsdelivr.net/npm/tinymce@6/langs/en.js';
            
            // Wait a bit for DOM to settle
            setTimeout(() => {
                // More specific selector for notification templates
                const templateTextareas = document.querySelectorAll('textarea[id*="_template"]');
                console.log('Found template textareas:', templateTextareas.length);
                
                const editors = document.querySelectorAll('textarea[id*="description"], textarea[id*="_template"]');
                console.log('Found all textareas:', editors.length);
                
                editors.forEach((textarea) => {
                    const editorId = textarea.id;
                    console.log('Processing textarea:', editorId);
                    
                    // Skip if already has editor
                    if (tinymce.get(editorId)) {
                        console.log('Already has editor:', editorId);
                        return;
                    }
                    
                    // Initialize TinyMCE
                    tinymce.init({
                        selector: '#' + editorId,
                        language: 'vi',
                        language_url: 'https://cdn.jsdelivr.net/npm/tinymce@6/langs/vi.js',
                        plugins: 'lists link image table codesample fullscreen',
                        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | fullscreen',
                        menubar: false,
                        height: 300,
                        setup: function(editor) {
                            editor.on('change', function() {
                                const content = editor.getContent();
                                textarea.value = content;
                                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                            });
                            editor.on('blur', function() {
                                const content = editor.getContent();
                                textarea.value = content;
                                textarea.dispatchEvent(new Event('change', { bubbles: true }));
                            });
                        }
                    });
                });
            }, 200);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initTinyMCE);
        
        // Watch for DOM changes using MutationObserver
        const observer = new MutationObserver((mutations) => {
            let hasNewTextarea = false;
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) {
                            if (node.matches && (node.matches('textarea[id*="description"]') || node.matches('textarea[id*="_template"]'))) {
                                hasNewTextarea = true;
                            }
                            if (node.querySelectorAll && (node.querySelectorAll('textarea[id*="description"]').length > 0 || node.querySelectorAll('textarea[id*="_template"]').length > 0)) {
                                hasNewTextarea = true;
                            }
                        }
                    });
                }
            });
            if (hasNewTextarea) {
                initTinyMCE();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: false,
            characterData: false
        });

        // Listen to Livewire events
        document.addEventListener('livewire:init', () => {
            console.log('livewire:init event');
            setTimeout(initTinyMCE, 300);
        });
        
        document.addEventListener('livewire:initialized', () => {
            console.log('livewire:initialized event');
            setTimeout(initTinyMCE, 300);
        });
        
        document.addEventListener('livewire:updated', () => {
            initTinyMCE();
        });
        
        document.addEventListener('livewire:loaded', () => {
            initTinyMCE();
        });
        
        // Also try on Livewire navigation events
        document.addEventListener('livewire:navigate', () => {
            setTimeout(initTinyMCE, 300);
        });
        
        // Custom browser event from Livewire
        document.addEventListener('browser-event', (event) => {
            console.log('Browser event received:', event.detail);
            setTimeout(initTinyMCE, 300);
        });
        
        // Listen for init-tinymce event from Livewire
        document.addEventListener('init-tinymce', () => {
            console.log('init-tinymce event received');
            setTimeout(initTinyMCE, 300);
        });
        
        // Fallback: periodic check when modal is opened
        let tinyMceCheckInterval = setInterval(() => {
            const textareas = document.querySelectorAll('textarea[id*="_template"]');
            textareas.forEach((textarea) => {
                if (!tinymce.get(textarea.id) && textarea.id.includes('_template')) {
                    console.log('Found template textarea:', textarea.id);
                    initTinyMCE();
                }
            });
        }, 1000);

        // Sidebar toggle functions
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleIcon = document.getElementById('toggleIcon');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            if (toggleIcon) {
                if (sidebar.classList.contains('collapsed')) {
                    toggleIcon.classList.remove('bi-chevron-left');
                    toggleIcon.classList.add('bi-chevron-right');
                } else {
                    toggleIcon.classList.remove('bi-chevron-right');
                    toggleIcon.classList.add('bi-chevron-left');
                }
            }
            
            // Save state
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }

        function toggleSidebarMobile() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show-mobile');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggleMobile');
            
            if (window.innerWidth <= 768 && sidebar.classList.contains('show-mobile')) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('show-mobile');
                }
            }
        });

        // Save sidebar state to localStorage
        function saveSidebarState(collapsed) {
            localStorage.setItem('sidebarCollapsed', collapsed);
        }

        function loadSidebarState() {
            const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (collapsed && window.innerWidth > 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                if (toggleIcon) {
                    toggleIcon.classList.remove('bi-chevron-left');
                    toggleIcon.classList.add('bi-chevron-right');
                }
            }
        }

        // Load saved state on page load
        document.addEventListener('DOMContentLoaded', loadSidebarState);
    </script>
</body>
</html>
