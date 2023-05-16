<?php

namespace App\Jobs;

use App\Enums\TronTxTypes;
use App\Models\{TronTx, User};
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class WithdrawDefrostedTrx implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly User $user)
    {
        //
    }

    /**
     * Execute the job.
     * @throws TronException
     */
    public function handle(): void
    {
        $tron = new Tron();
        $availableTrxAmount = $tron->getCanWithdrawUnfreezeAmount($this->user->wallet->address);

        if ($availableTrxAmount < 1) {
            return;
        }

        $response = $tron->withdrawDefrostedTrx($this->user->wallet->address);

        TronTx::create([
            'from' => null,
            'to' => data_get($response, 'raw_data.contract.0.parameter.value.owner_address'),
            'type' => TronTxTypes::fromName(data_get($response, 'raw_data.contract.0.type')),
            'trx_amount' => $availableTrxAmount,
            'tx_id' => $response['txID'],
        ]);
    }
}
