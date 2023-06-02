<?php

namespace App\Jobs;

use App\Enums\TronTxTypes;
use App\Events\UnStakeEvent;
use App\Mail\UserBan;
use Illuminate\Support\Facades\Mail;
use App\Models\{TronTx, User};
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\{ShouldBeUnique, ShouldQueue};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use Throwable;

class BanUser implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return $this->userId;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly int $userId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::with(['stakes', 'wallet:id,user_id,address', 'executions' => ['consumer']])->find($this->userId, ['id']);
        $tron = new Tron();

        foreach ($user->executions as $execution) {
            try {
                $delegated = $tron->getDelegatedResources($user->wallet->address, $execution->consumer->address);
                $trxAmount = data_get($delegated, 'delegatedResource.0.frozen_balance_for_energy', 0) / $tron::ONE_SUN;

                if ($trxAmount > 0) {
                    $response = $tron->undelegateEnergy($user->wallet->address, $execution->consumer->address, $trxAmount);

                    if (isset($response['code']) && $response['code'] != 'true') {
                        throw new TronException($response['code'] ?: 'Unknown error');
                    }

                    TronTx::create([
                        'from' => data_get($response, 'raw_data.contract.0.parameter.value.owner_address'),
                        'to' => data_get($response, 'raw_data.contract.0.parameter.value.receiver_address'),
                        'type' => TronTxTypes::UnDelegateResourceContract,
                        'trx_amount' => $trxAmount,
                        'tx_id' => $response['txID'],
                    ]);
                }

                $execution->update(['trx_amount' => 0, 'resource_amount' => 0, 'deleted_at' => now()]);
            } catch (TronException|Throwable $e) {
                Log::error('Failed to revoke banned user`s resources', [$execution->id => $e->getMessage()]);
            } finally {
                Sleep::for(config('app.sleep_ms'))->milliseconds();
            }
        }

        $user->stakes()->delete();
        $user->update(['is_banned' => true, 'leader_level' => 0]);

        event(new UnStakeEvent($user));
        RevokeBonusBandwidth::dispatch($user->wallet->address, true);
        Mail::to($user->email)->send(new UserBan());
    }
}
