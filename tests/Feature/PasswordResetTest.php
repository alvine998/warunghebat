<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_links_to_the_forgot_password_page(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'), false);
    }

    public function test_reset_link_is_emailed_for_a_registered_email(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'sari@example.com']);

        $this->post(route('password.email'), ['email' => 'sari@example.com'])
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_no_reset_link_is_emailed_for_an_unknown_email(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'tidak-ada@example.com'])
            ->assertSessionHasErrors(['email']);

        Notification::assertNothingSent();
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('Sandi baru');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'sandibaru123',
            'password_confirmation' => 'sandibaru123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('sandibaru123', $user->fresh()->password));

        // Kata sandi baru langsung bisa dipakai masuk.
        $this->post(route('login'), ['email' => $user->email, 'password' => 'sandibaru123'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_password_cannot_be_reset_with_an_expired_or_wrong_token(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.update'), [
            'token' => 'token-salah',
            'email' => $user->email,
            'password' => 'sandibaru123',
            'password_confirmation' => 'sandibaru123',
        ])
            ->assertSessionHasErrors(['email']);

        $this->assertFalse(Hash::check('sandibaru123', $user->fresh()->password));
    }

    public function test_guests_cannot_reset_without_matching_confirmation(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'sandibaru123',
            'password_confirmation' => 'salah-ketik',
        ])
            ->assertSessionHasErrors(['password']);
    }

    public function test_signed_in_users_are_redirected_away_from_reset_pages(): void
    {
        $user = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($user)->get(route('password.request'))->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get(route('password.reset', ['token' => 'token-apa-saja']))
            ->assertRedirect(route('dashboard'));
    }
}
