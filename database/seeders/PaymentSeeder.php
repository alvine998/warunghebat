<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@warunghebat.id')->first();
        $bank = PaymentMethod::where('type', PaymentMethod::TYPE_BANK)->orderBy('sort_order')->first();
        $qris = PaymentMethod::where('type', PaymentMethod::TYPE_QRIS)->first();

        if (! $admin || ! $bank) {
            return;
        }

        $orders = Order::whereIn('status', [
            Order::STATUS_WAITING_VERIFICATION,
            Order::STATUS_PAID,
            Order::STATUS_COMPLETED,
        ])->with('payments')->get();

        foreach ($orders as $order) {
            if ($order->payments->isNotEmpty()) {
                continue;
            }

            $verified = in_array($order->status, [Order::STATUS_PAID, Order::STATUS_COMPLETED], true);
            $method = $order->status === Order::STATUS_PAID && $qris ? $qris : $bank;

            $payment = $order->payments()->create([
                'payment_method_id' => $method->id,
                'amount' => $order->total,
                'proof_path' => PlaceholderImage::put("payment-proofs/order-{$order->id}.png", '#0D7A3B', 520, 700),
                'status' => $verified ? Payment::STATUS_VERIFIED : Payment::STATUS_PENDING,
                'verified_by' => $verified ? $admin->id : null,
                'verified_at' => $verified ? $order->created_at->copy()->addMinutes(25) : null,
            ]);

            $payment->created_at = $order->created_at->copy()->addMinutes(10);
            $payment->save();
        }
    }
}
