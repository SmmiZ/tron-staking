<?php

namespace App\Services;

use App\Enums\Statuses;
use App\Exceptions\NotEnoughBandwidthException;
use Illuminate\Support\Facades\Log;
use App\Jobs\{SendBonusBandwidth, UndelegateExecutorResources};
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
     * @throws TronException
     */
    public function execute(): void
    {
        User::with(['wallet', 'stake:id,user_id,trx_amount,failed_attempts'])
            ->whereHas('wallet')
            ->whereRelation('stake', 'trx_amount', '>', 0)
            ->whereRelation('stake', 'failed_attempts', '<', 3)
            ->orderBy('updated_at', 'desc')
            ->chunk(50, function ($users) {
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

                foreach ($usersWithoutBandwidth as $user) {
                    if ($this->orderIsFilled()) {
                        exit();
                    }

                    try {
                        SendBonusBandwidth::dispatchSync($user->wallet->address);
                        sleep(1);
                        (new StakeService($user->wallet))->delegateResourceToOrder($this->order, $user->stake->trx_amount);
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
