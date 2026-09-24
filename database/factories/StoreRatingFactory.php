<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Store;
use App\Models\StoreRating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreRating>
 */
class StoreRatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->completed(),
            'user_id' => User::factory(),
            'store_id' => Store::factory(),
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->optional()->sentence(6),
        ];
    }
}
