@extends('layouts.app')

@section('title', 'Dashboard — Warung Hebat')

@section('bare', true)

@section('content')
<section class="pt-6 sm:pt-8 pb-10 max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between gap-2 mb-5 flex-wrap">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <span class="w-9 h-9 rounded-xl bg-ink-900 grid place-items-center -rotate-3"><span class="text-brand-400 font-black">W</span></span>
            <span class="font-extrabold">Warung Hebat</span>
        </a>
        <div class="flex items-center gap-2 flex-wrap">
            @if(auth()->user()->canSell() && ! empty($store ?? null))
                @include('seller.store._open_toggle', ['store' => $store])
            @endif
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-[13px] font-extrabold border-2 border-ink-900/10 hover:border-ink-900 rounded-full px-5 py-2.5 transition"><x-icon name="globe" class="w-4 h-4" /> Lihat Situs</a>
        </div>
    </div>
    <div class="rounded-[28px] bg-ink-900 text-white p-6 sm:p-8 relative overflow-hidden grain">
        <div class="absolute -top-20 -right-20 w-72 h-72 bg-brand-500/30 blur-[90px] rounded-full"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center gap-5">
            <span class="w-16 h-16 rounded-3xl bg-brand-500 grid place-items-center text-2xl font-black shrink-0">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="flex-1">
                <p class="text-[12px] font-bold text-white/50 tracking-widest">SELAMAT DATANG KEMBALI 👋</p>
                <h1 class="font-black tracking-tight text-2xl sm:text-3xl">{{ auth()->user()->name }}</h1>
                <p class="text-sm font-medium text-white/60">{{ auth()->user()->email }} • Bergabung {{ auth()->user()->created_at->diffForHumans() }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="bg-white/10 border border-white/15 hover:bg-white/20 font-extrabold text-sm px-6 py-3 rounded-full transition">Keluar</button>
            </form>
        </div>
        <div class="relative mt-6 grid grid-cols-3 gap-2.5 max-w-lg">
            <div class="rounded-2xl bg-white/10 border border-white/10 p-4"><p class="font-black text-xl">{{ number_format($stats['points'] ?? 0, 0, ',', '.') }}</p><p class="text-[11px] font-bold text-white/60">POIN HEBAT</p></div>
            <div class="rounded-2xl bg-white/10 border border-white/10 p-4"><p class="font-black text-xl">{{ number_format($stats['orders'] ?? 0, 0, ',', '.') }}</p><p class="text-[11px] font-bold text-white/60">PESANAN</p></div>
            <div class="rounded-2xl bg-white/10 border border-white/10 p-4"><p class="font-black text-xl">{{ number_format($stats['favorites'] ?? 0, 0, ',', '.') }}</p><p class="text-[11px] font-bold text-white/60">WARUNG FAVORIT</p></div>
        </div>
    </div>

    @if(auth()->user()->role === 'penjual')
        @php
            $kyc = $verification ?? null;
        @endphp
        @if(! $kyc)
            <div class="mt-5 rounded-[28px] bg-amber-50 border border-amber-200 p-5 flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1">
                    <p class="font-extrabold">🛡️ Verifikasi warungmu dulu biar bisa jualan</p>
                    <p class="text-[13px] font-medium text-amber-900/70">Unggah KTP + selfie + foto depan warung. Admin memeriksa sebelum warungmu bisa jualan.</p>
                </div>
                <a href="{{ route('seller.verification.show') }}" class="text-sm font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition w-fit">Verifikasi →</a>
            </div>
        @elseif($kyc->isPending())
            <div class="mt-5 rounded-[28px] bg-amber-50 border border-amber-200 p-5 flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1">
                    <p class="font-extrabold">⏳ KYC menunggu verifikasi admin</p>
                    <p class="text-[13px] font-medium text-amber-900/70">Berkasmu masuk antrean (biasanya &lt; 1x24 jam). Jualan dibuka otomatis setelah disetujui.</p>
                </div>
                <a href="{{ route('seller.verification.show') }}" class="text-sm font-extrabold border border-ink-900/15 px-5 py-2.5 rounded-full hover:bg-ink-900 hover:text-white transition w-fit">Lihat status →</a>
            </div>
        @elseif($kyc->isRejected())
            <div class="mt-5 rounded-[28px] bg-red-50 border border-red-200 p-5 flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1">
                    <p class="font-extrabold text-red-700">❌ Verifikasi ditolak — {{ $kyc->rejection_reason }}</p>
                    <p class="text-[13px] font-medium text-red-700/70">Perbaiki berkasmu lalu kirim ulang agar bisa jualan lagi.</p>
                </div>
                <a href="{{ route('seller.verification.show') }}" class="text-sm font-extrabold bg-red-600 text-white px-5 py-2.5 rounded-full hover:bg-red-700 transition w-fit">Perbaiki →</a>
            </div>
        @endif
    @endif

    <div class="mt-5 grid gap-3.5 lg:grid-cols-[1fr_380px]">
        <div class="grid gap-3.5 content-start">
        @if(auth()->user()->role === 'penjual' && auth()->user()->isKycVerified() && ! empty($storeStats ?? null))
        <div class="rounded-[28px] bg-white border border-ink-900/10 p-6">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <p class="text-[11px] font-extrabold tracking-[0.2em] text-brand-600">📊 STATISTIK WARUNG</p>
                    <h2 class="font-extrabold text-lg mt-0.5">Performa tokomu</h2>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('seller.store.edit') }}" class="text-[13px] font-extrabold px-4 py-2 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">⚙️ Pengaturan</a>
                    <div class="flex items-center gap-3">
                        @if(auth()->user()->role === 'penjual' && auth()->user()->isKycVerified())
                            <a href="{{ route('seller.transactions.index') }}" class="text-[13px] font-extrabold text-brand-600">Transaksi →</a>
                        @endif
                        <a href="{{ route('seller.products.index') }}" class="text-[13px] font-extrabold text-brand-600">Kelola produk →</a>
                    </div>
                </div>
            </div>
            @include('seller.store._stats')
        </div>
        @endif
        @if(auth()->user()->role === 'penjual' && auth()->user()->isKycVerified() && ! empty($finance ?? null))
        <div class="rounded-[28px] bg-white border border-ink-900/10 p-6">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <p class="text-[11px] font-extrabold tracking-[0.2em] text-brand-600">💰 RINGKASAN KEUANGAN</p>
                    <h2 class="font-extrabold text-lg mt-0.5">Nilai daganganmu</h2>
                </div>
                <a href="{{ route('seller.products.index') }}" class="text-[13px] font-extrabold text-brand-600">Kelola produk →</a>
            </div>
            @include('seller.store._finance')
        </div>
        @endif
        <div class="rounded-[28px] bg-white border border-ink-900/10 p-6">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-extrabold text-lg">Pesanan terakhir</h2>
                <div class="flex items-center gap-3">
                    <a href="{{ route('orders.index') }}" class="text-[13px] font-extrabold text-brand-600">Lihat semua →</a>
                    <a href="{{ route('home') }}#warung" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">+ Pesan lagi</a>
                </div>
            </div>
            <div class="mt-4 grid gap-2.5">
                @php
                    $hasRecentOrders = $recentOrders->isNotEmpty();
                @endphp
                @if($hasRecentOrders)
                @foreach($recentOrders as $o)
                    @php
                        $tone = match ($o->status) {
                            'completed', 'paid' => 'bg-leaf-100 text-leaf-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                            'waiting_verification' => 'bg-amber-100 text-amber-800',
                            default => 'bg-brand-100 text-brand-700',
                        };
                    @endphp
                <a href="{{ route('orders.show', $o) }}" class="flex items-center gap-3 rounded-2xl border border-ink-900/10 p-3 hover:shadow-lg hover:shadow-ink-900/5 transition">
                    <span class="w-11 h-11 rounded-xl bg-cream-100 grid place-items-center text-xl">{{ $o->icon }}</span>
                    <div class="flex-1 min-w-0"><p class="text-sm font-extrabold truncate">{{ $o->item_name }}</p><p class="text-xs font-semibold text-ink-500 truncate">{{ $o->warung_name }} • {{ $o->created_at->diffForHumans() }}</p></div>
                    <div class="text-right shrink-0"><p class="text-sm font-black">Rp {{ number_format($o->total, 0, ',', '.') }}</p><span class="text-[11px] font-extrabold rounded-full px-2.5 py-1 {{ $tone }}">{{ $o->statusLabel() }}</span></div>
                </a>
                @endforeach
                @else
                <div class="rounded-2xl border border-dashed border-ink-900/15 p-6 text-center">
                    <p class="text-2xl">🍽️</p>
                    <p class="mt-1 text-sm font-extrabold">Belum ada pesanan</p>
                    <p class="text-xs font-semibold text-ink-500">Yuk jajan di warung terdekat dan kumpulkan poin.</p>
                    <a href="{{ route('home') }}#warung" class="mt-3 inline-block text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">Cari Warung →</a>
                </div>
                @endif
            </div>
        </div>
        </div>
        <div class="grid gap-3.5">
            @if(auth()->user()->role === 'penjual' && ! empty($wallet ?? null))
            <div class="rounded-[28px] bg-white border border-ink-900/10 p-6">
                <div class="flex items-center justify-between gap-3">
                    <p class="font-extrabold">Saldo warung 💰</p>
                    <a href="{{ route('seller.wallet.index') }}" class="text-[13px] font-extrabold text-brand-600">Tarik saldo →</a>
                </div>
                <p class="font-black text-2xl mt-2">Rp {{ number_format($wallet['balance'], 0, ',', '.') }}</p>
                <p class="text-[12px] font-semibold text-ink-500">Dana pesanan selesai yang siap ditarik.</p>
                @if($wallet['held'] > 0)
                <p class="mt-1 text-[12px] font-semibold text-amber-800">Rp {{ number_format($wallet['held'], 0, ',', '.') }} masih ditahan untuk pesanan yang belum selesai.</p>
                @endif
            </div>
            @endif
            @if(auth()->user()->role === 'penjual' && auth()->user()->isKycVerified())
            <div class="rounded-[28px] bg-ink-900 text-white p-6">
                <p class="font-extrabold">Kelola produkmu 📦</p>
                <p class="text-[13px] font-medium text-white/70 mt-1">Tambah, edit, dan pantau status verifikasi admin.</p>
                <div class="mt-4 flex gap-2 flex-wrap">
                    <a href="{{ route('seller.products.index') }}" class="bg-white text-ink-900 text-sm font-extrabold px-5 py-2.5 rounded-full">Kelola →</a>
                    <a href="{{ route('seller.transactions.index') }}" class="bg-white/10 border border-white/15 text-sm font-extrabold px-5 py-2.5 rounded-full hover:bg-white/20 transition">Transaksi</a>
                    <a href="{{ route('seller.transactions.create') }}" class="bg-brand-500 text-white text-sm font-extrabold px-5 py-2.5 rounded-full hover:bg-brand-600 transition">+ Catat penjualan</a>
                    <a href="{{ route('seller.products.create') }}" class="bg-white/10 border border-white/15 text-sm font-extrabold px-5 py-2.5 rounded-full hover:bg-white/20 transition">+ Tambah produk</a>
                    <a href="{{ route('seller.store.edit') }}" class="bg-white/10 border border-white/15 text-sm font-extrabold px-5 py-2.5 rounded-full hover:bg-white/20 transition">⚙️ Warung</a>
                </div>
            </div>
            @elseif(! auth()->user()->canSell())
            <div class="rounded-[28px] bg-leaf-600 text-white p-6">
                <p class="font-extrabold">Mau buka warung? 🏪</p>
                <p class="text-[13px] font-medium text-white/75 mt-1">Daftar jadi mitra gratis, 0% komisi 3 bulan pertama.</p>
                <a href="{{ route('home') }}#mitra" class="mt-4 inline-block bg-white text-leaf-700 text-sm font-extrabold px-5 py-2.5 rounded-full">Pelajari →</a>
            </div>
            @endif
            <div class="rounded-[28px] bg-white border border-ink-900/10 p-6">
                <p class="font-extrabold">Aksi cepat</p>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('home') }}#warung" class="text-center text-[13px] font-extrabold rounded-2xl bg-cream-100 py-3 hover:bg-ink-900 hover:text-white transition">📍 Terdekat</a>
                    <a href="{{ route('home') }}#kategori" class="text-center text-[13px] font-extrabold rounded-2xl bg-cream-100 py-3 hover:bg-ink-900 hover:text-white transition">🛍️ Kategori</a>
                    <a href="#" class="text-center text-[13px] font-extrabold rounded-2xl bg-cream-100 py-3 hover:bg-ink-900 hover:text-white transition">🎁 Voucher</a>
                    <a href="{{ route('contact') }}" class="text-center text-[13px] font-extrabold rounded-2xl bg-cream-100 py-3 hover:bg-ink-900 hover:text-white transition">💬 Bantuan</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
