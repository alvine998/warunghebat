<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_checking_out(): void
    {
        $this->post(route('checkout.store'))->assertRedirect(route('login'));
    }

    public function test_checkout_creates_the_order_with_items_and_reserves_stock(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        [$store, $product] = $this->storeWithProduct(stock: 10, price: 15000);

        $response = $this->actingAs($buyer)
            ->withSession(['cart' => $this->cart($store, $product, qty: 3)])
            ->post(route('checkout.store'));

        $order = Order::sole();

        $response->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success')
            ->assertSessionMissing('cart');

        $this->assertSame($buyer->id, $order->user_id);
        $this->assertSame($store->id, $order->store_id);
        $this->assertSame(Order::STATUS_PENDING_PAYMENT, $order->status);
        $this->assertSame(45000, $order->total);
        $this->assertSame('Warung Bang Jago', $order->warung_name);
        $this->assertSame(7, $product->fresh()->stock);

        $item = $order->items()->sole();

        $this->assertSame($product->id, $item->product_id);
        $this->assertSame('Nasi Goreng', $item->name);
        $this->assertSame(15000, $item->price);
        $this->assertSame(3, $item->qty);
        $this->assertSame(45000, $item->subtotal);
    }

    public function test_checkout_rejects_an_empty_cart(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($buyer)
            ->post(route('checkout.store'))
            ->assertSessionHasErrors(['cart']);

        $this->assertSame(0, Order::count());
    }

    public function test_checkout_rejects_when_stock_is_no_longer_enough(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        [$store, $product] = $this->storeWithProduct(stock: 2);

        $this->actingAs($buyer)
            ->withSession(['cart' => $this->cart($store, $product, qty: 5)])
            ->post(route('checkout.store'))
            ->assertSessionHasErrors(['cart']);

        $this->assertSame(0, Order::count());
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_checkout_rejects_a_product_that_is_no_longer_approved(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        [$store, $product] = $this->storeWithProduct(stock: 5);

        $product->update(['status' => 'pending']);

        $this->actingAs($buyer)
            ->withSession(['cart' => $this->cart($store, $product, qty: 1)])
            ->post(route('checkout.store'))
            ->assertSessionHasErrors(['cart']);

        $this->assertSame(0, Order::count());
        $this->assertSame(0, $store->orders()->count());
    }

    public function test_checkout_rejects_when_the_store_is_closed(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        [$store, $product] = $this->storeWithProduct(stock: 5, isOpen: false);

        $this->actingAs($buyer)
            ->withSession(['cart' => $this->cart($store, $product, qty: 1)])
            ->post(route('checkout.store'))
            ->assertSessionHasErrors(['cart']);

        $this->assertSame(0, Order::count());
    }

    /** @return array{0: Store, 1: Product} */
    private function storeWithProduct(int $stock, int $price = 15000, bool $isOpen = true): array
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create([
            'name' => 'Warung Bang Jago',
            'slug' => 'warung-bang-jago',
            'is_open' => $isOpen,
        ]);
        $product = Product::factory()->for($seller)->create([
            'name' => 'Nasi Goreng',
            'status' => 'approved',
            'stock' => $stock,
            'price' => $price,
        ]);

        return [$store, $product];
    }

    /** @return array<string, mixed> */
    private function cart(Store $store, Product $product, int $qty): array
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
