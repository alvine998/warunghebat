<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
        ]);
    }

    public function test_google_button_is_shown_when_configured(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('auth.google.redirect'), false)
            ->assertSee('Masuk dengan Google');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Daftar dengan Google');
    }

    public function test_google_button_is_hidden_without_credentials(): void
    {
        config()->set([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Masuk dengan Google');
    }

    public function test_redirect_sends_the_visitor_to_google(): void
    {
        Socialite::fake('google');

        $this->get(route('auth.google.redirect'))->assertRedirect();
    }

    public function test_redirect_reports_when_google_is_not_configured(): void
    {
        config()->set(['services.google.client_id' => null]);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email']);
    }

    public function test_new_google_user_is_registered_as_pembeli_and_signed_in(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-1',
            'name' => 'Sari Dewi',
            'email' => 'sari@example.com',
        ]));

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $user = User::where('email', 'sari@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('google-1', $user->google_id);
        $this->assertSame('pembeli', $user->role);
        $this->assertNull($user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_role_hint_creates_a_penjual(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-2',
            'name' => 'Bang Jago',
            'email' => 'jago@example.com',
        ]));

        // The role is stashed when leaving for Google, then applied on return.
        $this->get(route('auth.google.redirect', ['role' => 'penjual']));
        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $this->assertSame('penjual', User::where('email', 'jago@example.com')->value('role'));
    }

    public function test_existing_email_account_is_linked_without_losing_role_or_password(): void
    {
        $user = User::factory()->create([
            'email' => 'lama@example.com',
            'role' => 'penjual',
            'password' => 'rahasia-lama',
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-3',
            'name' => 'Sari Lama',
            'email' => 'lama@example.com',
        ]));

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertSame('google-3', $user->google_id);
        $this->assertSame('penjual', $user->role);
        $this->assertNotNull($user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_accounts_cannot_sign_in_with_google(): void
    {
        User::factory()->create(['email' => 'admin@example.com', 'role' => 'admin']);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-4',
            'email' => 'admin@example.com',
        ]));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_google_account_without_email_is_rejected(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-5',
            'email' => null,
        ]));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }
}
