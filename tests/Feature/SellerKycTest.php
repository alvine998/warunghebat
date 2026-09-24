<?php

namespace Tests\Feature;

use App\Models\SellerVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerKycTest extends TestCase
{
    use RefreshDatabase;

    public function test_penjual_register_is_redirected_to_kyc_form(): void
    {
        $this->post(route('register'), [
            'name' => 'Bang Jago',
            'email' => 'jago@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'penjual',
        ])->assertRedirect(route('seller.verification.show'));

        $this->assertSame('penjual', User::where('email', 'jago@example.com')->sole()->role);
    }

    public function test_unverified_penjual_is_blocked_from_selling(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);

        $this->actingAs($seller)
            ->get(route('seller.products.index'))
            ->assertRedirect(route('seller.verification.show'));

        $this->actingAs($seller)
            ->get(route('seller.products.create'))
            ->assertRedirect(route('seller.verification.show'));

        $this->actingAs($seller)
            ->get(route('seller.store.edit'))
            ->assertRedirect(route('seller.verification.show'));
    }

    public function test_penjual_can_submit_kyc_documents(): void
    {
        Storage::fake('public');

        $seller = User::factory()->create(['role' => 'penjual']);

        $this->actingAs($seller)
            ->post(route('seller.verification.store'), [
                'nik' => '3174051209900001',
                'full_name' => 'Bang Jago',
                'ktp_image' => UploadedFile::fake()->image('ktp.jpg'),
                'selfie_image' => UploadedFile::fake()->image('selfie.jpg'),
                'storefront_image' => UploadedFile::fake()->image('warung.jpg'),
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $verification = $seller->fresh()->sellerVerification;

        $this->assertNotNull($verification);
        $this->assertSame('3174051209900001', $verification->nik);
        $this->assertSame(SellerVerification::STATUS_PENDING, $verification->status);
        Storage::disk('public')->assertExists($verification->ktp_path);
        Storage::disk('public')->assertExists($verification->selfie_path);
        Storage::disk('public')->assertExists($verification->storefront_path);
    }

    public function test_nik_must_be_16_digits(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);

        $this->actingAs($seller)
            ->post(route('seller.verification.store'), [
                'nik' => '123',
                'full_name' => 'Bang Jago',
                'ktp_image' => UploadedFile::fake()->image('ktp.jpg'),
                'selfie_image' => UploadedFile::fake()->image('selfie.jpg'),
                'storefront_image' => UploadedFile::fake()->image('warung.jpg'),
            ])
            ->assertSessionHasErrors(['nik']);

        $this->assertNull($seller->fresh()->sellerVerification);
    }

    public function test_still_blocked_while_kyc_pending(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->create(['status' => SellerVerification::STATUS_PENDING]);

        $this->actingAs($seller)
            ->get(route('seller.products.index'))
            ->assertRedirect(route('seller.verification.show'));
    }

    public function test_verified_penjual_can_sell(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();

        $this->actingAs($seller)
            ->get(route('seller.products.index'))
            ->assertOk();

        $this->actingAs($seller)
            ->get(route('seller.products.create'))
            ->assertOk();
    }

    public function test_admin_can_verify_kyc(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $verification = SellerVerification::factory()->for($seller)->create();

        $this->actingAs($admin)
            ->patch(route('admin.kyc.verify', $verification))
            ->assertRedirect()
            ->assertSessionHas('success');

        $verification = $verification->fresh();

        $this->assertSame(SellerVerification::STATUS_VERIFIED, $verification->status);
        $this->assertSame($admin->id, $verification->verified_by);
        $this->assertNotNull($verification->verified_at);

        // Penjual langsung bisa jualan setelah disetujui.
        $this->actingAs($seller)
            ->get(route('seller.products.index'))
            ->assertOk();
    }

    public function test_admin_can_reject_kyc_with_reason_and_seller_can_resubmit(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $verification = SellerVerification::factory()->for($seller)->create();

        $this->actingAs($admin)
            ->patch(route('admin.kyc.reject', $verification), [
                'rejection_reason' => 'Foto KTP buram.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(SellerVerification::STATUS_REJECTED, $verification->fresh()->status);
        $this->assertSame('Foto KTP buram.', $verification->fresh()->rejection_reason);

        // Kirim ulang tanpa foto baru tetap bisa (pakai berkas lama) — kembali pending.
        $this->actingAs($seller)
            ->post(route('seller.verification.store'), [
                'nik' => '3174051209900001',
                'full_name' => 'Bang Jago',
            ])
            ->assertRedirect(route('dashboard'));

        $resubmitted = $verification->fresh();

        $this->assertSame(SellerVerification::STATUS_PENDING, $resubmitted->status);
        $this->assertNull($resubmitted->rejection_reason);
    }

    public function test_reject_requires_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $verification = SellerVerification::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.kyc.reject', $verification), [])
            ->assertSessionHasErrors(['rejection_reason']);

        $this->assertSame(SellerVerification::STATUS_PENDING, $verification->fresh()->status);
    }

    public function test_admin_cannot_verify_twice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $verification = SellerVerification::factory()->verified()->create();

        $this->actingAs($admin)
            ->patch(route('admin.kyc.verify', $verification))
            ->assertSessionHas('error');
    }

    public function test_admin_bypasses_seller_verified_gate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('seller.products.index'))
            ->assertOk();
    }
}
