@extends('layouts.admin')

@section('title', 'Metode Pembayaran — Backoffice Warung Hebat')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
        <p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="wallet" class="w-4 h-4" /> METODE PEMBAYARAN</p>
        <h1 class="font-black tracking-tight text-3xl mt-1">Rekening Warung Hebat</h1>
        <p class="text-sm font-medium text-ink-500">Pembeli mentransfer ke rekening ini, lalu dana diteruskan ke saldo warung setelah pesanan selesai.</p>
    </div>
    <a href="{{ route('admin.payment-methods.create') }}" class="text-sm font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition w-fit">+ Tambah metode</a>
</div>

<form method="GET" action="{{ route('admin.payment-methods') }}" class="mt-4 flex flex-col sm:flex-row gap-2">
    <label class="sr-only" for="method-search">Cari metode pembayaran</label>
    <div class="flex flex-1 items-center gap-2 rounded-2xl bg-white border border-ink-900/10 px-4 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10">
        <x-icon name="search" class="w-5 h-5 text-ink-400" />
        <input id="method-search" name="search" value="{{ request('search') }}" type="search" placeholder="Cari nama metode, tipe, atau nomor rekening..." class="w-full bg-transparent py-3 text-sm font-semibold outline-none placeholder:text-ink-400">
    </div>
    <button type="submit" class="rounded-2xl bg-ink-900 px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition">Cari</button>
</form>

<div class="mt-5 grid gap-2.5">
    @forelse($methods as $method)
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                @if($method->image_path)
                    <img src="{{ $method->image_url }}" alt="{{ $method->name }}" class="w-full sm:w-16 h-40 sm:h-16 rounded-2xl object-cover border border-ink-900/10 shrink-0">
                @else
                    <div class="w-full sm:w-16 h-40 sm:h-16 rounded-2xl bg-cream-100 border border-dashed border-ink-900/15 grid place-items-center shrink-0"><x-icon name="wallet" class="w-7 h-7 text-ink-500" /></div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-extrabold">{{ $method->name }}</p>
                        <span class="text-[11px] font-extrabold rounded-full px-3 py-1 bg-cream-100 text-ink-700">{{ $method->typeLabel() }}</span>
                        <span class="text-[11px] font-extrabold rounded-full px-3 py-1 {{ $method->is_active ? 'bg-leaf-100 text-leaf-700' : 'bg-red-100 text-red-700' }}">{{ $method->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        <span class="text-[11px] font-bold text-ink-500">Urutan {{ $method->sort_order }}</span>
                    </div>

                    @if($method->account_number)
                        <p class="mt-1 font-black tracking-wide">{{ $method->account_number }}</p>
                    @endif
                    @if($method->account_name)
                        <p class="text-[12px] font-semibold text-ink-500">a/n {{ $method->account_name }}</p>
                    @endif
                    @if($method->instructions)
                        <p class="mt-1 text-[13px] font-medium text-ink-700">{{ $method->instructions }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    <form method="POST" action="{{ route('admin.payment-methods.toggle', $method) }}">
                        @csrf
                        @method('PATCH')
                        <button class="text-[13px] font-extrabold px-4 py-2 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">{{ $method->is_active ? 'Matikan' : 'Aktifkan' }}</button>
                    </form>
                    <a href="{{ route('admin.payment-methods.edit', $method) }}" class="text-[13px] font-extrabold px-4 py-2 rounded-full bg-cream-100 hover:bg-ink-900 hover:text-white transition">Edit</a>
                    <form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}" onsubmit="return confirm('Hapus metode ini? Pembeli tidak bisa lagi transfer ke rekening ini.');">
                        @csrf
                        @method('DELETE')
                        <button class="text-[13px] font-extrabold px-4 py-2 rounded-full bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-8 text-center">
            @if(request('search'))
                <p class="font-extrabold">Tidak ada metode yang cocok dengan "{{ request('search') }}"</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Coba kata kunci lain seperti nama bank, tipe, atau nomor rekening.</p>
            @else
                <p class="text-3xl">🏦</p>
                <p class="font-extrabold mt-2">Belum ada metode pembayaran</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Pembeli tidak bisa membayar sebelum kamu menambahkan rekening tujuan.</p>
                <a href="{{ route('admin.payment-methods.create') }}" class="mt-4 inline-block text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">+ Tambah metode</a>
            @endif
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $methods->links() }}</div>
@endsection
