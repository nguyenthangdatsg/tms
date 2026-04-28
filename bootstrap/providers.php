<?php

use App\Providers\AppServiceProvider;
use App\Providers\HelperServiceProvider;
use App\Providers\MoodleServiceProvider;
use App\Providers\ScheduleServiceProvider;

return [
    AppServiceProvider::class,
    HelperServiceProvider::class,
    MoodleServiceProvider::class,
    ScheduleServiceProvider::class,
];
