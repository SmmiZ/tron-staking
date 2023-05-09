<?php

namespace App\Console;

use App\Console\Commands\{GetRewards, LeaderLevelDowngrade, MerchantCheckCommand, ProcessingOrders};
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
        $schedule->command(ProcessingOrders::class)->dailyAt('00:00');

        //Забрать доступные награды пользователей
        $schedule->command(GetRewards::class)->dailyAt('23:55');

        //Понижение лидерского уровня пользователей
        $schedule->command(LeaderLevelDowngrade::class)->everyFiveMinutes();

        //Проверяет merchant
        $schedule->command(MerchantCheckCommand::class)->everyFiveMinutes();
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
