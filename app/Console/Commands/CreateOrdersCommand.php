<?php

namespace App\Console\Commands;

use App\Models\Consumer;
use Illuminate\Console\Command;

class CreateOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание заказов по текущим потребителям';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Consumer::query()->where('resource_amount', '>', 0)->chunk(100, function ($consumers) {
            foreach ($consumers as $consumer) {
                $consumer->orders()->create(['resource_amount' => $consumer->resource_amount]);
            }
        });
    }
}
