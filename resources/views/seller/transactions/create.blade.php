@extends('layouts.app')

@section('title', 'Catat Transaksi Langsung — Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-10 sm:pb-14 max-w-4xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between gap-2 flex-wrap">
        <a href="{{ route('seller.transactions.index') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Transaksi</a>
        <a href="{{ route('dashboard') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">Dashboard</a>
    </div>

    <p class="mt-4 text-[11px] font-extrabold tracking-[0.2em] text-leaf-700">🏪 {{ $store->name }}</p>
    <h1 class="mt-1 font-black tracking-tight text-2xl sm:text-3xl">Catat transaksi langsung</h1>
    <p class="mt-1 text-[13px] font-medium text-ink-500">Total dihitung dari harga produk, stok langsung berkurang. Transaksi ini tidak masuk saldo platform.</p>

    @if($errors->any())
        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('seller.transactions.store') }}" class="mt-5 grid gap-3.5">
        @csrf
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6">
            <p class="font-extrabold">Data pembeli</p>
            <div class="mt-3 grid gap-3">
                <label class="grid gap-1.5">
                    <span class="text-[13px] font-bold">Jenis pembeli *</span>
                    <select name="buyer_type" id="buyer_type" required class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15">
                        <option value="registered" @selected(old('buyer_type') === 'registered')>Pengguna terdaftar</option>
                        <option value="walk_in" @selected(old('buyer_type', 'walk_in') === 'walk_in')>Pembeli langsung / tanpa akun</option>
                    </select>
                </label>
                <div id="registered_buyer" class="grid gap-1.5">
                    <label for="buyer_identifier" class="text-[13px] font-bold">Email atau nomor HP akun *</label>
                    <input id="buyer_identifier" name="buyer_identifier" value="{{ old('buyer_identifier') }}" placeholder="pembeli@email.com atau 08..." class="w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-sm font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15">
                    <p class="text-xs font-medium text-ink-500">Akun harus terdaftar sebagai pembeli.</p>
                </div>
                <div id="walk_in_buyer" class="grid gap-3 sm:grid-cols-2">
                    <label class="grid gap-1.5">
                        <span class="text-[13px] font-bold">Nama pembeli *</span>
                        <input name="buyer_name" value="{{ old('buyer_name') }}" maxlength="255" placeholder="Nama pembeli" class="w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-sm font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15">
                    </label>
                    <label class="grid gap-1.5">
                        <span class="text-[13px] font-bold">Email (opsional)</span>
                        <input name="buyer_email" type="email" value="{{ old('buyer_email') }}" maxlength="255" placeholder="pembeli@email.com" class="w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-sm font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15">
                    </label>
                    <label class="grid gap-1.5">
                        <span class="text-[13px] font-bold">Nomor HP (opsional)</span>
                        <input name="buyer_phone" value="{{ old('buyer_phone') }}" maxlength="30" placeholder="08..." class="w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-sm font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15">
                    </label>
                </div>
            </div>
        </div>

        <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <div>
                    <p class="font-extrabold">Produk terjual</p>
                    <p class="text-xs font-medium text-ink-500">Harga promo aktif akan digunakan.</p>
                </div>
                <button type="button" id="add_item" class="min-h-10 rounded-full border border-ink-900/15 px-4 text-[13px] font-extrabold hover:bg-ink-900 hover:text-white transition">+ Tambah produk</button>
            </div>
            @if($products->isEmpty())
                <div class="mt-3 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm font-semibold text-amber-900">Belum ada produk yang disetujui dan tersedia. Periksa stok atau status produkmu.</div>
            @else
                <div id="items" class="mt-3 grid gap-2.5">
                    <div class="item-row grid gap-2 sm:grid-cols-[1fr_130px_auto] sm:items-end rounded-2xl border border-ink-900/10 p-3">
                        <label class="grid gap-1">
                            <span class="text-xs font-bold">Produk</span>
                            <select name="items[0][product_id]" required class="product-select min-w-0 rounded-xl border border-ink-900/15 bg-white px-3 py-2.5 text-sm font-semibold">
                                <option value="">Pilih produk</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->effectivePrice() }}" @selected((string) old('items.0.product_id') === (string) $product->id)>{{ $product->name }} — Rp {{ number_format($product->effectivePrice(), 0, ',', '.') }} (stok {{ $product->stock }})</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="grid gap-1">
                            <span class="text-xs font-bold">Jumlah</span>
                            <input name="items[0][qty]" type="number" min="1" max="1000000" value="{{ old('items.0.qty', 1) }}" required inputmode="numeric" class="qty-input rounded-xl border border-ink-900/15 px-3 py-2.5 text-sm font-semibold">
                        </label>
                        <button type="button" class="remove-item min-h-10 rounded-full px-3 text-xs font-extrabold text-red-700 hover:bg-red-50" aria-label="Hapus produk">Hapus</button>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between gap-3 rounded-2xl bg-cream-100 p-4">
                    <span class="text-sm font-extrabold">Perkiraan total</span>
                    <span id="total" class="font-black text-lg">Rp 0</span>
                </div>
            @endif
        </div>

        @if($products->isNotEmpty())
            <button class="min-h-12 rounded-full bg-brand-500 hover:bg-brand-600 text-white font-extrabold transition">Simpan transaksi</button>
        @endif
    </form>
</section>

@if($products->isNotEmpty())
<template id="item_template">
    <div class="item-row grid gap-2 sm:grid-cols-[1fr_130px_auto] sm:items-end rounded-2xl border border-ink-900/10 p-3">
        <label class="grid gap-1">
            <span class="text-xs font-bold">Produk</span>
            <select required class="product-select min-w-0 rounded-xl border border-ink-900/15 bg-white px-3 py-2.5 text-sm font-semibold">
                <option value="">Pilih produk</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->effectivePrice() }}">{{ $product->name }} — Rp {{ number_format($product->effectivePrice(), 0, ',', '.') }} (stok {{ $product->stock }})</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-1">
            <span class="text-xs font-bold">Jumlah</span>
            <input type="number" min="1" max="1000000" value="1" required inputmode="numeric" class="qty-input rounded-xl border border-ink-900/15 px-3 py-2.5 text-sm font-semibold">
        </label>
        <button type="button" class="remove-item min-h-10 rounded-full px-3 text-xs font-extrabold text-red-700 hover:bg-red-50" aria-label="Hapus produk">Hapus</button>
    </div>
</template>
<script>
    const buyerType = document.getElementById('buyer_type');
    const registeredBuyer = document.getElementById('registered_buyer');
    const walkInBuyer = document.getElementById('walk_in_buyer');
    const buyerIdentifier = document.getElementById('buyer_identifier');
    const buyerName = document.querySelector('[name="buyer_name"]');

    function toggleBuyerFields() {
        const registered = buyerType.value === 'registered';
        registeredBuyer.hidden = !registered;
        walkInBuyer.hidden = registered;
        buyerIdentifier.required = registered;
        buyerName.required = !registered;
    }

    buyerType.addEventListener('change', toggleBuyerFields);
    toggleBuyerFields();

    const items = document.getElementById('items');
    const template = document.getElementById('item_template');
    const total = document.getElementById('total');
    let nextIndex = items.querySelectorAll('.item-row').length;

    function updateTotal() {
        const amount = [...items.querySelectorAll('.item-row')].reduce((sum, row) => {
            const price = Number(row.querySelector('.product-select').selectedOptions[0]?.dataset.price || 0);
            const qty = Number(row.querySelector('.qty-input').value || 0);
            return sum + price * qty;
        }, 0);
        total.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(amount)}`;
    }

    function updateNames() {
        [...items.querySelectorAll('.item-row')].forEach((row, index) => {
            row.querySelector('.product-select').name = `items[${index}][product_id]`;
            row.querySelector('.qty-input').name = `items[${index}][qty]`;
        });
    }

    document.getElementById('add_item').addEventListener('click', () => {
        const row = template.content.firstElementChild.cloneNode(true);
        row.querySelector('.product-select').name = `items[${nextIndex}][product_id]`;
        row.querySelector('.qty-input').name = `items[${nextIndex}][qty]`;
        nextIndex += 1;
        items.append(row);
    });

    items.addEventListener('input', updateTotal);
    items.addEventListener('change', updateTotal);
    items.addEventListener('click', (event) => {
        if (event.target.closest('.remove-item') && items.querySelectorAll('.item-row').length > 1) {
            event.target.closest('.item-row').remove();
            updateNames();
            updateTotal();
        }
    });
    updateTotal();
</script>
@endif
@endsection
