<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('reminder:send')->everyMinute()->withoutOverlapping();
    }
    
    public function register(): void
    {
        $this->app->booting(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('reminder:send')->everyMinute()->withoutOverlapping();
        });
    }
}