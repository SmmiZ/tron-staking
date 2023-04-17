<?php

namespace App\Services;

use App\Enums\TransactionTypes;
use App\Models\User;
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;

class StakeService
{
    private Tron $tron;

    public function __construct()
    {
        $this->tron = new Tron();
    }

    /**
     * Инициализация стейка
     *
     * @param User $user
     * @param int $amount
     * @return mixed
     * @throws TronException
     */
    public function store(User $user, int $amount): mixed
    {
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
}