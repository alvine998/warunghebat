<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Auth::user()->products()->latest();

        if ($request->filled('status') && in_array($request->string('status'), Product::STATUSES, true)) {
            $query->where('status', $request->string('status'));
        }

        $products = $query->paginate(12)->withQueryString();

        $counts = [
            'all' => Auth::user()->products()->count(),
            'pending' => Auth::user()->products()->where('status', 'pending')->count(),
            'approved' => Auth::user()->products()->where('status', 'approved')->count(),
            'rejected' => Auth::user()->products()->where('status', 'rejected')->count(),
        ];

        return view('seller.products.index', compact('products', 'counts'));
    }

    public function create(): View
    {
        return view('seller.products.form', ['product' => new Product()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'category' => ['required', 'string', 'in:' . implode(',', Product::CATEGORIES)],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.min' => 'Harga tidak boleh negatif.',
            'stock.required' => 'Stok wajib diisi.',
            'category.in' => 'Kategori tidak valid.',
        ]);

        Auth::user()->products()->create([
            ...$validated,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        return redirect()->route('seller.products.index')
            ->with('success', 'Produk ditambahkan dan menunggu verifikasi admin.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeOwner($product);

        return view('seller.products.form', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeOwner($product);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'category' => ['required', 'string', 'in:' . implode(',', Product::CATEGORIES)],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.min' => 'Harga tidak boleh negatif.',
            'stock.required' => 'Stok wajib diisi.',
            'category.in' => 'Kategori tidak valid.',
        ]);

        // Any edit sends the product back to pending for re-verification.
        $product->update([
            ...$validated,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        return redirect()->route('seller.products.index')
            ->with('success', 'Produk diperbarui dan menunggu verifikasi ulang admin.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeOwner($product);

        $product->delete();

        return redirect()->route('seller.products.index')
            ->with('success', 'Produk dihapus.');
    }

    protected function authorizeOwner(Product $product): void
    {
        // Admins pass the seller middleware but may only edit via backoffice.
        if ($product->user_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'Bukan produk milikmu.');
        }

        if ($product->user_id !== Auth::id() && Auth::user()->isAdmin()) {
            abort(403, 'Admin kelola produk lewat backoffice.');
        }
    }
}
