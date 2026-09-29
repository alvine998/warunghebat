<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Auth::user()->products()->with(['categoryRef:id,name,slug', 'brand:id,user_id,name,slug'])->latest();

        if ($request->filled('status') && in_array($request->string('status'), Product::STATUSES, true)) {
            $query->where('status', $request->string('status'));
        }

        $products = $query->paginate(12)->withQueryString();

        // Single GROUP BY instead of 4 separate COUNT(*) queries.
        $grouped = Auth::user()->products()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $counts = [
            'all' => (int) $grouped->sum(),
            'pending' => (int) ($grouped['pending'] ?? 0),
            'approved' => (int) ($grouped['approved'] ?? 0),
            'rejected' => (int) ($grouped['rejected'] ?? 0),
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
            'categories' => $this->cachedCategories(),
            'brands' => Auth::user()->brands()->orderBy('name')->get(['id', 'user_id', 'name', 'slug']),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->sanitizeNumeric($request);
        $this->normalizeBarcode($request);
        $this->normalizeCategory($request);
        $this->normalizeProductOptions($request);

        $validated = $this->validated($request);

        $imagePath = $request->file('image')->store('products', 'public');

        $category = Category::find($validated['category_id']);
        $brandId = $this->resolveBrandId($request, $validated['brand_id'] ?? null);

        Auth::user()->products()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'cost_price' => $validated['cost_price'] ?? null,
            'discount_price' => $validated['discount_price'] ?? null,
            'promo_starts_at' => $validated['promo_starts_at'] ?? null,
            'promo_ends_at' => $validated['promo_ends_at'] ?? null,
            'stock' => $validated['stock'],
            'barcode' => $validated['barcode'] ?? null,
            'category' => $category?->name ?? 'Lainnya',
            'category_id' => $category?->id,
            'brand_id' => $brandId,
            'image_path' => $imagePath,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'redirect_url' => route('seller.products.index'),
                'message' => 'Produk ditambahkan dan menunggu verifikasi admin.',
            ]);
        }

        return redirect()->route('seller.products.index')
            ->with('success', 'Produk ditambahkan dan menunggu verifikasi admin.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeOwner($product);

        return view('seller.products.form', [
            'product' => $product->load(['categoryRef', 'brand']),
            'store' => Store::resolveFor(Auth::user()),
            'categories' => $this->cachedCategories(),
            'brands' => Auth::user()->brands()->orderBy('name')->get(['id', 'user_id', 'name', 'slug']),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $this->authorizeOwner($product);

        $this->sanitizeNumeric($request);
        $this->normalizeBarcode($request);
        $this->normalizeCategory($request);
        $this->normalizeProductOptions($request);

        // 1 product = 1 image. New image optional only if product already has one.
        // Client compresses photos to under 1MB; the server enforces the same cap.
        $imageRule = $product->image_path
            ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:950']
            : ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:950'];

        $validated = $this->validated($request, $imageRule);

        $imagePath = $product->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $category = Category::find($validated['category_id']);
        $brandId = $this->resolveBrandId($request, $validated['brand_id'] ?? null);

        // Any edit sends the product back to pending for re-verification.
        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'cost_price' => $validated['cost_price'] ?? null,
            'discount_price' => $validated['discount_price'] ?? null,
            'promo_starts_at' => $validated['promo_starts_at'] ?? null,
            'promo_ends_at' => $validated['promo_ends_at'] ?? null,
            'stock' => $validated['stock'],
            'barcode' => $validated['barcode'] ?? null,
            'category' => $category?->name ?? 'Lainnya',
            'category_id' => $category?->id,
            'brand_id' => $brandId,
            'image_path' => $imagePath,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'redirect_url' => route('seller.products.index'),
                'message' => 'Produk diperbarui dan menunggu verifikasi ulang admin.',
            ]);
        }

        return redirect()->route('seller.products.index')
            ->with('success', 'Produk diperbarui dan menunggu verifikasi ulang admin.');
    }

    /**
     * Categories change rarely (admin backoffice) but are loaded on every
     * product create/edit. Cache the ordered list for an hour to skip the
     * query on warm pages. Invalidated in AdminCategoryController.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Category>
     */
    protected function cachedCategories(): \Illuminate\Support\Collection
    {
        return Cache::remember('categories-ordered-select', 3600, fn () => Category::ordered()->get(['id', 'name', 'slug', 'sort_order']));
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

    /** @return array<string, mixed> */
    protected function validated(Request $request, ?array $imageRule = null): array
    {
        $imageRule ??= ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:950'];

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'cost_price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'discount_price' => ['nullable', 'integer', 'min:0', 'lt:price'],
            'promo_starts_at' => ['nullable', 'date'],
            'promo_ends_at' => ['nullable', 'date', 'after_or_equal:promo_starts_at'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'barcode' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9.\-_]+$/', Rule::unique('products', 'barcode')->where('user_id', Auth::id())->ignore($request->route('product')?->id)],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'category' => ['nullable', 'string', 'max:60'],
            'brand_id' => ['nullable', 'integer', Rule::exists('brands', 'id')->where('user_id', Auth::id())],
            'new_brand' => ['nullable', 'string', 'max:80'],
            'image' => $imageRule,
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'price.required' => 'Harga jual wajib diisi.',
            'price.min' => 'Harga jual tidak boleh negatif.',
            'cost_price.min' => 'HPP tidak boleh negatif.',
            'discount_price.lt' => 'Harga promo harus lebih kecil dari harga jual.',
            'promo_ends_at.after_or_equal' => 'Akhir promo tidak boleh sebelum awal promo.',
            'stock.required' => 'Stok wajib diisi.',
            'barcode.max' => 'Barcode maksimal 64 karakter.',
            'barcode.regex' => 'Barcode hanya boleh huruf, angka, titik, strip, dan underscore.',
            'barcode.unique' => 'Barcode sudah dipakai produk lain di warungmu.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'brand_id.exists' => 'Brand tidak valid.',
            'image.required' => 'Foto produk wajib diunggah (1 foto).',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto maksimal 950KB. Foto otomatis dikompres di HP — coba ambil ulang dengan resolusi lebih rendah.',
        ]);
    }

    /**
     * Brand baru yang diketik penjual langsung dibuatkan master-nya
     * (per warung) lalu dipakai produk ini.
     */
    protected function resolveBrandId(Request $request, mixed $brandId): ?int
    {
        $newBrand = trim((string) $request->input('new_brand', ''));

        if ($newBrand !== '') {
            $existing = Auth::user()->brands()->whereRaw('LOWER(name) = ?', [mb_strtolower($newBrand)])->first();

            if ($existing) {
                return $existing->id;
            }

            $created = Auth::user()->brands()->create([
                'name' => $newBrand,
                'slug' => Brand::uniqueSlugForUser(Auth::id(), $newBrand),
            ]);

            return $created->id;
        }

        return $brandId ? (int) $brandId : null;
    }

    protected function sanitizeNumeric(Request $request): void
    {
        // Inputs are displayed with thousand separators ("15.000"); keep only digits.
        foreach (['price', 'cost_price', 'discount_price', 'stock'] as $key) {
            if ($request->filled($key)) {
                $request->merge([$key => preg_replace('/\D/', '', (string) $request->input($key))]);
            }
        }
    }

    protected function normalizeBarcode(Request $request): void
    {
        // Empty barcode means "no barcode" — store null so the unique index stays happy.
        $barcode = trim((string) $request->input('barcode', ''));

        $request->merge(['barcode' => $barcode === '' ? null : $barcode]);
    }

    protected function normalizeProductOptions(Request $request): void
    {
        if ($request->input('has_promo') === 'no') {
            $request->merge([
                'discount_price' => null,
                'promo_starts_at' => null,
                'promo_ends_at' => null,
            ]);
        }

        if ($request->input('has_brand') === 'no') {
            $request->merge([
                'brand_id' => null,
                'new_brand' => null,
            ]);
        }
    }

    /**
     * Back-compat: old forms/tests send `category` (name). Resolve it to
     * `category_id` so the new validation keeps passing.
     */
    protected function normalizeCategory(Request $request): void
    {
        if ($request->filled('category_id')) {
            return;
        }

        $name = trim((string) $request->input('category', ''));

        if ($name === '') {
            return;
        }

        $id = Category::where('name', $name)->value('id')
            ?? Category::where('slug', Str::slug($name))->value('id');

        if ($id) {
            $request->merge(['category_id' => $id]);
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
