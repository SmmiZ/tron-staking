<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Enums\{Statuses, TransactionTypes};
use App\Models\{Order, Stake, User, Wallet};
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;

class StakeService
{
    private Tron $tron;

    public function __construct()
    {
        //todo wallet? user?
        $this->tron = new Tron();
    }

    /**
     * Заморозить TRX (инициализация стейка)
     *
     * @param User $user
     * @param int $amount
     * @return mixed
     * @throws TronException
     */
    public function store(User $user, int $amount): mixed
    {
        //todo user -> wallet
        $balance = $this->tron->getBalance($user->wallet->address);
        $limit = $user->wallet->stake_limit * Tron::ONE_SUN;
        $stakeAmount = min($balance, $limit, $amount * Tron::ONE_SUN);

        if ($stakeAmount < Tron::ONE_SUN) {
            throw new TronException('Not enough TRX to freeze');
        }

        $response = $this->tron->freezeUserBalance($user->wallet, $stakeAmount);

        if (isset($response['code']) && $response['code'] != 'true') {
            throw new TronException($response['code'] ?: 'Unknown error');
        }

        $user->wallet->transactions()->create([
            'type' => TransactionTypes::staking,
            'amount' => data_get($response,'raw_data.contract.0.parameter.value.frozen_balance') / Tron::ONE_SUN ?: null,
            'tx_id' => $response['txID'],
        ]);

        return $user->stakes()->create(['amount' => $stakeAmount])->value('id');
    }

    /**
     * Проголосовать за SR
     *
     * @param Wallet $wallet
     * @return void
     * @throws TronException
     */
    public function vote(Wallet $wallet): void
    {
        $witnessAddress = $this->tron->getTopSrAddress();
        $response = $this->tron->voteWitness($witnessAddress, $wallet);

        $wallet->transactions()->create([
            'to' => $witnessAddress,
            'type' => TransactionTypes::vote,
            'amount' => data_get($response,'raw_data.contract.0.parameter.value.votes.0.vote_count') / Tron::ONE_SUN, //todo проверить amount
            'tx_id' => $response['txID'],
        ]);

        if (isset($response['code']) && $response['code'] != 'true') {
            throw new TronException($response['code'] ?: 'Unknown error');
        }
    }

    /**
     * Заполнить заказ ресурсом пользователя
     *
     * @param Stake $stake
     * @param Order $order
     * @return void
     * @throws TronException
     */
    public function fillOrder(Stake $stake, Order $order): void
    {
        $wallet = $stake->user->wallet;
        $leftToFill = $order->amount - $order->executors()->sum('amount');

        $resources = $this->tron->getAccountResources($wallet->address);
        $availableTrx = $resources['tronPowerLimit'] ?? 0;

        match (true) {
            $availableTrx <= 0 => throw new TronException('Not enough Energy to delegate'),
            $leftToFill <= 0 => throw new TronException('Order is already filled'),
            default => null,
        };

        $trxAmount = min($availableTrx, $stake->amount, $leftToFill);
        $response = $this->tron->delegateResource($wallet->address, $order->consumer->address, $trxAmount);

        if (isset($response['code']) && $response['code'] != 'true') {
            throw new TronException($response['code'] ?: 'Unknown error');
        }

        //Запись транзакции
        $wallet->transactions()->create([
            'to' => $order->consumer->address,
            'type' => TransactionTypes::delegate,
            'amount' => data_get($response,'raw_data.contract.0.parameter.value.balance') / Tron::ONE_SUN ?? 0,
            'tx_id' => $response['txID'],
        ]);
        //Запись исполнителя
        $order->executors()->updateOrCreate(
            ['user_id' => $stake->user_id],
            ['amount' => DB::raw('amount + ' . $trxAmount)] //todo проверить increment
        );
        //Обновление заказа
        $order->amount == $order->executors()->sum('amount')
            ? $order->update(['status' => Statuses::completed, 'executed_at' => now()])
            : $order->update(['status' => Statuses::pending]);
    }
}