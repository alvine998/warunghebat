<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(5000, 50000),
            'stock' => fake()->numberBetween(1, 50),
            'category' => fake()->randomElement(Product::CATEGORIES),
            'status' => 'approved',
        ];
    }
}
