<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $payment = $this->pendingPayment();

        $this->patch(route('admin.payments.verify', $payment))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_is_redirected_to_the_dashboard(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $payment = $this->pendingPayment();

        $this->actingAs($buyer)
            ->patch(route('admin.payments.verify', $payment))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_admin_verifying_a_payment_marks_the_order_paid(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payment = $this->pendingPayment();

        $this->actingAs($admin)
            ->patch(route('admin.payments.verify', $payment))
            ->assertRedirect()
            ->assertSessionHas('success');

        $payment->refresh();

        $this->assertSame(Payment::STATUS_VERIFIED, $payment->status);
        $this->assertSame($admin->id, $payment->verified_by);
        $this->assertNotNull($payment->verified_at);

        // The platform holds the money, so the wallet stays empty until completion.
        $this->assertSame(Order::STATUS_PAID, $payment->order->fresh()->status);
        $this->assertSame(0, (int) $payment->order->store->wallet()->value('balance'));
    }

    public function test_admin_rejecting_a_payment_returns_the_order_to_pending_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payment = $this->pendingPayment();

        $this->actingAs($admin)
            ->patch(route('admin.payments.reject', $payment), [
                'rejection_reason' => 'Bukti transfer tidak terbaca.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $payment->refresh();

        $this->assertSame(Payment::STATUS_REJECTED, $payment->status);
        $this->assertSame('Bukti transfer tidak terbaca.', $payment->rejection_reason);
        $this->assertSame(Order::STATUS_PENDING_PAYMENT, $payment->order->fresh()->status);
    }

    public function test_rejecting_a_payment_requires_a_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payment = $this->pendingPayment();

        $this->actingAs($admin)
            ->patch(route('admin.payments.reject', $payment), ['rejection_reason' => ''])
            ->assertSessionHasErrors(['rejection_reason']);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_a_payment_cannot_be_verified_twice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payment = $this->pendingPayment();

        $this->actingAs($admin)->patch(route('admin.payments.verify', $payment));
        $verifiedAt = $payment->fresh()->verified_at;

        $this->actingAs($admin)
            ->patch(route('admin.payments.verify', $payment))
            ->assertSessionHas('error');

        $this->assertSame($verifiedAt->timestamp, $payment->fresh()->verified_at->timestamp);
    }

    public function test_admin_can_open_the_payment_queue(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payment = $this->pendingPayment();

        $this->actingAs($admin)
            ->get(route('admin.payments'))
            ->assertOk()
            ->assertSee('Pembayaran pembeli')
            ->assertSee($payment->order->code());

        $this->actingAs($admin)
            ->get(route('admin.payments', ['status' => 'rejected']))
            ->assertOk()
            ->assertSee('Ditolak');
    }

    private function pendingPayment(): Payment
    {
        $order = Order::factory()->create([
            'total' => 40000,
            'status' => Order::STATUS_WAITING_VERIFICATION,
        ]);

        return $order->payments()->create([
            'amount' => 40000,
            'status' => Payment::STATUS_PENDING,
            'proof_path' => 'payment-proofs/bukti.jpg',
        ]);
    }
}
