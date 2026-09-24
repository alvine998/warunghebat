<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
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
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.1', 'max:50'],
        ]);

        $name = collect(Product::CATEGORIES)
            ->first(fn (string $option) => Str::lower($option) === Str::lower($category));

        abort_if($name === null, 404);

        $lat = isset($validated['lat']) ? (float) $validated['lat'] : null;
        $lng = isset($validated['lng']) ? (float) $validated['lng'] : null;
        $radius = isset($validated['radius']) ? (float) $validated['radius'] : 5.0;
        $hasCoords = $lat !== null && $lng !== null;

        $q = isset($validated['q']) ? trim($validated['q']) : '';

        $products = Product::query()
            ->with(['user:id,name', 'user.store:id,user_id,name,slug,is_open,latitude,longitude'])
            ->where('status', 'approved')
            ->where('category', $name)
            ->when($q !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$q}%")
                ->orWhereHas('user.store', fn ($store) => $store->where('name', 'like', "%{$q}%"))))
            ->whereHas('user.store')
            ->latest('id')
            ->take(150)
            ->get();

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
            'category' => $name,
            'q' => $q,
            'products' => $products->withQueryString(),
            'storeCount' => $nearby->map(fn (Product $product) => $product->user->store->id)->unique()->count(),
            'userLat' => $lat,
            'userLng' => $lng,
            'radius' => $radius,
            'seoTitle' => $name.' dari Warung Terdekat — Warung Hebat',
            'seoDescription' => 'Belanja '.$name.' dari warung di sekitarmu: lihat harga, stok, dan jarak tiap warung. Pesan dari HP, diantar atau ambil sendiri.',
        ]);
    }
}
