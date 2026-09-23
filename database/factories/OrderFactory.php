<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'store_id' => Store::factory(),
            'warung_name' => fake()->company(),
            'item_name' => fake()->words(2, true),
            'icon' => '🛒',
            'subtotal' => 20000,
            'total' => 20000,
            'commission_amount' => 0,
            'status' => Order::STATUS_PENDING_PAYMENT,
        ];
    }

    /** Buyer paid and the platform is holding the money in escrow. */
    public function paid(): static
    {
        return $this->state(fn (): array => ['status' => Order::STATUS_PAID]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => Order::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }
}
