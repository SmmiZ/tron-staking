<?php

namespace App\Jobs;

use App\Models\OrderExecutor;
use App\Services\StakeService;
use App\Services\TronApi\Exception\TronException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class UndelegateExecutorResources implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param int $executorId
     * @param int $trxAmount
     */
    public function __construct(
        private readonly int $executorId,
        private readonly int $trxAmount
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @throws TronException
     */
    public function handle(): void
    {
        $executor = OrderExecutor::with(['wallet', 'order'])->find($this->executorId);

        (new StakeService($executor->wallet))->undelegateResourceFromOrder($executor->order, $this->trxAmount);
    }
}
