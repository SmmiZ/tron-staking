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

    public function __construct(Wallet $wallet)
    {
        $this->tron = new Tron();
        $this->wallet = $wallet;
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
            $this->wallet->stake_limit,
            $this->tron->getBalance($this->wallet->address, true)
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
            'amount' => data_get($response,'raw_data.contract.0.parameter.value.frozen_balance') / Tron::ONE_SUN ?: null,
            'tx_id' => $response['txID'],
        ]);

        return $this->wallet->user->stakes()->create(['amount' => $trxAmount])->value('id');
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
     * @param Order $order
     * @param int $stakeAmount
     * @return void
     * @throws TronException
     */
    public function fillOrder(Order $order, int $stakeAmount): void
    {
        $leftToFill = $order->amount - $order->executors()->sum('amount');

        $resources = $this->tron->getAccountResources($this->wallet->address);
        $availableTrx = $resources['tronPowerLimit'] ?? 0;

        match (true) {
            $availableTrx <= 0 => throw new TronException('Not enough Energy to delegate'),
            $leftToFill <= 0 => throw new TronException('Order is already filled'),
            default => null,
        };

        $trxAmount = min($availableTrx, $stakeAmount, $leftToFill);
        $response = $this->tron->delegateResource($this->wallet->address, $order->consumer->address, $trxAmount);

        if (isset($response['code']) && $response['code'] != 'true') {
            throw new TronException($response['code'] ?: 'Unknown error');
        }

        //Запись транзакции
        $this->wallet->transactions()->create([
            'to' => $order->consumer->address,
            'type' => TransactionTypes::delegate,
            'amount' => data_get($response,'raw_data.contract.0.parameter.value.balance') / Tron::ONE_SUN ?? 0,
            'tx_id' => $response['txID'],
        ]);
        //Запись исполнителя
        $order->executors()->updateOrCreate(
            ['user_id' => $this->wallet->user_id],
            ['amount' => DB::raw('amount + ' . $trxAmount)]
        );
        //Обновление заказа
        $order->amount == $order->executors()->sum('amount')
            ? $order->update(['status' => Statuses::completed, 'executed_at' => now()])
            : $order->update(['status' => Statuses::pending]);
    }
}