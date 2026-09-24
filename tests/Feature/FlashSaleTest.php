<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\SellerVerification;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FlashSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_price_applies_only_inside_the_promo_window(): void
    {
        $base = ['price' => 20000, 'stock' => 10, 'status' => 'approved'];

        $active = new Product($base + ['discount_price' => 15000]);
        $this->assertTrue($active->hasActivePromo());
        $this->assertSame(15000, $active->effectivePrice());
        $this->assertSame(25, $active->discountPercent());

        $none = new Product($base);
        $this->assertFalse($none->hasActivePromo());
        $this->assertSame(20000, $none->effectivePrice());
        $this->assertNull($none->discountPercent());

        $tooHigh = new Product($base + ['discount_price' => 20000]);
        $this->assertFalse($tooHigh->hasActivePromo());
        $this->assertSame(20000, $tooHigh->effectivePrice());

        $expired = new Product($base + [
            'discount_price' => 15000,
            'promo_starts_at' => now()->subDays(3),
            'promo_ends_at' => now()->subDay(),
        ]);
        $this->assertFalse($expired->hasActivePromo());

        $future = new Product($base + [
            'discount_price' => 15000,
            'promo_starts_at' => now()->addDay(),
            'promo_ends_at' => now()->addDays(2),
        ]);
        $this->assertFalse($future->hasActivePromo());
    }

    public function test_flash_sale_scope_only_returns_live_promos(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        Store::factory()->for($seller)->create(['is_open' => true]);

        $live = Product::factory()->for($seller)->create([
            'price' => 20000, 'discount_price' => 15000, 'stock' => 5, 'status' => 'approved',
        ]);
        Product::factory()->for($seller)->create([
            'price' => 20000, 'discount_price' => null, 'stock' => 5, 'status' => 'approved',
        ]);
        Product::factory()->for($seller)->create([
            'price' => 20000, 'discount_price' => 25000, 'stock' => 5, 'status' => 'approved',
        ]);
        Product::factory()->for($seller)->create([
            'price' => 20000, 'discount_price' => 15000, 'stock' => 5, 'status' => 'pending',
        ]);
        Product::factory()->for($seller)->create([
            'price' => 20000, 'discount_price' => 15000, 'stock' => 0, 'status' => 'approved',
        ]);
        Product::factory()->for($seller)->create([
            'price' => 20000, 'discount_price' => 15000, 'stock' => 5, 'status' => 'approved',
            'promo_ends_at' => now()->subHour(),
        ]);

        $this->assertEquals([$live->id], Product::withActivePromo()->pluck('id')->all());
    }

    public function test_seller_cannot_price_promo_at_or_above_regular_price(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        Store::factory()->for($seller)->create();

        $this->actingAs($seller)->post(route('seller.products.store'), [
            'name' => 'Nasi Goreng',
            'price' => '15000',
            'discount_price' => '15000',
            'stock' => '10',
            'category' => 'Makanan',
            'image' => UploadedFile::fake()->image('produk.jpg'),
        ])->assertSessionHasErrors(['discount_price']);

        $this->assertSame(0, Product::count());
    }

    public function test_seller_can_set_a_promo_with_a_window(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        Store::factory()->for($seller)->create();

        $this->actingAs($seller)->post(route('seller.products.store'), [
            'name' => 'Nasi Goreng',
            'price' => '20000',
            'discount_price' => '15.000',
            'promo_starts_at' => now()->format('Y-m-d\TH:i'),
            'promo_ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'stock' => '10',
            'category' => 'Makanan',
            'image' => UploadedFile::fake()->image('produk.jpg'),
        ])->assertRedirect(route('seller.products.index'));

        $product = Product::sole();
        $this->assertSame(15000, $product->discount_price);
        $this->assertTrue($product->hasActivePromo());
        $this->assertSame('pending', $product->status);
    }

    public function test_cart_and_checkout_charge_the_promo_price(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        [$store, $product] = $this->storeWithPromoProduct(price: 20000, discount: 15000);

        $this->actingAs($buyer)
            ->post(route('cart.store'), ['product_id' => $product->id])
            ->assertSessionHas('success');

        $this->assertSame(15000, session('cart')['items'][$product->id]['price']);

        $this->actingAs($buyer)->post(route('checkout.store'))
            ->assertRedirectContains(route('orders.show', Order::sole()));

        $order = Order::sole();
        $this->assertSame(15000, $order->total);
        $this->assertSame(15000, $order->items()->sole()->price);
    }

    public function test_landing_shows_flash_sale_only_when_promos_exist(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('id="flash-sale"', false);

        [$store, $product] = $this->storeWithPromoProduct(price: 20000, discount: 12000);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="flash-sale"', false)
            ->assertSee('FLASH SALE', false)
            ->assertSee($product->name, false)
            ->assertSee('data-countdown', false)
            ->assertSee(route('promo.index'), false);
    }

    public function test_promo_page_lists_live_promos_and_joins_the_sitemap(): void
    {
        [$store, $product] = $this->storeWithPromoProduct(price: 20000, discount: 12000);

        $this->get(route('promo.index'))
            ->assertOk()
            ->assertSee($product->name, false)
            ->assertSee('−40%', false);

        $this->get('/sitemap.xml')->assertOk()->assertSee(route('promo.index'), false);
    }

    /** @return array{0: Store, 1: Product} */
    private function storeWithPromoProduct(int $price, int $discount): array
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create([
            'name' => 'Warung Bang Jago',
            'slug' => 'warung-bang-jago',
            'is_open' => true,
        ]);
        $product = Product::factory()->for($seller)->create([
            'name' => 'Nasi Goreng Promo',
            'status' => 'approved',
            'stock' => 10,
            'price' => $price,
            'discount_price' => $discount,
        ]);

        return [$store, $product];
    }
}
