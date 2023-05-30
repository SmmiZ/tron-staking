<?php

namespace App\Jobs;

use App\Enums\{Resources, TronTxTypes};
use App\Mail\ErrorOccurred;
use App\Models\TronTx;
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\{ShouldBeUnique, ShouldQueue};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\{Log, Mail};
use Illuminate\Support\Sleep;
use Throwable;

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
        try {
            $trxEquivalent = ceil($tron->bandwidth2Trx(config('app.bandwidth_bonus')));
            $response = $tron->delegateHotSpotBandwidth($this->receiverAddress, $trxEquivalent);

            Sleep::for(config('app.sleep_ms'))->milliseconds();

            if (isset($response['code']) && $response['code'] != 'true') {
                Log::error('Send bonus bandwidth response error', $response);
                throw new TronException('Send bonus bandwidth error');
            }
        } catch (TronException|Throwable $e) {
            $mail = new ErrorOccurred(SendBonusBandwidth::class, $e->getMessage());
            $mail->with([
                'availableTrx' => $tron->getCanDelegatedMaxTrx(Resources::BANDWIDTH),
            ]);

            Log::error('SendBonusBandwidth throwable error', ['error' => $e->getMessage() . $e->getLine() . $e->getFile()]);
            Mail::to(config('app.support_email'))->send($mail);

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
