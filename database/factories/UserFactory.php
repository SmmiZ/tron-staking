<?php

namespace Database\Factories;

use App\Models\{User, UserLine};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'the_code' => 'TE' . Str::random(6),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if ($user->id == 1) {
                //Добавляем реальный кошелек первому
                $user->wallet()->create([
                    'address' => env('MY_WALLET') ?? 'test_wallet_address',
                ]);
            } else {
                //Моделируем небольшую реферальную структуру
                if (rand(0, 7) == 7) {
                    return;
                }

                $leader = User::inRandomOrder()->whereNot('id', $user->id)->where('id', '<', $user->id)->first();
                $linearPath = $leader->linear_path ?? '/' . $leader->id . '/';

                $leadersIds = explode('/', trim($linearPath, '/'));
                foreach ($leadersIds as $i => $leaderId) {
                    if ($i > 19) {
                        break;
                    }

                    $lineIds = UserLine::where('user_id', $leaderId)->where('line', $i + 1)->value('ids') ?? [];
                    UserLine::updateOrCreate(
                        ['user_id' => $leaderId, 'line' => $i + 1],
                        ['ids' => array_merge($lineIds, [$user->id])]
                    );
                }

                $user->update(['linear_path' => '/' . $user->id . $linearPath]);
            }
        });
    }
}
