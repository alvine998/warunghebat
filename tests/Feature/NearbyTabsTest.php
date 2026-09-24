<?php

namespace Tests\Feature;

use App\Models\SellerVerification;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NearbyTabsTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_update_requires_coordinates(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        Store::resolveFor($seller);

        $this->actingAs($seller)
            ->put(route('seller.store.update'), ['name' => 'Warung Tanpa Titik'])
            ->assertSessionHasErrors(['latitude', 'longitude']);

        $this->actingAs($seller)
            ->put(route('seller.store.update'), [
                'name' => 'Warung Ada Titik',
                'latitude' => -6.2297,
                'longitude' => 106.8294,
            ])
            ->assertSessionHasNoErrors();

        $store = $seller->store()->first();
        $this->assertEquals(-6.2297, (float) $store->latitude);
        $this->assertEquals(106.8294, (float) $store->longitude);
    }

    public function test_home_warung_section_has_list_and_map_tabs(): void
    {
        $owner = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($owner)->create([
            'name' => 'Warung Peta',
            'latitude' => -6.2297,
            'longitude' => 106.8294,
            'is_open' => true,
        ]);

        $this->get('/?lat=-6.2297&lng=106.8294')
            ->assertOk()
            ->assertSee('data-nearby-tabs="home-warung"', false)
            ->assertSee('📋 Daftar', false)
            ->assertSee('🗺️ Peta', false)
            ->assertSee('maps.google.com/maps?q=-6.2297,106.8294', false)
            ->assertSee('Warung Peta');
    }

    public function test_store_index_has_list_and_map_tabs(): void
    {
        $owner = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($owner)->create([
            'name' => 'Warung Pin',
            'latitude' => -6.2352,
            'longitude' => 106.8498,
            'is_open' => true,
        ]);

        $this->get(route('store.index'))
            ->assertOk()
            ->assertSee('data-nearby-tabs="semua-warung"', false)
            ->assertSee('📋 Daftar', false)
            ->assertSee('🗺️ Peta', false)
            ->assertSee('maps.google.com/maps?q=-6.2352,106.8498', false)
            ->assertSee('Rute di Google Maps', false)
            ->assertSee('Warung Pin');
    }

    public function test_map_tab_empty_state_when_no_coordinates(): void
    {
        $owner = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($owner)->create([
            'name' => 'Warung Tanpa Koordinat',
            'latitude' => null,
            'longitude' => null,
            'is_open' => true,
        ]);

        $this->get(route('store.index'))
            ->assertOk()
            ->assertSee('Belum ada warung yang bisa dipetakan', false);
    }
}
