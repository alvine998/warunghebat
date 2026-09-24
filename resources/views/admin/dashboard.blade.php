@extends('layouts.admin')

@section('title', 'Ringkasan — Backoffice Warung Hebat')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
        <p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="chart" class="w-4 h-4" /> RINGKASAN</p>
        <h1 class="font-black tracking-tight text-3xl mt-1">Halo, {{ strtok(auth()->user()->name, ' ') }}</h1>
        <p class="text-sm font-medium text-ink-500">Kondisi marketplace hari ini.</p>
    </div>
    <a href="{{ route('admin.users') }}" class="text-sm font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition w-fit">Kelola Pengguna →</a>
</div>

<div class="mt-5 grid grid-cols-2 lg:grid-cols-4 gap-3">
    @foreach([
        ['users','Total Pengguna', $stats['users'], 'bg-ink-900 text-white'],
        ['shopping-bag','Pembeli', $stats['pembeli'], 'bg-white border border-ink-900/10'],
        ['store','Penjual', $stats['penjual'], 'bg-white border border-ink-900/10'],
        ['shield','Admin', $stats['admins'], 'bg-brand-500 text-white'],
    ] as $s)
    <div class="rounded-[24px] p-5 {{ $s[3] }}">
        <x-icon name="{{ $s[0] }}" class="w-7 h-7" />
        <p class="font-black text-3xl mt-1">{{ number_format($s[2], 0, ',', '.') }}</p>
        <p class="text-[12px] font-bold opacity-70">{{ $s[1] }}</p>
    </div>
    @endforeach
</div>

<div class="mt-3 rounded-[24px] bg-amber-50 border border-amber-200 p-5 flex flex-col sm:flex-row sm:items-center gap-3">
    <div class="flex-1">
        <p class="flex items-center gap-2 font-extrabold"><x-icon name="clock" class="w-5 h-5" /> {{ number_format($stats['pending_products'] ?? 0, 0, ',', '.') }} produk menunggu verifikasi</p>
        <p class="text-[13px] font-medium text-amber-900/70">Setujui produk yang layak tayang, tolak dengan alasan yang jelas.</p>
    </div>
    <a href="{{ route('admin.products', ['status' => 'pending']) }}" class="text-sm font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition w-fit">Verifikasi →</a>
</div>

@if(($stats['pending_kyc'] ?? 0) > 0)
<div class="mt-3 rounded-[24px] bg-white border border-amber-200 p-5">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1">
            <p class="flex items-center gap-2 font-extrabold">🛡️ {{ number_format($stats['pending_kyc'], 0, ',', '.') }} pengajuan KYC menunggu pemeriksaan</p>
            <p class="text-[13px] font-medium text-ink-500">Periksa KTP + selfie + foto warung sebelum warung boleh jualan.</p>
        </div>
        <a href="{{ route('admin.warungs', ['kyc' => 'pending']) }}" class="text-sm font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition w-fit">Periksa →</a>
    </div>
    @if(($pendingKyc ?? collect())->isNotEmpty())
    <div class="mt-4 grid gap-2">
        @foreach($pendingKyc as $kyc)
        <div class="flex items-center gap-3 rounded-2xl border border-ink-900/10 p-2.5">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-extrabold truncate">{{ $kyc->full_name }} — {{ $kyc->user?->store?->name ?? 'Warung' }}</p>
                <p class="text-xs font-semibold text-ink-500">NIK {{ $kyc->nik }} • {{ $kyc->created_at->diffForHumans() }}</p>
            </div>
            <a href="{{ route('admin.warungs', ['kyc' => 'pending', 'search' => $kyc->full_name]) }}" class="text-[11px] font-extrabold rounded-full px-3 py-1.5 bg-amber-100 text-amber-800 hover:bg-ink-900 hover:text-white transition">Periksa</a>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif

@if($stats['pending_payments'] > 0 || $stats['pending_withdrawals'] > 0)
<div class="mt-3 rounded-[24px] bg-white border border-ink-900/10 p-5 flex flex-col sm:flex-row sm:items-center gap-3">
    <div class="flex-1">
        <p class="flex items-center gap-2 font-extrabold"><x-icon name="wallet" class="w-5 h-5" /> Perlu tindakan keuangan</p>
        <p class="text-[13px] font-medium text-ink-500">
            {{ number_format($stats['pending_payments'], 0, ',', '.') }} bukti transfer menunggu verifikasi • {{ number_format($stats['pending_withdrawals'], 0, ',', '.') }} penarikan menunggu diproses
            @if($stats['escrow'] > 0)
                • Rp {{ number_format($stats['escrow'], 0, ',', '.') }} dana ditahan
            @endif
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.finance') }}" class="text-sm font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition">Overview →</a>
        <a href="{{ route('admin.payments') }}" class="text-sm font-extrabold border border-ink-900/15 px-5 py-2.5 rounded-full hover:bg-ink-900 hover:text-white transition">Verifikasi →</a>
        <a href="{{ route('admin.withdrawals') }}" class="text-sm font-extrabold border border-ink-900/15 px-5 py-2.5 rounded-full hover:bg-ink-900 hover:text-white transition">Penarikan →</a>
    </div>
</div>
@endif

<div class="mt-4 grid gap-3.5 lg:grid-cols-2">
    <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-lg">Pengguna terbaru</h2>
            <a href="{{ route('admin.users') }}" class="text-[13px] font-extrabold text-brand-600">Lihat semua →</a>
        </div>
        <div class="mt-4 grid gap-2">
            @forelse($latestUsers as $u)
            <div class="flex items-center gap-3 rounded-2xl border border-ink-900/10 p-2.5">
                <span class="w-10 h-10 rounded-xl grid place-items-center font-black text-white {{ $u->role === 'admin' ? 'bg-brand-500' : ($u->role === 'penjual' ? 'bg-leaf-600' : 'bg-ink-900') }}">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-extrabold truncate">{{ $u->name }}</p>
                    <p class="text-xs font-semibold text-ink-500 truncate">{{ $u->email }}</p>
                </div>
                <span class="text-[11px] font-extrabold rounded-full px-3 py-1.5 {{ $u->role === 'admin' ? 'bg-brand-100 text-brand-700' : ($u->role === 'penjual' ? 'bg-leaf-100 text-leaf-700' : 'bg-cream-100 text-ink-700') }}">{{ $u->role }}</span>
            </div>
            @empty
            <p class="text-sm font-semibold text-ink-500">Belum ada pengguna.</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-lg">Warung terbaru</h2>
            <a href="{{ route('admin.warungs') }}" class="text-[13px] font-extrabold text-brand-600">Kelola →</a>
        </div>
        <div class="mt-4 grid gap-2">
            @forelse($latestStores as $w)
            <div class="flex items-center gap-3 rounded-2xl border border-ink-900/10 p-2.5">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-extrabold truncate">{{ $w->name }}</p>
                    <p class="text-xs font-semibold text-ink-500 truncate">{{ $w->user?->name }} • {{ number_format($w->products_count, 0, ',', '.') }} produk</p>
                </div>
                <span class="text-[11px] font-extrabold rounded-full px-3 py-1.5 {{ $w->is_open ? 'bg-leaf-100 text-leaf-700' : 'bg-amber-100 text-amber-800' }}">{{ $w->is_open ? 'Buka' : 'Tutup' }}</span>
            </div>
            @empty
            <p class="text-sm font-semibold text-ink-500">Belum ada warung.</p>
            @endforelse
        </div>
    </div>
</div>

@if(($pendingProducts ?? collect())->isNotEmpty())
<div class="mt-3.5 rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6">
    <div class="flex items-center justify-between">
        <h2 class="font-extrabold text-lg">Perlu verifikasi</h2>
        <a href="{{ route('admin.products', ['status' => 'pending']) }}" class="text-[13px] font-extrabold text-brand-600">Semua →</a>
    </div>
    <div class="mt-4 grid gap-2">
        @foreach($pendingProducts as $p)
        <div class="flex items-center gap-3 rounded-2xl border border-ink-900/10 p-2.5">
            @if($p->image_path)
                <img src="{{ $p->image_url }}" alt="Foto {{ $p->name }}" class="w-10 h-10 rounded-xl object-cover border border-ink-900/10 shrink-0">
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-extrabold truncate">{{ $p->name }}</p>
                <p class="text-xs font-semibold text-ink-500">{{ $p->user->name }} • {{ $p->category }} • Rp {{ number_format($p->price, 0, ',', '.') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.products.approve', $p) }}">
                @csrf
                @method('PATCH')
                <button class="text-[11px] font-extrabold rounded-full px-3 py-1.5 bg-leaf-600 text-white hover:bg-leaf-700 transition">Setujui</button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
