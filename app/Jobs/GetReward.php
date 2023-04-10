<?php

namespace App\Jobs;

use App\Models\Wallet;
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Throwable;

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
     * @throws TronException
     */
    public function handle(): void
    {
        $tron = new Tron(config('app.hot_spot_wallet'), config('app.hot_spot_private_key'));

        Wallet::query()->chunk(50, function ($wallets) use ($tron) {
            foreach ($wallets as $wallet) {
                try {
                    $response = $tron->withdrawBlockRewards(config('app.hot_spot_wallet')); //todo прояснить по выводу

                    throw_if(isset($response['code']) && $response['code'] != 'true', TronException::class, $response['code']);
                } catch (TronException|Throwable $e) {
                    Log::emergency('GetReward-Exception', [
                        'wallet_id' => $wallet->id,
                        'error' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine(),
                    ]);
                    dump($e->getMessage());
                }
            }
        });
    }
}
