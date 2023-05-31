<?php

namespace App\Services;

use App\Enums\Statuses;
use App\Exceptions\NotEnoughBandwidthException;
use App\Jobs\SendBonusBandwidth;
use App\Models\{Consumer, Order, User};
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Support\{Collection, Sleep};
use Illuminate\Support\Facades\Log;

class OrderService
{
    private Order $order;
    private Consumer $consumer;
    private Tron $tron;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->consumer = $order->consumer;
        $this->tron = new Tron();
    }

    /**
     * Заполнение заказа ресурсами пользователей
     *
     * @return void
     * @throws TronException
     */
    public function execute(): void
    {
        User::with([
            'wallet' => fn($q) => $q->select(['id', 'user_id', 'address', 'failed_attempts'])->where('failed_attempts', '<', 3),
            'stakes' => fn($q) => $q->where('trx_amount', '>', 0)->whereDate('available_at', '<=', now())
        ])
            ->select(['id'])
            ->whereRelation('wallet', 'failed_attempts', '<', 3)
            ->chunkById(50, function (Collection $users) {
                $usersWithoutBandwidth = collect();
                $usersWithStake = $users->filter(fn($user) => $user->stakes->isNotEmpty());

                foreach ($usersWithStake as $user) {
                    if ($this->orderIsFilled()) {
                        exit();
                    }

                    try {
                        (new StakeService($user->wallet))->delegateResourceToOrder($this->order, $user->stakes->sum('trx_amount'));
                    } catch (NotEnoughBandwidthException $e) {
                        $usersWithoutBandwidth->push($user);
                    }
                    sleep(1);
                }

                foreach ($usersWithoutBandwidth as $user) {
                    if ($this->orderIsFilled()) {
                        exit();
                    }

                    try {
                        SendBonusBandwidth::dispatchSync($user->wallet->address);
                        sleep(1);
                        (new StakeService($user->wallet))->delegateResourceToOrder($this->order, $user->stakes->sum('trx_amount'));
                    } catch (\Throwable $e) {
                        Log::error('SendBonusBandwidth error. ' . $e->getMessage(), ['user_id' => $user->id]);
                        continue;
                    }
                }
            });
    }

    private function orderIsFilled(): bool
    {
        $this->order->refresh();

        return $this->order->resource_amount <= $this->order->executors()->sum('resource_amount');
    }

    /**
     * Обновление потребительского заказа
     *
     * @return void
     * @throws TronException
     */
    public function update(): void
    {
        if ($this->consumer->resource_amount < $this->order->executors()->sum('resource_amount')) {
            $this->refreshExecutorsResources();
        }

        $this->order->update([
            'resource_amount' => $this->consumer->resource_amount,
            'status' => $this->order->status === Statuses::new ? Statuses::new : Statuses::pending,
        ]);
    }

    /**
     * Актуализация делегированных ресурсов пользователей
     *
     * @return void
     * @throws TronException
     */
    private function refreshExecutorsResources(): void
    {
        $executors = $this->order->executors()->with(['wallet', 'order'])->get(['id', 'trx_amount']);
        $resourceDiff = $this->order->resource_amount - $this->consumer->resource_amount;
        $trx2Undelegate = floor($this->tron->energy2Trx($resourceDiff));

        foreach ($executors as $executor) {
            if ($trx2Undelegate <= 0) {
                break;
            }

            $trxAmount = min($executor->trx_amount, $trx2Undelegate);
            (new StakeService($executor->wallet))->undelegateResourceFromOrder($executor->order, $trxAmount);

            $trx2Undelegate -= $trxAmount;
            Sleep::for(config('app.sleep_ms'))->milliseconds();
        }
    }
}
