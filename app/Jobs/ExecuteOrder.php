<?php

namespace App\Jobs;

use App\Enums\Statuses;
use App\Models\{Order, Stake};
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
        //todo sort field для порядка у кого первым тянем энергию. Куда добавить?
        Stake::with(['user' => ['wallet']])->chunk(50, function ($stakes) {
            foreach ($stakes as $stake) {
                $this->order->refresh();

                if (in_array($this->order->status, Statuses::CLOSED_STATUSES)) {
                    exit();
                }

                try {
                    (new StakeService($stake->user->wallet))->fillOrder($this->order, $stake->amount);
                } catch (TronException|Throwable $e) {
                    Log::emergency('ExecuteOrder-Exception', [
                        'wallet_id' => $stake->id,
                        'error' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine(),
                    ]);
                    dump($e->getMessage());
                }
            }
        });
    }
}
