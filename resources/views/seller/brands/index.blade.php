@extends('layouts.app')

@section('title', 'Brand Saya — Warung Hebat')

@section('bare', true)

@section('content')
<section class="pt-6 sm:pt-8 pb-10 max-w-4xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between gap-2 flex-wrap">
        <a href="{{ route('seller.products.index') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Produk saya</a>
        @include('seller.store._open_toggle', ['store' => $store])
    </div>

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mt-2">
        <div>
            <p class="text-[11px] font-extrabold tracking-[0.2em] text-leaf-700">🏷️ BRAND SAYA</p>
            <h1 class="font-black tracking-tight text-2xl sm:text-3xl mt-1">Master brand</h1>
            <p class="text-sm font-medium text-ink-500 mt-1">Brand milik warungmu sendiri. Dipakai untuk mengelompokkan produk & filter di halaman kategori dan pencarian.</p>
        </div>
        <a href="{{ route('seller.brands.create') }}" class="text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition w-fit">+ Tambah brand</a>
    </div>

    <form method="GET" action="{{ route('seller.brands.index') }}" class="mt-4 flex gap-2">
        <label class="sr-only" for="brand-search">Cari brand</label>
        <div class="flex flex-1 items-center gap-2 rounded-2xl bg-white border border-ink-900/10 px-4 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10">
            <x-icon name="search" class="w-5 h-5 text-ink-400" />
            <input id="brand-search" name="search" value="{{ request('search') }}" type="search" placeholder="Cari brand..." class="w-full bg-transparent py-3 text-sm font-semibold outline-none placeholder:text-ink-400">
        </div>
        <button type="submit" class="rounded-2xl bg-ink-900 px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition">Cari</button>
    </form>

    <div class="mt-4 grid gap-2.5">
        @forelse($brands as $brand)
            <div class="rounded-[24px] bg-white border border-ink-900/10 p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1 min-w-0">
                    <p class="font-extrabold">{{ $brand->name }}</p>
                    <p class="text-xs font-semibold text-ink-500 mt-0.5">{{ $brand->products_count }} produk memakai brand ini</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('seller.brands.edit', $brand) }}" class="text-[13px] font-extrabold px-4 py-2 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">Edit</a>
                    <form method="POST" action="{{ route('seller.brands.destroy', $brand) }}" onsubmit="return confirm('Hapus brand {{ $brand->name }}?');">
                        @csrf
                        @method('DELETE')
                        <button class="text-[13px] font-extrabold px-4 py-2 rounded-full bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-8 text-center">
                <p class="text-3xl">🏷️</p>
                <p class="font-extrabold mt-2">Belum ada brand</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Tambahkan brand pertama (cth. “Indomie”, “Sasa”, “Homemade”) lalu pilih saat tambah produk.</p>
                <a href="{{ route('seller.brands.create') }}" class="mt-4 inline-block text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">+ Tambah brand</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4 flex justify-center">{{ $brands->links() }}</div>
</section>
@endsection
