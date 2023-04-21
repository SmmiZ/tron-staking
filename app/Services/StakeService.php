<?php

namespace App\Services;

use App\Enums\{Statuses, TransactionTypes};
use App\Models\{Order, Wallet};
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
     * @return mixed
     * @throws TronException
     */
    public function store(int $amount): mixed
    {
        $trxAmount = min(
            $amount,
            $this->tron->getTrxBalance($this->wallet->address)
        );

        if ($trxAmount < 1) {
            throw new TronException('Not enough TRX to freeze');
        }

        $response = $this->tron->freezeUserBalance($this->wallet, $trxAmount);

        if (isset($response['code']) && $response['code'] != 'true') {
            throw new TronException($response['code'] ?: 'Unknown error');
        }

        $this->wallet->transactions()->create([
            'type' => TransactionTypes::stake,
            'trx_amount' => data_get($response,'raw_data.contract.0.parameter.value.frozen_balance') / Tron::ONE_SUN ?: null,
            'tx_id' => $response['txID'],
        ]);

        return $this->wallet->user->stakes()->create(['trx_amount' => $trxAmount])->value('id');
    }

    /**
     * Проголосовать за SR
     *
     * @return void
     * @throws TronException
     */
    public function vote(): void
    {
        $witnessAddress = $this->tron->getTopSrAddress();
        $response = $this->tron->voteWitness($witnessAddress, $this->wallet);

        $this->wallet->transactions()->create([
            'to' => $witnessAddress,
            'type' => TransactionTypes::vote,
            'trx_amount' => data_get($response,'raw_data.contract.0.parameter.value.votes.0.vote_count'),
            'tx_id' => $response['txID'],
        ]);

        if (isset($response['code']) && $response['code'] != 'true') {
            throw new TronException($response['code'] ?: 'Unknown error');
        }
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

        if (isset($response['code']) && $response['code'] != 'true') {
            throw new TronException($response['code'] ?: 'Unknown error');
        }

        //Запись транзакции
        $this->wallet->transactions()->create([
            'to' => $order->consumer->address,
            'type' => TransactionTypes::delegate,
            'trx_amount' => data_get($response,'raw_data.contract.0.parameter.value.balance') / Tron::ONE_SUN ?? $trxAmount,
            'tx_id' => $response['txID'],
        ]);
        //Запись исполнителя
        $givenResourceAmount = $trxAmount / $resources['TotalEnergyWeight'] * $resources['TotalEnergyLimit'];
        $order->executors()->updateOrCreate(['user_id' => $this->wallet->user_id], [
            'trx_amount' => DB::raw('trx_amount + ' . $trxAmount),
            'resource_amount' => DB::raw('resource_amount + ' . $givenResourceAmount),
            'unlocked_at' => now()->addDays(3),
        ]);
        //Обновление заказа
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

        if (isset($response['code']) && $response['code'] != 'true') {
            throw new TronException($response['code'] ?: 'Unknown error');
        }

        $this->wallet->transactions()->create([
            'type' => TransactionTypes::reward,
            'trx_amount' => $availableTrxReward,
            'tx_id' => $response['txID'],
        ]);
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

        if (isset($response['code']) && $response['code'] != 'true') {
            throw new TronException($response['code'] ?: 'Unknown error');
        }

        $this->wallet->transactions()->create([
            'from' => $order->consumer->address,
            'type' => TransactionTypes::undelegate,
            'trx_amount' => data_get($response,'raw_data.contract.0.parameter.value.balance') / Tron::ONE_SUN ?? $trxAmount,
            'tx_id' => $response['txID'],
        ]);

        $resourceAmount = (new Tron())->trx2Energy($trxAmount);
        $executor = $order->executors()->firstWhere('user_id', $this->wallet->user_id);

        $executor->trx_amount - $trxAmount <= 0
            ? $executor->update(['trx_amount' => 0, 'resource_amount' => 0])//$executor->delete(); //todo
            : $executor->update([
            'trx_amount' => DB::raw('trx_amount - ' . $trxAmount),
            'resource_amount' => DB::raw('resource_amount - ' . $resourceAmount)
        ]);
    }
}
