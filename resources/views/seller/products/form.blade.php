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

    <form method="POST" action="{{ $product->exists ? route('seller.products.update', $product) : route('seller.products.store') }}" enctype="multipart/form-data" class="mt-5 rounded-[28px] bg-white border border-ink-900/10 p-6 grid gap-4">
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

        <div class="rounded-2xl bg-brand-50 border border-brand-200 p-4 grid gap-3">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <p class="text-[13px] font-extrabold">⚡ Harga promo <span class="font-semibold text-ink-500">(opsional)</span></p>
                @if($product->exists && $product->hasActivePromo())
                    <span class="text-[11px] font-extrabold text-white bg-brand-500 rounded-full px-3 py-1">Promo aktif −{{ $product->discountPercent() }}%</span>
                @endif
            </div>
            <div>
                <label class="text-[13px] font-extrabold">Harga promo (Rp) <span class="font-semibold text-ink-500">— harus lebih kecil dari harga jual</span></label>
                <input name="discount_price" type="text" inputmode="numeric" autocomplete="off" data-numeric data-max="1000000000" value="{{ old('discount_price', $product->discount_price ?? '') }}" placeholder="cth. 12000 (kosongkan = tanpa promo)" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                @error('discount_price')
                    <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-[13px] font-extrabold">Mulai promo <span class="font-semibold text-ink-500">(opsional)</span></label>
                    <input name="promo_starts_at" type="datetime-local" value="{{ old('promo_starts_at', $product->promo_starts_at?->format('Y-m-d\TH:i')) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                </div>
                <div>
                    <label class="text-[13px] font-extrabold">Berakhir <span class="font-semibold text-ink-500">(opsional)</span></label>
                    <input name="promo_ends_at" type="datetime-local" value="{{ old('promo_ends_at', $product->promo_ends_at?->format('Y-m-d\TH:i')) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    @error('promo_ends_at')
                        <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="text-[12px] font-medium text-ink-500">Tanpa tanggal = promo jalan terus sampai kamu hapus harganya. Harga promo ikut verifikasi admin seperti harga jual.</p>
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

        <div class="rounded-2xl bg-cream-50 border border-ink-900/10 p-4 grid gap-3">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <p class="text-[13px] font-extrabold">🏷️ Brand <span class="font-semibold text-ink-500">(opsional — milik warungmu)</span></p>
                <a href="{{ route('seller.brands.index') }}" class="text-[12px] font-extrabold text-brand-600 hover:text-brand-700 underline underline-offset-4">Kelola brand →</a>
            </div>
            <div>
                <label for="brand_id" class="text-[13px] font-extrabold">Pilih brand</label>
                <select id="brand_id" name="brand_id" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-semibold outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    <option value="">— Tanpa brand —</option>
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

        <div>
            <label class="text-[13px] font-extrabold">Foto produk * <span class="font-semibold text-ink-500">(1 foto, JPG/PNG/WebP, maks 2MB)</span></label>
            @if($product->exists && $product->image_path)
                <div class="mt-1.5 flex items-center gap-3">
                    <img src="{{ $product->image_url }}" alt="Foto {{ $product->name }}" class="w-20 h-20 rounded-2xl object-cover border border-ink-900/10">
                    <p class="text-xs font-semibold text-ink-500">Foto saat ini. Unggah file baru untuk mengganti (tetap 1 foto).</p>
                </div>
            @endif
            <input name="image" type="file" accept="image/jpeg,image/png,image/webp" {{ $product->exists && $product->image_path ? '' : 'required' }} class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[14px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition file:mr-3 file:rounded-xl file:border-0 file:bg-ink-900 file:text-white file:text-[13px] file:font-extrabold file:px-4 file:py-2">
        </div>

        <button class="mt-1 w-full py-3.5 rounded-2xl bg-ink-900 text-white font-extrabold text-[15px] hover:bg-brand-600 transition">{{ $product->exists ? 'Simpan & kirim verifikasi ulang' : 'Simpan & kirim verifikasi' }}</button>
    </form>
</section>

@include('components.barcode-scanner')

<script>
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
@endsection
