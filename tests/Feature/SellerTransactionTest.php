<?php

namespace Tests\Feature;

use App\Models\InStoreTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\SellerVerification;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_seller_can_record_walk_in_sale_and_stock_is_decremented(): void
    {
        [$seller, $store, $product] = $this->verifiedSellerWithProduct();

        $this->actingAs($seller)
            ->post(route('seller.transactions.store'), [
                'buyer_type' => InStoreTransaction::BUYER_WALK_IN,
                'buyer_name' => 'Budi Santoso',
                'buyer_phone' => '08123456789',
                'items' => [
                    ['product_id' => $product->id, 'qty' => 2],
                ],
            ])
            ->assertRedirect(route('seller.transactions.index'))
            ->assertSessionHas('success');

        $transaction = InStoreTransaction::sole();

        $this->assertSame($store->id, $transaction->store_id);
        $this->assertNull($transaction->buyer_user_id);
        $this->assertSame(InStoreTransaction::BUYER_WALK_IN, $transaction->buyer_type);
        $this->assertSame('Budi Santoso', $transaction->buyer_name);
        $this->assertSame('08123456789', $transaction->buyer_phone);
        $this->assertSame(30000, $transaction->total);
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame(0, Order::count());
        $this->assertSame(0, Wallet::forStore($store)->fresh()->balance);
        $this->assertDatabaseCount('wallet_transactions', 0);

        $item = $transaction->items()->sole();
        $this->assertSame($product->id, $item->product_id);
        $this->assertSame('Nasi Goreng', $item->name);
        $this->assertSame(15000, $item->price);
        $this->assertSame(2, $item->qty);
        $this->assertSame(30000, $item->subtotal);
    }

    public function test_registered_buyer_is_matched_by_email_and_promo_price_is_used(): void
    {
        [$seller, , $product] = $this->verifiedSellerWithProduct([
            'price' => 20000,
            'discount_price' => 12000,
            'promo_starts_at' => now()->subHour(),
            'promo_ends_at' => now()->addHour(),
        ]);
        $buyer = User::factory()->create([
            'name' => 'Sari Pembeli',
            'role' => 'pembeli',
            'email' => 'buyer@example.com',
            'phone' => '081234567890',
        ]);

        $this->actingAs($seller)
            ->post(route('seller.transactions.store'), [
                'buyer_type' => InStoreTransaction::BUYER_REGISTERED,
                'buyer_identifier' => 'buyer@example.com',
                'items' => [['product_id' => $product->id, 'qty' => 3]],
            ])
            ->assertRedirect(route('seller.transactions.index'));

        $transaction = InStoreTransaction::sole();

        $this->assertSame($buyer->id, $transaction->buyer_user_id);
        $this->assertSame('Sari Pembeli', $transaction->buyer_name);
        $this->assertSame('buyer@example.com', $transaction->buyer_email);
        $this->assertSame('081234567890', $transaction->buyer_phone);
        $this->assertSame(36000, $transaction->total);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_registered_buyer_can_be_matched_by_normalized_phone(): void
    {
        [$seller, , $product] = $this->verifiedSellerWithProduct();
        $buyer = User::factory()->create(['role' => 'pembeli', 'name' => 'Sari Pembeli', 'phone' => '08123456789']);

        $this->actingAs($seller)
            ->post(route('seller.transactions.store'), [
                'buyer_type' => InStoreTransaction::BUYER_REGISTERED,
                'buyer_identifier' => '+62 (812) 3456-789',
                'items' => [['product_id' => $product->id, 'qty' => 1]],
            ])
            ->assertRedirect(route('seller.transactions.index'));

        $this->assertSame($buyer->id, InStoreTransaction::sole()->buyer_user_id);
    }

    public function test_registered_buyer_lookup_requires_a_non_admin_account(): void
    {
        [$seller, , $product] = $this->verifiedSellerWithProduct();
        User::factory()->create(['role' => 'admin', 'email' => 'admin@example.com']);

        $this->actingAs($seller)
            ->from(route('seller.transactions.create'))
            ->post(route('seller.transactions.store'), [
                'buyer_type' => InStoreTransaction::BUYER_REGISTERED,
                'buyer_identifier' => 'admin@example.com',
                'items' => [['product_id' => $product->id, 'qty' => 1]],
            ])
            ->assertRedirect(route('seller.transactions.create'))
            ->assertSessionHasErrors(['buyer_identifier']);

        $this->assertDatabaseCount('in_store_transactions', 0);
    }

    public function test_unknown_registered_buyer_is_rejected_without_creating_transaction(): void
    {
        [$seller, , $product] = $this->verifiedSellerWithProduct();

        $this->actingAs($seller)
            ->from(route('seller.transactions.create'))
            ->post(route('seller.transactions.store'), [
                'buyer_type' => InStoreTransaction::BUYER_REGISTERED,
                'buyer_identifier' => 'missing@example.com',
                'items' => [['product_id' => $product->id, 'qty' => 1]],
            ])
            ->assertRedirect(route('seller.transactions.create'))
            ->assertSessionHasErrors(['buyer_identifier']);

        $this->assertDatabaseCount('in_store_transactions', 0);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_direct_sale_requires_a_buyer_identifier_and_at_least_one_product(): void
    {
        [$seller] = $this->verifiedSellerWithProduct();

        $this->actingAs($seller)
            ->from(route('seller.transactions.create'))
            ->post(route('seller.transactions.store'), [
                'buyer_type' => InStoreTransaction::BUYER_REGISTERED,
                'items' => [],
            ])
            ->assertRedirect(route('seller.transactions.create'))
            ->assertSessionHasErrors(['buyer_identifier', 'items']);

        $this->assertDatabaseCount('in_store_transactions', 0);
    }

    public function test_direct_sale_rejects_insufficient_stock_without_partial_writes(): void
    {
        [$seller, , $product] = $this->verifiedSellerWithProduct([], 2);

        $this->actingAs($seller)
            ->from(route('seller.transactions.create'))
            ->post(route('seller.transactions.store'), [
                'buyer_type' => InStoreTransaction::BUYER_WALK_IN,
                'buyer_name' => 'Budi Santoso',
                'items' => [['product_id' => $product->id, 'qty' => 3]],
            ])
            ->assertRedirect(route('seller.transactions.create'))
            ->assertSessionHasErrors(['items']);

        $this->assertDatabaseCount('in_store_transactions', 0);
        $this->assertDatabaseCount('in_store_transaction_items', 0);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_seller_cannot_record_another_sellers_product(): void
    {
        [$seller] = $this->verifiedSellerWithProduct();
        [, , $otherProduct] = $this->verifiedSellerWithProduct();

        $this->actingAs($seller)
            ->from(route('seller.transactions.create'))
            ->post(route('seller.transactions.store'), [
                'buyer_type' => InStoreTransaction::BUYER_WALK_IN,
                'buyer_name' => 'Budi Santoso',
                'items' => [['product_id' => $otherProduct->id, 'qty' => 1]],
            ])
            ->assertRedirect(route('seller.transactions.create'))
            ->assertSessionHasErrors(['items']);

        $this->assertDatabaseCount('in_store_transactions', 0);
        $this->assertSame(10, $otherProduct->fresh()->stock);
    }

    public function test_transaction_pages_require_a_verified_penjual(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $unverifiedSeller = User::factory()->create(['role' => 'penjual']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('seller.transactions.index'))->assertRedirect(route('login'));

        $this->actingAs($buyer)->get(route('seller.transactions.index'))->assertRedirect(route('dashboard'));

        $this->actingAs($unverifiedSeller)
            ->get(route('seller.transactions.index'))
            ->assertRedirect(route('seller.verification.show'));

        $this->actingAs($admin)
            ->get(route('seller.transactions.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_seller_history_and_report_only_include_their_own_sales(): void
    {
        [$seller, $store, $product] = $this->verifiedSellerWithProduct();
        [, $otherStore, $otherProduct] = $this->verifiedSellerWithProduct();
        $buyer = User::factory()->create(['role' => 'pembeli', 'name' => 'Sari Pembeli']);
        InStoreTransaction::recordSale($store, [['product_id' => $product->id, 'qty' => 1]], InStoreTransaction::BUYER_WALK_IN, null, ['name' => 'Budi']);
        InStoreTransaction::recordSale($store, [['product_id' => $product->id, 'qty' => 2]], InStoreTransaction::BUYER_REGISTERED, $buyer, []);
        InStoreTransaction::recordSale($otherStore, [['product_id' => $otherProduct->id, 'qty' => 2]], InStoreTransaction::BUYER_WALK_IN, null, ['name' => 'Dina']);

        $this->actingAs($seller)
            ->get(route('seller.transactions.index'))
            ->assertOk()
            ->assertSee('Budi')
            ->assertSee('Sari Pembeli')
            ->assertDontSee('Dina')
            ->assertSee('Rp 45.000')
            ->assertSee('Rp 30.000');

        $this->actingAs($seller)
            ->get(route('seller.transactions.index', ['buyer_type' => InStoreTransaction::BUYER_REGISTERED]))
            ->assertOk()
            ->assertSee('Rp 30.000')
            ->assertDontSee('Budi');
    }

    public function test_registration_accepts_optional_phone_and_normalizes_it(): void
    {
        $this->post(route('register'), [
            'name' => 'Sari Dewi',
            'email' => 'sari@example.com',
            'phone' => '+62 (812) 3456-789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pembeli',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame('08123456789', User::where('email', 'sari@example.com')->sole()->phone);
    }

    public function test_registration_rejects_invalid_phone_characters(): void
    {
        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'Sari Dewi',
                'email' => 'sari@example.com',
                'phone' => '08123abc',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'pembeli',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['phone']);

        $this->assertDatabaseMissing('users', ['email' => 'sari@example.com']);
    }

    /** @param array<string, mixed> $productAttributes
     * @return array{0: User, 1: Store, 2: Product}
     */
    private function verifiedSellerWithProduct(array $productAttributes = [], int $stock = 10): array
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        $store = Store::factory()->for($seller)->create(['name' => 'Warung Sari']);
        $product = Product::factory()->for($seller)->create(array_merge([
            'name' => 'Nasi Goreng',
            'price' => 15000,
            'discount_price' => null,
            'stock' => $stock,
            'status' => 'approved',
        ], $productAttributes));

        return [$seller, $store, $product];
    }
}
