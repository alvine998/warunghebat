<?php

namespace Tests\Feature;

use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_register_a_device_token(): void
    {
        $this->postJson(route('push.subscriptions.store'), ['token' => str_repeat('a', 100)])
            ->assertUnauthorized();

        $this->assertSame(0, FcmToken::count());
    }

    public function test_guest_cannot_remove_a_device_token(): void
    {
        $this->deleteJson(route('push.subscriptions.destroy'), ['token' => str_repeat('a', 100)])
            ->assertUnauthorized();
    }

    public function test_user_can_register_a_device_token_after_allowing_notifications(): void
    {
        $user = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($user)
            ->postJson(route('push.subscriptions.store'), [
                'token' => str_repeat('a', 100),
                'device_name' => 'Pixel 8',
            ])
            ->assertOk()
            ->assertJson(['saved' => true]);

        $this->assertDatabaseHas('fcm_tokens', [
            'user_id' => $user->id,
            'token' => str_repeat('a', 100),
            'device_name' => 'Pixel 8',
        ]);
    }

    public function test_registering_the_same_token_twice_keeps_a_single_row(): void
    {
        $user = User::factory()->create(['role' => 'pembeli']);

        foreach (['HP Lama', 'HP Baru'] as $device) {
            $this->actingAs($user)
                ->postJson(route('push.subscriptions.store'), [
                    'token' => str_repeat('b', 120),
                    'device_name' => $device,
                ])
                ->assertOk();
        }

        $this->assertSame(1, $user->fcmTokens()->count());
        $this->assertSame('HP Baru', $user->fcmTokens()->sole()->device_name);
    }

    public function test_token_validation_rejects_missing_or_garbage_tokens(): void
    {
        $user = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($user)
            ->postJson(route('push.subscriptions.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['token']);

        $this->actingAs($user)
            ->postJson(route('push.subscriptions.store'), ['token' => 'short'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['token']);

        $this->assertSame(0, FcmToken::count());
    }

    public function test_user_can_remove_their_own_device_token(): void
    {
        $user = User::factory()->create(['role' => 'pembeli']);
        $user->fcmTokens()->create(['token' => str_repeat('c', 100)]);

        $this->actingAs($user)
            ->deleteJson(route('push.subscriptions.destroy'), ['token' => str_repeat('c', 100)])
            ->assertOk()
            ->assertJson(['removed' => true]);

        $this->assertSame(0, FcmToken::count());
    }

    public function test_removing_an_unknown_token_is_idempotent(): void
    {
        $user = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($user)
            ->deleteJson(route('push.subscriptions.destroy'), ['token' => str_repeat('d', 100)])
            ->assertOk()
            ->assertJson(['removed' => true]);
    }

    public function test_user_cannot_remove_someone_elses_device_token(): void
    {
        $owner = User::factory()->create(['role' => 'pembeli']);
        $intruder = User::factory()->create(['role' => 'pembeli']);
        $owner->fcmTokens()->create(['token' => str_repeat('e', 100)]);

        $this->actingAs($intruder)
            ->deleteJson(route('push.subscriptions.destroy'), ['token' => str_repeat('e', 100)])
            ->assertOk();

        $this->assertSame(1, FcmToken::count());
    }

    public function test_worker_config_is_null_until_keys_are_set(): void
    {
        config()->set('services.firebase', []);

        $this->get(route('push.firebase-sw-config'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=UTF-8')
            ->assertSee('self.__FIREBASE_CONFIG__ = null;', false);
    }

    public function test_worker_config_exposes_public_keys_once_set(): void
    {
        config()->set('services.firebase', [
            'api_key' => 'api-key',
            'auth_domain' => 'warunghebat.firebaseapp.com',
            'project_id' => 'warunghebat',
            'storage_bucket' => 'warunghebat.appspot.com',
            'messaging_sender_id' => '123456789',
            'app_id' => '1:123456789:web:abc',
            'vapid_key' => str_repeat('v', 88),
        ]);

        $this->get(route('push.firebase-sw-config'))
            ->assertOk()
            ->assertSee('self.__FIREBASE_CONFIG__', false)
            ->assertSee('123456789', false);
    }

    public function test_service_worker_supports_offline_cache_and_push(): void
    {
        $worker = (string) file_get_contents(public_path('sw.js'));

        $this->assertStringContainsString('warunghebat-v1', $worker);
        $this->assertStringContainsString('/offline', $worker);
        $this->assertStringContainsString('firebase-messaging', $worker);
        $this->assertStringContainsString('onBackgroundMessage', $worker);
        $this->assertStringContainsString('notificationclick', $worker);
        $this->assertStringContainsString('/push/firebase-sw-config.js', $worker);
    }

    public function test_opt_in_banner_renders_for_signed_in_users_when_configured(): void
    {
        config()->set('services.firebase', [
            'api_key' => 'api-key',
            'auth_domain' => 'warunghebat.firebaseapp.com',
            'project_id' => 'warunghebat',
            'storage_bucket' => 'warunghebat.appspot.com',
            'messaging_sender_id' => '123456789',
            'app_id' => '1:123456789:web:abc',
            'vapid_key' => str_repeat('v', 88),
        ]);

        $user = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('push-notify', false)
            ->assertSee('Aktifkan Notifikasi', false);
    }

    public function test_opt_in_banner_stays_hidden_without_keys(): void
    {
        config()->set('services.firebase', []);

        $user = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertDontSee('push-notify', false);
    }
}
