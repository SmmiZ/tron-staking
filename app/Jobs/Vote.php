<?php

namespace App\Jobs;

use App\Enums\TronTxTypes;
use App\Models\TronTx;
use App\Services\TronApi\Tron;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\{ShouldBeUnique, ShouldQueue};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class Vote implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly string $walletAddress)
    {
        //
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return $this->walletAddress;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tron = new Tron();

        $witnessAddress = $tron->getTopSrAddress();
        $response = $tron->voteWitness($witnessAddress, $this->walletAddress);

        if (isset($response['code']) && $response['code'] != 'true') {
            Log::error('Vote error', $response);

            return;
        }

        TronTx::create([
            'from' => data_get($response, 'raw_data.contract.0.parameter.value.owner_address'),
            'to' => data_get($response, 'raw_data.contract.0.parameter.value.votes.0.vote_address'),
            'type' => TronTxTypes::VoteWitnessContract,
            'trx_amount' => data_get($response, 'raw_data.contract.0.parameter.value.votes.0.vote_count'),
            'tx_id' => $response['txID'],
        ]);
    }
}
