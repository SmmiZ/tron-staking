<?php

namespace App\Console\Commands;

use App\Services\Address;
use IEXBase\TronAPI\Exception\TronException;
use IEXBase\TronAPI\Provider\HttpProvider;
use IEXBase\TronAPI\Tron;
use Illuminate\Console\Command;

class GetReward extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staking:get-reward';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Забрать награду';

    public function handle()
    {
        try {
            $withdrawalWallet = config('app.withdrawal_wallet');
            $trxWalletHex = Address::decode(config('app.trx_wallet'));

            $fullNode = $solidityNode = $eventServer = new HttpProvider('https://api.trongrid.io');
            $tron = new Tron($fullNode, $solidityNode, $eventServer, null, null, config('app.private_key'));

            $getReward = $tron->getManager()->request('wallet/getReward', ['address' => $withdrawalWallet]);
            $signedTransaction = $tron->signTransaction($getReward);
            $tron->sendRawTransaction($signedTransaction);

            //todo просто получаем данные, потом еще забрать?
//            $tron->withdrawBlockRewards($trxWalletHex);
        } catch (TronException $e) {
            $this->error('Something went wrong');

            exit($e->getMessage());
        }

        $this->info('The command was successful!');
    }
}
