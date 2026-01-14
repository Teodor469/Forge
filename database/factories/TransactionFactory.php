<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'category_id' => Category::factory(),
            'amount' => fake()->randomFloat(2, 10, 10000),
            'type' => fake()->randomElement(TransactionType::cases()),
            'merchant' => fake()->name(),
            'description' => fake()->text(100),
            'transaction_date' => fake()->date('Y-m-d'),
        ];
    }
}
