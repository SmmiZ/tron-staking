<?php

namespace App\Console\Commands;

use App\Models\Consumer;
use Illuminate\Console\Command;

class UpdateConsumerOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-consumer-order {consumer_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Рассчитать средний расход ресурсов за неделю и создать/обновить заказ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$consumer = Consumer::find($this->argument('consumer_id'))) {
            $this->error('Consumer not found!');
            exit;
        }

        $avgEnergyAmount = $consumer->resourceConsumptions()->where('created_at', '>=', now()->subDays(7))->avg('energy_amount');

        if ($avgEnergyAmount < 1) {
            $this->error('Average energy amount is 0!');
            exit;
        }

        $consumer->order()->updateOrCreate(
            ['consumer_id' => $consumer->id],
            ['resource_amount' => $avgEnergyAmount]
        );

        $this->info('The command was successful!');
    }
}
