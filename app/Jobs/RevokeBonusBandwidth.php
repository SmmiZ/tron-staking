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

class RevokeBonusBandwidth implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Tron $tron;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly string $userAddress)
    {
        //
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return $this->userAddress;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->tron = new Tron();
        $trxAmount = $this->getDelegatedTrxAmount();

        if ($trxAmount < 1) {
            return;
        }

        $response = $this->tron->undelegateHotSpotBandwidth($this->userAddress, $trxAmount);

        if (isset($response['code']) && $response['code'] != 'true') {
            Log::error('RevokeBonusBandwidth error: ' . $this->userAddress, $response);

            return;
        }

        TronTx::create([
            'from' => data_get($response, 'raw_data.contract.0.parameter.value.owner_address'),
            'to' => data_get($response, 'raw_data.contract.0.parameter.value.receiver_address'),
            'type' => TronTxTypes::UnDelegateResourceContract,
            'trx_amount' => data_get($response, 'raw_data.contract.0.parameter.value.balance') / Tron::ONE_SUN,
            'tx_id' => $response['txID'],
        ]);
    }

    private function getDelegatedTrxAmount(): int
    {
        $response = $this->tron->getDelegatedResources($this->tron->address['base58'], $this->userAddress);

        if (!isset($response['delegatedResource'])) {
            Log::error('RevokeBonusBandwidth error: ' . $this->userAddress, $response);

            return 0;
        }

        foreach ($response['delegatedResource'] as $resource) {
            if (isset($resource['frozen_balance_for_bandwidth'])) {
                return $resource['frozen_balance_for_bandwidth'] / Tron::ONE_SUN;
            }
        }

        return 0;
    }
}
