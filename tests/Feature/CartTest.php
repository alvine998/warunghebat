<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_adding_to_cart(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($seller)->create();
        $product = Product::factory()->for($seller)->create(['status' => 'approved']);

        $this->post(route('cart.store'), ['product_id' => $product->id])
            ->assertRedirect(route('login'));
    }

    public function test_buyer_can_add_approved_product_to_cart(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['name' => 'Warung Bang Jago', 'slug' => 'warung-bang-jago']);
        $product = Product::factory()->for($seller)->create([
            'name' => 'Nasi Goreng',
            'status' => 'approved',
            'stock' => 10,
        ]);

        $this->actingAs($buyer)
            ->post(route('cart.store'), ['product_id' => $product->id])
            ->assertSessionHas('success')
            ->assertSessionHas('cart');

        $cart = session('cart');

        $this->assertSame($store->id, $cart['store_id']);
        $this->assertSame('Warung Bang Jago', $cart['store_name']);
        $this->assertSame(1, $cart['items'][$product->id]['qty']);
    }

    public function test_same_store_products_merge_in_one_cart(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $seller = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($seller)->create();
        $a = Product::factory()->for($seller)->create(['status' => 'approved', 'stock' => 5]);
        $b = Product::factory()->for($seller)->create(['status' => 'approved', 'stock' => 5]);

        $this->actingAs($buyer)->post(route('cart.store'), ['product_id' => $a->id]);
        $this->actingAs($buyer)->post(route('cart.store'), ['product_id' => $b->id]);

        $cart = session('cart');

        $this->assertCount(2, $cart['items']);
        $this->assertSame(1, $cart['items'][$a->id]['qty']);
        $this->assertSame(1, $cart['items'][$b->id]['qty']);
    }

    public function test_adding_from_other_store_returns_conflict_and_keeps_cart(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $sellerA = User::factory()->create(['role' => 'penjual']);
        $sellerB = User::factory()->create(['role' => 'penjual']);
        $storeA = Store::factory()->for($sellerA)->create(['name' => 'Warung A', 'slug' => 'warung-a']);
        Store::factory()->for($sellerB)->create(['name' => 'Warung B', 'slug' => 'warung-b']);
        $productA = Product::factory()->for($sellerA)->create(['status' => 'approved']);
        $productB = Product::factory()->for($sellerB)->create(['status' => 'approved']);

        $this->actingAs($buyer)->post(route('cart.store'), ['product_id' => $productA->id]);
        $cartBefore = session('cart');

        $this->actingAs($buyer)
            ->post(route('cart.store'), ['product_id' => $productB->id])
            ->assertSessionHas('cart_conflict')
            ->assertSessionMissing('success');

        $conflict = session('cart_conflict');
        $cartAfter = session('cart');

        $this->assertSame($storeA->id, $cartAfter['store_id']);
        $this->assertSame('Warung A', $conflict['current_store_name']);
        $this->assertSame('Warung B', $conflict['new_store_name']);
        $this->assertSame($productB->id, $conflict['product_id']);
        $this->assertSame($cartBefore['items'], $cartAfter['items']);
    }

    public function test_force_flag_replaces_cart_with_new_store(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $sellerA = User::factory()->create(['role' => 'penjual']);
        $sellerB = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($sellerA)->create(['name' => 'Warung A', 'slug' => 'warung-a']);
        $storeB = Store::factory()->for($sellerB)->create(['name' => 'Warung B', 'slug' => 'warung-b']);
        $productA = Product::factory()->for($sellerA)->create(['status' => 'approved']);
        $productB = Product::factory()->for($sellerB)->create(['name' => 'Produk B', 'status' => 'approved']);

        $this->actingAs($buyer)->post(route('cart.store'), ['product_id' => $productA->id]);
        $this->actingAs($buyer)->post(route('cart.store'), [
            'product_id' => $productB->id,
            'force' => 1,
        ])->assertSessionHas('success')
            ->assertSessionMissing('cart_conflict');

        $cart = session('cart');

        $this->assertSame($storeB->id, $cart['store_id']);
        $this->assertSame('Warung B', $cart['store_name']);
        $this->assertArrayNotHasKey($productA->id, $cart['items']);
        $this->assertSame(1, $cart['items'][$productB->id]['qty']);
    }

    public function test_pending_product_cannot_be_added_to_cart(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $seller = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($seller)->create();
        $product = Product::factory()->for($seller)->create(['status' => 'pending']);

        $this->actingAs($buyer)
            ->post(route('cart.store'), ['product_id' => $product->id])
            ->assertSessionMissing('cart')
            ->assertSessionHas('error');
    }

    public function test_out_of_stock_product_cannot_be_added_to_cart(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $seller = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($seller)->create();
        $product = Product::factory()->for($seller)->create(['status' => 'approved', 'stock' => 0]);

        $this->actingAs($buyer)
            ->post(route('cart.store'), ['product_id' => $product->id])
            ->assertSessionMissing('cart')
            ->assertSessionHas('error');
    }

    public function test_cart_page_shows_items_and_the_total(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['name' => 'Warung Bang Jago', 'slug' => 'warung-bang-jago']);
        $product = Product::factory()->for($seller)->create([
            'name' => 'Nasi Goreng',
            'status' => 'approved',
            'stock' => 10,
            'price' => 15000,
        ]);

        $this->actingAs($buyer)
            ->withSession(['cart' => $this->cartFor($store, $product, qty: 3)])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Nasi Goreng')
            ->assertSee('Rp 45.000')
            ->assertSee('Warung Bang Jago');
    }

    public function test_cart_page_drops_a_product_that_is_no_longer_available(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->for($seller)->create(['status' => 'approved', 'stock' => 5]);

        $this->actingAs($buyer)
            ->withSession(['cart' => $this->cartFor($store, $product, qty: 2)]);

        $product->update(['stock' => 0]);

        $this->actingAs($buyer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Keranjang masih kosong')
            ->assertSessionHas('error');

        $this->assertNull(session('cart'));
    }

    public function test_cart_quantity_is_clamped_to_available_stock(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->for($seller)->create(['status' => 'approved', 'stock' => 4]);

        $this->actingAs($buyer)
            ->withSession(['cart' => $this->cartFor($store, $product, qty: 1)])
            ->patch(route('cart.update', $product), ['qty' => 99])
            ->assertSessionHas('error');

        $this->assertSame(4, session('cart')['items'][$product->id]['qty']);
    }

    public function test_buyer_can_remove_a_product_from_the_cart(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->for($seller)->create(['status' => 'approved', 'stock' => 5]);

        $this->actingAs($buyer)
            ->withSession(['cart' => $this->cartFor($store, $product, qty: 2)])
            ->delete(route('cart.destroy', $product))
            ->assertSessionHas('success');

        $this->assertNull(session('cart'));
    }

    public function test_cart_quantity_must_be_at_least_one(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->for($seller)->create(['status' => 'approved', 'stock' => 5]);

        $this->actingAs($buyer)
            ->withSession(['cart' => $this->cartFor($store, $product, qty: 2)])
            ->patch(route('cart.update', $product), ['qty' => 0])
            ->assertSessionHasErrors(['qty']);

        $this->assertSame(2, session('cart')['items'][$product->id]['qty']);
    }

    public function test_cart_quantity_update_is_refused_for_a_product_outside_the_cart(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $seller = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($seller)->create();
        $product = Product::factory()->for($seller)->create(['status' => 'approved', 'stock' => 5]);

        $this->actingAs($buyer)
            ->patch(route('cart.update', $product), ['qty' => 2])
            ->assertSessionHas('error');

        $this->assertNull(session('cart'));
    }

    /** @return array<string, mixed> */
    private function cartFor(Store $store, Product $product, int $qty): array
    {
        return [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'store_slug' => $store->slug,
            'items' => [
                $product->id => [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'qty' => $qty,
                ],
            ],
        ];
    }
}
