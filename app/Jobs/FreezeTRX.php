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
            Wallet::query()->chunk(50, function ($wallets) {
                foreach ($wallets as $wallet) {
                    $tron = new Tron($wallet);

                    //Заморозить TRX и получить Energy & TP
                    $availableTrxSunAmount = $tron->getBalance();
                    $tron->freezeBalance2Energy($availableTrxSunAmount, config('app.withdrawal_wallet'));
                }
            });
        } catch (TronException $e) {
            Log::emergency('FreezeTRX-TronException: ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());

            exit($e->getMessage());
        }
    }
}
