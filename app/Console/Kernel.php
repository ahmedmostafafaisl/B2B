<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
$schedule->command('menu:sync-services --keep-custom=1')->dailyAt('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected $commands = [
//        Commands\Inspire::class,
        Commands\SyncSidebarServices::class,
    ];
}
