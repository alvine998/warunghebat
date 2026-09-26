<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * One category's products, gathered from every store around the buyer.
     */
    public function show(Request $request, string $category): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:100'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.1', 'max:50'],
        ]);

        $categoryModel = Category::where('slug', $category)->first()
            ?? Category::whereRaw('LOWER(slug) = ?', [mb_strtolower($category)])->first();

        abort_if($categoryModel === null || ! $categoryModel->is_active, 404);

        $name = $categoryModel->name;

        $lat = isset($validated['lat']) ? (float) $validated['lat'] : null;
        $lng = isset($validated['lng']) ? (float) $validated['lng'] : null;
        $radius = isset($validated['radius']) ? (float) $validated['radius'] : 5.0;
        $hasCoords = $lat !== null && $lng !== null;

        $q = isset($validated['q']) ? trim($validated['q']) : '';
        $brandFilter = isset($validated['brand']) ? trim($validated['brand']) : '';

        $products = Product::query()
            ->with(['user:id,name', 'user.store:id,user_id,name,slug,is_open,latitude,longitude', 'brand:id,name,slug'])
            ->where('status', 'approved')
            ->where(function ($query) use ($categoryModel, $name): void {
                $query->where('category_id', $categoryModel->id)
                    ->orWhere('category', $name);
            })
            ->when($q !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$q}%")
                ->orWhereHas('user.store', fn ($store) => $store->where('name', 'like', "%{$q}%"))))
            ->when($brandFilter !== '', fn ($query) => $query->whereHas('brand', fn ($brand) => $brand
                ->where('slug', $brandFilter)->orWhere('name', $brandFilter)))
            ->whereHas('user.store')
            ->latest('id')
            ->take(150)
            ->get();

        // Brand options for this category: distinct brands actually used here.
        $brands = $products->map(fn (Product $product) => $product->brand)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $activeBrand = null;

        if ($brandFilter !== '') {
            $activeBrand = $brands->first(fn ($brand) => $brand->slug === $brandFilter || $brand->name === $brandFilter)
                ?? Brand::where('slug', $brandFilter)->orWhere('name', $brandFilter)->first();
        }

        $products->each(function (Product $product) use ($lat, $lng, $hasCoords): void {
            $store = $product->user->store;
            $product->distance_km = ($hasCoords && $store->latitude !== null && $store->longitude !== null)
                ? HomeController::haversineKm($lat, $lng, (float) $store->latitude, (float) $store->longitude)
                : null;
        });

        // Same ranking as the store list: nearest first, open stores break ties.
        $primary = $hasCoords
            ? fn (Product $a, Product $b) => ($a->distance_km ?? PHP_FLOAT_MAX) <=> ($b->distance_km ?? PHP_FLOAT_MAX)
            : fn (Product $a, Product $b) => $b->id <=> $a->id;

        $sorted = $products->sortBy([
            $primary,
            fn (Product $a, Product $b) => ($b->user->store->is_open <=> $a->user->store->is_open) ?: $b->id <=> $a->id,
        ])->values();

        $nearby = $hasCoords
            ? $sorted->filter(fn (Product $product) => $product->distance_km === null || $product->distance_km <= $radius)
            : $sorted;

        $perPage = 12;
        $currentPage = Paginator::resolveCurrentPage('page');
        $products = new LengthAwarePaginator(
            $nearby->forPage($currentPage, $perPage)->values(),
            $nearby->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        return view('category.show', [
            'category' => $categoryModel,
            'categoryName' => $name,
            'q' => $q,
            'brandFilter' => $brandFilter,
            'activeBrand' => $activeBrand,
            'brands' => $brands,
            'products' => $products->withQueryString(),
            'storeCount' => $nearby->map(fn (Product $product) => $product->user->store->id)->unique()->count(),
            'userLat' => $lat,
            'userLng' => $lng,
            'radius' => $radius,
            'seoTitle' => $name.($activeBrand ? ' • '.$activeBrand->name : '').' dari Warung Terdekat — Warung Hebat',
            'seoDescription' => 'Belanja '.$name.($activeBrand ? ' brand '.$activeBrand->name : '').' dari warung di sekitarmu: lihat harga, stok, dan jarak tiap warung. Pesan dari HP, diantar atau ambil sendiri.',
        ]);
    }
}
