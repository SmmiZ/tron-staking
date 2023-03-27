<?php

namespace App\Console\Commands;

use App\Services\Address;
use IEXBase\TronAPI\Exception\TronException;
use IEXBase\TronAPI\Provider\HttpProvider;
use IEXBase\TronAPI\Tron;
use Illuminate\Console\Command;

class Start extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staking:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запустить процесс стейкинга TRX';

    public function handle()
    {
        $trxWalletHex = Address::decode(config('app.trx_wallet'));
        $bandwidthAmount = $energyAmount = 5000;

        try {
            $fullNode = $solidityNode = $eventServer = new HttpProvider('https://api.trongrid.io');
            $tron = new Tron($fullNode, $solidityNode, $eventServer, null, null, config('app.private_key'));

            //Заморозить TRX и получить TP
            $tron->freezeBalance($energyAmount, 3, 'ENERGY', $trxWalletHex);
            $tron->freezeBalance($bandwidthAmount, 3, 'BANDWIDTH', $trxWalletHex);
        } catch (TronException $e) {
            $this->error('Something went wrong');

            exit($e->getMessage());
        }

        $this->info('The command was successful!');
    }
}
