<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SellerVerification;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The full money path a real order takes, in one test: cart, checkout, transfer
 * proof, verification, escrow release with commission, withdrawal and payout.
 */
class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_whole_chain_from_cart_to_payout(): void
    {
        Storage::fake('public');

        Setting::setValue(Setting::COMMISSION_PERCENT, 10);
        Setting::setValue(Setting::WITHDRAWAL_MIN, 10000);
        Setting::setValue(Setting::WITHDRAWAL_MAX, 1000000);

        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'penjual']);
        SellerVerification::factory()->for($seller)->verified()->create();
        $store = Store::factory()->for($seller)->create(['name' => 'Warung Bang Jago', 'is_open' => true]);
        $product = Product::factory()->for($seller)->create([
            'price' => 20000,
            'stock' => 10,
            'status' => 'approved',
        ]);
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $method = PaymentMethod::factory()->create(['name' => 'BCA a/n Warung Hebat', 'is_active' => true]);

        // 1. Buyer adds the product to the cart.
        $this->actingAs($buyer)
            ->post(route('cart.store'), ['product_id' => $product->id])
            ->assertSessionHas('success');

        // 2. Checkout turns the cart into an order and reserves the stock.
        $this->actingAs($buyer)
            ->post(route('checkout.store'))
            ->assertSessionHas('success')
            ->assertSessionMissing('cart');

        $order = Order::sole();

        $this->assertSame(Order::STATUS_PENDING_PAYMENT, $order->status);
        $this->assertSame(20000, $order->total);
        $this->assertSame(9, $product->fresh()->stock);

        // 3. Buyer transfers to Warung Hebat and uploads the receipt.
        $this->actingAs($buyer)
            ->post(route('orders.proof', $order), [
                'payment_method_id' => $method->id,
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertSessionHas('success');

        $this->assertSame(Order::STATUS_WAITING_VERIFICATION, $order->fresh()->status);

        // 4. Admin verifies: the platform holds the money, the wallet is still empty.
        $this->actingAs($admin)
            ->patch(route('admin.payments.verify', $order->payments()->sole()))
            ->assertSessionHas('success');

        $this->assertSame(Order::STATUS_PAID, $order->fresh()->status);
        $this->assertSame(0, Wallet::forStore($store)->balance);

        // 5. Admin completes the order: 10% commission, the rest reaches the wallet.
        $this->actingAs($admin)
            ->patch(route('admin.orders.complete', $order))
            ->assertSessionHas('success');

        $wallet = Wallet::forStore($store);

        $this->assertSame(18000, $wallet->balance);
        $this->assertSame(2000, $order->fresh()->commission_amount);

        // 6. Seller requests a withdrawal; the money is held immediately.
        $this->actingAs($seller)
            ->post(route('seller.wallet.withdraw'), [
                'amount' => 18000,
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'Bang Jago',
            ])
            ->assertSessionHas('success');

        $withdrawal = $wallet->withdrawals()->sole();

        $this->assertSame(0, $wallet->fresh()->balance);

        // 7. Admin pays it out.
        $this->actingAs($admin)
            ->patch(route('admin.withdrawals.paid', $withdrawal))
            ->assertSessionHas('success');

        $this->assertSame(Withdrawal::STATUS_PAID, $withdrawal->fresh()->status);
        $this->assertSame(0, $wallet->fresh()->balance);
        $this->assertSame(2, $wallet->transactions()->count());
    }
}
