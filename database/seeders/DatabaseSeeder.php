<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $user = User::factory()->create(['name' => 'Test User', 'email' => 'user@example.com']);
        $user->wallet()->create([
            'address' => env('MY_WALLET'),
            'stake_limit' => 1,
        ]);
    }
}
