@extends('layouts.app')

@section('title', 'Verifikasi Warung — Warung Hebat')

@section('bare', true)

@section('content')
<section class="pt-6 sm:pt-8 pb-10 max-w-2xl mx-auto px-4 sm:px-6">
    <a href="{{ route('dashboard') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Dashboard</a>

    <p class="mt-3 text-[11px] font-extrabold tracking-[0.2em] text-brand-600">🛡️ VERIFIKASI KEPEMILIKAN WARUNG</p>
    <h1 class="font-black tracking-tight text-3xl mt-1">Buktikan warung ini milikmu</h1>
    <p class="text-sm font-medium text-ink-500 mt-1">Unggah KTP + selfie + foto depan warung. Admin memeriksa sebelum warungmu bisa jualan.</p>

    @if (session('success'))
        <div class="mt-4 rounded-2xl bg-leaf-50 border border-leaf-500/30 text-leaf-700 text-sm font-bold p-4">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-bold p-4">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-bold p-4">
            <ul class="list-disc list-inside grid gap-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($verification?->isVerified())
        <div class="mt-5 rounded-[28px] bg-leaf-600 text-white p-6">
            <p class="font-black text-lg">✅ Warungmu terverifikasi</p>
            <p class="text-sm font-medium text-white/80 mt-1">Nama KTP: {{ $verification->full_name }} • NIK: {{ substr($verification->nik, 0, 4) }}••••••••••••</p>
            <div class="mt-4 flex gap-2 flex-wrap">
                <a href="{{ route('seller.products.index') }}" class="bg-white text-leaf-700 text-sm font-extrabold px-5 py-2.5 rounded-full">Kelola produk →</a>
                <a href="{{ route('seller.store.edit') }}" class="bg-white/10 border border-white/15 text-sm font-extrabold px-5 py-2.5 rounded-full hover:bg-white/20 transition">⚙️ Pengaturan warung</a>
            </div>
        </div>
    @else
        @if($verification?->isPending())
            <div class="mt-5 rounded-[28px] bg-amber-50 border border-amber-200 p-5">
                <p class="font-extrabold">⏳ Menunggu verifikasi admin</p>
                <p class="text-[13px] font-medium text-amber-900/70 mt-1">Berkasmu masuk antrean (biasanya &lt; 1x24 jam). Kamu bisa memperbarui berkas di bawah — setiap kirim ulang kembali ke antrean.</p>
            </div>
        @endif

        @if($verification?->isRejected())
            <div class="mt-5 rounded-[28px] bg-red-50 border border-red-200 p-5">
                <p class="font-extrabold text-red-700">❌ Perlu diperbaiki</p>
                <p class="text-[13px] font-semibold text-red-700 mt-1">Alasan admin: {{ $verification->rejection_reason }}</p>
                <p class="text-[13px] font-medium text-red-700/70 mt-1">Perbaiki lalu kirim ulang di bawah.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('seller.verification.store') }}" enctype="multipart/form-data" class="mt-5 rounded-[28px] bg-white border border-ink-900/10 p-6 grid gap-4">
            @csrf

            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-[13px] font-extrabold">NIK (16 digit) *</label>
                    <input name="nik" required inputmode="numeric" maxlength="16" pattern="[0-9]{16}" value="{{ old('nik', $verification?->nik) }}" placeholder="cth. 3174xxxxxxxxxxxx" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                </div>
                <div>
                    <label class="text-[13px] font-extrabold">Nama sesuai KTP *</label>
                    <input name="full_name" required maxlength="120" value="{{ old('full_name', $verification?->full_name) }}" placeholder="cth. Sari Dewi" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                </div>
            </div>

            <div>
                <label class="text-[13px] font-extrabold">Foto KTP * <span class="font-semibold text-ink-500">(JPG/PNG/WebP, maks 2MB — pastikan NIK & foto jelas)</span></label>
                @if($verification?->ktp_path)
                    <div class="mt-1.5 flex items-center gap-3">
                        <img src="{{ $verification->ktp_url }}" alt="Foto KTP saat ini" class="w-20 h-14 rounded-xl object-cover border border-ink-900/10">
                        <p class="text-xs font-semibold text-ink-500">Sudah ada. Unggah baru untuk mengganti (opsional).</p>
                    </div>
                @endif
                <input name="ktp_image" type="file" accept="image/jpeg,image/png,image/webp" {{ $verification ? '' : 'required' }} class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[14px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition file:mr-3 file:rounded-xl file:border-0 file:bg-ink-900 file:text-white file:text-[13px] file:font-extrabold file:px-4 file:py-2">
            </div>

            <div>
                <label class="text-[13px] font-extrabold">Selfie pegang KTP * <span class="font-semibold text-ink-500">(wajah + KTP terlihat jelas)</span></label>
                @if($verification?->selfie_path)
                    <div class="mt-1.5 flex items-center gap-3">
                        <img src="{{ $verification->selfie_url }}" alt="Selfie saat ini" class="w-14 h-14 rounded-xl object-cover border border-ink-900/10">
                        <p class="text-xs font-semibold text-ink-500">Sudah ada. Unggah baru untuk mengganti (opsional).</p>
                    </div>
                @endif
                <input name="selfie_image" type="file" accept="image/jpeg,image/png,image/webp" {{ $verification ? '' : 'required' }} class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[14px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition file:mr-3 file:rounded-xl file:border-0 file:bg-ink-900 file:text-white file:text-[13px] file:font-extrabold file:px-4 file:py-2">
            </div>

            <div>
                <label class="text-[13px] font-extrabold">Foto depan warung * <span class="font-semibold text-ink-500">(plang/spanduk warung terlihat — bukti warung milikmu)</span></label>
                @if($verification?->storefront_path)
                    <div class="mt-1.5 flex items-center gap-3">
                        <img src="{{ $verification->storefront_url }}" alt="Foto warung saat ini" class="w-20 h-14 rounded-xl object-cover border border-ink-900/10">
                        <p class="text-xs font-semibold text-ink-500">Sudah ada. Unggah baru untuk mengganti (opsional).</p>
                    </div>
                @endif
                <input name="storefront_image" type="file" accept="image/jpeg,image/png,image/webp" {{ $verification ? '' : 'required' }} class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[14px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition file:mr-3 file:rounded-xl file:border-0 file:bg-ink-900 file:text-white file:text-[13px] file:font-extrabold file:px-4 file:py-2">
            </div>

            <p class="text-[12px] font-medium text-ink-500 leading-relaxed">Dengan mengirim, kamu menyatakan warung ini milikmu / kamu diberi kuasa pemiliknya. Data KTP hanya dipakai untuk verifikasi & tidak ditampilkan ke pembeli.</p>

            <button class="mt-1 w-full py-3.5 rounded-2xl bg-ink-900 text-white font-extrabold text-[15px] hover:bg-brand-600 transition">{{ $verification ? 'Kirim ulang untuk verifikasi' : 'Kirim untuk verifikasi' }}</button>
        </form>
    @endif
</section>
@endsection
