<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 100),
            'bank_id' => $this->faker->numberBetween(1, 100),
            'type' => $this->faker->randomElement(['1', '2']),
            'balance' => $this->faker->randomFloat(2, 100),
            'balance_currency' => 'BRL'
        ];
    }
}
