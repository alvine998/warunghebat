@extends('layouts.app')

@section('title', 'Keranjang — Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-10 sm:pb-14 max-w-2xl mx-auto px-4 sm:px-6">
    <a href="{{ ! empty($cart['store_slug']) ? route('store.show', $cart['store_slug']) : route('home').'#warung' }}" class="inline-flex items-center gap-1.5 min-h-10 text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Lanjut belanja</a>

    <div class="mt-3 flex items-end justify-between gap-3">
        <div>
            <h1 class="font-black tracking-tight text-[26px] sm:text-3xl">Keranjang</h1>
            @if(! empty($cart['store_name']))
                <p class="text-[13px] font-semibold text-ink-500 mt-1">dari <span class="font-extrabold text-ink-900">{{ $cart['store_name'] }}</span></p>
            @endif
        </div>
        @if($items)
            <p class="text-[13px] font-extrabold text-ink-500">{{ count($items) }} produk</p>
        @endif
    </div>

    @if(empty($items))
        <div class="mt-4 rounded-[28px] bg-white border border-dashed border-ink-900/15 p-7 sm:p-10 text-center">
            <p class="text-4xl">🛒</p>
            <p class="font-extrabold text-base sm:text-lg mt-2">Keranjang masih kosong</p>
            <p class="text-sm font-medium text-ink-500 mt-1">Cari warung terdekat dan tambahkan produk favoritmu.</p>
            <a href="{{ route('home') }}#warung" class="mt-4 inline-block text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">Cari Warung →</a>
        </div>
    @else
        <div class="mt-4 rounded-[28px] bg-white border border-ink-900/10 overflow-hidden divide-y divide-ink-900/5">
            @foreach($items as $item)
                @php($product = $products[$item['product_id']] ?? null)
                <div class="p-4 flex gap-3 sm:gap-4">
                    @if($product?->image_path)
                        <img src="{{ $product->image_url }}" alt="Foto {{ $item['name'] }}" class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 rounded-2xl object-cover border border-ink-900/10">
                    @else
                        <span class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 rounded-2xl bg-cream-100 border border-dashed border-ink-900/15 grid place-items-center text-2xl">📷</span>
                    @endif

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-extrabold text-[14px] sm:text-[15px] leading-snug break-words">{{ $item['name'] }}</p>
                            <form method="POST" action="{{ route('cart.destroy', $item['product_id']) }}" class="shrink-0">
                                @csrf
                                @method('DELETE')
                                <button class="text-[12px] font-extrabold text-ink-500 hover:text-brand-600 min-h-8 px-1" aria-label="Hapus {{ $item['name'] }}">Hapus</button>
                            </form>
                        </div>

                        <p class="mt-1 text-[12px] font-bold text-ink-500">Rp {{ number_format($item['price'], 0, ',', '.') }} <span class="text-ink-500/70">/ pcs</span></p>

                        <div class="mt-2.5 flex items-center justify-between gap-2 flex-wrap">
                            <form method="POST" action="{{ route('cart.update', $item['product_id']) }}" class="flex items-center gap-1.5">
                                @csrf
                                @method('PATCH')
                                <button name="qty" value="{{ $item['qty'] - 1 }}" @disabled($item['qty'] <= 1) class="w-9 h-9 rounded-xl border border-ink-900/15 font-black disabled:opacity-30 disabled:cursor-not-allowed hover:bg-ink-900 hover:text-white transition" aria-label="Kurangi jumlah">−</button>
                                <span class="w-10 text-center font-black tabular-nums">{{ $item['qty'] }}</span>
                                <button name="qty" value="{{ $item['qty'] + 1 }}" @disabled($item['qty'] >= ($product?->stock ?? 0)) class="w-9 h-9 rounded-xl border border-ink-900/15 font-black disabled:opacity-30 disabled:cursor-not-allowed hover:bg-ink-900 hover:text-white transition" aria-label="Tambah jumlah">+</button>
                            </form>

                            <p class="font-black text-[15px] sm:text-base whitespace-nowrap">Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</p>
                        </div>

                        @if($product && $product->stock <= 5)
                            <p class="mt-1.5 text-[11px] font-extrabold text-amber-800">Sisa stok {{ $product->stock }} pcs</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3 rounded-[28px] bg-white border border-ink-900/10 p-4 sm:p-5">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-bold text-ink-500">Total pembayaran</p>
                <p class="font-black text-xl sm:text-2xl whitespace-nowrap">Rp {{ number_format($total, 0, ',', '.') }}</p>
            </div>
            <p class="mt-1 text-[12px] font-semibold text-ink-500">Bayar ke rekening <span class="font-extrabold text-ink-900">Warung Hebat</span>, lalu dana diteruskan ke warung setelah pesanan selesai.</p>

            <form method="POST" action="{{ route('checkout.store') }}" class="mt-4">
                @csrf
                <button class="w-full min-h-12 rounded-full bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-sm transition">Lanjut ke pembayaran →</button>
            </form>
            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                <a href="{{ ! empty($cart['store_slug']) ? route('store.show', $cart['store_slug']) : route('home').'#warung' }}" class="text-center min-h-11 grid place-items-center rounded-full bg-cream-100 text-[13px] font-extrabold hover:bg-ink-900 hover:text-white transition">+ Tambah produk lain</a>
                <a href="{{ route('orders.index') }}" class="text-center min-h-11 grid place-items-center rounded-full border border-ink-900/15 text-[13px] font-extrabold hover:bg-ink-900 hover:text-white transition">Pesanan saya</a>
            </div>
        </div>
    @endif
</section>
@endsection
