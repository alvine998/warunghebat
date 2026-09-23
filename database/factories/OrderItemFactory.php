<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => null,
            'name' => fake()->words(3, true),
            'price' => 10000,
            'qty' => 2,
            'subtotal' => 20000,
        ];
    }
}
