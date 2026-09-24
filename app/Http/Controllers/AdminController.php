<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SellerVerification;
use App\Models\Store;
use App\Models\User;
use App\Models\Withdrawal;
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
            'pending_payments' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            'pending_withdrawals' => Withdrawal::where('status', Withdrawal::STATUS_PENDING)->count(),
            'pending_kyc' => SellerVerification::where('status', SellerVerification::STATUS_PENDING)->count(),
            'escrow' => (int) Order::where('status', Order::STATUS_PAID)->sum('total'),
        ];

        $latestUsers = User::latest()->take(8)->get();
        $pendingProducts = Product::with('user')->where('status', 'pending')->latest()->take(5)->get();
        $latestStores = Store::with('user.sellerVerification')->withCount('products')->latest('id')->take(5)->get();
        $pendingKyc = SellerVerification::with('user.store')->where('status', SellerVerification::STATUS_PENDING)->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'latestUsers', 'pendingProducts', 'latestStores', 'pendingKyc'));
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
        $query = Store::with(['user.sellerVerification', 'user' => fn ($query) => $query->withCount('products')])->latest();
        $search = trim((string) $request->input('search', ''));
        $kyc = $request->string('kyc')->toString();

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (in_array($kyc, SellerVerification::STATUSES, true)) {
            $query->whereHas('user.sellerVerification', fn ($query) => $query->where('status', $kyc));
        } elseif ($kyc === 'none') {
            $query->whereDoesntHave('user.sellerVerification');
        } elseif ($kyc !== '' && $kyc !== 'all') {
            $kyc = null;
        }

        $warungs = $query->paginate(12)->withQueryString();

        $counts = [
            'all' => Store::count(),
            'none' => Store::whereDoesntHave('user.sellerVerification')->count(),
        ];

        foreach (SellerVerification::STATUSES as $value) {
            $counts[$value] = Store::whereHas('user.sellerVerification', fn ($query) => $query->where('status', $value))->count();
        }

        return view('admin.warungs', compact('warungs', 'counts', 'kyc'));
    }

    public function suspendStore(Store $store): RedirectResponse
    {
        $store->update(['is_open' => false]);

        return back()->with('success', "Warung \"{$store->name}\" berhasil disuspend.");
    }

    public function activateStore(Store $store): RedirectResponse
    {
        $store->update(['is_open' => true]);

        return back()->with('success', "Warung \"{$store->name}\" berhasil diaktifkan kembali.");
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

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'rejection_reason' => ['required_if:action,reject', 'nullable', 'string', 'max:1000'],
        ], [
            'product_ids.required' => 'Pilih minimal satu produk.',
            'product_ids.min' => 'Pilih minimal satu produk.',
            'rejection_reason.required_if' => 'Alasan penolakan wajib diisi.',
        ]);

        $updates = $validated['action'] === 'approve'
            ? ['status' => 'approved', 'rejection_reason' => null]
            : ['status' => 'rejected', 'rejection_reason' => $validated['rejection_reason']];

        $count = Product::whereIn('id', $validated['product_ids'])->update($updates);
        $message = $validated['action'] === 'approve' ? 'disetujui' : 'ditolak';

        return back()->with('success', "{$count} produk berhasil {$message}.");
    }
}
