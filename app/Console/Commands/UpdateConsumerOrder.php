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
    protected $signature = 'app:update-consumer-order {consumer* : один или диапазон id потребителей в формате "1 50"}';

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
        $consumerIds = $this->argument('consumer');

        $consumers = Consumer::query()->when(count($consumerIds) < 2,
            fn($q) => $q->whereIn('id', $consumerIds),
            fn($q) => $q->whereBetween('id', $consumerIds))
            ->get();

        if ($consumers->isEmpty()) {
            $this->error('Consumers not found!');
            exit;
        }

        $bar = $this->output->createProgressBar($consumers->count());
        $bar->start();
        foreach ($consumers as $consumer) {
            $bar->advance();
            $avgEnergyAmount = $consumer->resourceConsumptions()->where('created_at', '>=', now()->subDays(7))->avg('energy_amount');

            if ($avgEnergyAmount < 1) {
                $this->error('Consumer #' . $consumer->id . ' average energy amount is 0!');
                continue;
            }

            $consumer->order()->updateOrCreate(
                ['consumer_id' => $consumer->id],
                ['resource_amount' => $avgEnergyAmount]
            );
        }

        $bar->finish();
        $this->info('The command was successful!');
    }
}
