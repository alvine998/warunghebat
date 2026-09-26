<?php

namespace Database\Factories;

use App\Models\Category;
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
        $price = fake()->numberBetween(5000, 50000);
        $category = fake()->randomElement(Product::CATEGORIES);

        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => $price,
            'cost_price' => (int) round($price * fake()->randomFloat(2, 0.5, 0.8)),
            'stock' => fake()->numberBetween(1, 50),
            'barcode' => fake()->optional(0.5)->ean13(),
            'category' => $category,
            // Resolve to the matching seeded category so legacy `category`
            // strings and the new `category_id` stay consistent, even when
            // tests override only `category`.
            'category_id' => fn (array $attributes) => Category::where('name', $attributes['category'] ?? 'Lainnya')->value('id')
                ?? Category::factory(),
            'status' => 'approved',
        ];
    }
}
