<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'balance' => 0,
        ];
    }

    public function withBalance(int $amount): static
    {
        return $this->state(fn (): array => ['balance' => $amount]);
    }
}
