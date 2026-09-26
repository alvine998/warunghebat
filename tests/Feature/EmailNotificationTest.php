<?php

namespace Tests\Feature;

use App\Mail\ProductApprovedMail;
use App\Mail\ProductRejectedMail;
use App\Mail\WarungApprovedMail;
use App\Mail\WarungRejectedMail;
use App\Mail\WelcomeMail;
use App\Models\Product;
use App\Models\SellerVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_sends_welcome_email(): void
    {
        Mail::fake();

        $this->post(route('register'), [
            'name' => 'Sari Dewi',
            'email' => 'sari@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pembeli',
        ])->assertRedirect(route('dashboard'));

        Mail::assertSent(WelcomeMail::class, fn (WelcomeMail $mail): bool => $mail->user->email === 'sari@example.com');
    }

    public function test_new_google_user_receives_welcome_email(): void
    {
        Mail::fake();

        config()->set([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-welcome-1',
            'name' => 'Sari Dewi',
            'email' => 'sari@example.com',
        ]));

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        Mail::assertSent(WelcomeMail::class, fn (WelcomeMail $mail): bool => $mail->user->email === 'sari@example.com');
    }

    public function test_existing_google_link_does_not_resend_welcome_email(): void
    {
        Mail::fake();

        config()->set([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
        ]);

        User::factory()->create(['email' => 'lama@example.com']);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-welcome-2',
            'name' => 'Sari Lama',
            'email' => 'lama@example.com',
        ]));

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        Mail::assertNotSent(WelcomeMail::class);
    }

    public function test_admin_verify_kyc_sends_warung_approved_email(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $verification = SellerVerification::factory()->for($seller)->create();

        $this->actingAs($admin)
            ->patch(route('admin.kyc.verify', $verification))
            ->assertRedirect();

        Mail::assertSent(WarungApprovedMail::class, fn (WarungApprovedMail $mail): bool => $mail->user->is($seller));
    }

    public function test_admin_reject_kyc_sends_warung_rejected_email(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $verification = SellerVerification::factory()->for($seller)->create();

        $this->actingAs($admin)
            ->patch(route('admin.kyc.reject', $verification), [
                'rejection_reason' => 'Foto KTP buram.',
            ])
            ->assertRedirect();

        Mail::assertSent(WarungRejectedMail::class, fn (WarungRejectedMail $mail): bool => $mail->verification->rejection_reason === 'Foto KTP buram.');
    }

    public function test_admin_approve_product_sends_email_to_seller(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $product = Product::factory()->for($seller)->create(['status' => 'pending']);

        $this->actingAs($admin)
            ->patch(route('admin.products.approve', $product))
            ->assertRedirect();

        Mail::assertSent(ProductApprovedMail::class, fn (ProductApprovedMail $mail): bool => $mail->product->is($product));
    }

    public function test_admin_reject_product_sends_email_with_reason(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $product = Product::factory()->for($seller)->create(['status' => 'pending']);

        $this->actingAs($admin)
            ->patch(route('admin.products.reject', $product), [
                'rejection_reason' => 'Foto buram, unggah ulang.',
            ])
            ->assertRedirect();

        Mail::assertSent(ProductRejectedMail::class, fn (ProductRejectedMail $mail): bool => $mail->product->rejection_reason === 'Foto buram, unggah ulang.');
    }

    public function test_admin_bulk_approve_sends_email_per_product(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $first = Product::factory()->for($seller)->create(['status' => 'pending']);
        $second = Product::factory()->for($seller)->create(['status' => 'pending']);

        $this->actingAs($admin)
            ->patch(route('admin.products.bulk-update'), [
                'action' => 'approve',
                'product_ids' => [$first->id, $second->id],
            ])
            ->assertRedirect();

        Mail::assertSent(ProductApprovedMail::class, 2);
    }
}
