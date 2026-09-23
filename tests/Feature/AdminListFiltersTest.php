<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminListFiltersTest extends TestCase
{
    use RefreshDatabase;

    // ---------- PEMBAYARAN ----------

    public function test_payment_queue_can_be_searched_by_order_code_buyer_warung_and_method(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $method = PaymentMethod::factory()->create(['name' => 'BCA a/n Warung Hebat']);
        $buyer = User::factory()->create(['role' => 'pembeli', 'name' => 'Siti Aminah']);

        $order = Order::factory()->for($buyer)->create(['warung_name' => 'Warung Sate Padang']);
        $this->paymentFor($order, $method);

        $otherBuyer = User::factory()->create(['role' => 'pembeli', 'name' => 'Orang Lain']);
        $other = $this->paymentFor(Order::factory()->for($otherBuyer)->create(['warung_name' => 'Warung Lain']));

        foreach ([$order->code(), 'Siti Aminah', 'Sate Padang', 'BCA a/n'] as $term) {
            $this->actingAs($admin)
                ->get(route('admin.payments', ['status' => 'all', 'search' => $term]))
                ->assertOk()
                ->assertSee($order->code())
                ->assertDontSee($other->order->code());
        }
    }

    public function test_payment_queue_shows_an_empty_state_for_an_unmatched_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.payments', ['search' => 'tidak-ada-hasil']))
            ->assertOk()
            ->assertSee('Tidak ada pembayaran yang cocok');
    }

    public function test_payment_tab_all_lists_every_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $verified = $this->paymentFor(Order::factory()->create());
        $verified->update(['status' => Payment::STATUS_VERIFIED]);

        $rejected = $this->paymentFor(Order::factory()->create());
        $rejected->update(['status' => Payment::STATUS_REJECTED]);

        // The default tab is the pending queue, so processed proofs stay hidden.
        $this->actingAs($admin)
            ->get(route('admin.payments'))
            ->assertOk()
            ->assertDontSee($verified->order->code())
            ->assertDontSee($rejected->order->code());

        $this->actingAs($admin)
            ->get(route('admin.payments', ['status' => 'all']))
            ->assertOk()
            ->assertSee($verified->order->code())
            ->assertSee($rejected->order->code());
    }

    public function test_payment_queue_is_paginated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $orders = Order::factory()->count(16)->create();

        foreach ($orders as $order) {
            $this->paymentFor($order);
        }

        $oldest = $orders->first();

        $this->actingAs($admin)
            ->get(route('admin.payments', ['status' => 'all']))
            ->assertOk()
            ->assertSee('Menampilkan')
            ->assertDontSee($oldest->code());

        $this->actingAs($admin)
            ->get(route('admin.payments', ['status' => 'all', 'page' => 2]))
            ->assertOk()
            ->assertSee($oldest->code());
    }

    // ---------- PESANAN ----------

    public function test_orders_can_be_searched_by_code_buyer_warung_and_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['role' => 'pembeli', 'name' => 'Budi Santoso']);
        $store = Store::factory()->create(['name' => 'Warung Sate Padang']);

        // item_name is the card summary, so "Sate Ayam" is only found through the order items.
        $order = Order::factory()->for($buyer)->for($store)->create([
            'warung_name' => 'Warung Sate Padang',
            'item_name' => 'Es Teh +1 lainnya',
        ]);
        $order->items()->create([
            'name' => 'Sate Ayam',
            'price' => 10000,
            'qty' => 2,
            'subtotal' => 20000,
        ]);

        $other = Order::factory()->create(['warung_name' => 'Warung Lain']);

        foreach ([$order->code(), 'Budi Santoso', 'Sate Padang', 'Sate Ayam'] as $term) {
            $this->actingAs($admin)
                ->get(route('admin.orders', ['search' => $term]))
                ->assertOk()
                ->assertSee($order->code())
                ->assertDontSee($other->code());
        }
    }

    public function test_orders_are_paginated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $orders = Order::factory()->count(16)->create();
        $oldest = $orders->first();

        $this->actingAs($admin)
            ->get(route('admin.orders'))
            ->assertOk()
            ->assertSee('Menampilkan')
            ->assertDontSee($oldest->code());

        $this->actingAs($admin)
            ->get(route('admin.orders', ['page' => 2]))
            ->assertOk()
            ->assertSee($oldest->code());
    }

    public function test_order_pagination_links_keep_the_search_term(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Order::factory()->count(16)->create(['warung_name' => 'Warung Sate Padang']);

        $this->actingAs($admin)
            ->get(route('admin.orders', ['search' => 'Sate Padang']))
            ->assertOk()
            ->assertSee('search=Sate');
    }

    // ---------- PENARIKAN ----------

    public function test_withdrawal_queue_can_be_searched_by_store_bank_and_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $wallet = Wallet::factory()->for(Store::factory()->create(['name' => 'Warung Bang Jago']))->create();

        $withdrawal = Withdrawal::factory()->for($wallet)->create([
            'bank_name' => 'BCA',
            'account_number' => '1111111111',
            'account_name' => 'Budi Santoso',
        ]);

        Withdrawal::factory()->create([
            'bank_name' => 'Mandiri',
            'account_number' => '9999999999',
            'account_name' => 'Orang Lain',
        ]);

        foreach (['Warung Bang Jago', 'BCA', '1111111111', 'Budi Santoso'] as $term) {
            $this->actingAs($admin)
                ->get(route('admin.withdrawals', ['status' => 'all', 'search' => $term]))
                ->assertOk()
                ->assertSee('1111111111')
                ->assertDontSee('9999999999');
        }

        // The card headline (#12) resolves back to the request id.
        $this->actingAs($admin)
            ->get(route('admin.withdrawals', ['status' => 'all', 'search' => '#'.$withdrawal->id]))
            ->assertOk()
            ->assertSee('1111111111')
            ->assertDontSee('9999999999');
    }

    public function test_withdrawal_queue_is_paginated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $withdrawals = collect(range(1, 16))->map(fn (int $i) => Withdrawal::factory()->create([
            'account_number' => '88888888'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
        ]));

        $oldest = $withdrawals->first();

        $this->actingAs($admin)
            ->get(route('admin.withdrawals'))
            ->assertOk()
            ->assertSee('Menampilkan')
            ->assertDontSee($oldest->account_number);

        $this->actingAs($admin)
            ->get(route('admin.withdrawals', ['page' => 2]))
            ->assertOk()
            ->assertSee($oldest->account_number);
    }

    // ---------- METODE BAYAR ----------

    public function test_payment_methods_can_be_searched_by_name_account_and_type_label(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bank = PaymentMethod::factory()->create(['name' => 'BCA a/n Warung Hebat', 'account_number' => '1234567890']);
        $qris = PaymentMethod::factory()->qris()->create();
        $ewallet = PaymentMethod::factory()->create([
            'type' => PaymentMethod::TYPE_EWALLET,
            'name' => 'DANA a/n Warung Hebat',
            'account_number' => '5550001112',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.payment-methods', ['search' => '1234567890']))
            ->assertOk()
            ->assertSee($bank->name)
            ->assertDontSee($qris->name);

        $this->actingAs($admin)
            ->get(route('admin.payment-methods', ['search' => 'qris']))
            ->assertOk()
            ->assertSee($qris->name)
            ->assertDontSee($bank->name);

        // "Transfer Bank" and "E-Wallet" are labels, not stored values.
        $this->actingAs($admin)
            ->get(route('admin.payment-methods', ['search' => 'transfer']))
            ->assertOk()
            ->assertSee($bank->name)
            ->assertDontSee($ewallet->name);

        $this->actingAs($admin)
            ->get(route('admin.payment-methods', ['search' => 'e-wallet']))
            ->assertOk()
            ->assertSee($ewallet->name)
            ->assertDontSee($bank->name);
    }

    public function test_payment_methods_are_paginated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (range(1, 21) as $i) {
            PaymentMethod::factory()->create(['name' => "Metode {$i}", 'sort_order' => $i]);
        }

        $this->actingAs($admin)
            ->get(route('admin.payment-methods'))
            ->assertOk()
            ->assertSee('Menampilkan')
            ->assertSee('Metode 1')
            ->assertDontSee('Metode 21');

        $this->actingAs($admin)
            ->get(route('admin.payment-methods', ['page' => 2]))
            ->assertOk()
            ->assertSee('Metode 21');
    }

    private function paymentFor(Order $order, ?PaymentMethod $method = null): Payment
    {
        return $order->payments()->create([
            'payment_method_id' => $method?->id,
            'amount' => $order->total,
            'status' => Payment::STATUS_PENDING,
            'proof_path' => 'payment-proofs/bukti.jpg',
        ]);
    }
}
