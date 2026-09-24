@extends('layouts.app')

@section('title', 'Transaksi Langsung — Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-10 sm:pb-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between gap-2 flex-wrap">
        <a href="{{ route('dashboard') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Dashboard</a>
        <a href="{{ route('seller.transactions.create') }}" class="min-h-10 inline-flex items-center rounded-full bg-ink-900 px-5 py-2.5 text-[13px] font-extrabold text-white hover:bg-brand-600 transition">+ Catat transaksi</a>
    </div>

    <p class="mt-4 text-[11px] font-extrabold tracking-[0.2em] text-leaf-700">🏪 {{ $store->name }}</p>
    <h1 class="mt-1 font-black tracking-tight text-2xl sm:text-3xl">Transaksi langsung</h1>
    <p class="mt-1 text-[13px] font-medium text-ink-500">Penjualan di warung kepada pengguna dan pembeli tanpa akun. Tidak termasuk saldo yang bisa ditarik.</p>

    @if(session('success'))
        <div class="mt-4 rounded-2xl border border-leaf-200 bg-leaf-50 p-4 text-sm font-semibold text-leaf-800">{{ session('success') }}</div>
    @endif

    <div class="mt-4 grid grid-cols-2 gap-2.5 lg:grid-cols-3">
        <div class="rounded-[24px] bg-ink-900 p-4 text-white sm:p-5">
            <p class="text-[11px] font-extrabold tracking-wider text-white/60">TOTAL PENJUALAN</p>
            <p class="mt-1 text-xl font-black sm:text-2xl">Rp {{ number_format($summary['total'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs font-semibold text-white/60">{{ number_format($summary['count'], 0, ',', '.') }} transaksi</p>
        </div>
        <div class="rounded-[24px] border border-ink-900/10 bg-white p-4 sm:p-5">
            <p class="text-[11px] font-extrabold tracking-wider text-ink-500">PENGGUNA TERDAFTAR</p>
            <p class="mt-1 text-xl font-black sm:text-2xl">Rp {{ number_format($summary['registered_total'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs font-semibold text-ink-500">{{ number_format($summary['registered_count'], 0, ',', '.') }} transaksi</p>
        </div>
        <div class="col-span-2 rounded-[24px] border border-ink-900/10 bg-white p-4 sm:p-5 lg:col-span-1">
            <p class="text-[11px] font-extrabold tracking-wider text-ink-500">PEMBELI LANGSUNG</p>
            <p class="mt-1 text-xl font-black sm:text-2xl">Rp {{ number_format($summary['walk_in_total'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs font-semibold text-ink-500">{{ number_format($summary['walk_in_count'], 0, ',', '.') }} transaksi</p>
        </div>
    </div>

    <form method="GET" class="mt-4 grid gap-2 rounded-[24px] border border-ink-900/10 bg-white p-4 sm:grid-cols-4 sm:items-end">
        <label class="grid gap-1 text-xs font-bold">
            <span>Jenis pembeli</span>
            <select name="buyer_type" class="rounded-xl border border-ink-900/15 bg-white px-3 py-2.5 text-sm">
                <option value="">Semua</option>
                <option value="registered" @selected(request('buyer_type') === 'registered')>Pengguna terdaftar</option>
                <option value="walk_in" @selected(request('buyer_type') === 'walk_in')>Pembeli langsung</option>
            </select>
        </label>
        <label class="grid gap-1 text-xs font-bold">
            <span>Dari tanggal</span>
            <input name="from" type="date" value="{{ request('from') }}" class="rounded-xl border border-ink-900/15 px-3 py-2.5 text-sm">
        </label>
        <label class="grid gap-1 text-xs font-bold">
            <span>Sampai tanggal</span>
            <input name="until" type="date" value="{{ request('until') }}" class="rounded-xl border border-ink-900/15 px-3 py-2.5 text-sm">
        </label>
        <div class="flex gap-2">
            <button class="min-h-10 flex-1 rounded-full bg-brand-500 px-4 text-sm font-extrabold text-white hover:bg-brand-600">Terapkan</button>
            <a href="{{ route('seller.transactions.index') }}" class="min-h-10 inline-flex items-center rounded-full border border-ink-900/15 px-4 text-sm font-extrabold">Reset</a>
        </div>
    </form>

    <div class="mt-4 overflow-hidden rounded-[24px] border border-ink-900/10 bg-white">
        @if($transactions->isEmpty())
            <div class="p-8 text-center sm:p-12">
                <p class="text-3xl">🧾</p>
                <p class="mt-2 font-extrabold">Belum ada transaksi langsung</p>
                <p class="mt-1 text-sm font-medium text-ink-500">Catat pembelian di warung agar stok dan laporan penjualanmu selalu sesuai.</p>
                <a href="{{ route('seller.transactions.create') }}" class="mt-4 inline-flex min-h-10 items-center rounded-full bg-ink-900 px-5 text-sm font-extrabold text-white">+ Catat transaksi</a>
            </div>
        @else
            <div class="divide-y divide-ink-900/5">
                @foreach($transactions as $transaction)
                    <article class="p-4 sm:p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-3 py-1 text-[11px] font-extrabold {{ $transaction->buyer_type === 'registered' ? 'bg-brand-100 text-brand-700' : 'bg-cream-100 text-ink-600' }}">{{ $transaction->buyer_type === 'registered' ? 'Pengguna terdaftar' : 'Pembeli langsung' }}</span>
                                    <span class="text-xs font-semibold text-ink-500">{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                                </div>
                                <p class="mt-1 font-extrabold">{{ $transaction->buyer_name ?: 'Pembeli langsung' }}</p>
                                @if($transaction->buyer_email || $transaction->buyer_phone)
                                    <p class="text-xs font-medium text-ink-500">{{ $transaction->buyer_email }}@if($transaction->buyer_email && $transaction->buyer_phone) • @endif{{ $transaction->buyer_phone }}</p>
                                @endif
                                <p class="mt-2 text-sm font-semibold text-ink-600">{{ $transaction->items->map(fn ($item) => $item->name.' × '.number_format($item->qty, 0, ',', '.'))->join(', ') }}</p>
                            </div>
                            <p class="shrink-0 font-black">Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="border-t border-ink-900/5 p-4">{{ $transactions->links() }}</div>
        @endif
    </div>
</section>
@endsection
