<?php

namespace App\Services;

use App\Enums\{Statuses, TronTxTypes};
use App\Events\{NewStakeEvent, UnStakeEvent};
use App\Models\{Order, OrderExecutor, TronTx, Wallet};
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Support\Facades\DB;

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
        $this->wallet = $wallet;

        if (!$this->tron->hasAccess($this->wallet->address)) {
            throw new TronException('Permission denied');
        }
    }

    /**
     * Заморозить TRX (инициализация стейка)
     *
     * @param int $amount
     * @return bool
     * @throws TronException
     */
    public function stake(int $amount): bool
    {
        $trxAmount = min($amount, $this->tron->getTrxBalance($this->wallet->address));

        if ($trxAmount < 1) {
            throw new TronException('Not enough TRX to freeze');
        }

        $this->wallet->user->stake()->updateOrCreate([], ['trx_amount' => DB::raw('trx_amount + ' . $trxAmount)]);
        $response = $this->tron->freezeUserBalance($this->wallet, $trxAmount);

        event(new NewStakeEvent($this->wallet->user));
        $this->storeTransaction($response);
        $this->vote();

        return true;
    }

    /**
     * Проголосовать за SR
     *
     * @return void
     * @throws TronException
     */
    private function vote(): void
    {
        $witnessAddress = $this->tron->getTopSrAddress();
        $response = $this->tron->voteWitness($witnessAddress, $this->wallet);

        $this->storeTransaction($response);
    }

    /**
     * Делегировать ресурс пользователя для заказа
     *
     * @param Order $order
     * @param int $stakeAmount
     * @return void
     * @throws TronException
     */
    public function delegateResourceToOrder(Order $order, int $stakeAmount): void
    {
        $requiredResource = $order->resource_amount - $order->executors()->sum('resource_amount');
        $resources = $this->tron->getAccountResources($this->wallet->address);
        $availableTrx = $resources['tronPowerLimit'] ?? 0;

        match (true) {
            $availableTrx <= 0 => throw new TronException('Not enough Energy to delegate'),
            $requiredResource <= 0 => throw new TronException('Order is already filled'),
            default => null,
        };

        $requiredTrx = ceil($requiredResource / $resources['TotalEnergyLimit'] * $resources['TotalEnergyWeight']);
        $trxAmount = min($availableTrx, $stakeAmount, $requiredTrx);

        $response = $this->tron->delegateResource($this->wallet->address, $order->consumer->address, $trxAmount);
        $this->storeTransaction($response);

        //Обновление исполнителя и заказа
        $givenResourceAmount = $trxAmount / $resources['TotalEnergyWeight'] * $resources['TotalEnergyLimit'];
        $order->executors()->updateOrCreate(['user_id' => $this->wallet->user_id], [
            'trx_amount' => DB::raw('trx_amount + ' . $trxAmount),
            'resource_amount' => DB::raw('resource_amount + ' . $givenResourceAmount),
            'unlocked_at' => now()->addDays(3),
        ]);
        $order->update(['status' => Statuses::pending]);
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
        $response = $this->tron->undelegateResource($this->wallet->address, $order->consumer->address, $trxAmount);
        $this->storeTransaction($response);

        $resourceAmount = (new Tron())->trx2Energy($trxAmount);
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
    public function unstake(int $trxAmount): bool
    {
        if (!$this->withdrawUnlockedResources($trxAmount)) {
            return false;
        }

        $response = $this->tron->unfreezeUserBalance($this->wallet->address, $trxAmount);
        $this->storeTransaction($response);
        event(new UnStakeEvent($this->wallet->user));

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
            TronTxTypes::VoteWitnessContract => data_get($contract, 'parameter.value.votes.0.vote_count'),
            TronTxTypes::FreezeBalanceV2Contract => data_get($contract, 'parameter.value.frozen_balance') / Tron::ONE_SUN,
            TronTxTypes::UnfreezeBalanceV2Contract => data_get($contract, 'parameter.value.unfreeze_balance') / Tron::ONE_SUN,
            TronTxTypes::DelegateResourceContract, TronTxTypes::UnDelegateResourceContract => data_get($contract, 'parameter.value.balance') / Tron::ONE_SUN,
            default => $trxAmount
        };

        $to = $type === TronTxTypes::VoteWitnessContract
            ? data_get($contract, 'parameter.value.votes.0.vote_address')
            : data_get($contract, 'parameter.value.receiver_address');

        TronTx::create([
            'from' => data_get($contract, 'parameter.value.owner_address'),
            'to' => $to,
            'type' => $type,
            'trx_amount' => $trxAmount,
            'tx_id' => $response['txID'],
        ]);
    }
}
