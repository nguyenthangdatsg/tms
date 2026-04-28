<div>
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">{{ __t('dashboard_title') }}</h2>
            <p class="text-muted">{{ __t('welcome') }}</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <a href="/tms/online-course" class="text-decoration-none">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-card-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-laptop-fill"></i>
                        </div>
                        <div class="ms-3">
                     <div class="stat-card-title">{{ __t('online_course_card') }}</div>
                     <div class="stat-card-value">{{ __t('view_label') }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="/tms/offline-course" class="text-decoration-none">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                         <div class="stat-card-icon bg-warning bg-opacity-10 text-warning">
                             <i class="bi bi-geo-alt-fill"></i>
                         </div>
                         <div class="ms-3">
                             <div class="stat-card-title">{{ __t('offline_course_card') }}</div>
                             <div class="stat-card-value">{{ __t('view_label') }}</div>
                         </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="/tms/blended-course" class="text-decoration-none">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                         <div class="stat-card-icon bg-info bg-opacity-10 text-info">
                             <i class="bi bi-collection-fill"></i>
                         </div>
                         <div class="ms-3">
                             <div class="stat-card-title">{{ __t('blended_course_card') }}</div>
                             <div class="stat-card-value">{{ __t('view_label') }}</div>
                         </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="/tms/users" class="text-decoration-none">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                         <div class="stat-card-icon bg-danger bg-opacity-10 text-danger">
                             <i class="bi bi-people-fill"></i>
                         </div>
                         <div class="ms-3">
                             <div class="stat-card-title">{{ __t('users_card') }}</div>
                             <div class="stat-card-value">{{ __t('manage_label') }}</div>
                         </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="content-card">
         <div class="content-card-header">
             <h5 class="m-0"><i class="bi bi-info-circle me-2"></i>{{ __t('instructions_title') }}</h5>
         </div>
         <div class="content-card-body">
              <ul class="list-unstyled mb-0">
                  <li class="mb-2"><i class="bi bi-check-circle text-primary me-2"></i><strong>{{ __t('instruction_online') }}</strong>: {{ __t('instruction_online_desc') }}</li>
                  <li class="mb-2"><i class="bi bi-check-circle text-primary me-2"></i><strong>{{ __t('instruction_offline') }}</strong>: {{ __t('instruction_offline_desc') }}</li>
                  <li class="mb-2"><i class="bi bi-check-circle text-primary me-2"></i><strong>{{ __t('instruction_blended') }}</strong>: {{ __t('instruction_blended_desc') }}</li>
                  <li><i class="bi bi-check-circle text-primary me-2"></i><strong>{{ __t('instruction_users') }}</strong>: {{ __t('instruction_users_desc') }}</li>
              </ul>
         </div>
    </div>
</div>