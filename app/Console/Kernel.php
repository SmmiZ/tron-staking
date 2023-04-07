<?php

namespace App\Console;

use App\Jobs\{FreezeTRX, GetReward, UnfreezeTRX, VoteSR};
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(UnfreezeTRX::class)->cron('0 0 */3 * *')->after(function (Schedule $schedule) {
            $schedule->job(FreezeTRX::class)->after(function (Schedule $schedule) {
                $schedule->job(VoteSR::class)->daily();
            });
        });

        $schedule->job(GetReward::class)->dailyAt('23:55');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
