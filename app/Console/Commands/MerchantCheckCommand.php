<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use App\Services\TronApi\Tron;
use Illuminate\Console\Command;

class MerchantCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:merchant-check-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private Tron $tron;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->tron = new Tron();

        Merchant::where('created_at', '>=', now()->subHour(1))
            ->orderBy('id')
            ->chunk(50, function ($merchants) {
                foreach ($merchants as $merchant) {
                    $trxAmount = $this->tron->getTrxBalance($merchant->address);
                    if ($trxAmount > 0) {
                        $tron = new Tron($merchant->address, $merchant->private_key);
                        $tron->sendTrx(config('app.hot_spot_wallet'), $trxAmount, $merchant->address);
                        //TODO добавить покупку consumer
                    }
                }
            });
    }
}
