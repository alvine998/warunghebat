@extends('layouts.app')

@section('title', ($product->exists ? 'Edit' : 'Tambah') . ' Produk — Warung Hebat')

@section('bare', true)

@section('content')
<section class="pt-6 sm:pt-8 pb-10 max-w-2xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between gap-2 flex-wrap">
        <a href="{{ route('seller.products.index') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Kembali</a>
        @include('seller.store._open_toggle', ['store' => $store])
    </div>

    <h1 class="font-black tracking-tight text-3xl mt-2">{{ $product->exists ? 'Edit produk' : 'Tambah produk' }}</h1>
    <p class="text-sm font-medium text-ink-500">{{ $product->exists ? 'Perubahan akan dikirim ulang ke admin untuk verifikasi.' : 'Produk baru menunggu verifikasi admin sebelum tayang.' }}</p>

    <form id="product-form" method="POST" action="{{ $product->exists ? route('seller.products.update', $product) : route('seller.products.store') }}" enctype="multipart/form-data" class="mt-5 rounded-[28px] bg-white border border-ink-900/10 p-6 grid gap-4">
        @csrf
        @if($product->exists)
            @method('PUT')
        @endif

        <div>
            <label class="text-[13px] font-extrabold">Nama produk *</label>
            <input name="name" required maxlength="120" value="{{ old('name', $product->name) }}" placeholder="cth. Nasi Goreng Tek-Tek" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
        </div>

        <div>
            <label class="text-[13px] font-extrabold">Deskripsi</label>
            <textarea name="description" rows="3" maxlength="2000" placeholder="Bahan, porsi, level pedas..." class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition resize-y">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="text-[13px] font-extrabold">Harga Jual (Rp) *
                    <span class="group relative inline-flex align-middle ml-1">
                        <button type="button" aria-label="Apa itu Harga Jual?" class="inline-grid place-items-center w-4 h-4 rounded-full bg-ink-900/10 text-[11px] font-black text-ink-500 hover:bg-ink-900 hover:text-white transition cursor-help">?</button>
                        <span class="pointer-events-none absolute left-0 top-full z-20 mt-2 hidden w-56 rounded-2xl border border-ink-900/10 bg-ink-900 p-3 text-left text-[12px] font-semibold leading-relaxed text-white shadow-xl group-hover:block group-focus-within:block">Harga yang dibayar pembeli per pcs. Tampil di etalase warungmu. Pastikan lebih besar dari HPP agar untung.</span>
                    </span>
                </label>
                <input id="price-input" name="price" type="text" inputmode="numeric" autocomplete="off" required data-numeric data-max="1000000000" value="{{ old('price', $product->price ?? 0) }}" placeholder="0" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                @error('price')
                    <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-[13px] font-extrabold">HPP (Rp)
                    <span class="group relative inline-flex align-middle ml-1">
                        <button type="button" aria-label="Apa itu HPP?" class="inline-grid place-items-center w-4 h-4 rounded-full bg-ink-900/10 text-[11px] font-black text-ink-500 hover:bg-ink-900 hover:text-white transition cursor-help">?</button>
                        <span class="pointer-events-none absolute left-0 sm:left-auto sm:right-0 top-full z-20 mt-2 hidden w-64 rounded-2xl border border-ink-900/10 bg-ink-900 p-3 text-left text-[12px] font-semibold leading-relaxed text-white shadow-xl group-hover:block group-focus-within:block">HPP = Harga Pokok Penjualan: total modal untuk 1 pcs — bahan baku + kemasan + gas/listrik + ongkos lain dibagi jumlah pcs. Cth: modal Rp 8.000, jual Rp 12.000 → untung Rp 4.000.</span>
                    </span>
                </label>
                <input id="cost-price-input" name="cost_price" type="text" inputmode="numeric" autocomplete="off" data-numeric data-max="1000000000" value="{{ old('cost_price', $product->cost_price ?? '') }}" placeholder="cth. 8000 (kosongkan jika belum tahu)" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                @error('cost_price')
                    <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <p id="margin-hint" class="hidden rounded-2xl border px-4 py-3 text-[13px] font-bold leading-relaxed" aria-live="polite"></p>
        <p class="text-[12px] font-medium text-ink-500 -mt-2">🔒 HPP hanya terlihat oleh kamu, tidak tampil ke pembeli. Dipakai untuk menghitung perkiraan untung = Harga Jual − HPP.</p>

        <div>
            <label class="text-[13px] font-extrabold">Stok *</label>
            <input name="stock" type="text" inputmode="numeric" autocomplete="off" required data-numeric data-max="1000000" value="{{ old('stock', $product->stock ?? 0) }}" placeholder="0" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            @error('stock')
                <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="barcode-input" class="text-[13px] font-extrabold">Barcode <span class="font-semibold text-ink-500">(opsional)</span>
                <span class="group relative inline-flex align-middle ml-1">
                    <button type="button" aria-label="Apa itu barcode?" class="inline-grid place-items-center w-4 h-4 rounded-full bg-ink-900/10 text-[11px] font-black text-ink-500 hover:bg-ink-900 hover:text-white transition cursor-help">?</button>
                    <span class="pointer-events-none absolute left-0 top-full z-20 mt-2 hidden w-64 rounded-2xl border border-ink-900/10 bg-ink-900 p-3 text-left text-[12px] font-semibold leading-relaxed text-white shadow-xl group-hover:block group-focus-within:block">Kode di kemasan produk (cth. 8991234567890). Dipakai untuk scan cepat saat Catat Transaksi Langsung. Boleh diketik manual atau tekan Scan.</span>
                </span>
            </label>
            <div class="mt-1.5 flex gap-2">
                <input id="barcode-input" name="barcode" type="text" inputmode="text" autocomplete="off" maxlength="64" value="{{ old('barcode', $product->barcode ?? '') }}" placeholder="cth. 8991234567890 (kosongkan jika tidak ada)" class="min-w-0 flex-1 rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                <button type="button" id="barcode-scan-btn" class="shrink-0 rounded-2xl bg-ink-900 px-4 py-3 text-[13px] font-extrabold text-white hover:bg-brand-600 transition">📷 Scan</button>
            </div>
            @error('barcode')
                <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @php($promoEnabled = old('has_promo', $product->discount_price ? 'yes' : 'no') === 'yes')
        <div class="rounded-2xl bg-brand-50 border border-brand-200 p-4 grid gap-3">
            <p class="text-[13px] font-extrabold">⚡ Gunakan harga promo?</p>
            <div class="flex gap-4 text-sm font-semibold">
                <label class="inline-flex items-center gap-2"><input type="radio" name="has_promo" value="yes" @checked($promoEnabled)> Ya</label>
                <label class="inline-flex items-center gap-2"><input type="radio" name="has_promo" value="no" @checked(!$promoEnabled)> Tidak</label>
            </div>
            <div id="promo-fields" class="grid gap-3" @if(!$promoEnabled) hidden @endif>
                @if($product->exists && $product->hasActivePromo())
                    <span class="w-fit text-[11px] font-extrabold text-white bg-brand-500 rounded-full px-3 py-1">Promo aktif −{{ $product->discountPercent() }}%</span>
                @endif
                <div>
                    <label class="text-[13px] font-extrabold">Harga promo (Rp) <span class="font-semibold text-ink-500">— harus lebih kecil dari harga jual</span></label>
                    <input name="discount_price" type="text" inputmode="numeric" autocomplete="off" data-numeric data-max="1000000000" value="{{ old('discount_price', $product->discount_price ?? '') }}" placeholder="cth. 12000" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    @error('discount_price')
                        <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-[13px] font-extrabold">Mulai promo <span class="font-semibold text-ink-500">(opsional)</span></label>
                        <input name="promo_starts_at" type="datetime-local" value="{{ old('promo_starts_at', $product->promo_starts_at?->format('Y-m-d\\TH:i')) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    </div>
                    <div>
                        <label class="text-[13px] font-extrabold">Berakhir <span class="font-semibold text-ink-500">(opsional)</span></label>
                        <input name="promo_ends_at" type="datetime-local" value="{{ old('promo_ends_at', $product->promo_ends_at?->format('Y-m-d\\TH:i')) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                        @error('promo_ends_at')
                            <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="text-[12px] font-medium text-ink-500">Tanpa tanggal = promo jalan terus sampai kamu hapus harganya. Harga promo ikut verifikasi admin seperti harga jual.</p>
            </div>
        </div>

        <div>
            <label class="text-[13px] font-extrabold">Kategori *</label>
            <select name="category_id" required class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-semibold outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected((int) old('category_id', $product->category_id) === (int) $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('category_id')
                <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @php($brandEnabled = old('has_brand', $product->brand_id || old('new_brand') ? 'yes' : 'no') === 'yes')
        <div class="rounded-2xl bg-cream-50 border border-ink-900/10 p-4 grid gap-3">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <p class="text-[13px] font-extrabold">🏷️ Gunakan brand?</p>
                <a href="{{ route('seller.brands.index') }}" class="text-[12px] font-extrabold text-brand-600 hover:text-brand-700 underline underline-offset-4">Kelola brand →</a>
            </div>
            <div class="flex gap-4 text-sm font-semibold">
                <label class="inline-flex items-center gap-2"><input type="radio" name="has_brand" value="yes" @checked($brandEnabled)> Ya</label>
                <label class="inline-flex items-center gap-2"><input type="radio" name="has_brand" value="no" @checked(!$brandEnabled)> Tidak</label>
            </div>
            <div id="brand-fields" class="grid gap-3" @if(!$brandEnabled) hidden @endif>
                <div>
                    <label for="brand_id" class="text-[13px] font-extrabold">Pilih brand</label>
                    <select id="brand_id" name="brand_id" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-semibold outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                        <option value="">— Pilih brand atau isi brand baru —</option>
                        @foreach($brands as $b)
                        <option value="{{ $b->id }}" @selected((int) old('brand_id', $product->brand_id) === (int) $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @error('brand_id')
                        <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="new_brand" class="text-[13px] font-extrabold">Atau tambah brand baru</label>
                    <input id="new_brand" name="new_brand" maxlength="80" value="{{ old('new_brand') }}" placeholder="cth. Indomie (kosongkan jika sudah pilih di atas)" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    <p class="mt-1 text-[12px] font-medium text-ink-500">Diketik di sini langsung tersimpan ke master brand warungmu dan dipakai produk ini.</p>
                    @error('new_brand')
                        <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div>
            <label class="text-[13px] font-extrabold">Foto produk * <span class="font-semibold text-ink-500">(1 foto, JPG/PNG/WebP — otomatis dikompres di bawah 1MB)</span></label>
            @if($product->exists && $product->image_path)
                <div class="mt-1.5 flex items-center gap-3">
                    <img src="{{ $product->image_url }}" alt="Foto {{ $product->name }}" loading="lazy" decoding="async" class="w-20 h-20 rounded-2xl object-cover border border-ink-900/10">
                    <p class="text-xs font-semibold text-ink-500">Foto saat ini. Pilih foto baru di bawah untuk mengganti (tetap 1 foto).</p>
                </div>
            @endif
            <div id="photo-preview-wrap" class="hidden mt-1.5 flex items-center gap-3">
                <img id="photo-preview" src="" alt="Pratinjau foto produk" decoding="async" class="w-20 h-20 rounded-2xl object-cover border border-ink-900/10">
                <div class="min-w-0">
                    <p id="photo-info" class="text-xs font-bold text-ink-900 truncate"></p>
                    <button type="button" id="photo-remove" class="mt-1 text-xs font-extrabold text-red-600 hover:text-red-700">Hapus foto</button>
                </div>
            </div>
            <div class="mt-1.5 grid grid-cols-2 gap-2">
                <button type="button" id="photo-camera-btn" class="rounded-2xl border border-ink-900/15 bg-ink-900 px-4 py-3 text-[14px] font-extrabold text-white hover:bg-brand-600 transition">📷 Ambil Foto</button>
                <button type="button" id="photo-gallery-btn" class="rounded-2xl border border-ink-900/15 bg-white px-4 py-3 text-[14px] font-extrabold text-ink-900 hover:border-brand-500 transition">🖼️ Galeri</button>
            </div>
            <input id="photo-camera-input" type="file" accept="image/jpeg,image/png,image/webp" capture="environment" class="hidden" tabindex="-1" aria-hidden="true">
            <input id="photo-gallery-input" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" tabindex="-1" aria-hidden="true">
            <input id="product-image-input" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-required="{{ ($product->exists && $product->image_path) ? '0' : '1' }}" class="hidden" tabindex="-1" aria-hidden="true">
            <p id="photo-status" class="hidden mt-1.5 rounded-xl p-3 text-[13px] font-bold" aria-live="polite"></p>
            @error('image')
                <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button id="product-submit-btn" type="submit" class="mt-1 w-full py-3.5 rounded-2xl bg-ink-900 text-white font-extrabold text-[15px] hover:bg-brand-600 transition disabled:opacity-60 disabled:cursor-wait inline-flex items-center justify-center gap-2">
            <svg id="product-submit-spinner" class="hidden w-5 h-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
            <span id="product-submit-label">{{ $product->exists ? 'Simpan & kirim verifikasi ulang' : 'Simpan & kirim verifikasi' }}</span>
        </button>

        {{-- Loading progress: compression (5→65%) + upload (65→100%) dengan persen real dari XHR. --}}
        <div id="product-progress" class="hidden rounded-2xl border border-brand-200 bg-brand-50 p-4" aria-live="polite">
            <div class="flex items-center justify-between gap-2 text-[13px] font-extrabold text-ink-900">
                <p id="product-progress-label" class="truncate">Menyiapkan data…</p>
                <p id="product-progress-pct" class="tabular-nums shrink-0">0%</p>
            </div>
            <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-ink-900/10" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="product-progress-track">
                <div id="product-progress-bar" class="h-full w-full origin-left scale-x-0 rounded-full bg-brand-500 transition-transform duration-150" style="transform:scaleX(0)"></div>
            </div>
            <p class="mt-1.5 text-[12px] font-medium text-ink-500">Jangan tutup halaman ini sampai selesai.</p>
        </div>
        <div id="product-error-box" class="hidden rounded-2xl border border-red-200 bg-red-50 p-4 text-[13px] font-bold text-red-700" aria-live="assertive"></div>
    </form>
</section>

@include('components.barcode-scanner')
@include('components.camera-capture')

<script>
(function () {
    const promoFields = document.getElementById('promo-fields');
    const brandFields = document.getElementById('brand-fields');
    const productForm = document.querySelector('form[action*="seller/products"]');

    function toggleFields(name, container) {
        const enabled = document.querySelector(`input[name="${name}"]:checked`)?.value === 'yes';
        container.hidden = !enabled;
        container.querySelectorAll('input, select').forEach((field) => {
            field.disabled = !enabled;
        });
    }

    document.querySelectorAll('input[name="has_promo"]').forEach((radio) => {
        radio.addEventListener('change', () => toggleFields('has_promo', promoFields));
    });
    document.querySelectorAll('input[name="has_brand"]').forEach((radio) => {
        radio.addEventListener('change', () => toggleFields('has_brand', brandFields));
    });
    toggleFields('has_promo', promoFields);
    toggleFields('has_brand', brandFields);

    productForm?.addEventListener('submit', () => {
        if (document.querySelector('input[name="has_promo"]:checked')?.value === 'no') {
            promoFields.querySelectorAll('input').forEach((field) => field.value = '');
        }

        if (document.querySelector('input[name="has_brand"]:checked')?.value === 'no') {
            brandFields.querySelectorAll('input, select').forEach((field) => field.value = '');
        }
    });
})();
(function () {
    const barcodeInput = document.getElementById('barcode-input');
    document.getElementById('barcode-scan-btn')?.addEventListener('click', () => {
        if (typeof window.openBarcodeScanner !== 'function') return;
        window.openBarcodeScanner((code) => {
            if (barcodeInput) {
                barcodeInput.value = code;
                barcodeInput.focus();
            }
        });
    });
})();
(function () {
    const priceInput = document.getElementById('price-input');
    const costInput = document.getElementById('cost-price-input');
    const hint = document.getElementById('margin-hint');
    if (!priceInput || !costInput || !hint) return;

    const digits = (v) => parseInt(String(v || '').replace(/\D/g, ''), 10);
    const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');

    function render() {
        const price = digits(priceInput.value);
        const cost = digits(costInput.value);

        if (Number.isNaN(price) && Number.isNaN(cost)) {
            hint.classList.add('hidden');
            return;
        }
        if (Number.isNaN(price) || Number.isNaN(cost)) {
            hint.classList.remove('hidden', 'border-red-200', 'bg-red-50', 'text-red-700', 'border-leaf-200', 'bg-leaf-50', 'text-leaf-700', 'border-ink-900/10', 'bg-cream-50', 'text-ink-500');
            hint.classList.add('border-ink-900/10', 'bg-cream-50', 'text-ink-500');
            hint.textContent = 'Isi Harga Jual & HPP untuk melihat perkiraan untung per pcs.';
            return;
        }

        const profit = price - cost;
        const margin = price > 0 ? Math.round(profit / price * 100) : null;

        hint.classList.remove('hidden', 'border-red-200', 'bg-red-50', 'text-red-700', 'border-leaf-200', 'bg-leaf-50', 'text-leaf-700', 'border-ink-900/10', 'bg-cream-50', 'text-ink-500');

        if (profit < 0) {
            hint.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
            hint.textContent = '⚠️ HPP lebih besar dari Harga Jual — kamu rugi ' + rupiah(Math.abs(profit)) + ' per pcs. Cek lagi modalnya.';
        } else {
            hint.classList.add('border-leaf-200', 'bg-leaf-50', 'text-leaf-700');
            hint.textContent = '💰 Perkiraan untung: ' + rupiah(profit) + ' per pcs' + (margin !== null ? ' (' + margin + '%)' : '') + '.';
        }
    }

    priceInput.addEventListener('input', render);
    costInput.addEventListener('input', render);
    render();
})();
</script>

<script>
(function () {
    const productForm = document.getElementById('product-form') || document.querySelector('form[enctype="multipart/form-data"]');
    const cameraBtn = document.getElementById('photo-camera-btn');
    const galleryBtn = document.getElementById('photo-gallery-btn');
    const cameraInput = document.getElementById('photo-camera-input');
    const galleryInput = document.getElementById('photo-gallery-input');
    const imageInput = document.getElementById('product-image-input');
    const previewWrap = document.getElementById('photo-preview-wrap');
    const previewImg = document.getElementById('photo-preview');
    const photoInfo = document.getElementById('photo-info');
    const removeBtn = document.getElementById('photo-remove');
    const statusBox = document.getElementById('photo-status');
    const submitBtn = document.getElementById('product-submit-btn');
    const submitLabelEl = document.getElementById('product-submit-label');
    const submitSpinner = document.getElementById('product-submit-spinner');
    const submitLabel = submitLabelEl?.textContent ?? submitBtn?.textContent ?? 'Simpan';
    const progressBox = document.getElementById('product-progress');
    const progressBar = document.getElementById('product-progress-bar');
    const progressLabel = document.getElementById('product-progress-label');
    const progressPct = document.getElementById('product-progress-pct');
    const progressTrack = document.getElementById('product-progress-track');
    const errorBox = document.getElementById('product-error-box');
    const imageRequired = imageInput?.dataset.required === '1';
    const maximumImageBytes = 950000;
    // Fast path: satu kali resize ke sisi panjang 1600px + 4 level kualitas.
    // Dulu 4 dimensi x 6 kualitas (24x toBlob) — kini maks 4x toBlob, ~6x lebih cepat di HP kentang.
    const maxLongEdge = 1600;
    const qualities = [0.82, 0.7, 0.58, 0.46];
    let isProcessing = false;
    let isSubmitting = false;
    let pendingSubmit = false;
    let rafQueued = false;

    if (!productForm || !cameraBtn || !galleryBtn || !cameraInput || !galleryInput || !imageInput) return;

    const yieldToUI = () => new Promise((resolve) => setTimeout(resolve, 0));

    function setProgress(pct, label) {
        const clamped = Math.max(0, Math.min(100, Math.round(pct)));
        if (progressBox) progressBox.classList.remove('hidden');
        if (progressLabel && label) progressLabel.textContent = label;
        if (progressPct) progressPct.textContent = clamped + '%';
        if (progressTrack) progressTrack.setAttribute('aria-valuenow', String(clamped));
        if (!progressBar) return;
        if (rafQueued) return;
        rafQueued = true;
        requestAnimationFrame(() => {
            rafQueued = false;
            progressBar.style.transform = `scaleX(${clamped / 100})`;
        });
    }

    function hideProgress() {
        progressBox?.classList.add('hidden');
    }

    function showErrors(messages) {
        if (!errorBox) return;
        const list = (Array.isArray(messages) ? messages : [messages]).filter(Boolean);
        if (!list.length) {
            errorBox.classList.add('hidden');
            errorBox.textContent = '';
            return;
        }
        errorBox.innerHTML = '';
        const title = document.createElement('p');
        title.textContent = 'Belum bisa disimpan:';
        errorBox.appendChild(title);
        const ul = document.createElement('ul');
        ul.className = 'mt-1 list-disc pl-5 font-semibold';
        list.slice(0, 5).forEach((msg) => {
            const li = document.createElement('li');
            li.textContent = msg;
            ul.appendChild(li);
        });
        errorBox.appendChild(ul);
        errorBox.classList.remove('hidden');
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function hideErrors() {
        if (!errorBox) return;
        errorBox.classList.add('hidden');
        errorBox.textContent = '';
    }

    function setSubmitBusy(busy, label) {
        if (!submitBtn) return;
        submitBtn.disabled = busy;
        submitBtn.setAttribute('aria-busy', busy ? 'true' : 'false');
        submitSpinner?.classList.toggle('hidden', !busy);
        if (submitLabelEl) submitLabelEl.textContent = busy ? (label || 'Mengompres foto…') : submitLabel;
        else submitBtn.textContent = busy ? (label || 'Mengompres foto…') : submitLabel;
    }

    const kilobytes = (bytes) => Math.max(1, Math.round(bytes / 1024)) + ' KB';

    function showPhotoStatus(message, isError = false) {
        if (!statusBox) return;
        statusBox.textContent = message;
        statusBox.classList.remove('hidden', 'bg-leaf-50', 'text-leaf-700', 'bg-red-50', 'text-red-700');
        statusBox.classList.add(isError ? 'bg-red-50' : 'bg-leaf-50', isError ? 'text-red-700' : 'text-leaf-700');
    }

    function hidePhotoStatus() {
        if (!statusBox) return;
        statusBox.classList.add('hidden');
        statusBox.textContent = '';
    }

    function decodeImage(file) {
        // createImageBitmap: decode off-main-thread + lebih hemat memori di HP low-end.
        if (typeof createImageBitmap === 'function') {
            return createImageBitmap(file).catch(() => loadViaImage(file));
        }
        return loadViaImage(file);
    }

    function loadViaImage(file) {
        return new Promise((resolve, reject) => {
            const image = new Image();
            const objectUrl = URL.createObjectURL(file);
            image.onload = () => {
                URL.revokeObjectURL(objectUrl);
                resolve(image);
            };
            image.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Foto tidak dapat dibaca. Coba pilih foto lain.'));
            };
            image.src = objectUrl;
        });
    }

    function loadImage(file) {
        return decodeImage(file);
    }

    function canvasBlob(canvas, quality) {
        return new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
    }

    function drawCover(context, source, sourceWidth, sourceHeight, targetWidth, targetHeight) {
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, targetWidth, targetHeight);
        context.drawImage(source, 0, 0, sourceWidth, sourceHeight, 0, 0, targetWidth, targetHeight);
    }

    async function compressImage(file, onStep) {
        if (file.size <= maximumImageBytes) {
            return { file, compressed: false };
        }

        const source = await decodeImage(file);
        const sourceWidth = source.width || source.videoWidth || 0;
        const sourceHeight = source.height || source.videoHeight || 0;
        if (!sourceWidth || !sourceHeight) {
            if (typeof source.close === 'function') source.close();
            throw new Error('Foto tidak dapat dibaca. Coba pilih foto lain.');
        }
        const filename = file.name.replace(/\.[^.]+$/, '') || 'foto-produk';
        const scale = Math.min(1, maxLongEdge / Math.max(sourceWidth, sourceHeight));
        const targetWidth = Math.max(1, Math.round(sourceWidth * scale));
        const targetHeight = Math.max(1, Math.round(sourceHeight * scale));

        const canvas = document.createElement('canvas');
        canvas.width = targetWidth;
        canvas.height = targetHeight;
        const context = canvas.getContext('2d', { alpha: false });
        if (!context) {
            if (typeof source.close === 'function') source.close();
            return { file, compressed: false };
        }
        drawCover(context, source, sourceWidth, sourceHeight, targetWidth, targetHeight);
        if (typeof source.close === 'function') source.close();

        for (let i = 0; i < qualities.length; i++) {
            onStep?.(i, qualities.length);
            await yieldToUI();
            const blob = await canvasBlob(canvas, qualities[i]);
            if (blob && blob.size <= maximumImageBytes) {
                canvas.width = 0;
                canvas.height = 0;
                return { file: new File([blob], `${filename}.jpg`, { type: 'image/jpeg', lastModified: Date.now() }), compressed: true };
            }
        }

        canvas.width = 0;
        canvas.height = 0;
        throw new Error(`Foto ${file.name} tetap lebih dari 1MB setelah dikompres. Coba ambil ulang dengan resolusi lebih rendah.`);
    }

    function setImageFile(file, compressed) {
        const transfer = new DataTransfer();
        transfer.items.add(file);
        imageInput.files = transfer.files;

        if (previewImg.dataset.objectUrl) {
            URL.revokeObjectURL(previewImg.dataset.objectUrl);
        }
        const objectUrl = URL.createObjectURL(file);
        previewImg.dataset.objectUrl = objectUrl;
        previewImg.src = objectUrl;
        photoInfo.textContent = `${file.name} • ${kilobytes(file.size)}${compressed ? ' • sudah dikompres' : ''}`;
        previewWrap.classList.remove('hidden');
        previewWrap.classList.add('flex');
    }

    function clearImageFile() {
        imageInput.value = '';
        if (previewImg.dataset.objectUrl) {
            URL.revokeObjectURL(previewImg.dataset.objectUrl);
            delete previewImg.dataset.objectUrl;
        }
        previewImg.src = '';
        previewWrap.classList.add('hidden');
        previewWrap.classList.remove('flex');
        hidePhotoStatus();
    }

    function setPhotoButtonsDisabled(disabled) {
        cameraBtn.disabled = disabled;
        galleryBtn.disabled = disabled;
    }

    async function handlePickedFile(file) {
        if (!file || isSubmitting) return;

        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
            showPhotoStatus('Format foto harus JPG, PNG, atau WebP.', true);
            return;
        }

        setPhotoButtonsDisabled(true);
        isProcessing = true;
        setSubmitBusy(true, 'Memproses foto…');
        setProgress(5, `Memproses ${file.name}…`);
        showPhotoStatus(`Memproses ${file.name}…`);

        try {
            const { file: finalFile, compressed } = await compressImage(file, (i, total) => {
                setProgress(5 + ((i + 1) / total) * 25, `Mengompres foto… (${i + 1}/${total})`);
            });
            setImageFile(finalFile, compressed);
            hidePhotoStatus();
            hideProgress();

            if (pendingSubmit) {
                pendingSubmit = false;
                submitProduct();
            }
        } catch (error) {
            pendingSubmit = false;
            hideProgress();
            showPhotoStatus(error.message || 'Foto gagal diproses. Coba pilih foto lain.', true);
        } finally {
            setPhotoButtonsDisabled(false);
            isProcessing = false;
            if (!isSubmitting) setSubmitBusy(false);
            cameraInput.value = '';
            galleryInput.value = '';
        }
    }

    function collectFormData() {
        // Kirim digit mentah utk input rupiah (15.000 → 15000) agar validasi integer lolos.
        const data = new FormData(productForm);
        productForm.querySelectorAll('[data-numeric]').forEach((input) => {
            if (input.name) data.set(input.name, (input.value || '').replace(/\D/g, ''));
        });
        if (imageInput.files[0]) {
            data.set('image', imageInput.files[0], imageInput.files[0].name);
        }
        return data;
    }

    function uploadWithProgress(url, method, data, onUploadProgress) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token);
            xhr.responseType = 'json';
            xhr.timeout = 120000;

            xhr.upload.onprogress = (event) => {
                if (event.lengthComputable) onUploadProgress(event.loaded / event.total);
                else onUploadProgress(null);
            };
            xhr.onload = () => resolve(xhr);
            xhr.onerror = () => reject(new Error('Koneksi terputus saat mengunggah.'));
            xhr.ontimeout = () => reject(new Error('Unggahan terlalu lama (timeout). Coba foto lebih kecil atau koneksi lebih stabil.'));

            // Laravel: PUT via POST + _method spoof agar file upload tetap terbaca.
            if (method !== 'POST') data.set('_method', method);
            xhr.send(data);
        });
    }

    function fallbackNativeSubmit() {
        isSubmitting = false;
        setSubmitBusy(false);
        hideProgress();
        productForm.submit();
    }

    async function submitProduct() {
        if (isSubmitting) return;

        if (!imageInput.files.length) {
            if (!imageRequired) return;
            showPhotoStatus('Pilih dulu foto produk — ketuk “Ambil Foto” atau “Galeri”.', true);
            document.getElementById('photo-camera-btn')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        isSubmitting = true;
        hideErrors();
        hidePhotoStatus();
        setPhotoButtonsDisabled(true);
        setSubmitBusy(true, 'Menyiapkan data…');
        setProgress(3, 'Menyiapkan data…');
        await yieldToUI();

        try {
            // Foto masih > 950KB (mis. dipilih sebelum kompresi sempat jalan) → kompres dulu.
            if (imageInput.files[0].size > maximumImageBytes) {
                setSubmitBusy(true, 'Mengompres foto…');
                const { file: finalFile } = await compressImage(imageInput.files[0], (i, total) => {
                    setProgress(5 + ((i + 1) / total) * 55, `Mengompres foto… (${i + 1}/${total})`);
                });
                setImageFile(finalFile, true);
            }

            setSubmitBusy(true, 'Mengunggah… 0%');
            setProgress(65, 'Mengunggah… 0%');

            const method = productForm.querySelector('input[name="_method"]')?.value?.toUpperCase() || 'POST';
            const xhr = await uploadWithProgress(productForm.action, method, collectFormData(), (ratio) => {
                if (ratio === null) {
                    setSubmitBusy(true, 'Mengunggah…');
                    setProgress(80, 'Mengunggah…');
                    return;
                }
                const pct = 65 + ratio * 32;
                setProgress(pct, `Mengunggah… ${Math.round(ratio * 100)}%`);
                setSubmitBusy(true, `Mengunggah… ${Math.round(ratio * 100)}%`);
            });

            const status = xhr.status;
            const body = xhr.response ?? (typeof xhr.responseText === 'string' && xhr.responseText ? JSON.parse(xhr.responseText) : null);

            if (status >= 200 && status < 300) {
                const fallbackUrl = document.querySelector('a[href*="seller/products"]')?.href || '/seller/products';
                const redirectUrl = body?.redirect_url || fallbackUrl;
                setProgress(100, 'Berhasil! Membuka daftar produk…');
                setSubmitBusy(true, 'Berhasil! Membuka daftar produk…');
                window.location.assign(redirectUrl);
                return;
            }

            if (status === 422) {
                const errors = body?.errors ? Object.values(body.errors).flat() : [body?.message || 'Periksa lagi isian form.'];
                isSubmitting = false;
                setPhotoButtonsDisabled(false);
                setSubmitBusy(false);
                hideProgress();
                // app.js mengubah input rupiah jadi digit mentah saat submit;
                // picu format ulang agar tetap tampil 15.000, bukan 15000.
                productForm.querySelectorAll('[data-numeric]').forEach((input) => {
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                });
                showErrors(errors);
                return;
            }

            if (status === 419) {
                showErrors(['Sesi kedaluwarsa. Halaman akan dimuat ulang — coba simpan lagi.']);
                setTimeout(() => window.location.reload(), 1500);
                return;
            }

            throw new Error(body?.message || `Gagal menyimpan (kode ${status}).`);
        } catch (error) {
            // Jaringan gagal total / JSON rusak / timeout: fallback ke submit biasa
            // agar user tidak pernah stuck (server tetap menangani redirect + flash).
            if (error instanceof SyntaxError || /Koneksi|timeout/i.test(error.message || '')) {
                // Coba native sekali; kalau itu pun gagal, tampilkan pesan.
                try {
                    fallbackNativeSubmit();
                    return;
                } catch { /* tampilkan pesan di bawah */ }
            }
            isSubmitting = false;
            setPhotoButtonsDisabled(false);
            setSubmitBusy(false);
            hideProgress();
            productForm.querySelectorAll('[data-numeric]').forEach((input) => {
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
            showErrors([error.message || 'Foto gagal dikompres. Coba pilih foto lain.']);
        }
    }

    // Di HP tetap buka aplikasi kamera native (capture="environment"); modal
    // getUserMedia hanya untuk desktop/webcam.
    const prefersNativeCamera = () => window.matchMedia?.('(pointer: coarse)').matches;

    cameraBtn.addEventListener('click', async () => {
        if (isSubmitting) return;
        if (prefersNativeCamera() || typeof window.openCameraCapture !== 'function') {
            cameraInput.click();
            return;
        }
        const file = await window.openCameraCapture();
        if (file) handlePickedFile(file);
        else cameraInput.click();
    });
    galleryBtn.addEventListener('click', () => galleryInput.click());
    cameraInput.addEventListener('change', () => handlePickedFile(cameraInput.files[0]));
    galleryInput.addEventListener('change', () => handlePickedFile(galleryInput.files[0]));
    removeBtn?.addEventListener('click', clearImageFile);

    productForm.addEventListener('submit', (event) => {
        event.preventDefault();

        if (isProcessing) {
            pendingSubmit = true;
            showPhotoStatus('Masih mengompres foto — tunggu sebentar, form terkirim otomatis.');
            setProgress(30, 'Masih mengompres foto — tunggu sebentar…');
            return;
        }

        if (isSubmitting) {
            return;
        }

        submitProduct();
    });
})();
</script>
@endsection
