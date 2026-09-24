<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_hero_searchbar_submits_to_the_search_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<form method="GET" action="'.route('search.index').'"', false)
            ->assertSee('name="q"', false);
    }

    public function test_search_finds_matching_stores_and_products(): void
    {
        [$store, $product] = $this->storeWithProduct('Warung Bang Jago', 'Nasi Goreng Spesial');

        $this->get(route('search.index', ['q' => 'Jago']))
            ->assertOk()
            ->assertSee('Warung Bang Jago', false)
            ->assertSee('name="robots" content="noindex, nofollow"', false);

        $this->get(route('search.index', ['q' => 'Spesial']))
            ->assertOk()
            ->assertSee('Nasi Goreng Spesial', false)
            ->assertSee('Warung Bang Jago', false);
    }

    public function test_search_shows_an_empty_state_without_a_match(): void
    {
        $this->storeWithProduct('Warung Bang Jago', 'Nasi Goreng');

        $this->get(route('search.index', ['q' => 'tidak-ada-hasil']))
            ->assertOk()
            ->assertSee('Tidak ketemu', false);
    }

    public function test_search_without_a_keyword_shows_starter_hints(): void
    {
        $this->get(route('search.index'))
            ->assertOk()
            ->assertSee('Mau cari apa hari ini?', false)
            ->assertDontSee('Hasil untuk', false);
    }

    public function test_warung_page_search_box_filters_by_keyword(): void
    {
        $this->storeWithProduct('Warung Bang Jago', 'Nasi Goreng');
        $this->storeWithProduct('Kopi Hebat', 'Kopi Susu');

        $response = $this->get(route('store.index', ['q' => 'Jago']))->assertOk();

        $response->assertSee('Warung Bang Jago', false);
        $response->assertDontSee('Kopi Hebat', false);
        $response->assertSee('id="warung-q"', false);
    }

    public function test_search_product_pagination_keeps_the_keyword(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['slug' => 'warung-promo']);
        Product::factory()->for($seller)->count(13)->create([
            'name' => 'Goreng Enak',
            'status' => 'approved',
            'stock' => 5,
        ]);

        $this->get(route('search.index', ['q' => 'Goreng']))
            ->assertOk()
            ->assertSee('q=Goreng', false)
            ->assertSee('page=2', false);
    }

    /** @return array{0: Store, 1: Product} */
    private function storeWithProduct(string $storeName, string $productName): array
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create([
            'name' => $storeName,
            'slug' => str()->slug($storeName).'-'.$seller->id,
            'is_open' => true,
        ]);
        $product = Product::factory()->for($seller)->create([
            'name' => $productName,
            'status' => 'approved',
            'stock' => 10,
        ]);

        return [$store, $product];
    }
}
