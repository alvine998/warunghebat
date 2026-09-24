<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        [$params, $nearby] = $this->nearbyStores($request);

        $flashSale = Product::query()
            ->withActivePromo()
            ->with(['user:id,name', 'user.store:id,user_id,name,slug,is_open'])
            ->whereHas('user.store', fn ($query) => $query->where('is_open', true))
            ->orderByRaw('(price - discount_price) * 100.0 / price DESC')
            ->take(8)
            ->get();

        // Countdown runs to the earliest promo deadline; promos without a
        // deadline share the end of today.
        $flashSaleEndsAt = $flashSale->map(fn (Product $product) => $product->promo_ends_at)
            ->filter()
            ->min() ?? now()->endOfDay();

        return view('landing', [
            'stores' => $nearby->take(6),
            'articles' => Article::query()->published()->with('author:id,name')->latest('published_at')->take(3)->get(),
            'flashSale' => $flashSale,
            'flashSaleEndsAt' => $flashSaleEndsAt->toIso8601String(),
            'nearbyParams' => array_filter(
                ['lat' => $params['lat'], 'lng' => $params['lng'], 'radius' => $params['radius']],
                fn ($value) => $value !== null
            ),
            'totalStores' => Store::count(),
            'openStores' => Store::where('is_open', true)->count(),
            'userLat' => $params['lat'],
            'userLng' => $params['lng'],
            'radius' => $params['radius'],
        ]);
    }

    public function nearby(Request $request): View
    {
        [$params, $nearby] = $this->nearbyStores($request);

        $perPage = 12;
        $currentPage = Paginator::resolveCurrentPage('page');
        $stores = new LengthAwarePaginator(
            $nearby->forPage($currentPage, $perPage)->values(),
            $nearby->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        return view('store.index', [
            'stores' => $stores->withQueryString(),
            'userLat' => $params['lat'],
            'userLng' => $params['lng'],
            'radius' => $params['radius'],
        ]);
    }

    /**
     * @return array{0: array{lat: float|null, lng: float|null, radius: float, search: string}, 1: Collection<int, Store>}
     */
    private function nearbyStores(Request $request): array
    {
        $validated = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.1', 'max:50'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $lat = isset($validated['lat']) ? (float) $validated['lat'] : null;
        $lng = isset($validated['lng']) ? (float) $validated['lng'] : null;
        $radius = isset($validated['radius']) ? (float) $validated['radius'] : 5.0;
        $search = isset($validated['q']) ? trim($validated['q']) : '';
        $params = ['lat' => $lat, 'lng' => $lng, 'radius' => $radius, 'search' => $search];

        $query = Store::query()
            ->with([
                'user:id,name',
                'products' => fn ($query) => $query->select('id', 'user_id', 'name', 'category')->where('status', 'approved')->latest('id')->take(3),
            ])
            ->withCount([
                'products as approved_products_count' => fn ($query) => $query->where('status', 'approved'),
                'ratings',
            ])
            ->withAvg('ratings', 'rating');

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            });
        }

        $stores = $query->latest()->take(60)->get();

        // ponytail: push haversine into SQL when stores > ~10k rows.
        $stores->each(function (Store $store) use ($lat, $lng): void {
            $store->distance_km = ($lat !== null && $lng !== null && $store->latitude !== null && $store->longitude !== null)
                ? self::haversineKm($lat, $lng, (float) $store->latitude, (float) $store->longitude)
                : null;
        });

        /** @var Collection<int, Store> $sorted */
        $sorted = $lat !== null && $lng !== null
            ? $stores->sortBy([
                fn (Store $a, Store $b) => ($a->distance_km ?? PHP_FLOAT_MAX) <=> ($b->distance_km ?? PHP_FLOAT_MAX),
                fn (Store $a, Store $b) => ($b->is_open <=> $a->is_open) ?: $b->approved_products_count <=> $a->approved_products_count,
            ])->values()
            : $stores->sortBy([
                fn (Store $a, Store $b) => $b->is_open <=> $a->is_open,
                fn (Store $a, Store $b) => $b->approved_products_count <=> $a->approved_products_count,
                fn (Store $a, Store $b) => $b->id <=> $a->id,
            ])->values();

        $nearby = $lat !== null && $lng !== null
            ? $sorted->filter(fn (Store $store) => $store->distance_km === null || $store->distance_km <= $radius)
            : $sorted;

        return [$params, $nearby];
    }

    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earthKm * asin(min(1.0, sqrt($a)));
    }
}
