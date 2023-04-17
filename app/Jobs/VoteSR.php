<?php

namespace App\Jobs;

use App\Models\Wallet;
use App\Services\StakeService;
use App\Services\TronApi\Exception\TronException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class VoteSR implements ShouldQueue
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
        Wallet::with(['user'])->chunk(50, function ($wallets) {
            foreach ($wallets as $wallet) {
                try {
                    (new StakeService())->vote($wallet);
                } catch (TronException $e) {
                    Log::emergency('VoteSR-Exception', [
                        'wallet_id' => $wallet->id,
                        'error' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine(),
                    ]);
                }
            }
        });
    }
}
