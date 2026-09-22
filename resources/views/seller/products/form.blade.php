@extends('layouts.app')

@section('title', ($product->exists ? 'Edit' : 'Tambah') . ' Produk — Warung Hebat')

@section('bare', true)

@section('content')
<section class="pt-6 sm:pt-8 pb-10 max-w-2xl mx-auto px-4 sm:px-6">
    <a href="{{ route('seller.products.index') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Kembali</a>

    <h1 class="font-black tracking-tight text-3xl mt-2">{{ $product->exists ? 'Edit produk' : 'Tambah produk' }}</h1>
    <p class="text-sm font-medium text-ink-500">{{ $product->exists ? 'Perubahan akan dikirim ulang ke admin untuk verifikasi.' : 'Produk baru menunggu verifikasi admin sebelum tayang.' }}</p>

    @if ($errors->any())
        <div class="mt-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-[13px] font-bold p-4">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $product->exists ? route('seller.products.update', $product) : route('seller.products.store') }}" class="mt-5 rounded-[28px] bg-white border border-ink-900/10 p-6 grid gap-4">
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

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-[13px] font-extrabold">Harga (Rp) *</label>
                <input name="price" type="number" required min="0" max="1000000000" value="{{ old('price', $product->price ?? 0) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>
            <div>
                <label class="text-[13px] font-extrabold">Stok *</label>
                <input name="stock" type="number" required min="0" max="1000000" value="{{ old('stock', $product->stock ?? 0) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>
        </div>

        <div>
            <label class="text-[13px] font-extrabold">Kategori *</label>
            <select name="category" required class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-semibold outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                @foreach(\App\Models\Product::CATEGORIES as $c)
                <option value="{{ $c }}" @selected(old('category', $product->category ?? 'Lainnya') === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>

        <button class="mt-1 w-full py-3.5 rounded-2xl bg-ink-900 text-white font-extrabold text-[15px] hover:bg-brand-600 transition">{{ $product->exists ? 'Simpan & kirim verifikasi ulang' : 'Simpan & kirim verifikasi' }}</button>
    </form>
</section>
@endsection
