<?php

namespace App\Jobs;

use App\Enums\Statuses;
use App\Models\{Order, User};
use App\Services\StakeService;
use App\Services\TronApi\Exception\TronException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;
use Throwable;

class ExecuteOrder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Order $order)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        User::with(['wallet', 'stakes'])->orderBy('sort')->chunk(50, function ($users) {
            foreach ($users as $user) {
                foreach ($user->stakes as $stake) {
                    $this->order->refresh();

                    if (in_array($this->order->status, Statuses::CLOSED_STATUSES)) {
                        exit();
                    }

                    try {
                        (new StakeService($user->wallet))->fillOrder($this->order, $stake->trx_amount);
                    } catch (TronException|Throwable $e) {
                        Log::emergency('ExecuteOrder-Exception', [
                            'stake_id' => $stake->id,
                            'wallet_id' => $user->wallet->id,
                            'error' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine(),
                        ]);
                        dump($e->getMessage());
                    }
                }
            }
        });
    }
}
