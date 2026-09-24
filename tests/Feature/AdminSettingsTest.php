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
            ->put(route('admin.settings.update'), $this->payload())
            ->assertRedirect(route('dashboard'));

        $this->assertSame(0, Setting::count());
    }

    public function test_admin_can_save_the_withdrawal_limits_and_commission(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->payload())
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(25000, Setting::withdrawalMin());
        $this->assertSame(3000000, Setting::withdrawalMax());
        $this->assertSame(5, Setting::commissionPercent());

        $this->assertDatabaseHas('settings', ['key' => 'withdrawal_min', 'value' => '25000']);
        $this->assertDatabaseHas('settings', ['key' => 'commission_percent', 'value' => '5']);
    }

    public function test_admin_can_save_the_contact_info_and_social_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->payload([
                'cs_whatsapp' => '+62 812-9999-0000',
                'official_email' => 'cs@warunghebat.id',
                'office_address' => 'Jl. Merdeka No. 1, Bandung',
                'operational_days' => 'Senin–Minggu',
                'operational_hours' => '08.00–21.00 WIB',
                'operational_note' => 'Hari libur: jam 09.00–17.00',
                'social_instagram' => 'https://instagram.com/warunghebat',
                'social_tiktok' => 'https://tiktok.com/@warunghebat',
                'social_x' => 'https://x.com/warunghebat',
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('6281299990000', Setting::csWhatsapp());
        $this->assertSame('https://wa.me/6281299990000', Setting::waLink());
        $this->assertSame('cs@warunghebat.id', Setting::officialEmail());
        $this->assertSame('Jl. Merdeka No. 1, Bandung', Setting::officeAddress());
        $this->assertSame('Senin–Minggu', Setting::operationalDays());
        $this->assertSame('08.00–21.00 WIB', Setting::operationalHours());
        $this->assertSame('Hari libur: jam 09.00–17.00', Setting::operationalNote());
        $this->assertSame('https://instagram.com/warunghebat', Setting::socialInstagram());
        $this->assertSame('https://tiktok.com/@warunghebat', Setting::socialTiktok());
        $this->assertSame('https://x.com/warunghebat', Setting::socialX());
    }

    public function test_whatsapp_number_must_start_with_62(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->payload(['cs_whatsapp' => '081234567890']))
            ->assertSessionHasErrors(['cs_whatsapp']);

        $this->assertSame(0, Setting::count());
    }

    public function test_the_contact_page_shows_the_saved_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->payload([
            'cs_whatsapp' => '6281299990000',
            'official_email' => 'cs@warunghebat.id',
            'office_address' => 'Jl. Merdeka No. 1, Bandung',
            'operational_days' => 'Senin–Minggu',
            'operational_hours' => '08.00–21.00 WIB',
            'operational_note' => 'Hari libur tutup',
        ]));

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('https://wa.me/6281299990000', false)
            ->assertSee('cs@warunghebat.id')
            ->assertSee('Jl. Merdeka No. 1, Bandung')
            ->assertSee('Senin–Minggu')
            ->assertSee('08.00–21.00 WIB')
            ->assertSee('Hari libur tutup');

        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('cs@warunghebat.id');

        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('cs@warunghebat.id');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://wa.me/6281299990000', false);
    }

    public function test_the_maximum_cannot_be_lower_than_the_minimum(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->payload([
                'withdrawal_min' => 100000,
                'withdrawal_max' => 50000,
            ]))
            ->assertSessionHasErrors(['withdrawal_max']);

        $this->assertSame(0, Setting::count());
    }

    public function test_commission_is_capped_at_one_hundred_percent(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->payload(['commission_percent' => 101]))
            ->assertSessionHasErrors(['commission_percent']);

        $this->assertSame(0, Setting::count());
    }

    public function test_money_inputs_accept_thousand_separators(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->payload([
                'withdrawal_min' => '25.000',
                'withdrawal_max' => '3.000.000',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(25000, Setting::withdrawalMin());
        $this->assertSame(3000000, Setting::withdrawalMax());
    }

    public function test_saved_limits_are_enforced_on_the_next_withdrawal_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->payload([
            'withdrawal_min' => 100000,
            'withdrawal_max' => 1000000,
        ]));

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
        $this->assertSame('6281234567890', Setting::csWhatsapp());
        $this->assertSame('halo@warunghebat.id', Setting::officialEmail());
        $this->assertSame('Jl. Tebet Raya No. 12, Jakarta Selatan', Setting::officeAddress());
        $this->assertSame('Senin–Sabtu', Setting::operationalDays());
        $this->assertSame('07.00–22.00 WIB', Setting::operationalHours());
        $this->assertSame('https://wa.me/6281234567890', Setting::waLink());
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'withdrawal_min' => 25000,
            'withdrawal_max' => 3000000,
            'commission_percent' => 5,
            'cs_whatsapp' => '6281234567890',
            'official_email' => 'halo@warunghebat.id',
            'office_address' => 'Jl. Tebet Raya No. 12, Jakarta Selatan',
            'operational_days' => 'Senin–Sabtu',
            'operational_hours' => '07.00–22.00 WIB',
            'operational_note' => 'Minggu & tanggal merah: slow response',
            'social_instagram' => null,
            'social_tiktok' => null,
            'social_x' => null,
        ], $overrides);
    }
}
