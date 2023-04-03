<?php

namespace App\Jobs;

use App\Services\Address;
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
            $tron = new Tron(config('app.trx_wallet'), config('app.private_key'));
            $availableTrxSunAmount = $tron->getBalance();

            //Получатель энергии (кого бустим)
            $receiverHexAddress = Address::decode(config('app.withdrawal_wallet'));

            //Заморозить TRX и получить Energy & TP
            $tron->freezeBalance2Energy($availableTrxSunAmount, $receiverHexAddress);
        } catch (TronException $e) {
            Log::emergency('TronException: ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }
}
