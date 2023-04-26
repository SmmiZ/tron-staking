<?php

namespace Database\Factories;

use App\Enums\ReactorTypes;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReactorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'type' => ReactorTypes::standard,
            'active_until' => now()->addYear(),
        ];
    }
}
