<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        if ($status === 'all') {
            $status = null;
        } elseif (! in_array($status, Payment::STATUSES, true)) {
            $status = Payment::STATUS_PENDING;
        }

        $search = trim((string) $request->input('search', ''));
        $orderId = Order::idFromCode($search);

        $payments = Payment::with(['order.user', 'order.store', 'paymentMethod', 'verifier'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search, $orderId): void {
                $query->where(function ($query) use ($search, $orderId): void {
                    $query->whereHas('paymentMethod', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('order', function ($query) use ($search): void {
                            $query->where('warung_name', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($query) use ($search): void {
                                    $query->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                });
                        });

                    if ($orderId) {
                        $query->orWhere('order_id', $orderId);
                    }
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => Payment::count(),
        ];

        foreach (Payment::STATUSES as $value) {
            $counts[$value] = Payment::where('status', $value)->count();
        }

        return view('admin.payments', [
            'payments' => $payments,
            'counts' => $counts,
            'status' => $status,
        ]);
    }

    /** Confirms the transfer arrived; the platform now holds the money in escrow. */
    public function verify(Payment $payment): RedirectResponse
    {
        if (! $payment->isPending()) {
            return back()->with('error', 'Pembayaran ini sudah diproses.');
        }

        DB::transaction(function () use ($payment): void {
            $payment->update([
                'status' => Payment::STATUS_VERIFIED,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            $payment->order->markPaid();
        });

        return back()->with(
            'success',
            "Pembayaran {$payment->order->code()} terverifikasi. Dana ditahan sampai pesanan selesai.",
        );
    }

    /** Sends the buyer back to re-upload: the order returns to pending payment. */
    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        if (! $payment->isPending()) {
            return back()->with('error', 'Pembayaran ini sudah diproses.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi agar pembeli bisa memperbaiki.',
        ]);

        DB::transaction(function () use ($payment, $validated): void {
            $payment->update([
                'status' => Payment::STATUS_REJECTED,
                'rejection_reason' => $validated['rejection_reason'],
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            $payment->order->update(['status' => Order::STATUS_PENDING_PAYMENT]);
        });

        return back()->with('success', 'Bukti pembayaran ditolak. Pembeli diminta mengunggah ulang.');
    }
}
