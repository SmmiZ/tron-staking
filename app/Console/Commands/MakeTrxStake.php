<?php

namespace App\Console\Commands;

use App\Services\Address;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{Http, Log};

class MakeTrxStake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tron:make-stake';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $url = 'https://api.trongrid.io';
        $authHeader = ['TRON-PRO-API-KEY' => config('app.tron_api_key')];

        $trxWallet = config('app.trx_wallet');
        $withdrawalWallet = config('app.withdrawal_wallet');

        $trxWalletHex = Address::decode($trxWallet);
        $withdrawalWalletHex = Address::decode($withdrawalWallet);

        // 10 000 000 007 всего
        $bandwidthAmount = $energyAmount = 5000000000;

        //Информация об аккаунте
//        $account = Http::withHeaders($authHeader)->post($url . '/wallet/getaccount', [
//            'address' => $trxWalletHex,
//        ])->json();
//        $account = Http::withHeaders($authHeader)->get($url . '/v1/accounts/' . $trxWalletHex)->json();

        try {
            //Получить список всех SR и определить лучший
            $witnesses = Http::withHeaders($authHeader)->get($url . '/wallet/listwitnesses')->json('witnesses');
            $maxVoteCount = $srAddress = 0;
            foreach ($witnesses as $sr) {
                if (isset($sr['voteCount'], $sr['address']) && $sr['voteCount'] > $maxVoteCount) {
                    $maxVoteCount = $sr['voteCount'];
                    $srAddress = $sr['address'];
                }
            }
            info('SR', ['voteCount' => $maxVoteCount, 'address' => $srAddress]);

            //Заморозить и получить TP
            $freezeBP = Http::withHeaders($authHeader)->post($url . '/wallet/freezebalance', [
                'owner_address' => $trxWalletHex,
                'frozen_balance' => $energyAmount,
                'resource' => 'ENERGY',
                'frozen_duration' => 3,
                'receiver_address' => $withdrawalWalletHex
            ]);
            info('$freezeBP', $freezeBP->json());
            $freezeEnergy = Http::withHeaders($authHeader)->post($url . '/wallet/freezebalance', [
                'owner_address' => $trxWalletHex,
                'frozen_balance' => $bandwidthAmount,
                'resource' => 'BANDWIDTH',
                'frozen_duration' => 3,
                'receiver_address' => $withdrawalWalletHex
            ]);
            info('$freezeEnergy', $freezeEnergy->json());

            //Проголосовать за топовый SR
            $vote = Http::withHeaders($authHeader)->post($url . '/wallet/votewitnessaccount', [
                'owner_address' => $trxWalletHex,
                'votes' => [
                    'vote_address' => $srAddress,
                    'vote_count' => ($bandwidthAmount + $energyAmount) / 1000000,
                ]
            ]);
            info('$vote', $vote->json());

            //Получить награду
//        $reward = Http::withHeaders($authHeader)->post($url . '/wallet/getReward', [
//            'address' => $withdrawalWallet,
//        ]);
        } catch (\Throwable $e) {
            Log::critical('Сбой в работе скрипта', [
                'error' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
            ]);
        }
    }
}
