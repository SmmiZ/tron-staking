<?php

namespace App\Jobs;

use App\Models\{Order, User};
use App\Services\StakeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

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
        User::with(['wallet', 'stake:id,user_id,trx_amount'])
            ->whereHas('wallet')
            ->whereRelation('stake', 'trx_amount', '>', 0)
            ->whereRelation('stake', 'failed_attempts', '<', 3)
            ->orderBy('sort')
            ->chunk(50, function ($users) {
                foreach ($users as $user) {
                    $this->order->refresh();

                    if ($this->order->resource_amount <= $this->order->executors()->sum('resource_amount')) {
                        exit();
                    }

                    (new StakeService($user->wallet))->delegateResourceToOrder($this->order, $user->stake->trx_amount);
                    sleep(1);
                }
            });
    }
}
