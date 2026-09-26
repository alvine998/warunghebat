<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        $categories = Category::query()
            ->withCount('products')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        return view('admin.categories', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.category-form', ['category' => new Category]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $category = Category::create([
            ...$validated,
            'slug' => Category::uniqueSlug($validated['name']),
        ]);

        return redirect()
            ->route('admin.categories')
            ->with('success', "Kategori \"{$category->name}\" ditambahkan.");
    }

    public function edit(Category $category): View
    {
        return view('admin.category-form', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $this->validated($request, $category->id);

        $category->update([
            ...$validated,
            'slug' => $category->slug ?: Category::uniqueSlug($validated['name'], $category->id),
        ]);

        // Keep the slug in sync when the name changes, unless it was customized.
        if ($category->wasChanged('name') && $category->slug === Category::uniqueSlug($category->getOriginal('name'), $category->id)) {
            $category->update(['slug' => Category::uniqueSlug($validated['name'], $category->id)]);
        }

        // Legacy product rows keep a denormalized name — sync it on rename.
        if ($category->wasChanged('name')) {
            $category->products()->where('category', $category->getOriginal('name'))->update(['category' => $category->name]);
        }

        return redirect()
            ->route('admin.categories')
            ->with('success', "Kategori \"{$category->name}\" diperbarui.");
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', $category->is_active
            ? "Kategori \"{$category->name}\" diaktifkan."
            : "Kategori \"{$category->name}\" dinonaktifkan dan disembunyikan dari pembeli.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => "Kategori \"{$category->name}\" masih dipakai {$category->products()->count()} produk dan tidak bisa dihapus. Nonaktifkan saja atau pindahkan produknya dulu."]);
        }

        $category->delete();

        return back()->with('success', "Kategori \"{$category->name}\" dihapus.");
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('categories', 'name')->ignore($ignoreId)],
            'icon' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah dipakai.',
        ]);

        return [
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? null,
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
