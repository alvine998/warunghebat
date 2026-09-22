<?php

namespace App\Http\Controllers;

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

        return view('dashboard', compact('stats', 'recentOrders'));
    }
}
