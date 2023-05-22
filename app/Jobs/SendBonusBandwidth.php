<?php

namespace App\Jobs;

use App\Enums\TronTxTypes;
use App\Models\TronTx;
use App\Services\TronApi\Tron;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\{ShouldBeUnique, ShouldQueue};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class SendBonusBandwidth implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly string $receiverAddress)
    {
        //
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return $this->receiverAddress;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tron = new Tron();

        $trxEquivalent = ceil($tron->bandwidth2Trx(config('app.bandwidth_bonus')));
        $response = $tron->delegateHotSpotBandwidth($this->receiverAddress, $trxEquivalent);

        if (isset($response['code']) && $response['code'] != 'true') {
            Log::error('Send bonus bandwidth error', $response);

            return;
        }

        TronTx::create([
            'from' => data_get($response, 'raw_data.contract.0.parameter.value.owner_address'),
            'to' => data_get($response, 'raw_data.contract.0.parameter.value.receiver_address'),
            'type' => TronTxTypes::DelegateResourceContract,
            'trx_amount' => data_get($response, 'raw_data.contract.0.parameter.value.balance') / Tron::ONE_SUN,
            'tx_id' => $response['txID'],
        ]);
    }
}
