<?php

namespace App\Console\Commands;

use App\Enums\Statuses;
use App\Jobs\ExecuteOrder;
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
        $totalAvailableTrx = Stake::where('failed_attempts', '<', 3)->count('trx_amount') - OrderExecutor::count('trx_amount');
        $totalAvailableEnergy = floor($tron->trx2Energy($totalAvailableTrx));

        Order::withSum('executors', 'resource_amount')
            ->where('resource_amount', '<=', $totalAvailableEnergy)
            ->whereIn('status', Statuses::OPEN_STATUSES)
            ->havingRaw('executors_sum_resource_amount < resource_amount')
            ->orHavingNull('executors_sum_resource_amount')
            ->orderBy('id')
            ->chunk(50, function ($orders) {
                foreach ($orders as $order) {
                    ExecuteOrder::dispatch($order);
                }
            });
    }
}
