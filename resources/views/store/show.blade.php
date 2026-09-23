@extends('layouts.app')

@section('title', $store->name.' — Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-14 max-w-7xl mx-auto px-4 sm:px-6">
    <a href="{{ route('home') }}#warung" class="inline-flex items-center gap-1.5 text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Semua warung</a>

    {{-- ===== STORE HEADER ===== --}}
    <div class="reveal mt-4 rounded-[28px] bg-white border border-ink-900/10 overflow-hidden">
        <div class="h-36 sm:h-48 relative flex items-center justify-center {{ $store->image_path ? '' : 'bg-gradient-to-br from-brand-100 via-cream-100 to-leaf-100' }}">
            @if($store->image_path)
                <img src="{{ $store->image_url }}" alt="Foto {{ $store->name }}" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-900/50 to-transparent"></div>
            @else
                <span class="text-6xl">🏪</span>
            @endif
            <span class="absolute top-4 right-4 inline-flex items-center gap-1.5 text-[12px] font-extrabold {{ $store->is_open ? 'bg-leaf-500 text-white' : 'bg-amber-400 text-ink-900' }} rounded-full px-3.5 py-1.5">
                <span class="w-1.5 h-1.5 rounded-full {{ $store->is_open ? 'bg-white animate-pulse' : 'bg-ink-900' }}"></span>
                {{ $store->is_open ? 'Buka' : 'Tutup' }}
            </span>
        </div>

        <div class="p-5 sm:p-7">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="font-black tracking-tight text-3xl sm:text-4xl">{{ $store->name }}</h1>
                    <p class="text-[13px] font-semibold text-ink-500 mt-1">{{ $store->user?->name ?? 'Penjual warung' }}</p>
                    @if($store->description)
                        <p class="text-[15px] font-medium text-ink-500 leading-relaxed mt-3 max-w-2xl">{{ $store->description }}</p>
                    @endif
                </div>
                <span class="shrink-0 inline-flex items-center gap-1.5 text-[12px] font-extrabold bg-cream-100 text-ink-700 rounded-full px-4 py-2">🛍️ {{ $store->products->count() }} produk</span>
            </div>

            <div class="mt-5 flex flex-wrap gap-2 text-[12px] font-bold">
                @if($store->address)
                    <span class="inline-flex items-center gap-1.5 bg-cream-100 text-ink-700 rounded-full px-3.5 py-2">📍 {{ $store->address }}</span>
                @endif
                @if($store->hours_label)
                    <span class="inline-flex items-center gap-1.5 bg-cream-100 text-ink-700 rounded-full px-3.5 py-2">🕐 {{ $store->hours_label }}</span>
                @endif
                @if($store->phone)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $store->phone) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 bg-leaf-100 text-leaf-700 rounded-full px-3.5 py-2 hover:bg-leaf-500 hover:text-white transition">✆ {{ $store->phone }}</a>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== MAP ===== --}}
    @if($store->latitude !== null && $store->longitude !== null)
    <div class="reveal mt-5 rounded-[28px] bg-white border border-ink-900/10 overflow-hidden">
        <div class="flex items-center justify-between gap-3 px-5 sm:px-7 pt-5">
            <div>
                <p class="inline-flex text-[11px] font-extrabold tracking-[0.18em] text-leaf-700 bg-leaf-100 border border-leaf-500/20 rounded-full px-3.5 py-1.5">📍 LOKASI WARUNG</p>
                <h2 class="font-black tracking-tight text-xl sm:text-2xl mt-2">Ada di sekitar sini</h2>
            </div>
            <a id="store-detail-osm" href="https://www.openstreetmap.org/#map=16/{{ $store->latitude }}/{{ $store->longitude }}" target="_blank" rel="noopener" class="shrink-0 text-[13px] font-extrabold px-4 py-2.5 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">Buka di OSM →</a>
        </div>
        <div
            id="store-detail-map"
            class="mt-4 h-72 sm:h-80"
            data-lat="{{ $store->latitude }}"
            data-lng="{{ $store->longitude }}"
            data-name="{{ $store->name }}"
            data-address="{{ $store->address }}"
        ></div>
    </div>
    @endif

    {{-- ===== PRODUCTS ===== --}}
    <div class="mt-9">
        <div class="flex items-end justify-between gap-4 mb-5">
            <div>
                <p class="reveal inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-600 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">🛍️ ETALASE</p>
                <h2 class="reveal font-black tracking-tight text-2xl sm:text-3xl mt-2" style="--reveal-delay:80ms">Produk warung ini</h2>
            </div>
        </div>

        @if($store->products->isEmpty())
            <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-10 text-center">
                <p class="text-4xl">📦</p>
                <p class="font-extrabold text-lg mt-2">Belum ada produk</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Produk yang sudah diverifikasi admin akan muncul di sini.</p>
            </div>
        @else
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($store->products as $i => $p)
                <article class="reveal group rounded-[24px] bg-white border border-ink-900/10 overflow-hidden hover:shadow-xl hover:shadow-ink-900/10 hover:-translate-y-1 transition-all duration-300" style="--reveal-delay:{{ ($i%3)*70 }}ms">
                    @if($p->image_path)
                        <img src="{{ $p->image_url }}" alt="Foto {{ $p->name }}" loading="lazy" class="w-full h-40 object-cover">
                    @else
                        <div class="w-full h-40 bg-cream-100 border-b border-dashed border-ink-900/15 grid place-items-center text-4xl">📷</div>
                    @endif
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-extrabold text-[15px] leading-snug min-w-0">{{ $p->name }}</p>
                            <span class="shrink-0 text-[11px] font-extrabold bg-cream-100 text-ink-700 rounded-full px-2.5 py-1">{{ $p->category }}</span>
                        </div>
                        @if($p->description)
                            <p class="text-[13px] font-medium text-ink-500 leading-snug mt-1 line-clamp-2">{{ $p->description }}</p>
                        @endif
                        <div class="flex items-center justify-between mt-3">
                            <p class="font-black text-[16px] text-brand-600">Rp {{ number_format($p->price, 0, ',', '.') }}</p>
                            @if($p->stock > 0)
                                <span class="text-[11px] font-extrabold text-leaf-700 bg-leaf-100 rounded-full px-2.5 py-1">Stok {{ $p->stock }}</span>
                            @else
                                <span class="text-[11px] font-extrabold text-amber-800 bg-amber-100 rounded-full px-2.5 py-1">Habis</span>
                            @endif
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        @endif
    </div>

    @guest
    <div class="reveal mt-10 rounded-[28px] bg-ink-900 text-white p-6 sm:p-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-4 grain relative overflow-hidden">
        <div class="hidden sm:block absolute -top-16 -right-16 w-56 h-56 bg-brand-500/30 blur-[80px] rounded-full pointer-events-none" aria-hidden="true"></div>
        <p class="relative flex-1 text-[15px] font-bold leading-snug">Mau pesan dari <strong class="text-brand-400">{{ $store->name }}</strong>? Daftar gratis 30 detik, langsung jajan.</p>
        <div class="relative flex gap-2">
            <a href="{{ route('register') }}" data-open-register class="text-center bg-brand-500 hover:bg-brand-600 font-extrabold text-sm px-6 py-3.5 rounded-full transition">Daftar Gratis →</a>
            <a href="{{ route('login') }}" data-open-login class="text-center bg-white/10 border border-white/15 hover:bg-white/20 font-extrabold text-sm px-6 py-3.5 rounded-full transition">Masuk</a>
        </div>
    </div>
    @endguest
</section>
@endsection

@if($store->latitude !== null && $store->longitude !== null)
@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>#store-detail-map img { max-width: none; }</style>
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
    var map = L.map(el, { scrollWheelZoom: false }).setView(center, 16);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    var name = el.dataset.name || 'Warung';
    var address = el.dataset.address || '';
    var popup = '<strong>' + name + '</strong>' + (address ? '<br>' + address : '');
    L.marker(center).addTo(map).bindPopup(popup).openPopup();

    setTimeout(function () { map.invalidateSize(); }, 200);
})();
</script>
@endpush
@endif
