<?php

namespace App\Jobs;

use App\Services\Address;
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class GetReward implements ShouldQueue
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
            $trxWalletHex = Address::decode(config('app.trx_wallet'));
            $tron = new Tron(config('app.trx_wallet'), config('app.private_key'));

            $tron->withdrawBlockRewards($trxWalletHex);
        } catch (TronException $e) {
            exit($e->getMessage());
        }
    }
}
