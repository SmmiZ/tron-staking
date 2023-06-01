<?php

namespace App\Jobs;

use App\Enums\TronTxTypes;
use App\Models\TronTx;
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class RevokeBonusBandwidth implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Tron $tron;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly string $userAddress, private readonly bool $revokeAll = false)
    {
        //
    }

    /**
     * Execute the job.
     * @throws TronException
     */
    public function handle(): void
    {
        $this->tron = new Tron();
        $totalTrx = $this->getTotalDelegatedTrx();

        if ($totalTrx < 1) {
            return;
        }

        $trxToRevoke = $this->revokeAll
            ? $totalTrx
            : ceil($this->tron->bandwidth2Trx(config('app.bandwidth_bonus')));

        $response = $this->tron->undelegateHotSpotBandwidth($this->userAddress, $trxToRevoke);

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

    /**
     * @throws TronException
     */
    private function getTotalDelegatedTrx(): int
    {
        $response = $this->tron->getDelegatedResources($this->tron->address['base58'], $this->userAddress);

        if (!isset($response['delegatedResource'])) {
            Log::error('RevokeBonusBandwidth error: ' . $this->userAddress, $response);

            return 0;
        }

        return data_get($response, 'delegatedResource.0.frozen_balance_for_bandwidth', 0) / Tron::ONE_SUN;
    }
}
