@extends('layouts.app')

@section('title', $q !== '' ? 'Cari "'.$q.'" — Warung Hebat' : 'Cari Warung & Produk — Warung Hebat')

@php($radiusLabel = rtrim(rtrim(number_format($radius ?? 5, 1), '0'), '.'))

@section('content')
<section class="pt-24 sm:pt-28 pb-14 sm:pb-20 max-w-7xl mx-auto px-4 sm:px-6">
    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 min-h-10 text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Beranda</a>

    <div class="mt-3">
        <p class="inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-600 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">🔍 PENCARIAN</p>
        <h1 class="font-black tracking-tight text-3xl sm:text-5xl mt-3">
            @if($q !== '')
                Hasil untuk “{{ $q }}”
            @else
                Mau cari apa hari ini?
            @endif
        </h1>
        @if($q !== '' && ($storeTotal > 0 || $products->total() > 0))
            <p class="text-ink-500 font-medium text-[15px] mt-2">{{ $storeTotal }} warung • {{ $products->total() }} produk{{ $userLat !== null && $userLng !== null ? ' • radius '.$radiusLabel.' km' : '' }}.</p>
        @endif
    </div>

    {{-- Search box (keeps location context) --}}
    <form method="GET" action="{{ route('search.index') }}" role="search" class="mt-6 bg-white rounded-[22px] border border-ink-900/10 shadow-xl shadow-ink-900/10 p-2 flex items-center gap-2 max-w-xl">
        <span class="pl-3 text-ink-500"><x-icon name="search" class="w-5 h-5" /></span>
        <label class="sr-only" for="search-q">Cari warung atau produk</label>
        <input id="search-q" name="q" type="search" autocomplete="off" value="{{ $q }}" placeholder="Cari nasi goreng, kopi susu, telur..." class="flex-1 min-w-0 bg-transparent outline-none text-[15px] font-semibold placeholder:text-ink-500/60 placeholder:font-medium py-2.5">
        @if($userLat !== null)<input type="hidden" name="lat" value="{{ $userLat }}">@endif
        @if($userLng !== null)<input type="hidden" name="lng" value="{{ $userLng }}">@endif
        <input type="hidden" name="radius" value="{{ $radius ?? 5 }}">
        <button type="submit" class="shrink-0 bg-ink-900 text-white text-sm font-extrabold px-5 py-3 rounded-2xl hover:bg-brand-600 transition">Cari</button>
    </form>
    <div class="mt-3 flex flex-wrap items-center gap-2 text-[13px] font-bold">
        <span class="inline-flex items-center gap-1.5 bg-ink-900 text-white rounded-full pl-3 pr-4 py-2">◎ Radius {{ $radiusLabel }} km</span>
        <button id="locate-btn" type="button" class="inline-flex items-center gap-1.5 bg-white border border-ink-900/15 rounded-full px-4 py-2 hover:border-ink-900 transition">📍 {{ $userLat !== null ? 'Perbarui lokasiku' : 'Gunakan lokasiku' }}</button>
    </div>
    <p id="locate-status" class="hidden mt-3 text-[13px] font-bold text-ink-500" role="status"></p>

    @if($q === '')
        <div class="mt-8 rounded-[28px] bg-white border border-ink-900/10 p-6 sm:p-8">
            <p class="font-extrabold text-lg">Mulai dengan kata kunci 🔎</p>
            <p class="text-sm font-medium text-ink-500 mt-1">Nama warung (“Bang Jago”), makanan (“nasi goreng”), atau kategori (“sembako”).</p>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach(['Nasi goreng', 'Kopi susu', 'Telur', 'Gorengan', 'Sembako'] as $s)
                <a href="{{ route('search.index', array_filter(['q' => $s, 'lat' => $userLat, 'lng' => $userLng, 'radius' => $radius], fn ($value) => $value !== null)) }}" class="text-[13px] font-extrabold bg-cream-100 hover:bg-ink-900 hover:text-white rounded-full px-4 py-2.5 transition">{{ $s }}</a>
                @endforeach
            </div>
            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ route('store.index') }}" class="text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">Lihat semua warung →</a>
                <a href="{{ route('promo.index') }}" class="text-[13px] font-extrabold border-2 border-ink-900/10 px-5 py-2.5 rounded-full">⚡ Semua promo</a>
            </div>
        </div>
    @elseif($storeTotal === 0 && $products->total() === 0)
        <div class="mt-8 rounded-[28px] bg-white border border-dashed border-ink-900/15 p-10 text-center">
            <p class="text-4xl">🔍</p>
            <p class="font-extrabold text-lg mt-2">Tidak ketemu “{{ $q }}”</p>
            <p class="text-sm font-medium text-ink-500 mt-1">Coba kata yang lebih umum (“gorengan”, “kopi”), perbesar radius, atau matikan lokasi.</p>
            <div class="mt-4 flex flex-wrap justify-center gap-2">
                <a href="{{ route('search.index') }}" class="text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">Bersihkan pencarian</a>
                <a href="{{ route('store.index', array_filter(['lat' => $userLat, 'lng' => $userLng, 'radius' => $radius], fn ($value) => $value !== null)) }}" class="text-[13px] font-extrabold border-2 border-ink-900/10 px-5 py-2.5 rounded-full">Lihat semua warung</a>
            </div>
        </div>
    @else
        {{-- ===== STORES ===== --}}
        @if($storeTotal > 0)
        <div class="mt-10 flex items-end justify-between gap-4">
            <h2 class="font-black tracking-tight text-xl sm:text-2xl">🏪 Warung <span class="text-ink-500 text-base font-bold">({{ $storeTotal }})</span></h2>
            @if($storeTotal > $stores->count())
            <a href="{{ route('store.index', array_merge(['q' => $q], array_filter(['lat' => $userLat, 'lng' => $userLng, 'radius' => $radius], fn ($value) => $value !== null))) }}" class="shrink-0 font-extrabold text-sm underline underline-offset-8 decoration-brand-500 decoration-2 hover:text-brand-600">Semua warung →</a>
            @endif
        </div>
        <div class="mt-4 grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($stores as $i => $w)
            <x-store-card :store="$w" :index="$i" />
            @endforeach
        </div>
        @endif

        {{-- ===== PRODUCTS ===== --}}
        @if($products->total() > 0)
        <h2 class="mt-10 font-black tracking-tight text-xl sm:text-2xl">🛍️ Produk <span class="text-ink-500 text-base font-bold">({{ $products->total() }})</span></h2>
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-3">
            @foreach($products as $i => $p)
            <x-product-card :product="$p" :index="$i" />
            @endforeach
        </div>
        @if($products->hasPages())
        <div class="mt-7 flex justify-center">{{ $products->links() }}</div>
        @endif
        @endif
    @endif
</section>

@include('components.locate-script', ['locateHash' => ''])
@endsection

@include('components.cart-conflict-modal')
