<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_lists_approved_products_from_every_store(): void
    {
        $first = $this->storeFor('Warung Bang Jago');
        $second = $this->storeFor('Kopi Hebat');

        Product::factory()->for($first->user)->create(['name' => 'Nasi Goreng Tek-Tek', 'category' => 'Makanan']);
        Product::factory()->for($second->user)->create(['name' => 'Ayam Geprek Mantul', 'category' => 'Makanan']);

        $this->get('/kategori/makanan')
            ->assertOk()
            ->assertSee('Nasi Goreng Tek-Tek')
            ->assertSee('Ayam Geprek Mantul')
            ->assertSee('Warung Bang Jago')
            ->assertSee('Kopi Hebat')
            ->assertSee(route('store.show', $first));
    }

    public function test_category_page_matches_the_category_name_in_any_letter_case(): void
    {
        $store = $this->storeFor('Warung Bang Jago');
        Product::factory()->for($store->user)->create(['name' => 'Nasi Uduk', 'category' => 'Makanan']);

        $this->get('/kategori/MAKANAN')->assertOk()->assertSee('Nasi Uduk');
    }

    public function test_category_page_search_filters_products_and_preserves_radius(): void
    {
        $store = $this->storeFor('Warung Bang Jago');
        Product::factory()->for($store->user)->create(['name' => 'Nasi Uduk', 'category' => 'Makanan']);
        Product::factory()->for($store->user)->create(['name' => 'Ayam Geprek', 'category' => 'Makanan']);
        Product::factory()->for($store->user)->create(['name' => 'Nasi Uduk Minuman', 'category' => 'Minuman']);

        $this->get('/kategori/makanan?q=Uduk&radius=5')
            ->assertOk()
            ->assertSee('Nasi Uduk')
            ->assertDontSee('Ayam Geprek')
            ->assertDontSee('Nasi Uduk Minuman')
            ->assertSee('id="category-q"', false)
            ->assertSee('name="radius" value="5"', false)
            ->assertSee(route('category.show', ['category' => 'makanan', 'radius' => 5]))
            ->assertSee('1 hasil untuk “Uduk”');
    }

    public function test_category_search_pagination_keeps_keyword_and_radius(): void
    {
        $store = $this->storeFor('Warung Bang Jago');
        Product::factory()->count(13)->for($store->user)->create([
            'name' => 'Nasi Uduk',
            'category' => 'Makanan',
        ]);

        $this->get('/kategori/makanan?q=Uduk&radius=5')
            ->assertOk()
            ->assertSee('q=Uduk', false)
            ->assertSee('radius=5', false)
            ->assertSee('page=2', false);
    }

    public function test_category_page_hides_other_categories_and_unapproved_products(): void
    {
        $store = $this->storeFor('Warung Bang Jago');

        Product::factory()->for($store->user)->create(['name' => 'Es Teh Manis', 'category' => 'Minuman']);
        Product::factory()->for($store->user)->create(['name' => 'Gorengan Pending', 'category' => 'Makanan', 'status' => 'pending']);
        Product::factory()->for($store->user)->create(['name' => 'Gorengan Ditolak', 'category' => 'Makanan', 'status' => 'rejected']);
        Product::factory()->for($store->user)->create(['name' => 'Tahu Isi Laris', 'category' => 'Makanan', 'status' => 'approved']);

        $this->get('/kategori/makanan')
            ->assertOk()
            ->assertSee('Tahu Isi Laris')
            ->assertDontSee('Es Teh Manis')
            ->assertDontSee('Gorengan Pending')
            ->assertDontSee('Gorengan Ditolak');
    }

    public function test_category_page_hides_products_without_a_storefront(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        Product::factory()->for($seller)->create(['name' => 'Produk Tanpa Warung', 'category' => 'Makanan']);

        $this->get('/kategori/makanan')->assertOk()->assertDontSee('Produk Tanpa Warung');
    }

    public function test_category_page_returns_404_for_unknown_category(): void
    {
        $this->get('/kategori/elektronik')->assertNotFound();
    }

    public function test_category_page_sorts_products_by_store_distance_when_coords_given(): void
    {
        $near = $this->storeFor('Warung Dekat', -6.2297, 106.8294);
        $far = $this->storeFor('Warung Jauh', -6.20, 106.85);

        Product::factory()->for($far->user)->create(['name' => 'Produk Jauh', 'category' => 'Makanan']);
        Product::factory()->for($near->user)->create(['name' => 'Produk Dekat', 'category' => 'Makanan']);

        $this->get('/kategori/makanan?lat=-6.2297&lng=106.8294')
            ->assertOk()
            ->assertSeeInOrder(['Produk Dekat', 'Produk Jauh']);
    }

    public function test_category_page_filters_products_outside_radius(): void
    {
        $far = $this->storeFor('Warung Luar', -6.0, 107.0);
        Product::factory()->for($far->user)->create(['name' => 'Produk Luar', 'category' => 'Makanan']);

        $this->get('/kategori/makanan?lat=-6.2297&lng=106.8294&radius=1')
            ->assertOk()
            ->assertDontSee('Produk Luar')
            ->assertSee('Belum ada Makanan di sekitar sini');
    }

    public function test_category_page_paginates_products(): void
    {
        $store = $this->storeFor('Warung Bang Jago');

        Product::factory()->count(13)->for($store->user)->create(['category' => 'Makanan'])->each(
            fn (Product $product, int $index) => $product->update(['name' => 'Produk-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)])
        );

        $this->get('/kategori/makanan')->assertOk()->assertSee('Produk-13')->assertDontSee('Produk-01');
        $this->get('/kategori/makanan?page=2')->assertOk()->assertSee('Produk-01')->assertDontSee('Produk-13');
    }

    public function test_home_category_cards_link_to_every_category_page(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (['makanan', 'minuman', 'sembako', 'harian', 'jajanan', 'frozen'] as $category) {
            $response->assertSee(route('category.show', ['category' => $category, 'radius' => 5]));
        }
    }

    public function test_home_category_cards_carry_location_to_the_category_page(): void
    {
        $this->get('/?lat=-6.2297&lng=106.8294&radius=2')
            ->assertOk()
            ->assertSee(route('category.show', ['category' => 'minuman', 'lat' => -6.2297, 'lng' => 106.8294, 'radius' => 2]));
    }

    public function test_category_page_offers_cart_button_for_open_stores(): void
    {
        $store = $this->storeFor('Warung Bang Jago');
        $product = Product::factory()->for($store->user)->create(['name' => 'Nasi Uduk', 'category' => 'Makanan']);

        $this->actingAs(User::factory()->create())
            ->get('/kategori/makanan')
            ->assertOk()
            ->assertSee('+ Keranjang')
            ->assertSee('name="product_id" value="'.$product->id.'"', false);
    }

    public function test_category_page_hides_cart_button_for_closed_and_sold_out_stores(): void
    {
        $closed = $this->storeFor('Warung Tutup');
        $closed->update(['is_open' => false]);
        Product::factory()->for($closed->user)->create(['name' => 'Gorengan Tutup', 'category' => 'Makanan']);

        $soldOut = $this->storeFor('Warung Habis');
        Product::factory()->for($soldOut->user)->create(['name' => 'Gorengan Habis', 'category' => 'Makanan', 'stock' => 0]);

        $this->actingAs(User::factory()->create())
            ->get('/kategori/makanan')
            ->assertOk()
            ->assertSee('Gorengan Tutup')
            ->assertSee('Gorengan Habis')
            ->assertDontSee('+ Keranjang');
    }

    public function test_category_page_asks_guests_to_login_before_adding_to_cart(): void
    {
        $store = $this->storeFor('Warung Bang Jago');
        Product::factory()->for($store->user)->create(['name' => 'Nasi Uduk', 'category' => 'Makanan']);

        $this->get('/kategori/makanan')
            ->assertOk()
            ->assertSee('+ Keranjang')
            ->assertDontSee('<input type="hidden" name="product_id"', false);
    }

    public function test_category_page_shows_cart_conflict_modal(): void
    {
        $store = $this->storeFor('Warung Bang Jago');
        Product::factory()->for($store->user)->create(['name' => 'Nasi Uduk', 'category' => 'Makanan']);

        $this->withSession(['cart_conflict' => [
            'product_id' => 1,
            'current_store_name' => 'Kopi Hebat',
            'new_store_name' => 'Warung Bang Jago',
        ]])->get('/kategori/makanan')
            ->assertOk()
            ->assertSee('modal-cart-conflict', false)
            ->assertSee('Ganti isi keranjang?');
    }

    private function storeFor(string $name, ?float $latitude = null, ?float $longitude = null): Store
    {
        $seller = User::factory()->create(['role' => 'penjual']);

        return Store::factory()->for($seller)->create([
            'name' => $name,
            'slug' => Str::slug($name),
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }
}
