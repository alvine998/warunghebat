@props(['stores', 'userLat' => null, 'userLng' => null, 'prefix' => 'nearby'])

@php
    $nearbyCollection = $stores instanceof \Illuminate\Pagination\AbstractPaginator
        ? $stores->getCollection()
        : collect($stores);
    $mappedStores = $nearbyCollection
        ->filter(fn ($store) => $store->latitude !== null && $store->longitude !== null)
        ->values();
    $defaultStore = $mappedStores->first();
    $hasUserLocation = $userLat !== null && $userLng !== null;
@endphp

<div data-nearby-tabs="{{ $prefix }}">
    <div role="tablist" aria-label="Tampilan daftar warung" class="mb-5 inline-flex rounded-full bg-white border border-ink-900/10 p-1 shadow-sm">
        <button id="{{ $prefix }}-tab-list" type="button" role="tab" aria-selected="true" aria-controls="{{ $prefix }}-panel-list" data-tab="list" class="rounded-full px-5 py-2.5 text-[13px] font-extrabold bg-ink-900 text-white transition">📋 Daftar</button>
        <button id="{{ $prefix }}-tab-map" type="button" role="tab" aria-selected="false" aria-controls="{{ $prefix }}-panel-map" data-tab="map" class="rounded-full px-5 py-2.5 text-[13px] font-extrabold text-ink-700 hover:text-ink-900 transition">🗺️ Peta{{ $mappedStores->isNotEmpty() ? ' ('.$mappedStores->count().')' : '' }}</button>
    </div>

    <div id="{{ $prefix }}-panel-list" role="tabpanel" aria-labelledby="{{ $prefix }}-tab-list" data-panel="list">
        {{ $list }}
        @isset($footer)
            {{ $footer }}
        @endisset
    </div>

    <div id="{{ $prefix }}-panel-map" role="tabpanel" aria-labelledby="{{ $prefix }}-tab-map" data-panel="map" hidden>
        @if($mappedStores->isNotEmpty())
            <div class="grid gap-3 lg:grid-cols-[1fr_300px] lg:items-start">
                <div class="overflow-hidden rounded-[24px] bg-white border border-ink-900/10">
                    <iframe
                        id="{{ $prefix }}-gmap"
                        title="Peta Google Maps lokasi warung"
                        src="https://maps.google.com/maps?q={{ $defaultStore->latitude }},{{ $defaultStore->longitude }}&z=16&output=embed"
                        class="h-72 w-full border-0 sm:h-[420px]"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen></iframe>
                    <div class="border-t border-ink-900/10 p-4 sm:p-5">
                        <p data-map-detail-name class="font-extrabold text-[16px] leading-snug">{{ $defaultStore->name }}</p>
                        <p data-map-detail-meta class="mt-1 text-[12px] font-bold text-ink-500">
                            @php
                                $defaultDist = $defaultStore->distance_km ?? null;
                                $defaultDistLabel = $defaultDist !== null
                                    ? ($defaultDist < 1 ? round($defaultDist * 1000).' m' : number_format($defaultDist, 1).' km')
                                    : null;
                            @endphp
                            {{ $defaultStore->is_open ? '🟢 Buka' : '🟡 Tutup' }}{{ $defaultDistLabel ? ' • 📍 '.$defaultDistLabel : '' }}
                        </p>
                        @if($defaultStore->address)
                            <p data-map-detail-address class="mt-1.5 text-[13px] font-medium text-ink-500 leading-relaxed">{{ $defaultStore->address }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a data-map-detail-show href="{{ route('store.show', $defaultStore) }}" class="inline-flex items-center gap-1.5 text-[13px] font-extrabold bg-ink-900 text-white px-4 py-2.5 rounded-full hover:bg-brand-500 transition">Lihat warung →</a>
                            <a data-map-detail-route href="https://www.google.com/maps/dir/?api=1&destination={{ $defaultStore->latitude }},{{ $defaultStore->longitude }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[13px] font-extrabold px-4 py-2.5 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">Rute di Google Maps →</a>
                        </div>
                    </div>
                </div>

                <div class="rounded-[24px] bg-white border border-ink-900/10 p-3 sm:p-4">
                    <p class="px-1 pb-2 text-[11px] font-extrabold tracking-[0.14em] text-ink-500">📍 PIN WARUNG</p>
                    <div class="grid gap-2 lg:max-h-[480px] lg:overflow-y-auto lg:pr-1" data-map-options>
                        @if($hasUserLocation)
                            <button type="button" data-user-pin="1" data-lat="{{ $userLat }}" data-lng="{{ $userLng }}" class="flex items-center gap-2.5 rounded-2xl border border-dashed border-ink-900/20 px-3.5 py-3 text-left hover:border-ink-900 transition">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-leaf-100 text-base">🧭</span>
                                <span class="min-w-0">
                                    <span class="block truncate text-[13px] font-extrabold">Lokasiku</span>
                                    <span class="block text-[11px] font-bold text-ink-500">Titik awal • radius {{ rtrim(rtrim(number_format($radius ?? 5, 1), '0'), '.') }} km</span>
                                </span>
                            </button>
                        @endif
                        @foreach($mappedStores as $mapStore)
                            @php
                                $mapDist = $mapStore->distance_km ?? null;
                                $mapDistLabel = $mapDist !== null
                                    ? ($mapDist < 1 ? round($mapDist * 1000).' m' : number_format($mapDist, 1).' km')
                                    : null;
                            @endphp
                            <button
                                type="button"
                                data-store-pin="1"
                                data-lat="{{ $mapStore->latitude }}"
                                data-lng="{{ $mapStore->longitude }}"
                                data-name="{{ $mapStore->name }}"
                                data-address="{{ $mapStore->address ?? '' }}"
                                data-open="{{ $mapStore->is_open ? '1' : '0' }}"
                                data-dist="{{ $mapDistLabel ?? '' }}"
                                data-url="{{ route('store.show', $mapStore) }}"
                                aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                class="flex items-center gap-2.5 rounded-2xl border px-3.5 py-3 text-left transition {{ $loop->first ? 'border-ink-900 bg-cream-100' : 'border-ink-900/10 hover:border-ink-900' }}">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $mapStore->is_open ? 'bg-leaf-100' : 'bg-amber-100' }} text-base">🏪</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[13px] font-extrabold">{{ $mapStore->name }}</span>
                                    <span class="block truncate text-[11px] font-bold text-ink-500">{{ $mapDistLabel ? '📍 '.$mapDistLabel.' • ' : '' }}{{ $mapStore->is_open ? 'Buka' : 'Tutup' }}</span>
                                </span>
                                <span class="shrink-0 text-[11px] font-black text-brand-600">{{ $loop->iteration }}</span>
                            </button>
                        @endforeach
                    </div>
                    @if($nearbyCollection->count() > $mappedStores->count())
                        <p class="px-1 pt-2.5 text-[11px] font-semibold leading-relaxed text-ink-500">{{ $mappedStores->count() }} dari {{ $nearbyCollection->count() }} warung punya titik lokasi — yang belum ada titiknya hanya tampil di tab Daftar.</p>
                    @endif
                </div>
            </div>
        @else
            <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-10 text-center">
                <p class="text-4xl">🗺️</p>
                <p class="font-extrabold text-lg mt-2">Belum ada warung yang bisa dipetakan</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Warung muncul di peta setelah pemiliknya mengisi titik lokasi (latitude & longitude).</p>
                <button type="button" data-trigger-locate class="mt-4 text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">📍 Gunakan lokasiku</button>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
    var ACTIVE_TAB = 'rounded-full px-5 py-2.5 text-[13px] font-extrabold bg-ink-900 text-white transition';
    var IDLE_TAB = 'rounded-full px-5 py-2.5 text-[13px] font-extrabold text-ink-700 hover:text-ink-900 transition';
    var ACTIVE_PIN = 'flex items-center gap-2.5 rounded-2xl border px-3.5 py-3 text-left transition border-ink-900 bg-cream-100';
    var IDLE_PIN = 'flex items-center gap-2.5 rounded-2xl border px-3.5 py-3 text-left transition border-ink-900/10 hover:border-ink-900';

    function gmapSrc(lat, lng) {
        return 'https://maps.google.com/maps?q=' + encodeURIComponent(lat) + ',' + encodeURIComponent(lng) + '&z=16&output=embed';
    }

    function init(root) {
        var prefix = root.getAttribute('data-nearby-tabs');
        var tabs = Array.from(root.querySelectorAll('[data-tab]'));
        var panels = Array.from(root.querySelectorAll('[data-panel]'));
        if (!tabs.length || !panels.length) return;

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var target = tab.getAttribute('data-tab');
                tabs.forEach(function (t) {
                    var active = t === tab;
                    t.setAttribute('aria-selected', active ? 'true' : 'false');
                    t.className = active ? ACTIVE_TAB : IDLE_TAB;
                });
                panels.forEach(function (p) {
                    p.hidden = p.getAttribute('data-panel') !== target;
                });
            });
        });

        var frame = document.getElementById(prefix + '-gmap');
        var detailName = root.querySelector('[data-map-detail-name]');
        var detailMeta = root.querySelector('[data-map-detail-meta]');
        var detailAddress = root.querySelector('[data-map-detail-address]');
        var detailShow = root.querySelector('[data-map-detail-show]');
        var detailRoute = root.querySelector('[data-map-detail-route]');
        var pins = Array.from(root.querySelectorAll('[data-store-pin],[data-user-pin]'));
        if (!frame || !pins.length) return;

        function markActive(btn) {
            pins.forEach(function (p) {
                var isStore = p.hasAttribute('data-store-pin');
                var active = p === btn;
                if (isStore) p.className = active ? ACTIVE_PIN : IDLE_PIN;
                p.setAttribute('aria-current', active ? 'true' : 'false');
            });
        }

        pins.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var lat = btn.getAttribute('data-lat');
                var lng = btn.getAttribute('data-lng');
                if (!lat || !lng) return;
                frame.setAttribute('src', gmapSrc(lat, lng));
                markActive(btn);
                if (btn.hasAttribute('data-user-pin')) {
                    if (detailName) detailName.textContent = 'Lokasimu 📍';
                    if (detailMeta) detailMeta.textContent = 'Titik awal pencarian warung terdekat';
                    if (detailAddress) detailAddress.textContent = '';
                    if (detailShow) detailShow.style.display = 'none';
                    if (detailRoute) detailRoute.style.display = 'none';
                } else {
                    var open = btn.getAttribute('data-open') === '1';
                    var dist = btn.getAttribute('data-dist');
                    if (detailName) detailName.textContent = btn.getAttribute('data-name') || 'Warung';
                    if (detailMeta) detailMeta.textContent = (open ? '🟢 Buka' : '🟡 Tutup') + (dist ? ' • 📍 ' + dist : '');
                    if (detailAddress) detailAddress.textContent = btn.getAttribute('data-address') || '';
                    if (detailShow) {
                        detailShow.style.display = '';
                        detailShow.setAttribute('href', btn.getAttribute('data-url'));
                    }
                    if (detailRoute) {
                        detailRoute.style.display = '';
                        detailRoute.setAttribute('href', 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(lat) + ',' + encodeURIComponent(lng));
                    }
                }
            });
        });

        var locateFallback = root.querySelector('[data-trigger-locate]');
        if (locateFallback) {
            locateFallback.addEventListener('click', function () {
                var locateBtn = document.getElementById('locate-btn');
                if (locateBtn) locateBtn.click();
            });
        }
    }

    document.querySelectorAll('[data-nearby-tabs]').forEach(init);
})();
</script>
@endpush
