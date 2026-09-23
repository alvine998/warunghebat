<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Store;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $stats = [
            'points' => (int) ($user->points ?? 0),
            'orders' => $user->orders()->count(),
            'favorites' => $user->favorites()->count(),
            'total_spent' => (int) $user->orders()->sum('total'),
            // Orders that still need something from the buyer or the platform.
            'pending' => $user->orders()
                ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
                ->count(),
        ];

        $recentOrders = $user->orders()->with('store')->latest()->take(5)->get();

        $storeStats = $user->canSell() ? $user->storeStats() : null;
        $lowStockProducts = $user->canSell() ? $user->lowStockProducts() : collect();
        $store = $user->canSell() ? Store::resolveFor($user) : null;
        $finance = $user->canSell() ? $user->financialOverview() : null;

        $wallet = null;

        if ($store) {
            $wallet = [
                'balance' => Wallet::forStore($store)->balance,
                // Paid orders whose money the platform still holds.
                'held' => (int) $store->orders()->where('status', Order::STATUS_PAID)->sum('total'),
            ];
        }

        return view('dashboard', compact('stats', 'recentOrders', 'storeStats', 'lowStockProducts', 'store', 'finance', 'wallet'));
    }
}
