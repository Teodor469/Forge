<?php

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
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
            'type' => fake()->randomElement(CategoryType::cases()),
            'color' => fake()->hexColor(),
            'icon' => 'shopping-cart',
            'parent_id' => null,
        ];
    }
}
