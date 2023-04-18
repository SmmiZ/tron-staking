<?php

namespace App\Jobs;

use App\Enums\Statuses;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class ProcessingOrders implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Order::query()->whereIn('status', Statuses::OPEN_STATUSES)->chunk(50, function ($orders) {
            foreach ($orders as $order) {
                ExecuteOrder::dispatch($order);
            }
        });
    }
}
