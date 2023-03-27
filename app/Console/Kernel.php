<?php

namespace App\Console;

use App\Console\Commands\{GetReward, Start, Vote};
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
        $schedule->command(Start::class)->cron('1 0 */3 * *');

        //Голосование
        $schedule->command(Vote::class)->everySixHours();

        //Получение награды
        $schedule->command(GetReward::class)->dailyAt('23:55');
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
