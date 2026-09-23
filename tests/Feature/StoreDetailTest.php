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
            ->assertSee('google.com/maps?q=-6.2297,106.8294', false);
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

    public function test_store_detail_disables_add_to_cart_when_store_is_closed(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['slug' => 'warung-tutup-toko', 'is_open' => false]);
        Product::factory()->for($seller)->create(['name' => 'Nasi Goreng Tek-Tek', 'status' => 'approved']);

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertSee('Nasi Goreng Tek-Tek')
            ->assertDontSee('+ Keranjang')
            ->assertSee('Warung tutup');
    }

    public function test_store_detail_shows_add_to_cart_when_store_is_open(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['slug' => 'warung-buka-toko', 'is_open' => true]);
        Product::factory()->for($seller)->create(['name' => 'Nasi Goreng Tek-Tek', 'status' => 'approved']);

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertSee('+ Keranjang');
    }

    public function test_store_detail_product_images_can_be_zoomed(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['slug' => 'warung-zoom-foto']);
        Product::factory()->for($seller)->create([
            'name' => 'Nasi Goreng Tek-Tek',
            'status' => 'approved',
            'image_path' => 'products/nasi-goreng.png',
        ]);

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertSee('modal-product-zoom', false)
            ->assertSee('data-zoom-src', false)
            ->assertSee('storage/products/nasi-goreng.png', false)
            ->assertSee('Perbesar foto Nasi Goreng Tek-Tek', false);
    }

    public function test_store_detail_omits_zoom_without_product_images(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['slug' => 'warung-tanpa-foto']);
        Product::factory()->for($seller)->create(['name' => 'Produk Polos', 'status' => 'approved', 'image_path' => null]);

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertSee('Produk Polos')
            ->assertDontSee('modal-product-zoom', false)
            ->assertDontSee('data-zoom-src', false);
    }

    public function test_store_detail_has_product_search(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['slug' => 'warung-cari-produk']);
        Product::factory()->for($seller)->create([
            'name' => 'Nasi Goreng Tek-Tek',
            'category' => 'Makanan',
            'status' => 'approved',
        ]);

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertSee('id="product-search"', false)
            ->assertSee('data-product-search', false)
            ->assertSee('Nasi Goreng Tek-Tek Makanan', false);
    }

    public function test_store_detail_omits_product_search_without_products(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['slug' => 'warung-tanpa-produk-cari']);

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertSee('Belum ada produk')
            ->assertDontSee('id="product-search"', false);
    }
}
