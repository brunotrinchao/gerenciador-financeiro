<?php

namespace Database\Factories;

use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    protected $model = Card::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 10),
            'bank_id' => $this->faker->numberBetween(1, 10),
            'name' => $this->faker->name(),
            'number' => $this->faker->numberBetween(1, 100),
            'brand_id' => $this->faker->numberBetween(1, 100),
            'limit' => $this->faker->numberBetween(1, 10000),
            'due_date' => $this->faker->numberBetween(1, 30),
        ];
    }
}
