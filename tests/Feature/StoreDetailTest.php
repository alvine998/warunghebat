<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_store_detail_by_slug(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create([
            'name' => 'Warung Bang Jago',
            'slug' => 'warung-bang-jago',
            'description' => 'Gorengan hangat tiap sore.',
            'address' => 'Jl. Tebet Raya No. 12',
        ]);
        Product::factory()->for($seller)->create([
            'name' => 'Nasi Goreng Tek-Tek',
            'price' => 15000,
            'status' => 'approved',
        ]);

        $this->get('/w/warung-bang-jago')
            ->assertOk()
            ->assertSee('Warung Bang Jago')
            ->assertSee('Gorengan hangat tiap sore.')
            ->assertSee('Jl. Tebet Raya No. 12')
            ->assertSee('Nasi Goreng Tek-Tek')
            ->assertSee('Rp 15.000');
    }

    public function test_store_detail_shows_only_approved_products(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['slug' => 'warung-saring']);
        Product::factory()->for($seller)->create(['name' => 'Produk Tayang', 'status' => 'approved']);
        Product::factory()->for($seller)->create(['name' => 'Produk Pending', 'status' => 'pending']);
        Product::factory()->for($seller)->create(['name' => 'Produk Ditolak', 'status' => 'rejected']);

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertSee('Produk Tayang')
            ->assertDontSee('Produk Pending')
            ->assertDontSee('Produk Ditolak');
    }

    public function test_store_detail_shows_empty_state_without_products(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['slug' => 'warung-kosong']);

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertSee('Belum ada produk');
    }

    public function test_store_detail_returns_404_for_unknown_slug(): void
    {
        $this->get('/w/warung-tidak-ada')->assertNotFound();
    }

    public function test_store_detail_shows_map_when_coordinates_exist(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create([
            'slug' => 'warung-berpeta',
            'latitude' => -6.2297,
            'longitude' => 106.8294,
            'address' => 'Jl. Tebet Raya No. 12',
        ]);

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertSee('store-detail-map', false)
            ->assertSee('data-lat="-6.2297"', false)
            ->assertSee('data-lng="106.8294"', false)
            ->assertSee('openstreetmap.org/#map=16/-6.2297/106.8294', false);
    }

    public function test_store_detail_hides_map_without_coordinates(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create([
            'slug' => 'warung-tanpa-peta',
            'latitude' => null,
            'longitude' => null,
        ]);

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertDontSee('store-detail-map')
            ->assertDontSee('LOKASI WARUNG');
    }

    public function test_landing_links_to_store_detail(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create([
            'name' => 'Warung Tautan',
            'slug' => 'warung-tautan',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee(route('store.show', $store), false);
    }
}
