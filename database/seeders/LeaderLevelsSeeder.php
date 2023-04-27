<?php

namespace Database\Seeders;

use App\Models\LeaderLevel;
use Illuminate\Database\Seeder;

class LeaderLevelsSeeder extends Seeder
{
    private array $levels = [
        ['level' => 0, 'name_ru' => 'Электрик 1 разряда', 'name_en' => 'Electrician 1st category', 'reward' => 0],
        ['level' => 1, 'name_ru' => 'Электрик 2 разряда', 'name_en' => 'Electrician 2nd category', 'reward' => 50],
        ['level' => 2, 'name_ru' => 'Электрик 3 разряда', 'name_en' => 'Electrician 3th category', 'reward' => 0],
        ['level' => 3, 'name_ru' => 'Электрик 4 разряда', 'name_en' => 'Electrician 4th category', 'reward' => 200],
        ['level' => 4, 'name_ru' => 'Электрик 5 разряда', 'name_en' => 'Electrician 5th category', 'reward' => 0],
        ['level' => 5, 'name_ru' => 'Электрик 6 разряда', 'name_en' => 'Electrician 6th category', 'reward' => 600],
        ['level' => 6, 'name_ru' => 'Начальник отдела электро производства', 'name_en' => 'Head of electrical production department', 'reward' => 1000],
        ['level' => 7, 'name_ru' => 'Главный инженер электростанции', 'name_en' => 'Chief engineer of the power plant', 'reward' => 5000],
        ['level' => 8, 'name_ru' => 'Raiden', 'name_en' => 'Raiden', 'reward' => 10000],
    ];

    private array $conditions = [
        null,
        ['reactors' => 5, 'trx' => 100_000],
        ['reactors' => 10, 'trx' => 200_000],
        ['reactors' => 30, 'trx' => 500_000],
        ['reactors' => 50, 'trx' => 1_000_000],
        ['reactors' => 100, 'trx' => 2_500_000],
        ['reactors' => 200, 'leaders' => ['level' => 5, 'number' => 2]],
        ['reactors' => 500, 'leaders' => ['level' => 6, 'number' => 2]],
        ['reactors' => 1000, 'leaders' => ['level' => 7, 'number' => 3]],
    ];

    private array $altConditions = [
        6 => ['trx' => 10_000_000],
        7 => ['trx' => 30_000_000],
        8 => ['trx' => 100_000_000],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->levels as $i => $level) {
            $level['conditions'] = $this->conditions[$i];

            if ($level['level'] > 5) {
                $level['alt_conditions'] = $this->altConditions[$level['level']];
            }

            LeaderLevel::query()->create($level);
        }
    }
}
