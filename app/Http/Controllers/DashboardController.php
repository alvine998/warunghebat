<?php

namespace App\Http\Controllers;

use App\Models\Store;
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
            'pending' => $user->orders()->where('status', '!=', 'Selesai')->count(),
        ];

        $recentOrders = $user->orders()->latest()->take(5)->get();

        $storeStats = $user->canSell() ? $user->storeStats() : null;
        $lowStockProducts = $user->canSell() ? $user->lowStockProducts() : collect();
        $store = $user->canSell() ? Store::resolveFor($user) : null;
        $finance = $user->canSell() ? $user->financialOverview() : null;

        return view('dashboard', compact('stats', 'recentOrders', 'storeStats', 'lowStockProducts', 'store', 'finance'));
    }
}
