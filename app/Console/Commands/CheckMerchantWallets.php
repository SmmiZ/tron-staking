<?php

namespace App\Console\Commands;

use App\Enums\InternalTxTypes;
use App\Models\{InternalTx, MerchantWallet};
use App\Services\TronApi\Tron;
use Illuminate\Console\Command;

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
        MerchantWallet::where('created_at', '>=', now()->subHour())->orderBy('id')->chunk(50, function ($merchants) {
            foreach ($merchants as $merchant) {
                $trxAmount = $this->tron->getTrxBalance($merchant->address);

                if ($trxAmount < 1) continue;

                $tron = new Tron($merchant->address, $merchant->private_key);
                $tron->sendTrx(config('app.hot_spot_wallet'), $trxAmount, $merchant->address);

                InternalTx::create([
                    'user_id' => $merchant->user_id,
                    'amount' => $trxAmount,
                    'received' => $trxAmount,
                    'type' => InternalTxTypes::fromName('topUp'),
                ]);
            }
        });
    }
}
