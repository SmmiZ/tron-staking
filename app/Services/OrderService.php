<?php

namespace App\Services;

use App\Enums\Statuses;
use App\Exceptions\NotEnoughBandwidthException;
use App\Jobs\UndelegateExecutorResources;
use App\Models\{Consumer, Order, User};
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;

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
     * @throws NotEnoughBandwidthException
     * @throws TronException
     */
    public function execute(): void
    {
        $trxBandwidthBonus = ceil($this->tron->bandwidth2Trx(config('app.bandwidth_bonus')));

        User::with(['wallet', 'stake:id,user_id,trx_amount,failed_attempts'])
            ->whereHas('wallet')
            ->whereRelation('stake', 'trx_amount', '>', 0)
            ->whereRelation('stake', 'failed_attempts', '<', 3)
            ->orderBy('sort')
            ->chunk(50, function ($users) use ($trxBandwidthBonus) {
                $usersWithoutBandwidth = collect();
                foreach ($users as $user) {
                    if ($this->orderIsFilled()) {
                        exit();
                    }

                    try {
                        (new StakeService($user->wallet))->delegateResourceToOrder($this->order, $user->stake->trx_amount);
                    } catch (NotEnoughBandwidthException $e) {
                        $usersWithoutBandwidth->push($user);
                    }
                    sleep(1);
                }

                $usersWithoutBandwidth->each(function (User $user) use ($trxBandwidthBonus) {
                    if ($this->orderIsFilled()) {
                        exit();
                    }

                    $this->tron->delegateHotSpotBandwidth($user->wallet->address, $trxBandwidthBonus);
                    sleep(1);
                    (new StakeService($user->wallet))->delegateResourceToOrder($this->order, $user->stake->trx_amount);
                });
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
     */
    private function refreshExecutorsResources(): void
    {
        $executors = $this->order->executors()->orderBy('unlocked_at')->get(['id', 'trx_amount', 'unlocked_at']);
        $resourceDiff = $this->order->resource_amount - $this->consumer->resource_amount;
        $trx2Undelegate = floor($this->tron->energy2Trx($resourceDiff));

        foreach ($executors as $executor) {
            if ($trx2Undelegate <= 0) {
                break;
            }

            $trxAmount = min($executor->trx_amount, $trx2Undelegate);
            UndelegateExecutorResources::dispatch($executor->id, $trxAmount)->delay(
                $executor->unlocked_at <= now() ? now() : $executor->unlocked_at
            );

            $trx2Undelegate -= $trxAmount;
        }
    }
}
