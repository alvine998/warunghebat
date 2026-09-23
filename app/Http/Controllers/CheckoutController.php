<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Turns the session cart into an order, reserving its stock.
     *
     * When a product sold out meanwhile, Order::placeFromCart throws a
     * ValidationException and the buyer lands back on the cart with the reason.
     */
    public function store(Request $request): RedirectResponse
    {
        $order = Order::placeFromCart((array) session('cart'), $request->user());

        session()->forget('cart');

        return redirect()
            ->route('orders.show', $order)
            ->with('success', "Pesanan {$order->code()} dibuat. Selesaikan pembayarannya ya.");
    }
}
