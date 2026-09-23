<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()
            ->with(['store', 'items'])
            ->latest('id')
            ->paginate(10);

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        $this->authorizeBuyer($order);

        $order->load(['items.product', 'store', 'latestPayment.paymentMethod']);

        return view('orders.show', [
            'order' => $order,
            'latestPayment' => $order->latestPayment,
            'paymentMethods' => PaymentMethod::active()->get(),
        ]);
    }

    /** Uploads the transfer receipt that the admin will verify. */
    public function uploadProof(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeBuyer($order);

        if ($order->status !== Order::STATUS_PENDING_PAYMENT) {
            return back()->with('error', 'Pembayaran pesanan ini sudah diproses.');
        }

        $validated = $request->validate([
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'payment_method_id.required' => 'Pilih metode pembayaran dulu.',
            'payment_method_id.exists' => 'Metode pembayaran tidak ditemukan.',
            'proof.required' => 'Bukti transfer wajib diunggah.',
            'proof.image' => 'File harus berupa gambar.',
            'proof.mimes' => 'Format bukti harus JPG, PNG, atau WebP.',
            'proof.max' => 'Ukuran bukti maksimal 2MB.',
        ]);

        $method = PaymentMethod::active()->whereKey($validated['payment_method_id'])->first();

        if (! $method) {
            return back()->with('error', 'Metode pembayaran itu sudah tidak tersedia.');
        }

        $order->payments()->create([
            'payment_method_id' => $method->id,
            'amount' => $order->total,
            'proof_path' => $request->file('proof')->store('payment-proofs', 'public'),
            'status' => Payment::STATUS_PENDING,
        ]);

        $order->markAwaitingVerification();

        return back()->with('success', 'Bukti transfer terkirim. Admin akan memverifikasi pembayaranmu.');
    }

    protected function authorizeBuyer(Order $order): void
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Ini bukan pesananmu.');
        }
    }
}
