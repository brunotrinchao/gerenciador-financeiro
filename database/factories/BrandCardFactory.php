<?php

namespace Database\Factories;

use App\Models\BrandCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BrandCardFactory extends Factory
{
    protected $model = BrandCard::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'brand' => $this->faker->imageUrl()
        ];
    }
}
