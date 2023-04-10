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

    private Tron $tron;

    /**
     * Create a new job instance.
     * @throws TronException
     */
    public function __construct()
    {
        $this->tron = new Tron(config('app.hot_spot_wallet'), config('app.hot_spot_private_key'));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Wallet::query()->chunk(50, function ($wallets) {
            foreach ($wallets as $wallet) {
                try {
                    $freezeAmount = $this->tron->getBalance($wallet->address);
                    $response = $this->tron->freezeUserBalance($freezeAmount, $wallet->address);

                    throw_if(isset($response['code']) && $response['code'] != 'true', TronException::class, $response['code']);
                } catch (TronException|Throwable $e) {
                    Log::emergency('FreezeTRX-Exception', [
                        'wallet_id' => $wallet->id,
                        'error' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine(),
                    ]);
                    dump($e->getMessage());
                }
            }
        });
    }
}
