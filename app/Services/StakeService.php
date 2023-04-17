<?php

namespace App\Services;

use App\Enums\TransactionTypes;
use App\Models\{User, Wallet};
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Support\Facades\Log;

class StakeService
{
    private Tron $tron;

    public function __construct()
    {
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
            Log::emergency('VoteSR-Exception', [
                'wallet_id' => $wallet->id,
                'error' => $response['code'],
            ]);
        }
    }
}