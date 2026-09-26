<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_an_inline_json_error_for_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'buyer@example.com']);

        $this->postJson(route('login'), [
            'email' => 'buyer@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Email atau kata sandi salah. Coba lagi ya.');

        $this->assertGuest();
    }

    public function test_register_returns_json_validation_errors_without_creating_a_user(): void
    {
        $this->postJson(route('register'), [
            'name' => 'Sari Dewi',
            'email' => 'sari@example.com',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.password.0', 'Konfirmasi kata sandi tidak cocok.');

        $this->assertDatabaseMissing('users', ['email' => 'sari@example.com']);
        $this->assertGuest();
    }

    public function test_register_returns_a_redirect_url_and_creates_a_user_for_valid_json_data(): void
    {
        $this->postJson(route('register'), [
            'name' => 'Sari Dewi',
            'email' => 'sari@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pembeli',
        ])
            ->assertOk()
            ->assertJsonPath('redirect_url', route('dashboard'));

        $this->assertDatabaseHas('users', [
            'name' => 'Sari Dewi',
            'email' => 'sari@example.com',
            'role' => 'pembeli',
        ]);
        $this->assertAuthenticated();
    }

    public function test_login_returns_a_redirect_url_for_valid_json_credentials(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);

        $this->postJson(route('login'), [
            'email' => 'buyer@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('redirect_url', route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }
}
