<?php

namespace App\Jobs;

use App\Models\Wallet;
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;
use Throwable;

class FreezeTRX implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $hotSpotWallet = new Wallet([
                'address' => config('app.hot_spot_wallet'),
                'private_key' => config('app.hot_spot_private_key')
            ]);
            $tron = new Tron($hotSpotWallet);

            Wallet::query()->chunk(50, function ($wallets) use ($tron) {
                foreach ($wallets as $wallet) {
                    $freezeAmount = $tron->getBalance($wallet->address);
                    $response = $tron->freezeTrustedBalance($freezeAmount, $wallet->address);

                    if (isset($response['code']) && $response['code'] != 'true') {
                        throw new TronException($response['code']);
                    }
                }
            });
            //todo мы морозим, но делегировать ресурсы теперь надо отдельно
        } catch (TronException|Throwable $e) {
            Log::emergency('FreezeTRX-TronException: ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());

            exit($e->getMessage());
        }
    }
}
