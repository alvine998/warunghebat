<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        return view('seller.products.index', [
            'products' => $products,
            'counts' => $counts,
            'store' => Store::resolveFor(Auth::user()),
        ]);
    }

    public function create(): View
    {
        return view('seller.products.form', [
            'product' => new Product,
            'store' => Store::resolveFor(Auth::user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->sanitizeNumeric($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'discount_price' => ['nullable', 'integer', 'min:0', 'lt:price'],
            'promo_starts_at' => ['nullable', 'date'],
            'promo_ends_at' => ['nullable', 'date', 'after_or_equal:promo_starts_at'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'category' => ['required', 'string', 'in:'.implode(',', Product::CATEGORIES)],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.min' => 'Harga tidak boleh negatif.',
            'discount_price.lt' => 'Harga promo harus lebih kecil dari harga normal.',
            'promo_ends_at.after_or_equal' => 'Akhir promo tidak boleh sebelum awal promo.',
            'stock.required' => 'Stok wajib diisi.',
            'category.in' => 'Kategori tidak valid.',
            'image.required' => 'Foto produk wajib diunggah (1 foto).',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $imagePath = $request->file('image')->store('products', 'public');

        Auth::user()->products()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'promo_starts_at' => $validated['promo_starts_at'] ?? null,
            'promo_ends_at' => $validated['promo_ends_at'] ?? null,
            'stock' => $validated['stock'],
            'category' => $validated['category'],
            'image_path' => $imagePath,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        return redirect()->route('seller.products.index')
            ->with('success', 'Produk ditambahkan dan menunggu verifikasi admin.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeOwner($product);

        return view('seller.products.form', [
            'product' => $product,
            'store' => Store::resolveFor(Auth::user()),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeOwner($product);

        $this->sanitizeNumeric($request);

        // 1 product = 1 image. New image optional only if product already has one.
        $imageRule = $product->image_path
            ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']
            : ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'discount_price' => ['nullable', 'integer', 'min:0', 'lt:price'],
            'promo_starts_at' => ['nullable', 'date'],
            'promo_ends_at' => ['nullable', 'date', 'after_or_equal:promo_starts_at'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'category' => ['required', 'string', 'in:'.implode(',', Product::CATEGORIES)],
            'image' => $imageRule,
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.min' => 'Harga tidak boleh negatif.',
            'discount_price.lt' => 'Harga promo harus lebih kecil dari harga normal.',
            'promo_ends_at.after_or_equal' => 'Akhir promo tidak boleh sebelum awal promo.',
            'stock.required' => 'Stok wajib diisi.',
            'category.in' => 'Kategori tidak valid.',
            'image.required' => 'Foto produk wajib diunggah (1 foto).',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $imagePath = $product->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Any edit sends the product back to pending for re-verification.
        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'promo_starts_at' => $validated['promo_starts_at'] ?? null,
            'promo_ends_at' => $validated['promo_ends_at'] ?? null,
            'stock' => $validated['stock'],
            'category' => $validated['category'],
            'image_path' => $imagePath,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        return redirect()->route('seller.products.index')
            ->with('success', 'Produk diperbarui dan menunggu verifikasi ulang admin.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeOwner($product);

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('seller.products.index')
            ->with('success', 'Produk dihapus.');
    }

    protected function sanitizeNumeric(Request $request): void
    {
        // Inputs are displayed with thousand separators ("15.000"); keep only digits.
        foreach (['price', 'discount_price', 'stock'] as $key) {
            if ($request->filled($key)) {
                $request->merge([$key => preg_replace('/\D/', '', (string) $request->input($key))]);
            }
        }
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
