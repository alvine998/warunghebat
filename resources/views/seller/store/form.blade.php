@extends('layouts.app')

@section('title', 'Pengaturan Warung — Warung Hebat')

@section('bare', true)

@section('content')
<section class="pt-6 sm:pt-8 pb-10 max-w-2xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between gap-2 flex-wrap">
        <a href="{{ route('seller.products.index') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Produk saya</a>
        @include('seller.store._open_toggle', ['store' => $store])
    </div>

    <h1 class="font-black tracking-tight text-3xl mt-2">Pengaturan warung</h1>
    <p class="text-sm font-medium text-ink-500">Nama, alamat, jam operasional & foto warungmu. Ditampilkan ke pembeli.</p>

    <div class="mt-5 rounded-[28px] bg-white border border-ink-900/10 p-6">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div>
                <p class="text-[11px] font-extrabold tracking-[0.2em] text-brand-600">📊 STATISTIK WARUNG</p>
                <h2 class="font-extrabold text-lg mt-0.5">Performa tokomu</h2>
            </div>
            <a href="{{ route('seller.products.index') }}" class="text-[13px] font-extrabold text-brand-600">Kelola produk →</a>
        </div>
        @include('seller.store._stats')
    </div>

    <form method="POST" action="{{ route('seller.store.update') }}" enctype="multipart/form-data" class="mt-5 rounded-[28px] bg-white border border-ink-900/10 p-6 grid gap-4">
        @csrf
        @method('PUT')

        <div>
            <label class="text-[13px] font-extrabold">Nama warung *</label>
            <input id="store-name" name="name" required maxlength="80" value="{{ old('name', $store->name) }}" placeholder="cth. Warung Bang Jago" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            <p class="mt-1.5 text-xs font-semibold text-ink-500">URL warungmu: <span class="font-extrabold text-ink-900">/w/<span id="store-slug-preview">{{ $store->slug }}</span></span> (otomatis dari nama)</p>
        </div>

        <div>
            <label class="text-[13px] font-extrabold">Deskripsi warung</label>
            <textarea name="description" rows="3" maxlength="1000" placeholder="Ceritakan warungmu: jualan apa, sejak kapan, keunggulan..." class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition resize-y">{{ old('description', $store->description) }}</textarea>
        </div>

        <div>
            <label class="text-[13px] font-extrabold">Alamat warung</label>
            <textarea name="address" rows="2" maxlength="500" placeholder="cth. Jl. Tebet Raya No. 12, Jakarta Selatan" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition resize-y">{{ old('address', $store->address) }}</textarea>
        </div>

        <div>
            <label class="text-[13px] font-extrabold">Lokasi warung <span class="font-semibold text-ink-500">(klik peta untuk menandai)</span></label>
            <div id="store-map" class="mt-1.5 w-full h-48 sm:h-64 rounded-2xl border border-ink-900/15 z-0"></div>
            <p class="mt-1.5 text-xs font-semibold text-ink-500">Klik peta atau geser pin — atau tekan tombol lokasi. Koordinat tersimpan otomatis di kolom bawah.</p>
            <div class="mt-2 flex flex-col sm:flex-row gap-2">
                <button type="button" id="use-my-location" class="text-[13px] font-extrabold px-4 py-2.5 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">📍 Gunakan lokasi saya</button>
                <a id="open-osm" href="https://www.openstreetmap.org/#map=15/-6.2/106.8167" target="_blank" rel="noopener" class="text-[13px] font-extrabold px-4 py-2.5 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">Lihat di OSM →</a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="text-[13px] font-extrabold">Latitude</label>
                <input id="store-latitude" name="latitude" type="number" step="any" min="-90" max="90" value="{{ old('latitude', $store->latitude) }}" placeholder="cth. -6.2297" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>
            <div>
                <label class="text-[13px] font-extrabold">Longitude</label>
                <input id="store-longitude" name="longitude" type="number" step="any" min="-180" max="180" value="{{ old('longitude', $store->longitude) }}" placeholder="cth. 106.8294" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>
        </div>

        <div>
            <label class="text-[13px] font-extrabold">No. HP / WhatsApp</label>
            <input name="phone" maxlength="20" value="{{ old('phone', $store->phone) }}" placeholder="cth. 081234567890" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="text-[13px] font-extrabold">Jam buka</label>
                <input name="open_time" type="time" value="{{ old('open_time', $store->open_time ? substr((string) $store->open_time, 0, 5) : '') }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>
            <div>
                <label class="text-[13px] font-extrabold">Jam tutup</label>
                <input name="close_time" type="time" value="{{ old('close_time', $store->close_time ? substr((string) $store->close_time, 0, 5) : '') }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>
        </div>

        <div>
            <label class="text-[13px] font-extrabold">Foto warung <span class="font-semibold text-ink-500">(JPG/PNG/WebP, maks 2MB)</span></label>
            @if($store->image_path)
                <div class="mt-1.5 flex items-center gap-3">
                    <img src="{{ $store->image_url }}" alt="Foto {{ $store->name }}" class="w-20 h-20 rounded-2xl object-cover border border-ink-900/10">
                    <p class="text-xs font-semibold text-ink-500">Foto saat ini. Unggah file baru untuk mengganti.</p>
                </div>
            @endif
            <input name="image" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[14px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition file:mr-3 file:rounded-xl file:border-0 file:bg-ink-900 file:text-white file:text-[13px] file:font-extrabold file:px-4 file:py-2">
        </div>

        <button class="mt-1 w-full py-3.5 rounded-2xl bg-ink-900 text-white font-extrabold text-[15px] hover:bg-brand-600 transition">Simpan pengaturan</button>
    </form>
</section>
@endsection

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>#store-map img { max-width: none; }</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    // Live slug preview mirrors name.
    var nameInput = document.getElementById('store-name');
    var slugPreview = document.getElementById('store-slug-preview');
    function slugify(s) {
        return (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 75) || 'warung';
    }
    nameInput?.addEventListener('input', function () {
        if (slugPreview) slugPreview.textContent = slugify(nameInput.value);
    });
    var latInput = document.getElementById('store-latitude');
    var lngInput = document.getElementById('store-longitude');
    var osmLink = document.getElementById('open-osm');
    if (!latInput || !lngInput || typeof L === 'undefined') return;

    var DEFAULT = { lat: -6.2297, lng: 106.8294, zoom: 13 }; // Tebet, Jakarta
    var lat = parseFloat(latInput.value);
    var lng = parseFloat(lngInput.value);
    var hasCoords = Number.isFinite(lat) && Number.isFinite(lng);
    var center = hasCoords ? [lat, lng] : [DEFAULT.lat, DEFAULT.lng];

    var map = L.map('store-map').setView(center, hasCoords ? 16 : DEFAULT.zoom);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    var marker = L.marker(center, { draggable: true }).addTo(map);
    if (!hasCoords) marker.bindPopup('Klik peta untuk menandai lokasi warungmu.').openPopup();

    function sync(latLng, moveMap) {
        var fixed = [Number(latLng.lat.toFixed(7)), Number(latLng.lng.toFixed(7))];
        latInput.value = fixed[0];
        lngInput.value = fixed[1];
        marker.setLatLng(latLng);
        if (moveMap) map.setView(latLng, Math.max(map.getZoom(), 15));
        if (osmLink) osmLink.href = 'https://www.openstreetmap.org/#map=16/' + fixed[0] + '/' + fixed[1];
    }

    map.on('click', function (e) { sync(e.latlng, false); });
    marker.on('dragend', function () { sync(marker.getLatLng(), false); });

    [latInput, lngInput].forEach(function (el) {
        el.addEventListener('change', function () {
            var la = parseFloat(latInput.value);
            var ln = parseFloat(lngInput.value);
            if (Number.isFinite(la) && Number.isFinite(ln)) sync({ lat: la, lng: ln }, true);
        });
    });

    var geoBtn = document.getElementById('use-my-location');
    if (geoBtn && navigator.geolocation) {
        geoBtn.addEventListener('click', function () {
            geoBtn.disabled = true;
            geoBtn.textContent = '⏳ Mencari lokasi...';
            navigator.geolocation.getCurrentPosition(function (pos) {
                sync({ lat: pos.coords.latitude, lng: pos.coords.longitude }, true);
                geoBtn.disabled = false;
                geoBtn.textContent = '📍 Gunakan lokasi saya';
            }, function () {
                geoBtn.disabled = false;
                geoBtn.textContent = '📍 Gunakan lokasi saya';
                alert('Tidak bisa mendapatkan lokasimu. Pastikan izin lokasi diaktifkan.');
            });
        });
    } else if (geoBtn) {
        geoBtn.style.display = 'none';
    }

    setTimeout(function () { map.invalidateSize(); }, 200);
})();
</script>
@endpush
