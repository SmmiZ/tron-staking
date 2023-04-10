<?php

namespace App\Jobs;

use App\Enums\TransactionTypes;
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
     * @throws TronException
     */
    public function handle(): void
    {
        $tron = new Tron(config('app.hot_spot_wallet'), config('app.hot_spot_private_key'));

        Wallet::query()->chunk(50, function ($wallets) use ($tron) {
            foreach ($wallets as $wallet) {
                try {
                    $response = $tron->freezeUserBalance($wallet);

                    if (isset($response['code']) && $response['code'] != 'true') {
                        throw new TronException($response['code'] ?: 'Unknown error');
                    }

                    $wallet->transactions()->create([
                        'to' => $tron->address['base58'],
                        'type' => TransactionTypes::staking,
                        'amount' => data_get($response, 'raw_data.contract.0.parameter.value.frozen_balance') / Tron::ONE_SUN ?: null,
                        'tx_id' => $response['txID'],
                    ]);
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
