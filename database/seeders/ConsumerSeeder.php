<?php

namespace Database\Seeders;

use App\Models\{Consumer, Order};
use Illuminate\Database\Seeder;

class ConsumerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $consumer = Consumer::query()->create([
            'name' => 'Main consumer',
            'address' => config('app.hot_spot_wallet') ?? 'address',
            'resource_amount' => 10000,
        ]);

        Order::query()->create([
            'consumer_id' => $consumer->id,
            'resource_amount' => 10000,
        ]);
    }
}
