<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_open_the_settings_page(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($buyer)
            ->get(route('admin.settings'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_non_admin_cannot_update_the_settings(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($buyer)
            ->put(route('admin.settings.update'), [
                'withdrawal_min' => 1000,
                'withdrawal_max' => 2000,
                'commission_percent' => 0,
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertSame(0, Setting::count());
    }

    public function test_admin_can_save_the_withdrawal_limits_and_commission(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'withdrawal_min' => 25000,
                'withdrawal_max' => 3000000,
                'commission_percent' => 5,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(25000, Setting::withdrawalMin());
        $this->assertSame(3000000, Setting::withdrawalMax());
        $this->assertSame(5, Setting::commissionPercent());

        $this->assertDatabaseHas('settings', ['key' => 'withdrawal_min', 'value' => '25000']);
        $this->assertDatabaseHas('settings', ['key' => 'commission_percent', 'value' => '5']);
    }

    public function test_the_maximum_cannot_be_lower_than_the_minimum(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'withdrawal_min' => 100000,
                'withdrawal_max' => 50000,
                'commission_percent' => 0,
            ])
            ->assertSessionHasErrors(['withdrawal_max']);

        $this->assertSame(0, Setting::count());
    }

    public function test_commission_is_capped_at_one_hundred_percent(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'withdrawal_min' => 10000,
                'withdrawal_max' => 1000000,
                'commission_percent' => 101,
            ])
            ->assertSessionHasErrors(['commission_percent']);

        $this->assertSame(0, Setting::count());
    }

    public function test_money_inputs_accept_thousand_separators(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'withdrawal_min' => '25.000',
                'withdrawal_max' => '3.000.000',
                'commission_percent' => 0,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(25000, Setting::withdrawalMin());
        $this->assertSame(3000000, Setting::withdrawalMax());
    }

    public function test_saved_limits_are_enforced_on_the_next_withdrawal_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'withdrawal_min' => 100000,
            'withdrawal_max' => 1000000,
            'commission_percent' => 0,
        ]);

        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create();
        Wallet::factory()->for($store)->withBalance(500000)->create();

        $this->actingAs($seller)
            ->post(route('seller.wallet.withdraw'), [
                'amount' => 50000,
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'Budi Santoso',
            ])
            ->assertSessionHasErrors(['amount']);

        $this->assertSame(0, $store->wallet->withdrawals()->count());
    }

    public function test_settings_fall_back_to_defaults_before_an_admin_saves(): void
    {
        $this->assertSame(10000, Setting::withdrawalMin());
        $this->assertSame(5000000, Setting::withdrawalMax());
        $this->assertSame(0, Setting::commissionPercent());
    }
}
