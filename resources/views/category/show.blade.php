@extends('layouts.app')

@section('title', $category.' — Warung Hebat')

@php($radiusLabel = rtrim(rtrim(number_format($radius ?? 5, 1), '0'), '.'))

@section('content')
<section class="pt-24 sm:pt-28 pb-14 sm:pb-20 max-w-7xl mx-auto px-4 sm:px-6">
    <a href="{{ route('home') }}#kategori" class="inline-flex items-center gap-1.5 min-h-10 text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Semua kategori</a>

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mt-3 mb-7">
        <div>
            <p class="inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-600 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">🛍️ KATEGORI</p>
            <h1 class="font-black tracking-tight text-3xl sm:text-5xl mt-3">{{ $category }}</h1>
            <p class="text-ink-500 font-medium text-[15px] mt-2">
                @if($userLat !== null && $userLng !== null)
                    {{ $products->total() }} produk dari {{ $storeCount }} warung • radius {{ $radiusLabel }} km, diurut dari yang paling dekat.
                @else
                    {{ $products->total() }} produk dari {{ $storeCount }} warung mitra. Nyalakan lokasi biar diurut dari jarakmu.
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-[13px] font-bold">
            <span class="inline-flex items-center gap-1.5 bg-ink-900 text-white rounded-full pl-3 pr-4 py-2">◎ Radius {{ $radiusLabel }} km</span>
            <button id="locate-btn" type="button" class="inline-flex items-center gap-1.5 bg-white border border-ink-900/15 rounded-full px-4 py-2 hover:border-ink-900 transition">📍 {{ $userLat !== null ? 'Perbarui lokasiku' : 'Gunakan lokasiku' }}</button>
        </div>
    </div>
    <p id="locate-status" class="hidden mb-4 text-[13px] font-bold text-ink-500" role="status"></p>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-3">
        @forelse($products as $i => $p)
        @php($store = $p->user->store)
        @php($dist = $p->distance_km)
        <article class="reveal group flex flex-col rounded-[20px] sm:rounded-[24px] bg-white border border-ink-900/10 overflow-hidden hover:shadow-xl hover:shadow-ink-900/10 hover:-translate-y-1 transition-all duration-300" style="--reveal-delay:{{ ($i % 4) * 70 }}ms">
            <a href="{{ route('store.show', $store) }}" class="block">
                @if($p->image_path)
                    <img src="{{ $p->image_url }}" alt="Foto {{ $p->name }}" loading="lazy" class="w-full h-28 sm:h-36 object-cover">
                @else
                    <div class="w-full h-28 sm:h-36 bg-cream-100 border-b border-dashed border-ink-900/15 grid place-items-center text-3xl">📷</div>
                @endif
            </a>
            <div class="flex flex-1 flex-col p-2.5 sm:p-4">
                <a href="{{ route('store.show', $store) }}" class="block">
                    <p class="font-extrabold text-[13px] sm:text-[15px] leading-snug break-words line-clamp-2 min-h-[2.1em] sm:min-h-0">{{ $p->name }}</p>
                    <p class="mt-1 flex items-center gap-1.5">
                        <span class="min-w-0 truncate text-[11px] font-extrabold bg-cream-100 text-ink-700 rounded-full px-2 py-0.5">🏪 {{ $store->name }}</span>
                        @unless($store->is_open)
                            <span class="shrink-0 text-[11px] font-extrabold bg-amber-100 text-amber-800 rounded-full px-2 py-0.5">Tutup</span>
                        @endunless
                    </p>
                    <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1.5">
                        <p class="font-black text-[13px] sm:text-[16px] text-brand-600 leading-tight">Rp {{ number_format($p->price, 0, ',', '.') }}</p>
                        @if($p->stock > 0)
                            <span class="text-[10px] sm:text-[11px] font-extrabold text-leaf-700 bg-leaf-100 rounded-full px-2 py-0.5 whitespace-nowrap">Stok {{ $p->stock }}</span>
                        @else
                            <span class="text-[10px] sm:text-[11px] font-extrabold text-amber-800 bg-amber-100 rounded-full px-2 py-0.5 whitespace-nowrap">Habis</span>
                        @endif
                    </div>
                    <p class="mt-2 sm:mt-3 text-[11px] sm:text-[12px] font-bold text-ink-500 truncate">📍 {{ $dist !== null ? ($dist < 1 ? round($dist * 1000).'m' : number_format($dist, 1).' km') : ($store->address ? \Str::limit($store->address, 20) : 'Lokasi menyusul') }}</p>
                </a>

                @if($store->is_open && $p->stock > 0)
                <div class="mt-auto pt-2.5">
                    @auth
                    <form method="POST" action="{{ route('cart.store') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $p->id }}">
                        <button type="submit" class="w-full min-h-11 py-2.5 px-2 rounded-full bg-ink-900 text-white text-[12px] sm:text-[13px] font-extrabold hover:bg-brand-500 active:scale-[.98] transition">+ Keranjang</button>
                    </form>
                    @else
                    <button type="button" data-open-login class="w-full min-h-11 py-2.5 px-2 rounded-full bg-ink-900 text-white text-[12px] sm:text-[13px] font-extrabold hover:bg-brand-500 active:scale-[.98] transition">+ Keranjang</button>
                    @endauth
                </div>
                @endif
            </div>
        </article>
        @empty
        <div class="col-span-full rounded-[24px] bg-white border border-dashed border-ink-900/15 p-10 text-center">
            <p class="text-4xl">🛍️</p>
            <p class="font-extrabold text-lg mt-2">Belum ada {{ $category }} di sekitar sini</p>
            <p class="text-sm font-medium text-ink-500 mt-1">Coba perbesar radius, matikan lokasi, atau lihat warung terdekat dulu.</p>
            <div class="mt-4 flex flex-wrap justify-center gap-2">
                <a href="{{ route('store.index', array_filter(['lat' => $userLat, 'lng' => $userLng, 'radius' => $radius], fn ($value) => $value !== null)) }}" class="text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">Lihat warung terdekat</a>
                <a href="{{ route('home') }}#kategori" class="text-[13px] font-extrabold border-2 border-ink-900/10 px-5 py-2.5 rounded-full">Kategori lain</a>
            </div>
        </div>
        @endforelse
    </div>

    @if($products->hasPages())
    <div class="mt-7 flex justify-center">{{ $products->links() }}</div>
    @endif
</section>

@include('components.locate-script', ['locateHash' => ''])
@endsection

@include('components.cart-conflict-modal')
