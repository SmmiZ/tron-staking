<?php

namespace App\Services;

use App\Enums\Statuses;
use App\Jobs\UndelegateExecutorResources;
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
        if ($this->consumer->resource_amount < $this->order->executors()->sum('resource_amount')) {
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
        $executors = $this->order->executors()->orderBy('unlocked_at')->get(['id', 'trx_amount', 'unlocked_at']);
        $resourceDiff = $this->order->resource_amount - $this->consumer->resource_amount;
        $trx2Undelegate = (new Tron())->energy2Trx($resourceDiff);

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
