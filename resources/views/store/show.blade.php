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
                <a id="store-detail-gmaps" href="https://www.google.com/maps?q={{ $store->latitude }},{{ $store->longitude }}" target="_blank" rel="noopener" class="hidden sm:inline-flex justify-center shrink-0 text-[13px] font-extrabold px-4 py-2.5 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">Buka dengan Google Maps →</a>
            </div>
            <a id="store-detail-gmaps-mobile" href="https://www.google.com/maps?q={{ $store->latitude }},{{ $store->longitude }}" target="_blank" rel="noopener" class="sm:hidden text-center text-[13px] font-extrabold px-4 py-3 rounded-full border border-ink-900/15 active:bg-ink-900 active:text-white transition">Buka dengan Google Maps →</a>
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
            <div class="mb-4 bg-white rounded-[22px] border border-ink-900/10 shadow-sm p-2 flex items-center gap-2 max-w-md">
                <span class="pl-2 text-ink-500"><x-icon name="search" class="w-5 h-5" /></span>
                <input id="product-search" type="search" autocomplete="off" aria-label="Cari produk di warung ini" placeholder="Cari produk di warung ini..." class="flex-1 min-w-0 bg-transparent outline-none text-[15px] font-semibold placeholder:text-ink-500/60 placeholder:font-medium py-2.5">
            </div>
            <p id="product-search-hint" class="hidden mb-4 text-[12px] font-semibold text-ink-500" role="status"></p>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-3">
                @foreach($store->products as $i => $p)
                <article data-product-search="{{ trim($p->name.' '.$p->category.' '.($p->description ?? '')) }}" class="reveal group rounded-[20px] sm:rounded-[24px] bg-white border border-ink-900/10 overflow-hidden hover:shadow-xl hover:shadow-ink-900/10 transition-all duration-300" style="--reveal-delay:{{ ($i%4)*70 }}ms">
                    @if($p->image_path)
                        <button type="button" class="relative block w-full cursor-zoom-in" data-zoom-src="{{ $p->image_url }}" data-zoom-alt="Foto {{ $p->name }}" data-zoom-caption="{{ $p->name }}" aria-label="Perbesar foto {{ $p->name }}">
                            <img src="{{ $p->image_url }}" alt="Foto {{ $p->name }}" loading="lazy" class="w-full h-28 sm:h-36 object-cover">
                            <span class="absolute top-2 right-2 w-7 h-7 grid place-items-center rounded-full bg-white/90 text-ink-900 text-[13px] font-black shadow-sm">🔍</span>
                        </button>
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
                        @if($p->stock > 0)
                            @if(! $store->is_open)
                            <button type="button" disabled aria-disabled="true" title="Warung sedang tutup" class="mt-2 sm:mt-3 w-full min-h-11 py-2.5 px-2 rounded-full bg-ink-900/10 text-ink-500 text-[12px] sm:text-[13px] font-extrabold cursor-not-allowed">Warung tutup</button>
                            @else
                            @auth
                            <form method="POST" action="{{ route('cart.store') }}" class="mt-2 sm:mt-3">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $p->id }}">
                                <button type="submit" class="w-full min-h-11 py-2.5 px-2 rounded-full bg-ink-900 text-white text-[12px] sm:text-[13px] font-extrabold hover:bg-brand-500 active:scale-[.98] transition">+ Keranjang</button>
                            </form>
                            @else
                            <button type="button" data-open-login class="mt-2 sm:mt-3 w-full min-h-11 py-2.5 px-2 rounded-full bg-ink-900 text-white text-[12px] sm:text-[13px] font-extrabold hover:bg-brand-500 active:scale-[.98] transition">+ Keranjang</button>
                            @endauth
                            @endif
                        @endif
                    </div>
                </article>
                @endforeach
            </div>
            <div id="product-search-empty" class="hidden rounded-[24px] bg-white border border-dashed border-ink-900/15 p-7 sm:p-10 text-center">
                <p class="text-4xl">🔍</p>
                <p class="font-extrabold text-base sm:text-lg mt-2">Produk tidak ditemukan</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Coba kata kunci lain — nama produk atau kategori.</p>
            </div>

            @push('scripts')
            <script>
            (function () {
                var input = document.getElementById('product-search');
                if (!input) return;
                var hint = document.getElementById('product-search-hint');
                var empty = document.getElementById('product-search-empty');
                var cards = Array.from(document.querySelectorAll('[data-product-search]')).map(function (card) {
                    return { card: card, text: (card.dataset.productSearch || '').toLowerCase() };
                });
                var queued = false;
                input.addEventListener('input', function () {
                    if (queued) return;
                    queued = true;
                    requestAnimationFrame(function () {
                        queued = false;
                        var raw = input.value;
                        var q = raw.toLowerCase().trim();
                        var visible = 0;
                        for (var i = 0; i < cards.length; i++) {
                            var match = !q || cards[i].text.includes(q);
                            cards[i].card.style.display = match ? '' : 'none';
                            if (match) visible++;
                        }
                        if (hint) {
                            hint.classList.toggle('hidden', !q);
                            hint.textContent = q ? visible + ' produk ditemukan untuk "' + raw + '"' : '';
                        }
                        if (empty) empty.classList.toggle('hidden', !(q && visible === 0));
                    });
                });
            })();
            </script>
            @endpush
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

    {{-- ===== PRODUCT ZOOM (lightbox) ===== --}}
    @if($store->products->contains(fn ($p) => $p->image_path !== null))
    <div id="modal-product-zoom" class="hidden fixed inset-0 z-[70] modal-backdrop" role="dialog" aria-modal="true" aria-label="Perbesar foto produk">
        <div class="absolute inset-0 bg-ink-900/85 backdrop-blur-sm"></div>
        <div class="absolute inset-0 overflow-y-auto p-4 sm:p-8 flex items-center justify-center">
            <button type="button" class="absolute top-4 right-4 w-10 h-10 grid place-items-center rounded-full bg-white/10 text-white hover:bg-white/20 transition" aria-label="Tutup" data-zoom-close>✕</button>
            <figure class="relative max-w-full">
                <img id="product-zoom-img" alt="" class="mx-auto block max-w-full max-h-[72vh] sm:max-h-[78vh] object-contain rounded-2xl bg-white shadow-2xl">
                <figcaption id="product-zoom-caption" class="mt-3 text-center text-[13px] font-bold text-white/75"></figcaption>
            </figure>
        </div>
    </div>
    @endif
</section>
@endsection

@include('components.cart-conflict-modal')

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
