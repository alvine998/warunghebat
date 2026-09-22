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

    @if (session('success'))
        <div class="mt-4 rounded-2xl bg-leaf-50 border border-leaf-500/30 text-leaf-700 text-sm font-bold p-4">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-[13px] font-bold p-4">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('seller.store.update') }}" enctype="multipart/form-data" class="mt-5 rounded-[28px] bg-white border border-ink-900/10 p-6 grid gap-4">
        @csrf
        @method('PUT')

        <div>
            <label class="text-[13px] font-extrabold">Nama warung *</label>
            <input name="name" required maxlength="80" value="{{ old('name', $store->name) }}" placeholder="cth. Warung Bang Jago" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
        </div>

        <div>
            <label class="text-[13px] font-extrabold">Slug * <span class="font-semibold text-ink-500">(unik, untuk URL warungmu)</span></label>
            <input name="slug" required maxlength="80" value="{{ old('slug', $store->slug) }}" placeholder="cth. warung-bang-jago" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
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
            <label class="text-[13px] font-extrabold">No. HP / WhatsApp</label>
            <input name="phone" maxlength="20" value="{{ old('phone', $store->phone) }}" placeholder="cth. 081234567890" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-[13px] font-extrabold">Jam buka</label>
                <input name="open_time" type="time" value="{{ old('open_time', $store->open_time ? substr((string) $store->open_time, 0, 5) : '') }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>
            <div>
                <label class="text-[13px] font-extrabold">Jam tutup</label>
                <input name="close_time" type="time" value="{{ old('close_time', $store->close_time ? substr((string) $store->close_time, 0, 5) : '') }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>
        </div>

        <label class="flex items-center gap-3 rounded-2xl border border-ink-900/10 bg-cream-50/60 px-4 py-3.5 cursor-pointer">
            <input type="hidden" name="is_open" value="0">
            <input name="is_open" type="checkbox" value="1" @checked(old('is_open', $store->is_open)) class="w-5 h-5 accent-leaf-600">
            <span>
                <span class="block text-[13px] font-extrabold">Warung sedang buka</span>
                <span class="block text-xs font-semibold text-ink-500">Matikan jika tutup sementara — pembeli akan melihat status tutup.</span>
            </span>
        </label>

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
