<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'pending_products' => Product::where('status', 'pending')->count(),
        ];

        $latestUsers = User::latest()->take(8)->get();
        $pendingProducts = Product::with('user')->where('status', 'pending')->latest()->take(5)->get();

        // Dummy warung rows until a Warung model exists.
        $warungs = collect([
            ['name' => 'Warung Bang Jago', 'owner' => 'Bang Jago', 'cat' => 'Makanan', 'orders' => 1240, 'status' => 'Aktif'],
            ['name' => 'Kopi Hebat Tebet', 'owner' => 'Rina', 'cat' => 'Minuman', 'orders' => 986, 'status' => 'Aktif'],
            ['name' => 'Sembako Bu RT', 'owner' => 'Bu RT', 'cat' => 'Sembako', 'orders' => 764, 'status' => 'Aktif'],
            ['name' => 'Jajan Pasar Yu Ning', 'owner' => 'Yu Ning', 'cat' => 'Jajanan', 'orders' => 542, 'status' => 'Review'],
            ['name' => 'Dapur Nusa Frozen', 'owner' => 'Nusa', 'cat' => 'Frozen', 'orders' => 318, 'status' => 'Aktif'],
        ]);

        return view('admin.dashboard', compact('stats', 'latestUsers', 'warungs', 'pendingProducts'));
    }

    public function users(Request $request): View
    {
        $query = User::latest();
        $role = $request->input('role');
        $search = trim((string) $request->input('search', ''));

        if (is_string($role) && in_array($role, ['pembeli', 'penjual', 'admin'], true)) {
            $query->where('role', $role);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function warungs(Request $request): View
    {
        $warungs = collect([
            ['name' => 'Warung Bang Jago', 'owner' => 'Bang Jago', 'cat' => 'Makanan', 'distance' => '200m', 'rating' => '4.9', 'status' => 'Aktif'],
            ['name' => 'Kopi Hebat Tebet', 'owner' => 'Rina', 'cat' => 'Minuman', 'distance' => '350m', 'rating' => '4.8', 'status' => 'Aktif'],
            ['name' => 'Sembako Bu RT', 'owner' => 'Bu RT', 'cat' => 'Sembako', 'distance' => '500m', 'rating' => '4.9', 'status' => 'Aktif'],
            ['name' => 'Jajan Pasar Yu Ning', 'owner' => 'Yu Ning', 'cat' => 'Jajanan', 'distance' => '650m', 'rating' => '4.7', 'status' => 'Review'],
            ['name' => 'Dapur Nusa Frozen', 'owner' => 'Nusa', 'cat' => 'Frozen', 'distance' => '800m', 'rating' => '4.8', 'status' => 'Aktif'],
            ['name' => 'Toko Harian Berkah', 'owner' => 'Pak Berkah', 'cat' => 'Harian', 'distance' => '900m', 'rating' => '4.6', 'status' => 'Nonaktif'],
        ]);
        $search = trim((string) $request->input('search', ''));

        if ($search !== '') {
            $search = strtolower($search);
            $warungs = $warungs->filter(function (array $warung) use ($search): bool {
                return str_contains(strtolower(implode(' ', $warung)), $search);
            })->values();
        }

        return view('admin.warungs', compact('warungs'));
    }

    public function products(Request $request): View
    {
        $query = Product::with('user')->latest();
        $search = trim((string) $request->input('search', ''));

        if ($request->filled('status') && in_array($request->string('status'), Product::STATUSES, true)) {
            $query->where('status', $request->string('status'));
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $products = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => Product::count(),
            'pending' => Product::where('status', 'pending')->count(),
            'approved' => Product::where('status', 'approved')->count(),
            'rejected' => Product::where('status', 'rejected')->count(),
        ];

        return view('admin.products', compact('products', 'counts'));
    }

    public function approve(Product $product): RedirectResponse
    {
        $product->update(['status' => 'approved', 'rejection_reason' => null]);

        return back()->with('success', "Produk \"{$product->name}\" disetujui dan sudah tayang.");
    }

    public function reject(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi agar penjual bisa memperbaiki.',
        ]);

        $product->update(['status' => 'rejected', 'rejection_reason' => $validated['rejection_reason']]);

        return back()->with('success', "Produk \"{$product->name}\" ditolak dengan alasan.");
    }
}
