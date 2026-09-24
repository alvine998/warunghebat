@extends('layouts.admin')

@section('title', 'Warung — Backoffice Warung Hebat')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
        <p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="store" class="w-4 h-4" /> WARUNG MITRA</p>
        <h1 class="font-black tracking-tight text-3xl mt-1">{{ $warungs->total() }} warung terdaftar</h1>
        <p class="text-sm font-medium text-ink-500">Periksa bukti kepemilikan (KTP + selfie + foto warung) sebelum warung boleh jualan.</p>
    </div>
</div>

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

<div class="mt-4 flex flex-wrap gap-2 text-[12px] font-extrabold">
    @php
        $kycTabs = [
            '' => 'Semua ('.($counts['all'] ?? $warungs->total()).')',
            'pending' => '⏳ KYC pending ('.($counts['pending'] ?? 0).')',
            'verified' => '✅ Terverifikasi ('.($counts['verified'] ?? 0).')',
            'rejected' => '❌ Ditolak ('.($counts['rejected'] ?? 0).')',
            'none' => 'Belum ajukan ('.($counts['none'] ?? 0).')',
        ];
    @endphp
    @foreach($kycTabs as $value => $label)
        <a href="{{ route('admin.warungs', array_filter(['search' => request('search'), 'kyc' => $value ?: null])) }}" class="px-4 py-2 rounded-full {{ ($kyc ?? '') === $value || ($kyc === null && $value === '') ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">{{ $label }}</a>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.warungs') }}" class="mt-4 flex flex-col sm:flex-row gap-2">
    <input type="hidden" name="kyc" value="{{ request('kyc') }}">
    <label class="sr-only" for="warung-search">Cari warung</label>
    <div class="flex flex-1 items-center gap-2 rounded-2xl bg-white border border-ink-900/10 px-4 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10">
        <x-icon name="search" class="w-5 h-5 text-ink-400" />
        <input id="warung-search" name="search" value="{{ request('search') }}" type="search" placeholder="Cari nama warung, pemilik, atau kategori..." class="w-full bg-transparent py-3 text-sm font-semibold outline-none placeholder:text-ink-400">
    </div>
    <button type="submit" class="rounded-2xl bg-ink-900 px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition">Cari</button>
</form>

<div class="mt-5 grid gap-3 sm:grid-cols-2">
    @foreach($warungs as $w)
    @php($kycStatus = $w->user?->sellerVerification?->status)
    <article class="rounded-[24px] bg-white border border-ink-900/10 p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3 min-w-0">
                @if($w->image_path)
                    <img src="{{ $w->image_url }}" alt="Foto {{ $w->name }}" class="w-14 h-14 rounded-2xl object-cover border border-ink-900/10 shrink-0">
                @else
                    <div class="w-14 h-14 rounded-2xl bg-cream-100 border border-dashed border-ink-900/15 grid place-items-center shrink-0"><x-icon name="store" class="w-6 h-6 text-ink-400" /></div>
                @endif
                <div class="min-w-0">
                    <p class="font-extrabold text-lg truncate">{{ $w->name }}</p>
                    <p class="text-[13px] font-semibold text-ink-500 truncate">{{ $w->user?->name ?? 'Pemilik dihapus' }} • {{ $w->user?->email ?? '-' }}</p>
                    <p class="mt-1">
                        @if($kycStatus === 'verified')
                            <span class="text-[11px] font-extrabold rounded-full px-3 py-1 bg-leaf-100 text-leaf-700">✅ KYC terverifikasi</span>
                        @elseif($kycStatus === 'pending')
                            <span class="text-[11px] font-extrabold rounded-full px-3 py-1 bg-amber-100 text-amber-800">⏳ KYC pending</span>
                        @elseif($kycStatus === 'rejected')
                            <span class="text-[11px] font-extrabold rounded-full px-3 py-1 bg-red-100 text-red-700">❌ KYC ditolak</span>
                        @else
                            <span class="text-[11px] font-extrabold rounded-full px-3 py-1 bg-cream-100 text-ink-500">Belum ajukan KYC</span>
                        @endif
                    </p>
                </div>
            </div>
            <span class="shrink-0 text-[11px] font-extrabold rounded-full px-3 py-1.5 {{ $w->is_open ? 'bg-leaf-100 text-leaf-700' : 'bg-red-100 text-red-700' }}">{{ $w->is_open ? 'Aktif' : 'Disuspend' }}</span>
        </div>

        @if($w->user?->sellerVerification)
            @php($v = $w->user->sellerVerification)
            <details class="mt-4 rounded-2xl bg-cream-50 p-3" {{ $v->isPending() ? 'open' : '' }}>
                <summary class="cursor-pointer text-[12px] font-extrabold text-ink-700">Periksa berkas KYC — {{ $v->statusLabel() }}</summary>
                <dl class="mt-3 grid gap-2 text-[12px]">
                    <div><dt class="font-bold text-ink-500">Nama KTP</dt><dd class="font-semibold">{{ $v->full_name }}</dd></div>
                    <div><dt class="font-bold text-ink-500">NIK</dt><dd class="font-semibold font-mono">{{ $v->nik }}</dd></div>
                    <div><dt class="font-bold text-ink-500">Diajukan</dt><dd class="font-semibold">{{ $v->created_at->format('d M Y H:i') }} • diperbarui {{ $v->updated_at->diffForHumans() }}</dd></div>
                    @if($v->rejection_reason)
                        <div><dt class="font-bold text-ink-500">Alasan penolakan</dt><dd class="font-semibold text-red-700">{{ $v->rejection_reason }}</dd></div>
                    @endif
                </dl>
                <div class="mt-3 grid grid-cols-3 gap-2">
                    <a href="{{ $v->ktp_url }}" target="_blank" rel="noopener" class="block">
                        <img src="{{ $v->ktp_url }}" alt="Foto KTP {{ $v->full_name }}" loading="lazy" class="w-full h-24 rounded-xl object-cover border border-ink-900/10">
                        <span class="block mt-1 text-[11px] font-extrabold text-center">KTP</span>
                    </a>
                    <a href="{{ $v->selfie_url }}" target="_blank" rel="noopener" class="block">
                        <img src="{{ $v->selfie_url }}" alt="Selfie {{ $v->full_name }}" loading="lazy" class="w-full h-24 rounded-xl object-cover border border-ink-900/10">
                        <span class="block mt-1 text-[11px] font-extrabold text-center">Selfie + KTP</span>
                    </a>
                    <a href="{{ $v->storefront_url }}" target="_blank" rel="noopener" class="block">
                        <img src="{{ $v->storefront_url }}" alt="Foto warung {{ $w->name }}" loading="lazy" class="w-full h-24 rounded-xl object-cover border border-ink-900/10">
                        <span class="block mt-1 text-[11px] font-extrabold text-center">Depan warung</span>
                    </a>
                </div>
                @if($v->isPending())
                    <div class="mt-3 grid gap-2">
                        <form method="POST" action="{{ route('admin.kyc.verify', $v) }}">
                            @csrf
                            @method('PATCH')
                            <button class="w-full py-2.5 rounded-xl bg-leaf-600 text-white hover:bg-leaf-700 transition text-[12px] font-extrabold">✅ Setujui — warung boleh jualan</button>
                        </form>
                        <form method="POST" action="{{ route('admin.kyc.reject', $v) }}" class="grid gap-2">
                            @csrf
                            @method('PATCH')
                            <input name="rejection_reason" required maxlength="1000" placeholder="Alasan penolakan, cth. Foto KTP buram — foto ulang yang jelas" class="w-full rounded-xl border border-ink-900/15 px-3 py-2.5 text-[12px] font-semibold outline-none focus:border-red-500">
                            <button class="w-full py-2.5 rounded-xl bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition text-[12px] font-extrabold">Tolak dengan alasan</button>
                        </form>
                    </div>
                @endif
            </details>
        @endif

        <details class="mt-3 rounded-2xl bg-cream-50 p-3">
            <summary class="cursor-pointer text-[12px] font-extrabold text-ink-700">Lihat detail warung</summary>
            <dl class="mt-3 grid gap-2 text-[12px]">
                <div><dt class="font-bold text-ink-500">Slug</dt><dd class="font-semibold">{{ $w->slug }}</dd></div>
                <div><dt class="font-bold text-ink-500">Deskripsi</dt><dd class="font-semibold">{{ $w->description ?: 'Belum diisi' }}</dd></div>
                <div><dt class="font-bold text-ink-500">Alamat</dt><dd class="font-semibold">{{ $w->address ?: 'Belum diisi' }}</dd></div>
                <div><dt class="font-bold text-ink-500">Telepon</dt><dd class="font-semibold">{{ $w->phone ?: 'Belum diisi' }}</dd></div>
                <div><dt class="font-bold text-ink-500">Jam operasional</dt><dd class="font-semibold">{{ $w->hours_label ?: 'Belum diisi' }}</dd></div>
                <div><dt class="font-bold text-ink-500">Produk</dt><dd class="font-semibold">{{ $w->user?->products_count ?? 0 }} produk</dd></div>
                <div><dt class="font-bold text-ink-500">Dibuat</dt><dd class="font-semibold">{{ $w->created_at->format('d M Y') }}</dd></div>
            </dl>
        </details>
        <div class="mt-3">
            @if($w->is_open)
            <form method="POST" action="{{ route('admin.warungs.suspend', $w) }}" onsubmit="return confirm('Suspend warung ini? Warung tidak akan dapat menerima pesanan.');">
                @csrf
                @method('PATCH')
                <button class="w-full py-2.5 rounded-xl bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition text-[12px] font-extrabold">Suspend Warung</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.warungs.activate', $w) }}" onsubmit="return confirm('Aktifkan kembali warung ini?');">
                @csrf
                @method('PATCH')
                <button class="w-full py-2.5 rounded-xl bg-leaf-600 text-white hover:bg-leaf-700 transition text-[12px] font-extrabold">Aktifkan Kembali</button>
            </form>
            @endif
        </div>
    </article>
    @endforeach
    @if($warungs->isEmpty())
        <div class="sm:col-span-2 rounded-[24px] bg-white border border-dashed border-ink-900/15 p-8 text-center font-semibold text-ink-500">Tidak ada warung yang cocok dengan pencarian.</div>
    @endif
</div>
<div class="mt-4">{{ $warungs->links() }}</div>
@endsection
