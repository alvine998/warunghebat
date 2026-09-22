@extends('layouts.admin')

@section('title', 'Ringkasan — Backoffice Warung Hebat')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
        <p class="text-[11px] font-extrabold tracking-[0.2em] text-brand-600">📊 RINGKASAN</p>
        <h1 class="font-black tracking-tight text-3xl mt-1">Halo, {{ strtok(auth()->user()->name, ' ') }} 👋</h1>
        <p class="text-sm font-medium text-ink-500">Kondisi marketplace hari ini, Selasa 23 Sep 2026.</p>
    </div>
    <a href="{{ route('admin.users') }}" class="text-sm font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition w-fit">Kelola Pengguna →</a>
</div>

<div class="mt-5 grid grid-cols-2 lg:grid-cols-4 gap-3">
    @foreach([
        ['👥','Total Pengguna', $stats['users'], 'bg-ink-900 text-white'],
        ['🛍️','Pembeli', $stats['pembeli'], 'bg-white border border-ink-900/10'],
        ['🏪','Penjual', $stats['penjual'], 'bg-white border border-ink-900/10'],
        ['🛠️','Admin', $stats['admins'], 'bg-brand-500 text-white'],
    ] as $s)
    <div class="rounded-[24px] p-5 {{ $s[3] }}">
        <p class="text-2xl">{{ $s[0] }}</p>
        <p class="font-black text-3xl mt-1">{{ number_format($s[2], 0, ',', '.') }}</p>
        <p class="text-[12px] font-bold opacity-70">{{ $s[1] }}</p>
    </div>
    @endforeach
</div>

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
            <h2 class="font-extrabold text-lg">Warung populer</h2>
            <a href="{{ route('admin.warungs') }}" class="text-[13px] font-extrabold text-brand-600">Kelola →</a>
        </div>
        <div class="mt-4 grid gap-2">
            @foreach($warungs as $w)
            <div class="flex items-center gap-3 rounded-2xl border border-ink-900/10 p-2.5">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-extrabold truncate">{{ $w['name'] }}</p>
                    <p class="text-xs font-semibold text-ink-500">{{ $w['owner'] }} • {{ $w['cat'] }} • {{ number_format($w['orders'], 0, ',', '.') }} pesanan</p>
                </div>
                <span class="text-[11px] font-extrabold rounded-full px-3 py-1.5 {{ $w['status'] === 'Aktif' ? 'bg-leaf-100 text-leaf-700' : 'bg-amber-100 text-amber-800' }}">{{ $w['status'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
