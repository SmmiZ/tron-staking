<?php

namespace App\Console;

use App\Jobs\{FreezeTRX, GetReward, VoteSR};
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        //Основной запуск в 00:01 каждые 3 дня
        $schedule->job(FreezeTRX::class)->cron('1 0 */3 * *')->after(function (Schedule $schedule) {
            $schedule->job(VoteSR::class)->daily();
        });

        //Получение награды
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
