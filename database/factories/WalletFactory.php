<?php

namespace Database\Factories;

use App\Enums\CurrencyType;
use App\Enums\WalletType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'type' => fake()->randomElement(WalletType::cases()),
            'balance' => fake()->randomFloat(2, 10, 10000),
            'currency' => fake()->randomElement(CurrencyType::cases()),
            'institution' => fake()->company(),
            'last_four_digits' => fake()->numerify('####'),
            'is_active' => true,
        ];
    }
}
