@extends('layouts.admin')

@section('title', ($category->exists ? 'Edit' : 'Tambah').' Kategori — Backoffice Warung Hebat')

@section('content')
<a href="{{ route('admin.categories') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Kategori produk</a>

<h1 class="font-black tracking-tight text-3xl mt-2">{{ $category->exists ? 'Edit kategori' : 'Tambah kategori' }}</h1>
<p class="text-sm font-medium text-ink-500">Slug URL dibuat otomatis dari nama (cth. “Makanan Beku” → /kategori/makanan-beku).</p>

<form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="mt-5 max-w-2xl grid gap-3.5">
    @csrf
    @if($category->exists)
        @method('PUT')
    @endif

    <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6 grid gap-4">
        <div>
            <label for="name" class="text-[13px] font-extrabold">Nama kategori *</label>
            <input id="name" name="name" required maxlength="60" value="{{ old('name', $category->name) }}" placeholder="cth. Makanan Beku" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            @error('name')
                <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
            @enderror
            @if($category->exists)
                <p class="mt-1.5 text-xs font-semibold text-ink-500">Slug saat ini: <span class="font-mono">/kategori/{{ $category->slug }}</span></p>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="icon" class="text-[13px] font-extrabold">Ikon</label>
                <input id="icon" name="icon" maxlength="40" value="{{ old('icon', $category->icon) }}" placeholder="cth. utensils, cup, basket" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                <p class="mt-1.5 text-xs font-semibold text-ink-500">Nama ikon: utensils, cup, basket, package, cookie, snowflake.</p>
            </div>
            <div>
                <label for="sort_order" class="text-[13px] font-extrabold">Urutan tampil</label>
                <input id="sort_order" name="sort_order" type="number" min="0" max="1000" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                <p class="mt-1.5 text-xs font-semibold text-ink-500">Angka kecil tampil lebih dulu di beranda.</p>
            </div>
        </div>

        <div>
            <label for="description" class="text-[13px] font-extrabold">Deskripsi singkat</label>
            <input id="description" name="description" maxlength="255" value="{{ old('description', $category->description) }}" placeholder="cth. Siap saji & masakan" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->exists ? $category->is_active : true)) class="h-5 w-5 rounded accent-brand-500">
            <span class="text-[13px] font-extrabold">Aktif dan tampil ke pembeli</span>
        </label>
    </div>

    <div class="flex flex-col sm:flex-row gap-2">
        <button class="w-full sm:w-fit rounded-full bg-ink-900 hover:bg-brand-600 text-white font-extrabold text-sm px-8 py-3.5 transition">{{ $category->exists ? 'Simpan perubahan' : 'Tambah kategori' }}</button>
        <a href="{{ route('admin.categories') }}" class="w-full sm:w-fit text-center rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white font-extrabold text-sm px-8 py-3.5 transition">Batal</a>
    </div>
</form>
@endsection
