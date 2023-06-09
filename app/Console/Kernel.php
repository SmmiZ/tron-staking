<?php

namespace App\Console;

use App\Console\Commands\{CalcResourceConsumption,
    CheckMerchantWallets,
    GetRewards,
    LeaderLevelDowngrade,
    ProcessingOrders,
    SendProfitToUsers,
    UnfreezeClosingStakes};
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
        $schedule->command(ProcessingOrders::class)->everyFiveMinutes();

        //Забрать доступные награды пользователей (по четным дням в 23:55)
        $schedule->command(GetRewards::class)->cron('55 23 */2 * *');

        //Понижение лидерского уровня пользователей
        $schedule->command(LeaderLevelDowngrade::class)->everyFiveMinutes();

        //Проверяет торговые кошельки мерчантов на наличие средств и зачисляет на баланс пользователей
        $schedule->command(CheckMerchantWallets::class)->everyFiveMinutes();

        //Формирует статистику потребления ресурсов
        $schedule->command(CalcResourceConsumption::class)->dailyAt('23:59');

        //Начисление прибыли пользователям
        $schedule->command(SendProfitToUsers::class)->dailyAt('22:50')->after(function () {
            $this->call(UnfreezeClosingStakes::class);
        });
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
