<?php

namespace Database\Factories;

use App\Models\SellerVerification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SellerVerification>
 */
class SellerVerificationFactory extends Factory
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
            'nik' => (string) fake()->numerify('################'),
            'full_name' => fake()->name(),
            'ktp_path' => 'kyc/ktp-example.jpg',
            'selfie_path' => 'kyc/selfie-example.jpg',
            'storefront_path' => 'kyc/storefront-example.jpg',
            'status' => SellerVerification::STATUS_PENDING,
            'rejection_reason' => null,
            'verified_by' => null,
            'verified_at' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SellerVerification::STATUS_VERIFIED,
            'rejection_reason' => null,
            'verified_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SellerVerification::STATUS_REJECTED,
            'rejection_reason' => 'Foto KTP buram, mohon foto ulang.',
        ]);
    }
}
