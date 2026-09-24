<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Unified search across warungs and products. Stores show the top
     * matches; products are paginated. Location params keep results
     * sorted and scoped the same way as the nearby lists.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.1', 'max:50'],
        ]);

        $q = isset($validated['q']) ? trim($validated['q']) : '';
        $lat = isset($validated['lat']) ? (float) $validated['lat'] : null;
        $lng = isset($validated['lng']) ? (float) $validated['lng'] : null;
        $radius = isset($validated['radius']) ? (float) $validated['radius'] : 5.0;
        $hasCoords = $lat !== null && $lng !== null;

        $stores = collect();
        $storeTotal = 0;
        $products = new LengthAwarePaginator([], 0, 12);

        if ($q !== '') {
            [$stores, $storeTotal] = $this->searchStores($q, $lat, $lng, $radius, $hasCoords);
            $products = $this->searchProducts($request, $q, $lat, $lng, $radius, $hasCoords);
        }

        return view('search.index', [
            'q' => $q,
            'stores' => $stores,
            'storeTotal' => $storeTotal,
            'products' => $products->withQueryString(),
            'userLat' => $lat,
            'userLng' => $lng,
            'radius' => $radius,
            'seoTitle' => $q !== '' ? 'Cari "'.$q.'" — Warung Hebat' : 'Cari Warung & Produk — Warung Hebat',
            'seoDescription' => 'Cari warung dan produk dari warung terdekatmu: ketik nama warung, makanan, minuman, atau kebutuhan harian.',
            'seoRobots' => 'noindex, nofollow',
        ]);
    }

    /**
     * @return array{0: Collection<int, Store>, 1: int}
     */
    private function searchStores(string $q, ?float $lat, ?float $lng, float $radius, bool $hasCoords): array
    {
        $candidates = Store::query()
            ->with([
                'user:id,name',
                'products' => fn ($query) => $query->select('id', 'user_id', 'name', 'category')->where('status', 'approved')->latest('id')->take(3),
            ])
            ->withCount([
                'products as approved_products_count' => fn ($query) => $query->where('status', 'approved'),
                'ratings',
            ])
            ->withAvg('ratings', 'rating')
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$q}%")
                ->orWhere('address', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$q}%")))
            ->latest('id')
            ->take(60)
            ->get();

        $candidates->each(function (Store $store) use ($lat, $lng): void {
            $store->distance_km = ($lat !== null && $lng !== null && $store->latitude !== null && $store->longitude !== null)
                ? HomeController::haversineKm($lat, $lng, (float) $store->latitude, (float) $store->longitude)
                : null;
        });

        $sorted = $hasCoords
            ? $candidates->sortBy([
                fn (Store $a, Store $b) => ($a->distance_km ?? PHP_FLOAT_MAX) <=> ($b->distance_km ?? PHP_FLOAT_MAX),
                fn (Store $a, Store $b) => ($b->is_open <=> $a->is_open) ?: $b->approved_products_count <=> $a->approved_products_count,
            ])->values()
            : $candidates->sortBy([
                fn (Store $a, Store $b) => $b->is_open <=> $a->is_open,
                fn (Store $a, Store $b) => $b->approved_products_count <=> $a->approved_products_count,
                fn (Store $a, Store $b) => $b->id <=> $a->id,
            ])->values();

        $nearby = $hasCoords
            ? $sorted->filter(fn (Store $store) => $store->distance_km === null || $store->distance_km <= $radius)
            : $sorted;

        return [$nearby->take(6)->values(), $nearby->count()];
    }

    private function searchProducts(Request $request, string $q, ?float $lat, ?float $lng, float $radius, bool $hasCoords): LengthAwarePaginator
    {
        $candidates = Product::query()
            ->with(['user:id,name', 'user.store:id,user_id,name,slug,is_open,address,latitude,longitude'])
            ->where('status', 'approved')
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->orWhere('category', 'like', "%{$q}%"))
            ->whereHas('user.store')
            ->latest('id')
            ->take(150)
            ->get();

        $candidates->each(function (Product $product) use ($lat, $lng, $hasCoords): void {
            $store = $product->user->store;
            $product->distance_km = ($hasCoords && $store->latitude !== null && $store->longitude !== null)
                ? HomeController::haversineKm($lat, $lng, (float) $store->latitude, (float) $store->longitude)
                : null;
        });

        $primary = $hasCoords
            ? fn (Product $a, Product $b) => ($a->distance_km ?? PHP_FLOAT_MAX) <=> ($b->distance_km ?? PHP_FLOAT_MAX)
            : fn (Product $a, Product $b) => $b->id <=> $a->id;

        $sorted = $candidates->sortBy([
            $primary,
            fn (Product $a, Product $b) => ($b->user->store->is_open <=> $a->user->store->is_open) ?: $b->id <=> $a->id,
        ])->values();

        $nearby = $hasCoords
            ? $sorted->filter(fn (Product $product) => $product->distance_km === null || $product->distance_km <= $radius)
            : $sorted;

        $perPage = 12;
        $currentPage = Paginator::resolveCurrentPage('page');

        return new LengthAwarePaginator(
            $nearby->forPage($currentPage, $perPage)->values(),
            $nearby->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );
    }
}
