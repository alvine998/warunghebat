<?php

namespace Database\Factories;

use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'amount' => 50000,
            'status' => Withdrawal::STATUS_PENDING,
            'bank_name' => 'BCA',
            'account_number' => fake()->numerify('##########'),
            'account_name' => fake()->name(),
            'store_note' => null,
            'admin_note' => null,
        ];
    }
}
