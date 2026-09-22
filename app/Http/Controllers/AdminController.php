<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'users' => User::count(),
            'pembeli' => User::where('role', 'pembeli')->count(),
            'penjual' => User::where('role', 'penjual')->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        $latestUsers = User::latest()->take(8)->get();

        // Dummy warung rows until a Warung model exists.
        $warungs = collect([
            ['name' => 'Warung Bang Jago', 'owner' => 'Bang Jago', 'cat' => 'Makanan', 'orders' => 1240, 'status' => 'Aktif'],
            ['name' => 'Kopi Hebat Tebet', 'owner' => 'Rina', 'cat' => 'Minuman', 'orders' => 986, 'status' => 'Aktif'],
            ['name' => 'Sembako Bu RT', 'owner' => 'Bu RT', 'cat' => 'Sembako', 'orders' => 764, 'status' => 'Aktif'],
            ['name' => 'Jajan Pasar Yu Ning', 'owner' => 'Yu Ning', 'cat' => 'Jajanan', 'orders' => 542, 'status' => 'Review'],
            ['name' => 'Dapur Nusa Frozen', 'owner' => 'Nusa', 'cat' => 'Frozen', 'orders' => 318, 'status' => 'Aktif'],
        ]);

        return view('admin.dashboard', compact('stats', 'latestUsers', 'warungs'));
    }

    public function users(): View
    {
        $users = User::latest()->paginate(15);

        return view('admin.users', compact('users'));
    }

    public function warungs(): View
    {
        $warungs = collect([
            ['name' => 'Warung Bang Jago', 'owner' => 'Bang Jago', 'cat' => 'Makanan', 'distance' => '200m', 'rating' => '4.9', 'status' => 'Aktif'],
            ['name' => 'Kopi Hebat Tebet', 'owner' => 'Rina', 'cat' => 'Minuman', 'distance' => '350m', 'rating' => '4.8', 'status' => 'Aktif'],
            ['name' => 'Sembako Bu RT', 'owner' => 'Bu RT', 'cat' => 'Sembako', 'distance' => '500m', 'rating' => '4.9', 'status' => 'Aktif'],
            ['name' => 'Jajan Pasar Yu Ning', 'owner' => 'Yu Ning', 'cat' => 'Jajanan', 'distance' => '650m', 'rating' => '4.7', 'status' => 'Review'],
            ['name' => 'Dapur Nusa Frozen', 'owner' => 'Nusa', 'cat' => 'Frozen', 'distance' => '800m', 'rating' => '4.8', 'status' => 'Aktif'],
            ['name' => 'Toko Harian Berkah', 'owner' => 'Pak Berkah', 'cat' => 'Harian', 'distance' => '900m', 'rating' => '4.6', 'status' => 'Nonaktif'],
        ]);

        return view('admin.warungs', compact('warungs'));
    }
}
