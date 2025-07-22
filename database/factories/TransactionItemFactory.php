<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionItem>
 */
class TransactionItemFactory extends Factory
{
    protected $model = \App\Models\TransactionItem::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' =>  $this->faker->randomElement([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            'due_date' =>  Carbon::now(),
            'amount' => rand(10000, 500000),
            'installment_number' => 1,
            'status' => 'PENDING',
        ];
    }
}
