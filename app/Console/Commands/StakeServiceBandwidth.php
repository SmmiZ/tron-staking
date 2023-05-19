<?php

namespace App\Console\Commands;

use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Console\Command;

class StakeServiceBandwidth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:stake-service-bandwidth {trx_amount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Застейкать TRX для получения сервисного bandwidth на хот спот кошелек';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $trxAmount = (int)$this->argument('trx_amount');

        if ($trxAmount <= 0) {
            $this->error('The amount must be greater than zero!');

            return;
        }

        $tron = new Tron();
        $resources = $tron->getAccountResources();
        $currentBandwidth = data_get($resources, 'freeNetLimit', 0) + data_get($resources, 'NetLimit', 0);

        if ($this->confirm("Текущий bandwidth = $currentBandwidth. Застейкать еще $trxAmount TRX?", true)) {
            try {
                $tron->freezeHotSpotBalance($trxAmount);
            } catch (TronException $e) {
                $this->error($e->getMessage());

                return;
            }

            $this->info('The command was successful!');
        }
    }
}
