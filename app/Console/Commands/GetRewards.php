<?php

namespace App\Console\Commands;

use App\Models\Wallet;
use App\Services\StakeService;
use Illuminate\Console\Command;

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
                (new StakeService($wallet))->getReward();
            }
        });
    }
}
