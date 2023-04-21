<?php

namespace App\Console;

use App\Jobs\{GetVotesRewards, ProcessingOrders};
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(PruneCommand::class)->everyFifteenMinutes();

        //Обработка заказов
        $schedule->job(ProcessingOrders::class)->dailyAt('00:00');

        //Забрать доступные, от голосования, вознаграждения по всем кошелькам
        $schedule->job(GetVotesRewards::class)->dailyAt('23:55');
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
