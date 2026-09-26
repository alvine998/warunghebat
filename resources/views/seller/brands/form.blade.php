@extends('layouts.app')

@section('title', ($brand->exists ? 'Edit' : 'Tambah') . ' Brand — Warung Hebat')

@section('bare', true)

@section('content')
<section class="pt-6 sm:pt-8 pb-10 max-w-2xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between gap-2 flex-wrap">
        <a href="{{ route('seller.brands.index') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Brand saya</a>
        @include('seller.store._open_toggle', ['store' => $store])
    </div>

    <h1 class="font-black tracking-tight text-3xl mt-2">{{ $brand->exists ? 'Edit brand' : 'Tambah brand' }}</h1>
    <p class="text-sm font-medium text-ink-500">Nama brand harus unik di warungmu. Contoh: “Indomie”, “Sasa”, “Homemade Bu RT”.</p>

    <form method="POST" action="{{ $brand->exists ? route('seller.brands.update', $brand) : route('seller.brands.store') }}" class="mt-5 rounded-[28px] bg-white border border-ink-900/10 p-6 grid gap-4">
        @csrf
        @if($brand->exists)
            @method('PUT')
        @endif

        <div>
            <label for="name" class="text-[13px] font-extrabold">Nama brand *</label>
            <input id="name" name="name" required maxlength="80" value="{{ old('name', $brand->name) }}" placeholder="cth. Indomie" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            @error('name')
                <p class="mt-1 text-[13px] font-bold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button class="mt-1 w-full py-3.5 rounded-2xl bg-ink-900 text-white font-extrabold text-[15px] hover:bg-brand-600 transition">{{ $brand->exists ? 'Simpan perubahan' : 'Tambah brand' }}</button>
    </form>
</section>
@endsection
