<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;

class PromoController extends Controller
{
    /**
     * Every live promo around the buyer, biggest discount first.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.1', 'max:50'],
        ]);

        $lat = isset($validated['lat']) ? (float) $validated['lat'] : null;
        $lng = isset($validated['lng']) ? (float) $validated['lng'] : null;
        $radius = isset($validated['radius']) ? (float) $validated['radius'] : 5.0;
        $hasCoords = $lat !== null && $lng !== null;

        $products = Product::query()
            ->withActivePromo()
            ->with(['user:id,name', 'user.store:id,user_id,name,slug,is_open,address,latitude,longitude'])
            ->whereHas('user.store')
            ->orderByRaw('(price - discount_price) * 100.0 / price DESC')
            ->take(150)
            ->get();

        $products->each(function (Product $product) use ($lat, $lng, $hasCoords): void {
            $store = $product->user->store;
            $product->distance_km = ($hasCoords && $store->latitude !== null && $store->longitude !== null)
                ? HomeController::haversineKm($lat, $lng, (float) $store->latitude, (float) $store->longitude)
                : null;
        });

        // Same ranking as categories: nearest first, open stores break ties.
        $primary = $hasCoords
            ? fn (Product $a, Product $b) => ($a->distance_km ?? PHP_FLOAT_MAX) <=> ($b->distance_km ?? PHP_FLOAT_MAX)
            : fn (Product $a, Product $b) => ($b->discountPercent() ?? 0) <=> ($a->discountPercent() ?? 0);

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

        return view('promo.index', [
            'products' => $products->withQueryString(),
            'storeCount' => $nearby->map(fn (Product $product) => $product->user->store->id)->unique()->count(),
            'userLat' => $lat,
            'userLng' => $lng,
            'radius' => $radius,
            'seoTitle' => 'Promo & Flash Sale Warung Terdekat — Warung Hebat',
            'seoDescription' => 'Serbu promo dan flash sale dari warung di sekitarmu: harga coret, diskon persen, dan stok terbatas. Pesan dari HP sebelum kehabisan.',
        ]);
    }
}
