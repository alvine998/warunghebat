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

class AdminFinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.finance'))->assertRedirect(route('login'));
    }

    public function test_non_admin_is_redirected_to_the_dashboard(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);

        $this->actingAs($buyer)
            ->get(route('admin.finance'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_sees_the_platform_money_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['name' => 'Warung Uji']);
        $wallet = Wallet::forStore($store);

        // A finished order: 60.000 collected, 5.000 kept as commission.
        $completed = Order::factory()->for($store)->completed()->create([
            'warung_name' => 'Warung Uji',
            'subtotal' => 60000,
            'total' => 60000,
            'commission_amount' => 5000,
        ]);
        $wallet->credit(55000, "Pesanan {$completed->code()}", $completed);

        // A credit that is not an order release must not count as money released.
        $wallet->credit(13000, 'Penyesuaian saldo');

        // Money the platform is holding, plus orders that must not count at all.
        $paid = Order::factory()->for($store)->paid()->create(['subtotal' => 20000, 'total' => 20000]);
        Order::factory()->for($store)->create(['subtotal' => 10000, 'total' => 10000]);
        Order::factory()->for($store)->create([
            'subtotal' => 7000,
            'total' => 7000,
            'status' => Order::STATUS_CANCELLED,
        ]);

        // Withdrawals hold the money on request, so the balance drops by both.
        $pending = Withdrawal::factory()->for($wallet)->create([
            'amount' => 31000,
            'status' => Withdrawal::STATUS_PENDING,
        ]);
        $wallet->debit($pending->amount, "Penarikan #{$pending->id}", $pending);

        $paidOut = Withdrawal::factory()->for($wallet)->create([
            'amount' => 19000,
            'status' => Withdrawal::STATUS_PAID,
        ]);
        $wallet->debit($paidOut->amount, "Penarikan #{$paidOut->id}", $paidOut);

        // 55.000 + 13.000 credited, 31.000 + 19.000 held back.
        $this->assertSame(18000, $wallet->fresh()->balance);

        $method = PaymentMethod::factory()->create(['name' => 'BCA a/n Warung Hebat']);
        $paid->payments()->create([
            'payment_method_id' => $method->id,
            'amount' => 20000,
            'status' => Payment::STATUS_VERIFIED,
            'verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.finance'));

        $response->assertOk()
            ->assertSee('Overview keuangan')
            // Position: escrow, seller balances, pending payouts, platform revenue.
            ->assertSee('Rp 20.000')
            ->assertSee('Rp 18.000')
            ->assertSee('Rp 31.000')
            ->assertSee('Rp 5.000')
            // Lifetime: order value excludes the cancelled order, released and paid out.
            ->assertSee('Rp 90.000')
            ->assertSee('Rp 55.000')
            ->assertSee('Rp 19.000')
            ->assertSee('Rp 7.000')
            // Store breakdown and per-method reconciliation.
            ->assertSee('Warung Uji')
            ->assertSee('BCA a/n Warung Hebat');

        // Escrow must only hold paid orders: 20.000, never 30.000 or 37.000.
        $response->assertDontSee('Rp 30.000')
            ->assertDontSee('Rp 37.000')
            ->assertDontSee('Rp 68.000');

        // ApexCharts mounts into these containers, and the donut payload must
        // match the cards: escrow, wallet balance, pending withdrawals.
        $response->assertSee('id="chart-inflow"', false)
            ->assertSee('id="chart-position"', false)
            ->assertSee('id="chart-stores"', false)
            ->assertSee('id="chart-statuses"', false)
            ->assertSee('window.financeCharts', false)
            ->assertSee('[20000,18000,31000]', false);
    }

    public function test_empty_platform_shows_zeroes_and_empty_states(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.finance'));

        $response->assertOk()
            ->assertSee('Overview keuangan')
            ->assertSee('Rp 0')
            ->assertSee('Belum ada warung')
            ->assertSee('Belum ada metode pembayaran')
            // Charts with nothing to plot fall back to empty states instead of
            // rendering an empty canvas; the 7-day trend always has days.
            ->assertSee('Belum ada uang dipegang')
            ->assertSee('Belum ada pesanan selesai')
            ->assertSee('id="chart-inflow"', false)
            ->assertDontSee('id="chart-position"', false)
            ->assertDontSee('id="chart-stores"', false)
            ->assertDontSee('id="chart-statuses"', false);
    }
}
