@extends('layouts.admin')

@section('title', 'Pengguna — Backoffice Warung Hebat')

@section('content')
<p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="users" class="w-4 h-4" /> PENGGUNA</p>
<h1 class="font-black tracking-tight text-3xl mt-1">Semua pengguna</h1>
<p class="text-sm font-medium text-ink-500">{{ $users->total() }} akun terdaftar • peran: pembeli, penjual, admin.</p>

<div class="mt-5 flex flex-wrap gap-2 text-[13px] font-extrabold">
    <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full {{ !request('role') ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}"><x-icon name="users" class="w-4 h-4" /> Semua</a>
    <a href="{{ route('admin.users', ['role' => 'pembeli']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full {{ request('role') === 'pembeli' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}"><x-icon name="shopping-bag" class="w-4 h-4" /> Pembeli</a>
    <a href="{{ route('admin.users', ['role' => 'penjual']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full {{ request('role') === 'penjual' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}"><x-icon name="store" class="w-4 h-4" /> Penjual</a>
    <a href="{{ route('admin.users', ['role' => 'admin']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full {{ request('role') === 'admin' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}"><x-icon name="shield" class="w-4 h-4" /> Admin</a>
</div>

<form method="GET" action="{{ route('admin.users') }}" class="mt-4 flex flex-col sm:flex-row gap-2">
    @if(request('role'))
        <input type="hidden" name="role" value="{{ request('role') }}">
    @endif
    <label class="sr-only" for="user-search">Cari pengguna</label>
    <div class="flex flex-1 items-center gap-2 rounded-2xl bg-white border border-ink-900/10 px-4 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10">
        <x-icon name="search" class="w-5 h-5 text-ink-400" />
        <input id="user-search" name="search" value="{{ request('search') }}" type="search" placeholder="Cari nama atau email pengguna..." class="w-full bg-transparent py-3 text-sm font-semibold outline-none placeholder:text-ink-400">
    </div>
    <button type="submit" class="rounded-2xl bg-ink-900 px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition">Cari</button>
</form>

<div class="mt-5 rounded-[24px] bg-white border border-ink-900/10 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[640px]">
            <thead>
                <tr class="text-left text-[11px] font-extrabold tracking-widest text-ink-500 border-b border-ink-900/10">
                    <th class="px-5 py-4">NAMA</th>
                    <th class="px-5 py-4">EMAIL</th>
                    <th class="px-5 py-4">PERAN</th>
                    <th class="px-5 py-4">GABUNG</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr class="border-b border-ink-900/5 last:border-0 hover:bg-cream-50">
                    <td class="px-5 py-3.5 font-extrabold whitespace-nowrap">
                        <span class="inline-grid place-items-center w-8 h-8 rounded-lg text-white text-xs font-black mr-2 {{ $u->role === 'admin' ? 'bg-brand-500' : ($u->role === 'penjual' ? 'bg-leaf-600' : 'bg-ink-900') }}">{{ strtoupper(substr($u->name, 0, 1)) }}</span>{{ $u->name }}
                    </td>
                    <td class="px-5 py-3.5 font-medium text-ink-500">{{ $u->email }}</td>
                    <td class="px-5 py-3.5"><span class="text-[11px] font-extrabold rounded-full px-3 py-1.5 {{ $u->role === 'admin' ? 'bg-brand-100 text-brand-700' : ($u->role === 'penjual' ? 'bg-leaf-100 text-leaf-700' : 'bg-cream-100 text-ink-700') }}">{{ $u->role }}</span></td>
                    <td class="px-5 py-3.5 font-semibold text-ink-500 whitespace-nowrap">{{ $u->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-5 py-8 text-center font-semibold text-ink-500">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
