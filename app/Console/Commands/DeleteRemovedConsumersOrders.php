<?php

namespace App\Console\Commands;

use App\Enums\{Statuses, TronTxTypes};
use App\Models\{Order, TronTx};
use App\Services\TronApi\Tron;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;

class DeleteRemovedConsumersOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-removed-consumers-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отзывает ресурсы и удаляет заказы по удаленным потребителям';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tron = new Tron();

        Order::with([
            'consumer' => fn($q) => $q->onlyTrashed()->select(['id', 'user_id', 'address']),
            'executors' => ['wallet']
        ])
            ->whereHas('consumer', fn($q) => $q->onlyTrashed())
            ->chunkById(100, function ($orders) use ($tron) {
                foreach ($orders as $order) {
                    foreach ($order->executors as $executor) {
                        $delegatedInfo = $tron->getDelegatedResources($executor->wallet->address, $order->consumer->address);
                        $trxAmount = data_get($delegatedInfo, 'delegatedResource.0.frozen_balance_for_energy', 0) / Tron::ONE_SUN;

                        if ($trxAmount < 1) continue;

                        Sleep::for(config('app.sleep_ms'))->milliseconds();
                        $response = $tron->undelegateEnergy($executor->wallet->address, $order->consumer->address, $trxAmount);

                        if (isset($response['code']) && $response['code'] != 'true') {
                            Log::error('Revoke resources error', $response);

                            continue 2;
                        }

                        TronTx::create([
                            'from' => data_get($response, 'raw_data.contract.0.parameter.value.owner_address'),
                            'to' => data_get($response, 'raw_data.contract.0.parameter.value.receiver_address'),
                            'type' => TronTxTypes::UnDelegateResourceContract,
                            'trx_amount' => $trxAmount,
                            'tx_id' => $response['txID'],
                        ]);

                        $executor->update(['trx_amount' => 0, 'resource_amount' => 0, 'deleted_at' => now()]);
                    }

                    $order->update(['status' => Statuses::completed, 'deleted_at' => now()]);
                    sleep(1);
                }
                sleep(1);
            });
    }
}
