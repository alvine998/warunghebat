@props(['stores', 'userLat' => null, 'userLng' => null, 'prefix' => 'nearby'])

@php
    $items = $stores instanceof \Illuminate\Pagination\AbstractPaginator ? $stores->getCollection() : $stores;
    $total = $items->count();
    $mapped = $items->filter(fn ($store) => $store->latitude !== null && $store->longitude !== null)->values();
    $mappedCount = $mapped->count();
    $first = $mapped->first();
    $firstDist = $first ? ($first->distance_km ?? null) : null;
    $hasUser = $userLat !== null && $userLng !== null;
    $label = fn ($km) => $km === null ? '' : ($km < 1 ? round($km * 1000).' m' : number_format($km, 1).' km');
@endphp

@once
@push('head')
<style>
    [data-nearby-tabs] .nb-tab { display: inline-flex; border-radius: 9999px; padding: .625rem 1.25rem; font-size: 13px; font-weight: 800; cursor: pointer; transition: color .15s, background-color .15s; }
    [data-nearby-tabs] .nb-tab[aria-selected="false"] { color: var(--color-ink-700); }
    [data-nearby-tabs] .nb-tab[aria-selected="false"]:hover { color: var(--color-ink-900); }
    [data-nearby-tabs] .nb-tab[aria-selected="true"] { background: var(--color-ink-900); color: #fff; }
    [data-nearby-tabs] .nb-pin { display: flex; width: 100%; align-items: center; gap: .625rem; border-radius: 1rem; border: 1px solid rgba(26, 19, 13, .1); padding: .75rem .875rem; text-align: left; cursor: pointer; transition: border-color .15s, background-color .15s; }
    [data-nearby-tabs] .nb-pin:hover { border-color: var(--color-ink-900); }
    [data-nearby-tabs] .nb-pin[data-user] { border-style: dashed; border-color: rgba(26, 19, 13, .2); }
    [data-nearby-tabs] .nb-pin[aria-current="true"] { border-style: solid; border-color: var(--color-ink-900); background: var(--color-cream-100); }
</style>
@endpush
@endonce

<div data-nearby-tabs="{{ $prefix }}">
    <div role="tablist" aria-label="Tampilan daftar warung" class="mb-5 inline-flex rounded-full bg-white border border-ink-900/10 p-1 shadow-sm">
        <button id="{{ $prefix }}-tab-list" type="button" role="tab" aria-selected="true" aria-controls="{{ $prefix }}-panel-list" data-tab="list" class="nb-tab">📋 Daftar</button>
        <button id="{{ $prefix }}-tab-map" type="button" role="tab" aria-selected="false" aria-controls="{{ $prefix }}-panel-map" data-tab="map" class="nb-tab">🗺️ Peta{{ $mappedCount ? ' ('.$mappedCount.')' : '' }}</button>
    </div>

    <div id="{{ $prefix }}-panel-list" role="tabpanel" aria-labelledby="{{ $prefix }}-tab-list" data-panel="list">
        <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3">
            {{ $list }}
        </div>
        @isset($footer)
            {{ $footer }}
        @endisset
    </div>

    <div id="{{ $prefix }}-panel-map" role="tabpanel" aria-labelledby="{{ $prefix }}-tab-map" data-panel="map" hidden>
        @if($mappedCount)
            <div class="grid gap-3 lg:grid-cols-[1fr_300px] lg:items-start">
                <div class="overflow-hidden rounded-[24px] bg-white border border-ink-900/10">
                    <iframe
                        data-gmap
                        data-src="https://maps.google.com/maps?q={{ $first->latitude }},{{ $first->longitude }}&z=16&output=embed"
                        title="Peta Google Maps lokasi warung"
                        class="h-72 w-full border-0 sm:h-[420px]"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen></iframe>
                    <div class="border-t border-ink-900/10 p-4 sm:p-5">
                        <p data-map-detail-name class="font-extrabold text-[16px] leading-snug">{{ $first->name }}</p>
                        <p data-map-detail-meta class="mt-1 text-[12px] font-bold text-ink-500">
                            {{ $first->is_open ? '🟢 Buka' : '🟡 Tutup' }}{{ $label($firstDist) !== '' ? ' • 📍 '.$label($firstDist) : '' }}
                        </p>
                        <p data-map-detail-address @unless($first->address) hidden @endunless class="mt-1.5 text-[13px] font-medium text-ink-500 leading-relaxed">{{ $first->address }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a data-map-detail-show href="{{ route('store.show', $first) }}" class="inline-flex items-center gap-1.5 text-[13px] font-extrabold bg-ink-900 text-white px-4 py-2.5 rounded-full hover:bg-brand-500 transition">Lihat warung →</a>
                            <a data-map-detail-route href="https://www.google.com/maps/dir/?api=1&destination={{ $first->latitude }},{{ $first->longitude }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[13px] font-extrabold px-4 py-2.5 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">Rute di Google Maps →</a>
                        </div>
                    </div>
                </div>

                <div class="rounded-[24px] bg-white border border-ink-900/10 p-3 sm:p-4">
                    <p class="px-1 pb-2 text-[11px] font-extrabold tracking-[0.14em] text-ink-500">📍 PIN WARUNG</p>
                    <div class="grid gap-2 lg:max-h-[480px] lg:overflow-y-auto lg:pr-1">
                        @if($hasUser)
                            <button type="button" class="nb-pin" data-pin data-user data-lat="{{ $userLat }}" data-lng="{{ $userLng }}" aria-current="false">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-leaf-100 text-base">🧭</span>
                                <span class="min-w-0">
                                    <span class="block truncate text-[13px] font-extrabold">Lokasiku</span>
                                    <span class="block text-[11px] font-bold text-ink-500">Titik awal pencarian</span>
                                </span>
                            </button>
                        @endif
                        @foreach($mapped as $pinStore)
                            <button
                                type="button"
                                class="nb-pin"
                                data-pin
                                data-lat="{{ $pinStore->latitude }}"
                                data-lng="{{ $pinStore->longitude }}"
                                data-name="{{ $pinStore->name }}"
                                data-address="{{ $pinStore->address ?? '' }}"
                                data-open="{{ $pinStore->is_open ? '1' : '0' }}"
                                data-dist="{{ $label($pinStore->distance_km ?? null) }}"
                                data-url="{{ route('store.show', $pinStore) }}"
                                aria-current="{{ $loop->first ? 'true' : 'false' }}">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $pinStore->is_open ? 'bg-leaf-100' : 'bg-amber-100' }} text-base">🏪</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[13px] font-extrabold">{{ $pinStore->name }}</span>
                                    <span class="block truncate text-[11px] font-bold text-ink-500">{{ $label($pinStore->distance_km ?? null) !== '' ? '📍 '.$label($pinStore->distance_km ?? null).' • ' : '' }}{{ $pinStore->is_open ? 'Buka' : 'Tutup' }}</span>
                                </span>
                                <span class="shrink-0 text-[11px] font-black text-brand-600">{{ $loop->iteration }}</span>
                            </button>
                        @endforeach
                    </div>
                    @if($total > $mappedCount)
                        <p class="px-1 pt-2.5 text-[11px] font-semibold leading-relaxed text-ink-500">{{ $mappedCount }} dari {{ $total }} warung punya titik lokasi — yang belum ada titiknya hanya tampil di tab Daftar.</p>
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

@once
@push('scripts')
<script>
(function () {
    function gmap(lat, lng) {
        return 'https://maps.google.com/maps?q=' + encodeURIComponent(lat) + ',' + encodeURIComponent(lng) + '&z=16&output=embed';
    }

    document.querySelectorAll('[data-nearby-tabs]').forEach(function (root) {
        var frame = root.querySelector('[data-gmap]');
        var loaded = false;
        var nameEl = root.querySelector('[data-map-detail-name]');
        var metaEl = root.querySelector('[data-map-detail-meta]');
        var addrEl = root.querySelector('[data-map-detail-address]');
        var showEl = root.querySelector('[data-map-detail-show]');
        var routeEl = root.querySelector('[data-map-detail-route]');
        var pins = root.querySelectorAll('[data-pin]');

        function selectTab(name) {
            root.querySelectorAll('[data-tab]').forEach(function (tab) {
                tab.setAttribute('aria-selected', tab.dataset.tab === name ? 'true' : 'false');
            });
            root.querySelectorAll('[data-panel]').forEach(function (panel) {
                panel.hidden = panel.dataset.panel !== name;
            });
            // Google Maps is heavy — only fetch it the first time the tab opens.
            if (name === 'map' && !loaded && frame) {
                loaded = true;
                frame.src = frame.dataset.src;
            }
        }

        function activatePin(pin) {
            var lat = pin.dataset.lat;
            var lng = pin.dataset.lng;
            if (frame && lat && lng) frame.src = gmap(lat, lng);
            pins.forEach(function (p) {
                p.setAttribute('aria-current', p === pin ? 'true' : 'false');
            });

            var user = pin.hasAttribute('data-user');
            var dist = pin.dataset.dist || '';
            if (nameEl) nameEl.textContent = user ? 'Lokasimu 📍' : (pin.dataset.name || 'Warung');
            if (metaEl) {
                metaEl.textContent = user
                    ? 'Titik awal pencarian warung terdekat'
                    : (pin.dataset.open === '1' ? '🟢 Buka' : '🟡 Tutup') + (dist ? ' • 📍 ' + dist : '');
            }
            if (addrEl) {
                addrEl.textContent = user ? '' : (pin.dataset.address || '');
                addrEl.hidden = !addrEl.textContent;
            }
            if (showEl) {
                showEl.hidden = user;
                if (!user) showEl.href = pin.dataset.url;
            }
            if (routeEl) {
                routeEl.hidden = user;
                if (!user) routeEl.href = 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(lat) + ',' + encodeURIComponent(lng);
            }
        }

        root.addEventListener('click', function (event) {
            var el = event.target.closest('[data-tab]');
            if (el) return selectTab(el.dataset.tab);
            el = event.target.closest('[data-pin]');
            if (el) return activatePin(el);
            if (event.target.closest('[data-trigger-locate]')) {
                var locate = document.getElementById('locate-btn');
                if (locate) locate.click();
            }
        });
    });
})();
</script>
@endpush
@endonce
