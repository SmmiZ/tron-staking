<?php

namespace App\Console\Commands;

use App\Models\Wallet;
use App\Services\StakeService;
use App\Services\TronApi\Exception\TronException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetRewards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-rewards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Забрать доступные награды пользователей';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Wallet::query()->orderBy('id')->chunk(50, function ($wallets) {
            foreach ($wallets as $wallet) {
                try {
                    (new StakeService($wallet))->getReward();
                } catch (TronException|Throwable $e) {
                    $this->error($e->getMessage());

                    Log::emergency('GetReward-Exception', [
                        'wallet_id' => $wallet->id,
                        'error' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine(),
                    ]);
                }
            }
        });
    }
}
