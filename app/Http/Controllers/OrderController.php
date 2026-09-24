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

        $order->load(['items.product', 'store', 'latestPayment.paymentMethod', 'rating']);

        return view('orders.show', [
            'order' => $order,
            'latestPayment' => $order->latestPayment,
            'paymentMethods' => PaymentMethod::active()->get(),
        ]);
    }

    /** One star rating per completed order (1 transaction = 1 rating). */
    public function rate(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeBuyer($order);

        if ($order->rating()->exists()) {
            return back()->with('error', 'Pesanan ini sudah kamu beri penilaian.');
        }

        if (! $order->isCompleted()) {
            return back()->with('error', 'Penilaian hanya bisa diberikan setelah pesanan selesai.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ], [
            'rating.required' => 'Pilih jumlah bintang dulu.',
            'rating.min' => 'Penilaian minimal 1 bintang.',
            'rating.max' => 'Penilaian maksimal 5 bintang.',
            'comment.max' => 'Komentar maksimal 500 karakter.',
        ]);

        $order->rating()->create([
            'user_id' => $order->user_id,
            'store_id' => $order->store_id,
            'rating' => (int) $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Terima kasih! Penilaianmu terkirim.');
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
