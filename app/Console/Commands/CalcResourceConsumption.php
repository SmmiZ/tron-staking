<?php

namespace App\Console\Commands;

use App\Models\{Consumer, ResourceConsumption};
use App\Services\TronApi\Tron;
use Illuminate\Console\Command;

class CalcResourceConsumption extends Command
{
    private Tron $tron;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calc-resource-consumption';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Формирует статистику потребления ресурсов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $toInsert = [];
        $now = now();
        $this->tron = new Tron();

        Consumer::query()->select(['id', 'address'])->chunkById(100, function ($consumers) use (&$toInsert, $now) {
            foreach ($consumers as $consumer) {
                $response = $this->getUsdtTransactions($consumer->address, $now->startOfDay()->getTimestampMs());

                $result = collect($response['data'])->where('value', '>', 0)->count();

                if ($result == 0) continue;

                $toInsert[] = [
                    'consumer_id' => $consumer->id,
                    'energy_amount' => $result * 32000,
                    'bandwidth_amount' => $result * 350,
                    'day' => $now,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            sleep(2);
        });

        ResourceConsumption::query()->insert($toInsert);

        $this->info('The command was successful!');
    }

    private function getUsdtTransactions(string $ownerAddress, int $minTimestamp): array
    {
        $filters = 'only_from=true&only_confirmed=true&limit=200'
            . '&contract_address=' . Tron::USDT_CONTRACT
            . '&min_timestamp=' . $minTimestamp;

        return $this->tron->getManager()->request("v1/accounts/$ownerAddress/transactions/trc20?$filters", [], 'get');
    }
}
