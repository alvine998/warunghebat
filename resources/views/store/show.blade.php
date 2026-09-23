@extends('layouts.app')

@section('title', $store->name.' — Warung Hebat')

@section('content')
<section class="pt-20 sm:pt-28 pb-10 sm:pb-14 max-w-7xl mx-auto px-4 sm:px-6">
    <a href="{{ route('home') }}#warung" class="inline-flex items-center gap-1.5 min-h-10 text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Semua warung</a>

    {{-- ===== STORE HEADER ===== --}}
    <div class="reveal mt-3 sm:mt-4 rounded-[28px] bg-white border border-ink-900/10 overflow-hidden">
        <div class="h-40 sm:h-48 relative flex items-center justify-center {{ $store->image_path ? '' : 'bg-gradient-to-br from-brand-100 via-cream-100 to-leaf-100' }}">
            @if($store->image_path)
                <img src="{{ $store->image_url }}" alt="Foto {{ $store->name }}" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-900/50 to-transparent"></div>
            @else
                <span class="text-5xl sm:text-6xl">🏪</span>
            @endif
            <span class="absolute top-3 left-3 inline-flex items-center gap-1 text-[11px] font-extrabold bg-white/95 backdrop-blur rounded-full px-3 py-1.5 max-w-[calc(100%-6.5rem)] truncate">🛍️ {{ $store->products->count() }} produk</span>
            <span class="absolute top-3 right-3 inline-flex items-center gap-1.5 text-[11px] font-extrabold {{ $store->is_open ? 'bg-leaf-500 text-white' : 'bg-amber-400 text-ink-900' }} rounded-full px-3 py-1.5">
                <span class="w-1.5 h-1.5 rounded-full {{ $store->is_open ? 'bg-white animate-pulse' : 'bg-ink-900' }}"></span>
                {{ $store->is_open ? 'Buka' : 'Tutup' }}
            </span>
        </div>

        <div class="p-4 sm:p-7">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4">
                <div class="min-w-0">
                    <h1 class="font-black tracking-tight text-[26px] sm:text-4xl leading-tight break-words">{{ $store->name }}</h1>
                    <p class="text-[13px] font-semibold text-ink-500 mt-1 break-words">{{ $store->user?->name ?? 'Penjual warung' }}</p>
                </div>
            </div>

            @if($store->description)
                <p class="text-[15px] font-medium text-ink-500 leading-relaxed mt-3">{{ $store->description }}</p>
            @endif

            <div class="mt-4 flex flex-wrap gap-2 text-[12px] font-bold">
                @if($store->address)
                    <span class="inline-flex items-start gap-1.5 bg-cream-100 text-ink-700 rounded-2xl sm:rounded-full px-3.5 py-2 w-full sm:w-auto sm:max-w-full">
                        <span class="shrink-0">📍</span>
                        <span class="break-words min-w-0">{{ $store->address }}</span>
                    </span>
                @endif
                @if($store->hours_label)
                    <span class="inline-flex items-center gap-1.5 bg-cream-100 text-ink-700 rounded-full px-3.5 py-2">🕐 {{ $store->hours_label }}</span>
                @endif
                @if($store->phone)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $store->phone) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 bg-leaf-100 text-leaf-700 rounded-full px-3.5 py-2 min-h-10 active:bg-leaf-500 active:text-white hover:bg-leaf-500 hover:text-white transition">✆ {{ $store->phone }}</a>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== MAP ===== --}}
    @if($store->latitude !== null && $store->longitude !== null)
    <div class="reveal mt-4 sm:mt-5 rounded-[28px] bg-white border border-ink-900/10 overflow-hidden">
        <div class="flex flex-col gap-3 px-4 sm:px-7 pt-4 sm:pt-5">
            <div class="sm:flex sm:items-center sm:justify-between sm:gap-3">
                <div>
                    <p class="inline-flex text-[11px] font-extrabold tracking-[0.18em] text-leaf-700 bg-leaf-100 border border-leaf-500/20 rounded-full px-3.5 py-1.5">📍 LOKASI WARUNG</p>
                    <h2 class="font-black tracking-tight text-lg sm:text-2xl mt-2">Ada di sekitar sini</h2>
                </div>
                <a id="store-detail-osm" href="https://www.openstreetmap.org/#map=16/{{ $store->latitude }}/{{ $store->longitude }}" target="_blank" rel="noopener" class="hidden sm:inline-flex justify-center shrink-0 text-[13px] font-extrabold px-4 py-2.5 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">Buka di OSM →</a>
            </div>
            <a id="store-detail-osm-mobile" href="https://www.openstreetmap.org/#map=16/{{ $store->latitude }}/{{ $store->longitude }}" target="_blank" rel="noopener" class="sm:hidden text-center text-[13px] font-extrabold px-4 py-3 rounded-full border border-ink-900/15 active:bg-ink-900 active:text-white transition">Buka di OSM →</a>
        </div>
        <div
            id="store-detail-map"
            class="mt-3 sm:mt-4 h-56 sm:h-80 border-t border-ink-900/5"
            data-lat="{{ $store->latitude }}"
            data-lng="{{ $store->longitude }}"
            data-name="{{ $store->name }}"
            data-address="{{ $store->address }}"
        ></div>
    </div>
    @endif

    {{-- ===== PRODUCTS ===== --}}
    <div class="mt-7 sm:mt-9">
        <div class="mb-4 sm:mb-5">
            <p class="reveal inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-600 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">🛍️ ETALASE</p>
            <h2 class="reveal font-black tracking-tight text-xl sm:text-3xl mt-2" style="--reveal-delay:80ms">Produk warung ini</h2>
        </div>

        @if($store->products->isEmpty())
            <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-7 sm:p-10 text-center">
                <p class="text-4xl">📦</p>
                <p class="font-extrabold text-base sm:text-lg mt-2">Belum ada produk</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Produk yang sudah diverifikasi admin akan muncul di sini.</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-3">
                @foreach($store->products as $i => $p)
                <article class="reveal group rounded-[20px] sm:rounded-[24px] bg-white border border-ink-900/10 overflow-hidden hover:shadow-xl hover:shadow-ink-900/10 transition-all duration-300" style="--reveal-delay:{{ ($i%4)*70 }}ms">
                    @if($p->image_path)
                        <img src="{{ $p->image_url }}" alt="Foto {{ $p->name }}" loading="lazy" class="w-full h-28 sm:h-36 object-cover">
                    @else
                        <div class="w-full h-28 sm:h-36 bg-cream-100 border-b border-dashed border-ink-900/15 grid place-items-center text-3xl">📷</div>
                    @endif
                    <div class="p-2.5 sm:p-4">
                        <p class="font-extrabold text-[13px] sm:text-[15px] leading-snug break-words line-clamp-2 min-h-[2.1em] sm:min-h-0">{{ $p->name }}</p>
                        <p class="mt-1 text-[11px] font-extrabold bg-cream-100 text-ink-700 rounded-full px-2 py-0.5 w-fit max-w-full truncate">{{ $p->category }}</p>
                        @if($p->description)
                            <p class="hidden sm:block text-[13px] font-medium text-ink-500 leading-snug mt-1 line-clamp-2">{{ $p->description }}</p>
                        @endif
                        <div class="mt-2 sm:mt-3 flex flex-wrap items-center gap-x-2 gap-y-1.5">
                            <p class="font-black text-[13px] sm:text-[16px] text-brand-600 leading-tight">Rp {{ number_format($p->price, 0, ',', '.') }}</p>
                            @if($p->stock > 0)
                                <span class="text-[10px] sm:text-[11px] font-extrabold text-leaf-700 bg-leaf-100 rounded-full px-2 py-0.5 whitespace-nowrap">Stok {{ $p->stock }}</span>
                            @else
                                <span class="text-[10px] sm:text-[11px] font-extrabold text-amber-800 bg-amber-100 rounded-full px-2 py-0.5 whitespace-nowrap">Habis</span>
                            @endif
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        @endif
    </div>

    @guest
    <div class="reveal mt-8 sm:mt-10 rounded-[28px] bg-ink-900 text-white p-5 sm:p-8 grain relative overflow-hidden">
        <div class="hidden sm:block absolute -top-16 -right-16 w-56 h-56 bg-brand-500/30 blur-[80px] rounded-full pointer-events-none" aria-hidden="true"></div>
        <p class="relative text-[15px] font-bold leading-snug">Mau pesan dari <strong class="text-brand-400">{{ $store->name }}</strong>? Daftar gratis 30 detik, langsung jajan.</p>
        <div class="relative mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
            <a href="{{ route('register') }}" data-open-register class="text-center bg-brand-500 hover:bg-brand-600 font-extrabold text-sm px-6 py-3.5 rounded-full transition min-h-11">Daftar Gratis →</a>
            <a href="{{ route('login') }}" data-open-login class="text-center bg-white/10 border border-white/15 hover:bg-white/20 font-extrabold text-sm px-6 py-3.5 rounded-full transition min-h-11">Masuk</a>
        </div>
    </div>
    @endguest
</section>
@endsection

@if($store->latitude !== null && $store->longitude !== null)
@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    #store-detail-map img { max-width: none; }
    #store-detail-map { touch-action: pan-x pan-y pinch-zoom; }
    #store-detail-map .leaflet-control-attribution { font-size: 10px; max-width: 70vw; }
    #store-detail-map .leaflet-popup-content-wrapper { border-radius: 16px; }
    #store-detail-map .leaflet-popup-content { font-size: 13px; line-height: 1.4; word-break: break-word; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var el = document.getElementById('store-detail-map');
    if (!el || typeof L === 'undefined') return;

    var lat = parseFloat(el.dataset.lat);
    var lng = parseFloat(el.dataset.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

    var center = [lat, lng];
    var map = L.map(el, {
        scrollWheelZoom: false,
        tap: true,
    }).setView(center, 16);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    var name = el.dataset.name || 'Warung';
    var address = el.dataset.address || '';
    var popup = '<strong>' + name + '</strong>' + (address ? '<br>' + address : '');
    L.marker(center).addTo(map).bindPopup(popup).openPopup();

    function refresh() { map.invalidateSize(); }
    setTimeout(refresh, 200);
    window.addEventListener('orientationchange', function () { setTimeout(refresh, 300); });
    window.addEventListener('resize', function () { setTimeout(refresh, 200); });
})();
</script>
@endpush
@endif
