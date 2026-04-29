<div>
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">{{ __t('dashboard') }}</h2>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">{{ __t('online_users') }}</h6>
                            <h2 class="mb-0">{{ $onlineUsers }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people-fill" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">{{ __t('total_users') }}</h6>
                            <h2 class="mb-0">{{ $totalUsers }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-person-check-fill" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">{{ __t('total_courses') }}</h6>
                            <h2 class="mb-0">{{ $totalCourses }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-book-fill" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">{{ __t('learning_paths') }}</h6>
                            <h2 class="mb-0">{{ $totalLearningPaths }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-signpost-2-fill" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Course Type Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title text-muted mb-0">{{ __t('special_courses') }}</h6>
                        <i class="bi bi-book text-primary"></i>
                    </div>
                    <h3 class="mb-0">{{ $specialCourses }}</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title text-muted mb-0">{{ __t('catalogue_courses') }}</h6>
                        <i class="bi bi-collection text-info"></i>
                    </div>
                    <h3 class="mb-0">{{ $catalogueCourses }}</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title text-muted mb-0">{{ __t('blended_courses') }}</h6>
                        <i class="bi bi-intersect text-warning"></i>
                    </div>
                    <h3 class="mb-0">{{ $blendedCourses }}</h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">{{ __t('user_login_stats') }} (7 {{ __t('days') }})</h5>
                </div>
                <div class="card-body">
                    <canvas id="loginChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">{{ __t('course_distribution') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="courseChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Login Chart
            const loginCtx = document.getElementById('loginChart').getContext('2d');
            const loginStats = @json($this->loginStats);
            
            new Chart(loginCtx, {
                type: 'line',
                data: {
                    labels: loginStats.map(item => item.date),
                    datasets: [{
                        label: '{{ __t("
                        data: loginStats.map(item => item.count),
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
            
            // Course Distribution Chart
            const courseCtx = document.getElementById('courseChart').getContext('2d');
            const courseStats = @json($this->courseStats);
            
            new Chart(courseCtx, {
                type: 'doughnut',
                data: {
                    labels: courseStats.labels,
                    datasets: [{
                        data: courseStats.data,
                        backgroundColor: [
                            'rgb(54, 162, 235)',
                            'rgb(75, 192, 192)',
                            'rgb(255, 205, 86)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</div>
