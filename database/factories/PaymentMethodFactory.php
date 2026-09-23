<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => PaymentMethod::TYPE_BANK,
            'name' => 'BCA a/n Warung Hebat',
            'account_number' => fake()->numerify('##########'),
            'account_name' => 'Warung Hebat',
            'instructions' => fake()->sentence(),
            'image_path' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function qris(): static
    {
        return $this->state(fn (): array => [
            'type' => PaymentMethod::TYPE_QRIS,
            'name' => 'QRIS Warung Hebat',
            'account_number' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
