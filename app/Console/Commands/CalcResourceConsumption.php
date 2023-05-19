<?php

namespace App\Console\Commands;

use App\Models\{Consumer, ResourceConsumption};
use App\Services\TronApi\Tron;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalcResourceConsumption extends Command
{
    private Tron $tron;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calc-resource-consumption {date?}';

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
        $this->tron = new Tron();
        $startDate = now()->createFromDate($this->argument('date'));

        Consumer::query()->select(['id', 'address'])->chunkById(100, function ($consumers) use (&$toInsert, $startDate) {
            foreach ($consumers as $consumer) {
                foreach ($startDate->toPeriod(now()->endOfDay(), '1', 'day') as $day) {
                    try {
                        $response = $this->getUsdtTransactions(
                            $consumer->address,
                            $day->startOfDay()->getTimestampMs(),
                            $day->endOfDay()->getTimestampMs()
                        );

                        $result = collect($response['data'])->where('value', '>', 0)->count();

                        if ($result == 0) continue;

                        $toInsert[] = [
                            'consumer_id' => $consumer->id,
                            'energy_amount' => $result * 32000,
                            'bandwidth_amount' => $result * 350,
                            'day' => $day,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } catch (\Throwable $e) {
                        $this->error('Error consumer # ' . $consumer->id);
                        Log::error('Error in CalcResourceConsumption', [
                            'message' => $e->getMessage(),
                            'line' => $e->getLine(),
                            'file' => $e->getFile(),
                        ]);
                    }
                }
            }

            ResourceConsumption::query()->insert($toInsert);
            $toInsert = [];
            sleep(1);
        });

        $this->info('The command was successful!');
    }

    private function getUsdtTransactions(string $ownerAddress, int $minTimestamp, int $maxTimestamp): array
    {
        $filters = 'only_from=true&only_confirmed=true&limit=200'
            . '&contract_address=' . Tron::USDT_CONTRACT
            . '&min_timestamp=' . $minTimestamp
            . '&max_timestamp=' . $maxTimestamp;

        return $this->tron->getManager()->request("v1/accounts/$ownerAddress/transactions/trc20?$filters", [], 'get');
    }
}
