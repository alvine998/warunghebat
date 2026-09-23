<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        if (! in_array($status, Order::STATUSES, true)) {
            $status = null;
        }

        $orders = Order::with(['user', 'store', 'items'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => Order::count(),
        ];

        foreach (Order::STATUSES as $value) {
            $counts[$value] = Order::where('status', $value)->count();
        }

        return view('admin.orders', [
            'orders' => $orders,
            'counts' => $counts,
            'status' => $status,
        ]);
    }

    /** Escrow release: withholds the commission and credits the store wallet. */
    public function complete(Order $order): RedirectResponse
    {
        $transaction = $order->releaseFunds();

        if (! $transaction) {
            return back()->with('error', 'Pesanan ini belum dibayar atau dananya sudah dilepas.');
        }

        $amount = number_format($transaction->amount, 0, ',', '.');
        $store = $order->store->name;

        return back()->with('success', "Dana {$order->code()} dilepas: Rp {$amount} masuk ke saldo \"{$store}\".");
    }

    public function cancel(Order $order): RedirectResponse
    {
        if (! $order->canBeCancelled()) {
            return back()->with('error', 'Pesanan yang sudah dibayar tidak bisa dibatalkan.');
        }

        $order->cancel();

        return back()->with('success', "Pesanan {$order->code()} dibatalkan dan stok dikembalikan ke warung.");
    }
}
