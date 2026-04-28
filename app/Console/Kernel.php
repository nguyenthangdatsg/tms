<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('reminder:send')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
        // Auto-register demo seed command when present
        if (class_exists(\App\Console\Commands\SeedDemoData::class)) {
            $this->commands([\App\Console\Commands\SeedDemoData::class]);
        }
    }
}
