<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SellerVerification;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletCreditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_completing_a_paid_order_credits_the_store_wallet(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$store, $order] = $this->paidOrder(total: 50000);

        $this->actingAs($admin)
            ->patch(route('admin.orders.complete', $order))
            ->assertRedirect()
            ->assertSessionHas('success');

        $wallet = Wallet::forStore($store);
        $transaction = $wallet->transactions()->sole();

        $this->assertSame(50000, $wallet->balance);
        $this->assertSame(WalletTransaction::TYPE_CREDIT, $transaction->type);
        $this->assertSame(50000, $transaction->amount);
        $this->assertSame(50000, $transaction->balance_after);
        $this->assertSame($order->id, $transaction->reference_id);

        $order->refresh();

        $this->assertSame(Order::STATUS_COMPLETED, $order->status);
        $this->assertSame(0, $order->commission_amount);
        $this->assertNotNull($order->completed_at);
    }

    public function test_completing_a_paid_order_twice_credits_the_wallet_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$store, $order] = $this->paidOrder(total: 50000);

        $this->actingAs($admin)->patch(route('admin.orders.complete', $order));
        $this->actingAs($admin)->patch(route('admin.orders.complete', $order));

        $wallet = Wallet::forStore($store);

        $this->assertSame(50000, $wallet->balance);
        $this->assertSame(1, $wallet->transactions()->count());
    }

    public function test_commission_is_withheld_from_the_wallet_credit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Setting::setValue(Setting::COMMISSION_PERCENT, 5);

        [$store, $order] = $this->paidOrder(total: 50000);

        $this->actingAs($admin)->patch(route('admin.orders.complete', $order));

        $wallet = Wallet::forStore($store);

        // 5% of 50.000 = 2.500 commission, so 47.500 reaches the wallet.
        $this->assertSame(47500, $wallet->balance);
        $this->assertSame(2500, $order->fresh()->commission_amount);
        $this->assertSame(47500, $wallet->transactions()->value('amount'));
    }

    public function test_an_unpaid_order_cannot_release_funds(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$store, $order] = $this->paidOrder(total: 50000);

        $order->update(['status' => Order::STATUS_PENDING_PAYMENT]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.complete', $order))
            ->assertSessionHas('error');

        $this->assertSame(0, Wallet::forStore($store)->balance);
        $this->assertSame(Order::STATUS_PENDING_PAYMENT, $order->fresh()->status);
    }

    public function test_cancelling_an_unpaid_order_returns_its_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->for($seller)->create(['stock' => 10, 'status' => 'approved']);

        $order = Order::factory()->for($store)->create(['status' => Order::STATUS_PENDING_PAYMENT]);
        $order->items()->create([
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => 10000,
            'qty' => 3,
            'subtotal' => 30000,
        ]);
        $product->decrement('stock', 3);

        $this->actingAs($admin)
            ->patch(route('admin.orders.cancel', $order))
            ->assertSessionHas('success');

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_a_paid_order_cannot_be_cancelled(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, $order] = $this->paidOrder(total: 50000);

        $this->actingAs($admin)
            ->patch(route('admin.orders.cancel', $order))
            ->assertSessionHas('error');

        $this->assertSame(Order::STATUS_PAID, $order->fresh()->status);
    }

    public function test_seller_wallet_page_shows_balance_and_held_funds(): void
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        $store = Store::factory()->for($seller)->create(['name' => 'Warung Bang Jago']);
        Wallet::factory()->for($store)->withBalance(75000)->create();

        Order::factory()->for($store)->paid()->create(['total' => 20000]);

        $this->actingAs($seller)
            ->get(route('seller.wallet.index'))
            ->assertOk()
            ->assertSee('Rp 75.000')
            ->assertSee('Rp 20.000');
    }

    public function test_admin_can_open_the_orders_and_wallet_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$store, $order] = $this->paidOrder(total: 50000);
        $order->items()->create([
            'name' => 'Nasi Goreng',
            'price' => 25000,
            'qty' => 2,
            'subtotal' => 50000,
        ]);

        // A second order whose transfer still waits for verification.
        $awaiting = Order::factory()->for($store)->create([
            'total' => 20000,
            'status' => Order::STATUS_WAITING_VERIFICATION,
        ]);
        $awaiting->payments()->create([
            'amount' => 20000,
            'status' => Payment::STATUS_PENDING,
            'proof_path' => 'payment-proofs/bukti.jpg',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders'))
            ->assertOk()
            ->assertSee('Pesanan marketplace')
            ->assertSee($order->code())
            ->assertSee('Nasi Goreng');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Perlu tindakan keuangan')
            ->assertSee($store->name);
    }

    /** @return array{0: Store, 1: Order} */
    private function paidOrder(int $total): array
    {
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create();
        $order = Order::factory()->for($store)->paid()->create(['total' => $total]);

        return [$store, $order];
    }
}
