<?php

namespace App\Services;

use App\Enums\{Statuses, TronTxTypes};
use App\Events\{NewStakeEvent, UnStakeEvent};
use App\Exceptions\NotEnoughBandwidthException;
use App\Jobs\{RevokeBonusBandwidth, SendBonusBandwidth, Vote};
use App\Models\{Order, OrderExecutor, Stake, TronTx, Wallet};
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Sleep;

class StakeService
{
    private Tron $tron;
    private Wallet $wallet;

    /**
     * @throws TronException
     */
    public function __construct(Wallet $wallet)
    {
        $this->tron = new Tron();
        $this->wallet = $wallet->load(['user']);

        if (!$this->tron->hasAccess($this->wallet->address)) {
            throw new TronException('Permission denied');
        }
        Sleep::for(config('app.sleep_ms'))->milliseconds();
    }

    /**
     * Заморозить TRX (инициализация нового стейка)
     *
     * @param int $amount
     * @return bool
     * @throws TronException
     */
    public function freeze(int $amount): bool
    {
        $trxAmount = min($amount, $this->tron->getTrxBalance($this->wallet->address));

        if ($trxAmount < 1) {
            throw new TronException('Not enough TRX to freeze');
        }

        $response = $this->tron->freezeTrx2Energy($this->wallet, $trxAmount);
        $this->wallet->user->stakes()->create(['trx_amount' => $trxAmount]);

        SendBonusBandwidth::dispatch($this->wallet->address);
        Vote::dispatch($this->wallet->address)->delay(now()->addMinute());
        event(new NewStakeEvent($this->wallet->user));

        $this->storeTransaction($response);

        return true;
    }

    /**
     * Делегировать ресурс пользователя для заказа
     *
     * @param Order $order
     * @param int $availableStakedTrx
     * @return void
     * @throws TronException|NotEnoughBandwidthException
     */
    public function delegateResourceToOrder(Order $order, int $availableStakedTrx): void
    {
        $requiredResource = $order->resource_amount - $order->executors()->sum('resource_amount');
        $resources = $this->tron->getAccountResources($this->wallet->address);

        $walletTrx = $resources['tronPowerLimit'] ?? 0;
        $stakedFreeTrx = $availableStakedTrx - OrderExecutor::where('user_id', $this->wallet->user_id)->sum('trx_amount');

        match (true) {
            $stakedFreeTrx <= 1 => $this->handleFailedAttempt('Not enough staked TRX'),
            $walletTrx <= 1 => $this->handleFailedAttempt('Not enough TRX in the wallet'),
            $requiredResource <= 0 => throw new TronException('Order is already filled'),
            $this->getAvailableBandwidth($resources) <= 300 => throw new NotEnoughBandwidthException(),
            default => null
        };

        $requiredTrx = floor($requiredResource / $resources['TotalEnergyLimit'] * $resources['TotalEnergyWeight']);
        $trx2Delegate = min($walletTrx, $requiredTrx, $stakedFreeTrx);

        $response = $this->tron->delegateEnergy($this->wallet->address, $order->consumer->address, $trx2Delegate);
        $this->storeTransaction($response);

        //Обновление исполнителя и заказа
        $givenResourceAmount = $trx2Delegate / $resources['TotalEnergyWeight'] * $resources['TotalEnergyLimit'];
        $order->executors()->updateOrCreate(['user_id' => $this->wallet->user_id], [
            'trx_amount' => DB::raw('trx_amount + ' . $trx2Delegate),
            'resource_amount' => DB::raw('resource_amount + ' . $givenResourceAmount),
            'unlocked_at' => now()->addDays(3),
        ]);
        $order->update(['status' => Statuses::pending]);
    }

    private function handleFailedAttempt(string $message)
    {
        Wallet::firstWhere('id', $this->wallet->id)->increment('failed_attempts');
        throw new TronException($message);
    }

    private function getAvailableBandwidth(array $resources): int
    {
        return data_get($resources, 'freeNetLimit', 0)
            + data_get($resources, 'NetLimit', 0)
            - data_get($resources, 'NetUsed', 0)
            - data_get($resources, 'freeNetUsed', 0);
    }

    /**
     * Забрать награду для пользователя
     *
     * @return void
     * @throws TronException
     */
    public function getReward(): void
    {
        $availableTrxReward = $this->tron->getRewardAmount($this->wallet->address);

        if ($availableTrxReward <= 0) {
            return;
        }

        $response = $this->tron->rewardWithdraw($this->wallet->address);
        $this->storeTransaction($response, $availableTrxReward);
    }

    /**
     * Отозвать ресурс пользователя из заказа
     *
     * @param Order $order
     * @param int $trxAmount
     * @return void
     * @throws TronException
     */
    public function undelegateResourceFromOrder(Order $order, int $trxAmount): void
    {
        $response = $this->tron->undelegateEnergy($this->wallet->address, $order->consumer->address, $trxAmount);
        $this->storeTransaction($response);

        $resourceAmount = $this->tron->trx2Energy($trxAmount);
        $executor = $order->executors()->firstWhere('user_id', $this->wallet->user_id);

        $executor->trx_amount - $trxAmount <= 0
            ? $executor->update(['trx_amount' => 0, 'resource_amount' => 0, 'deleted_at' => now()])
            : $executor->update([
            'trx_amount' => DB::raw('trx_amount - ' . $trxAmount),
            'resource_amount' => DB::raw('resource_amount - ' . $resourceAmount)
        ]);
    }

    /**
     * Разморозить TRX
     *
     * @param int $trxAmount
     * @return bool
     * @throws TronException
     */
    public function unfreeze(int $trxAmount): bool
    {
        if (!$this->withdrawUnlockedResources($trxAmount)) {
            return false;
        }

        $response = $this->tron->unfreezeUserBalance($this->wallet->address, $trxAmount);
        $this->storeTransaction($response);

        event(new UnStakeEvent($this->wallet->user));
        RevokeBonusBandwidth::dispatch($this->wallet->address);

        return true;
    }

    /**
     * Отозвать не заблокированные ресурсы
     *
     * @param int $withdrawAmount
     * @return bool
     * @throws TronException
     */
    private function withdrawUnlockedResources(int $withdrawAmount): bool
    {
        $executors = OrderExecutor::with(['order'])
            ->where('user_id', $this->wallet->user_id)
            ->where('unlocked_at', '<=', now())
            ->orderBy('trx_amount')
            ->get();

        foreach ($executors as $executor) {
            $trxToUndelegate = min($withdrawAmount, $executor->trx_amount);
            $withdrawAmount -= $trxToUndelegate;

            $this->undelegateResourceFromOrder($executor->order, $trxToUndelegate);

            if ($withdrawAmount <= 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Запись транзакции в БД
     *
     * @param array $response
     * @param int $trxAmount
     * @return void
     * @throws TronException
     */
    private function storeTransaction(array $response, int $trxAmount = 0): void
    {
        if (isset($response['code']) && $response['code'] != 'true') {
            throw new TronException($response['code'] ?: 'Unknown error');
        }

        $contract = data_get($response, 'raw_data.contract.0');
        $type = TronTxTypes::fromName(data_get($contract, 'type'));

        $trxAmount = match ($type) {
            TronTxTypes::FreezeBalanceV2Contract => data_get($contract, 'parameter.value.frozen_balance') / Tron::ONE_SUN,
            TronTxTypes::UnfreezeBalanceV2Contract => data_get($contract, 'parameter.value.unfreeze_balance') / Tron::ONE_SUN,
            TronTxTypes::DelegateResourceContract, TronTxTypes::UnDelegateResourceContract => data_get($contract, 'parameter.value.balance') / Tron::ONE_SUN,
            default => $trxAmount
        };

        TronTx::create([
            'from' => data_get($contract, 'parameter.value.owner_address'),
            'to' => data_get($contract, 'parameter.value.receiver_address'),
            'type' => $type,
            'trx_amount' => $trxAmount,
            'tx_id' => $response['txID'],
        ]);
    }
}
