<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SellerBrandController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        $brands = Auth::user()->brands()
            ->withCount('products')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('seller.brands.index', [
            'brands' => $brands,
            'store' => Store::resolveFor(Auth::user()),
        ]);
    }

    public function create(): View
    {
        return view('seller.brands.form', [
            'brand' => new Brand,
            'store' => Store::resolveFor(Auth::user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $brand = Auth::user()->brands()->create([
            'name' => $validated['name'],
            'slug' => Brand::uniqueSlugForUser(Auth::id(), $validated['name']),
        ]);

        return redirect()
            ->route('seller.brands.index')
            ->with('success', "Brand \"{$brand->name}\" ditambahkan dan bisa dipakai di produk.");
    }

    public function edit(Brand $brand): View
    {
        $this->authorizeOwner($brand);

        return view('seller.brands.form', [
            'brand' => $brand,
            'store' => Store::resolveFor(Auth::user()),
        ]);
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $this->authorizeOwner($brand);

        $validated = $this->validated($request, $brand->id);

        $brand->update([
            'name' => $validated['name'],
            'slug' => Brand::uniqueSlugForUser(Auth::id(), $validated['name'], $brand->id),
        ]);

        return redirect()
            ->route('seller.brands.index')
            ->with('success', "Brand \"{$brand->name}\" diperbarui.");
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->authorizeOwner($brand);

        if ($brand->products()->exists()) {
            return back()->withErrors(['brand' => "Brand \"{$brand->name}\" masih dipakai {$brand->products()->count()} produk. Pindahkan produk ke brand lain dulu atau kosongkan brand-nya."]);
        }

        $brand->delete();

        return back()->with('success', "Brand \"{$brand->name}\" dihapus.");
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:80',
                Rule::unique('brands', 'name')->where('user_id', Auth::id())->ignore($ignoreId),
            ],
        ], [
            'name.required' => 'Nama brand wajib diisi.',
            'name.unique' => 'Brand ini sudah ada di daftar brand warungmu.',
        ]);
    }

    protected function authorizeOwner(Brand $brand): void
    {
        if ($brand->user_id !== Auth::id()) {
            abort(403, 'Bukan brand milikmu.');
        }
    }
}
