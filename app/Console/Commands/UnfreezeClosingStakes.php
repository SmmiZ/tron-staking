<?php

namespace App\Console\Commands;

use App\Jobs\WithdrawDefrostedTrx;
use App\Models\Stake;
use App\Services\StakeService;
use App\Services\TronApi\Exception\TronException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;

class UnfreezeClosingStakes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:unfreeze-closing-stakes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Разморозить стейки, которые юзеры закрывают и запланировать отзыв TRX через 14 дней';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $failed = collect();

        Stake::with('wallet:wallets.id,user_id,address')
            ->where('is_closes', true)
            ->chunkById(100, function ($stakes) use ($failed) {
                foreach ($stakes as $stake) {
                    try {
                        (new StakeService($stake->wallet))->unfreeze($stake->trx_amount);
                        $stake->delete();
                        WithdrawDefrostedTrx::dispatch($stake->wallet->address)->delay(now()->addDays(14));
                    } catch (TronException $e) {
                        $failed->push([$stake->id => $e->getMessage()]);
                    } finally {
                        Sleep::for(config('app.sleep_ms'))->milliseconds();
                    }
                }
                sleep(1);
            });

        if ($failed->isNotEmpty()) {
            Log::error('UnfreezeClosingStakes failed', $failed->toArray());
        }
    }
}
