<?php

namespace App\Console\Commands;

use App\Enums\Statuses;
use App\Services\OrderService;
use App\Models\{Order, OrderExecutor, Stake};
use App\Services\TronApi\Tron;
use Illuminate\Console\Command;

class ProcessingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:processing-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запуск обработки заказов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tron = new Tron();
        $totalAvailableTrx = Stake::where('failed_attempts', '<', 3)->sum('trx_amount') - OrderExecutor::sum('trx_amount');
        $totalAvailableEnergy = floor($tron->trx2Energy($totalAvailableTrx));

        Order::withSum('executors', 'resource_amount')
            ->where('resource_amount', '<=', $totalAvailableEnergy)
            ->whereIn('status', Statuses::OPEN_STATUSES)
            ->havingRaw('executors_sum_resource_amount < resource_amount')
            ->orHavingNull('executors_sum_resource_amount')
            ->orderBy('id')
            ->chunk(50, function ($orders) {
                foreach ($orders as $order) {
                    (new OrderService($order))->execute();

                    sleep(1);
                }
            });
    }
}
