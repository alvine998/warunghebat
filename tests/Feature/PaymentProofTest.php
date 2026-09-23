<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_uploading_proof(): void
    {
        $order = Order::factory()->create();

        $this->post(route('orders.proof', $order))->assertRedirect(route('login'));
    }

    public function test_buyer_uploading_proof_sends_the_order_to_verification(): void
    {
        Storage::fake('public');

        $buyer = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->for($buyer)->create(['total' => 30000]);
        $method = PaymentMethod::factory()->create(['is_active' => true]);

        $this->actingAs($buyer)
            ->post(route('orders.proof', $order), [
                'payment_method_id' => $method->id,
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $payment = $order->payments()->sole();

        $this->assertSame($method->id, $payment->payment_method_id);
        $this->assertSame(30000, $payment->amount);
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertNotNull($payment->proof_path);
        Storage::disk('public')->assertExists($payment->proof_path);

        $this->assertSame(Order::STATUS_WAITING_VERIFICATION, $order->fresh()->status);
    }

    public function test_buyer_cannot_upload_proof_for_another_buyers_order(): void
    {
        Storage::fake('public');

        $buyer = User::factory()->create(['role' => 'pembeli']);
        $otherBuyer = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->for($otherBuyer)->create();
        $method = PaymentMethod::factory()->create(['is_active' => true]);

        $this->actingAs($buyer)
            ->post(route('orders.proof', $order), [
                'payment_method_id' => $method->id,
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertForbidden();

        $this->assertSame(0, $order->payments()->count());
        $this->assertSame(Order::STATUS_PENDING_PAYMENT, $order->fresh()->status);
    }

    public function test_proof_must_be_an_image(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->for($buyer)->create();
        $method = PaymentMethod::factory()->create(['is_active' => true]);

        $this->actingAs($buyer)
            ->post(route('orders.proof', $order), [
                'payment_method_id' => $method->id,
                'proof' => UploadedFile::fake()->create('bukti.pdf', 20),
            ])
            ->assertSessionHasErrors(['proof']);

        $this->assertSame(0, $order->payments()->count());
    }

    public function test_an_inactive_payment_method_cannot_be_used(): void
    {
        Storage::fake('public');

        $buyer = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->for($buyer)->create();
        $method = PaymentMethod::factory()->inactive()->create();

        $this->actingAs($buyer)
            ->post(route('orders.proof', $order), [
                'payment_method_id' => $method->id,
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, $order->payments()->count());
    }

    public function test_proof_cannot_be_uploaded_for_an_order_that_is_already_paid(): void
    {
        Storage::fake('public');

        $buyer = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->for($buyer)->paid()->create();
        $method = PaymentMethod::factory()->create(['is_active' => true]);

        $this->actingAs($buyer)
            ->post(route('orders.proof', $order), [
                'payment_method_id' => $method->id,
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, $order->payments()->count());
        $this->assertSame(Order::STATUS_PAID, $order->fresh()->status);
    }

    public function test_buyer_order_page_shows_the_transfer_instructions(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $seller = User::factory()->create(['role' => 'penjual']);
        $store = Store::factory()->for($seller)->create(['name' => 'Warung Bang Jago']);
        $order = Order::factory()->for($buyer)->for($store)->create([
            'total' => 30000,
            'warung_name' => 'Warung Bang Jago',
        ]);

        PaymentMethod::factory()->create([
            'name' => 'BCA a/n Warung Hebat',
            'account_number' => '1234567890',
            'is_active' => true,
        ]);

        $this->actingAs($buyer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('BCA a/n Warung Hebat')
            ->assertSee('1234567890')
            ->assertSee('Rp 30.000')
            ->assertSee('Warung Bang Jago');
    }

    public function test_buyer_cannot_open_another_buyers_order(): void
    {
        $buyer = User::factory()->create(['role' => 'pembeli']);
        $order = Order::factory()->create();

        $this->actingAs($buyer)
            ->get(route('orders.show', $order))
            ->assertForbidden();
    }
}
