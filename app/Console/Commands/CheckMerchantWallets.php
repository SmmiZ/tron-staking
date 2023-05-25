<?php

namespace App\Console\Commands;

use App\Enums\InternalTxTypes;
use App\Models\{InternalTx, MerchantWallet};
use App\Services\TronApi\Tron;
use Illuminate\Console\Command;
use Illuminate\Support\Sleep;

class CheckMerchantWallets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-merchant-wallets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверяет кошельки на наличие средств и зачисляет на баланс пользователей';

    private Tron $tron;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->tron = new Tron();
        MerchantWallet::where('created_at', '>=', now()->subHour())->orderBy('id')->chunk(50, function ($merchantWallets) {
            foreach ($merchantWallets as $merchantWallet) {
                $trxAmount = $this->tron->getTrxBalance($merchantWallet->address);
                Sleep::for(150)->milliseconds();

                if ($trxAmount < 1) continue;

                $tron = new Tron($merchantWallet->address, $merchantWallet->private_key);
                $tron->sendTrx($merchantWallet->address, config('app.hot_spot_wallet'), $trxAmount);

                InternalTx::create([
                    'user_id' => $merchantWallet->user_id,
                    'amount' => $trxAmount,
                    'received' => $trxAmount,
                    'type' => InternalTxTypes::topUp,
                ]);
            }
        });
    }
}
