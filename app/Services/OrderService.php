<?php

namespace App\Services;

use App\Enums\Statuses;
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use App\Models\{Consumer, Order};

class OrderService
{
    private Order $order;
    private Consumer $consumer;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->consumer = $order->consumer;
    }

    /**
     * Обновление потребительского заказа
     *
     * @return void
     * @throws TronException
     */
    public function update(): void
    {
        if ($this->consumer->resource_amount < $this->order->resource_amount && $this->order->executors) {
            $this->refreshExecutorsResources();
        }

        $status = $this->order->status === Statuses::new ? Statuses::new : Statuses::pending;

        $this->order->update([
            'resource_amount' => $this->consumer->resource_amount,
            'status' => $status,
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
        $unlockedExecutors = $this->order->executors()->with(['wallet'])
            ->where('unlocked_at', '<=', now())
            ->orderBy('trx_amount')
            ->get();

        $resourceDiff = $this->order->resource_amount - $this->consumer->resource_amount;
        $trxDiff = (new Tron())->energy2Trx($resourceDiff);

        if ($unlockedExecutors->sum('trx_amount') >= $trxDiff) {
            $leftToUndelegate = $trxDiff;
            foreach ($unlockedExecutors as $executor) {
                if ($leftToUndelegate <= 0) break;

                $trxAmount = min($executor->trx_amount, $leftToUndelegate);
                (new StakeService($executor->wallet))->undelegateResourceFromOrder($this->order, $trxAmount);

                $leftToUndelegate -= $trxAmount;
            }
        }

        //todo иначе - планируем джобу
//        $maxUnlockedAt = $executors->max('unlocked_at');
//        dd($maxUnlockedAt);
    }
}
