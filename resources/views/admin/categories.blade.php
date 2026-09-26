@extends('layouts.admin')

@section('title', 'Kategori Produk — Backoffice Warung Hebat')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
        <p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="package" class="w-4 h-4" /> KATEGORI PRODUK</p>
        <h1 class="font-black tracking-tight text-3xl mt-1">Kategori</h1>
        <p class="text-sm font-medium text-ink-500">Penjual memilih salah satu kategori ini saat tambah produk. Kategori nonaktif disembunyikan dari pembeli.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="text-sm font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition w-fit">+ Tambah kategori</a>
</div>

<form method="GET" action="{{ route('admin.categories') }}" class="mt-4 flex flex-col sm:flex-row gap-2">
    <label class="sr-only" for="category-search">Cari kategori</label>
    <div class="flex flex-1 items-center gap-2 rounded-2xl bg-white border border-ink-900/10 px-4 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10">
        <x-icon name="search" class="w-5 h-5 text-ink-400" />
        <input id="category-search" name="search" value="{{ request('search') }}" type="search" placeholder="Cari nama kategori atau deskripsi..." class="w-full bg-transparent py-3 text-sm font-semibold outline-none placeholder:text-ink-400">
    </div>
    <button type="submit" class="rounded-2xl bg-ink-900 px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition">Cari</button>
</form>

<div class="mt-5 grid gap-2.5">
    @forelse($categories as $category)
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <span class="w-12 h-12 shrink-0 rounded-2xl bg-brand-500 grid place-items-center text-white"><x-icon name="{{ $category->icon ?? 'package' }}" class="w-6 h-6" /></span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-extrabold">{{ $category->name }}</p>
                        <span class="text-[11px] font-extrabold rounded-full px-3 py-1 {{ $category->is_active ? 'bg-leaf-100 text-leaf-700' : 'bg-red-100 text-red-700' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        <span class="text-[11px] font-bold text-ink-500">/{{ $category->slug }} • Urutan {{ $category->sort_order }} • {{ $category->products_count }} produk</span>
                    </div>
                    @if($category->description)
                        <p class="mt-1 text-[13px] font-medium text-ink-700">{{ $category->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                        @csrf
                        @method('PATCH')
                        <button class="text-[13px] font-extrabold px-4 py-2 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </form>
                    <a href="{{ route('admin.categories.edit', $category) }}" class="text-[13px] font-extrabold px-4 py-2 rounded-full bg-cream-100 hover:bg-ink-900 hover:text-white transition">Edit</a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori {{ $category->name }}? Hanya bisa jika belum dipakai produk.');">
                        @csrf
                        @method('DELETE')
                        <button class="text-[13px] font-extrabold px-4 py-2 rounded-full bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-8 text-center">
            <p class="text-3xl">🛍️</p>
            <p class="font-extrabold mt-2">Belum ada kategori</p>
            <p class="text-sm font-medium text-ink-500 mt-1">Tambahkan kategori pertama agar penjual bisa memilihnya.</p>
            <a href="{{ route('admin.categories.create') }}" class="mt-4 inline-block text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">+ Tambah kategori</a>
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $categories->links() }}</div>
@endsection
