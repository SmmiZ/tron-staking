<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Staff::query()->create([
            'email' => 'admin@admin.com',
            'name' => 'Admin',
            'pin' => 111111,
            'password' => Hash::make(111111),
            'access_level' => 100,
        ]);
    }
}
