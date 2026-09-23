@extends('layouts.admin')

@section('title', ($method->exists ? 'Edit' : 'Tambah').' Metode Pembayaran — Backoffice Warung Hebat')

@section('content')
<a href="{{ route('admin.payment-methods') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Metode pembayaran</a>

<h1 class="font-black tracking-tight text-3xl mt-2">{{ $method->exists ? 'Edit metode' : 'Tambah metode' }}</h1>
<p class="text-sm font-medium text-ink-500">Rekening tujuan transfer pembeli. Dana masuk ke Warung Hebat dulu, baru diteruskan ke warung.</p>

<form method="POST" action="{{ $method->exists ? route('admin.payment-methods.update', $method) : route('admin.payment-methods.store') }}" enctype="multipart/form-data" class="mt-5 max-w-2xl grid gap-3.5">
    @csrf
    @if($method->exists)
        @method('PUT')
    @endif

    <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6 grid gap-4">
        <div>
            <label for="type" class="text-[13px] font-extrabold">Tipe metode *</label>
            <select id="type" name="type" required class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                @foreach(\App\Models\PaymentMethod::TYPES as $type)
                    <option value="{{ $type }}" @selected(old('type', $method->type) === $type)>{{ \App\Models\PaymentMethod::TYPE_LABELS[$type] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="name" class="text-[13px] font-extrabold">Nama metode *</label>
            <input id="name" name="name" required maxlength="80" value="{{ old('name', $method->name) }}" placeholder="cth. BCA a/n Warung Hebat" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="account_number" class="text-[13px] font-extrabold">Nomor rekening</label>
                <input id="account_number" name="account_number" maxlength="50" value="{{ old('account_number', $method->account_number) }}" placeholder="1234567890" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                <p class="mt-1.5 text-xs font-semibold text-ink-500">Wajib untuk transfer bank dan e-wallet. QRIS boleh kosong.</p>
            </div>
            <div>
                <label for="account_name" class="text-[13px] font-extrabold">Nama pemilik rekening</label>
                <input id="account_name" name="account_name" maxlength="80" value="{{ old('account_name', $method->account_name) }}" placeholder="cth. PT Warung Hebat" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            </div>
        </div>

        <div>
            <label for="instructions" class="text-[13px] font-extrabold">Instruksi untuk pembeli</label>
            <textarea id="instructions" name="instructions" rows="3" maxlength="2000" placeholder="cth. Transfer sesuai nominal, lalu unggah bukti transfer." class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition resize-y">{{ old('instructions', $method->instructions) }}</textarea>
        </div>

        <div>
            <label for="image" class="text-[13px] font-extrabold">Gambar QRIS / logo</label>
            <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[13px] font-semibold file:mr-3 file:rounded-full file:border-0 file:bg-ink-900 file:px-4 file:py-2 file:text-[12px] file:font-extrabold file:text-white outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            <p class="mt-1.5 text-xs font-semibold text-ink-500">Opsional. JPG, PNG, atau WebP, maksimal 2MB.</p>
            @if($method->image_path)
                <img src="{{ $method->image_url }}" alt="{{ $method->name }}" class="mt-2 w-32 rounded-2xl border border-ink-900/10">
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="sort_order" class="text-[13px] font-extrabold">Urutan tampil</label>
                <input id="sort_order" name="sort_order" type="number" min="0" max="1000" value="{{ old('sort_order', $method->sort_order ?? 0) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                <p class="mt-1.5 text-xs font-semibold text-ink-500">Angka kecil tampil lebih dulu.</p>
            </div>
            <label class="flex items-center gap-2.5 mt-1 sm:mt-7 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $method->exists ? $method->is_active : true)) class="h-5 w-5 rounded accent-brand-500">
                <span class="text-[13px] font-extrabold">Aktif dan bisa dipakai pembeli</span>
            </label>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-2">
        <button class="w-full sm:w-fit rounded-full bg-ink-900 hover:bg-brand-600 text-white font-extrabold text-sm px-8 py-3.5 transition">{{ $method->exists ? 'Simpan perubahan' : 'Tambah metode' }}</button>
        <a href="{{ route('admin.payment-methods') }}" class="w-full sm:w-fit text-center rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white font-extrabold text-sm px-8 py-3.5 transition">Batal</a>
    </div>
</form>
@endsection
