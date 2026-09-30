@extends('layouts.app')

@section('title', 'Transaksi Langsung — Warung Hebat')

@section('bare', true)

@section('content')
<section class="pt-6 sm:pt-8 pb-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between gap-2 mb-5 flex-wrap">
        <a href="{{ route('dashboard') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Dashboard</a>
        <a href="{{ route('seller.transactions.create') }}" class="min-h-10 inline-flex items-center gap-1.5 rounded-full bg-ink-900 px-5 py-2.5 text-[13px] font-extrabold text-white hover:bg-brand-600 transition"><x-icon name="plus" class="w-4 h-4" /> Catat transaksi</a>
    </div>

    {{-- ===== Hero ringkasan ===== --}}
    <div class="rounded-[28px] bg-ink-900 text-white p-6 sm:p-8 relative overflow-hidden grain">
        <div class="absolute -top-20 -right-20 w-72 h-72 bg-brand-500/30 blur-[90px] rounded-full"></div>
        <div class="relative flex gap-4 sm:gap-5">
            <span class="w-14 h-14 sm:w-16 sm:h-16 rounded-3xl bg-brand-500 grid place-items-center shrink-0"><x-icon name="ticket" class="w-7 h-7 text-white" /></span>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-extrabold tracking-[0.2em] text-brand-300">🏪 {{ $store->name }}</p>
                <h1 class="mt-1 font-black tracking-tight text-2xl sm:text-3xl">Transaksi langsung</h1>
                <p class="mt-1 text-[13px] font-medium text-white/60">Penjualan di warung kepada pengguna dan pembeli tanpa akun. Tidak termasuk saldo yang bisa ditarik.</p>
            </div>
        </div>
        {{-- Fluid type + min-w-0 so three tiles stay inside 320px screens. --}}
        <div class="relative mt-6 grid grid-cols-3 gap-2 sm:gap-2.5 max-w-lg">
            <div class="min-w-0 rounded-2xl bg-white/10 border border-white/10 p-2.5 sm:p-4">
                <p class="font-black tabular-nums text-[clamp(15px,4.6vw,20px)] leading-tight">Rp {{ number_format($summary['total'], 0, ',', '.') }}</p>
                <p class="mt-0.5 text-[clamp(9px,2.5vw,11px)] font-bold text-white/60 leading-tight">TOTAL • {{ number_format($summary['count'], 0, ',', '.') }} TRANSAKSI</p>
            </div>
            <div class="min-w-0 rounded-2xl bg-white/10 border border-white/10 p-2.5 sm:p-4">
                <p class="font-black tabular-nums text-[clamp(15px,4.6vw,20px)] leading-tight">Rp {{ number_format($summary['registered_total'], 0, ',', '.') }}</p>
                <p class="mt-0.5 text-[clamp(9px,2.5vw,11px)] font-bold text-white/60 leading-tight">TERDAFTAR • {{ number_format($summary['registered_count'], 0, ',', '.') }}</p>
            </div>
            <div class="min-w-0 rounded-2xl bg-white/10 border border-white/10 p-2.5 sm:p-4">
                <p class="font-black tabular-nums text-[clamp(15px,4.6vw,20px)] leading-tight">Rp {{ number_format($summary['walk_in_total'], 0, ',', '.') }}</p>
                <p class="mt-0.5 text-[clamp(9px,2.5vw,11px)] font-bold text-white/60 leading-tight">LANGSUNG • {{ number_format($summary['walk_in_count'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 rounded-2xl border border-leaf-200 bg-leaf-50 p-4 text-sm font-semibold text-leaf-800">{{ session('success') }}</div>
    @endif

    {{-- ===== Filter: tab jenis pembeli + rentang tanggal ===== --}}
    <div class="mt-4 rounded-[24px] bg-white border border-ink-900/10 p-3 sm:p-4 flex flex-col xl:flex-row xl:items-center gap-3">
        @php($tabBase = array_filter(['from' => request('from'), 'until' => request('until')]))
        <div class="flex gap-1.5 flex-wrap text-[13px] font-extrabold">
            <a href="{{ route('seller.transactions.index', $tabBase) }}" class="px-4 py-2 rounded-full transition {{ !request('buyer_type') ? 'bg-ink-900 text-white' : 'bg-cream-100 text-ink-700 hover:bg-ink-900/10' }}">Semua • {{ number_format($summary['count'], 0, ',', '.') }}</a>
            <a href="{{ route('seller.transactions.index', $tabBase + ['buyer_type' => 'registered']) }}" class="px-4 py-2 rounded-full transition {{ request('buyer_type') === 'registered' ? 'bg-ink-900 text-white' : 'bg-cream-100 text-ink-700 hover:bg-ink-900/10' }}">Terdaftar • {{ number_format($summary['registered_count'], 0, ',', '.') }}</a>
            <a href="{{ route('seller.transactions.index', $tabBase + ['buyer_type' => 'walk_in']) }}" class="px-4 py-2 rounded-full transition {{ request('buyer_type') === 'walk_in' ? 'bg-ink-900 text-white' : 'bg-cream-100 text-ink-700 hover:bg-ink-900/10' }}">Langsung • {{ number_format($summary['walk_in_count'], 0, ',', '.') }}</a>
        </div>
        <form method="GET" class="flex flex-col sm:flex-row gap-2 xl:ml-auto">
            @if(request('buyer_type'))
                <input type="hidden" name="buyer_type" value="{{ request('buyer_type') }}">
            @endif
            <label class="grid gap-1 text-xs font-bold flex-1 sm:flex-none">
                <span>Dari tanggal</span>
                <input name="from" type="date" value="{{ request('from') }}" class="rounded-xl border border-ink-900/15 px-3 py-2.5 text-sm font-semibold bg-white">
            </label>
            <label class="grid gap-1 text-xs font-bold flex-1 sm:flex-none">
                <span>Sampai tanggal</span>
                <input name="until" type="date" value="{{ request('until') }}" class="rounded-xl border border-ink-900/15 px-3 py-2.5 text-sm font-semibold bg-white">
            </label>
            <div class="flex gap-2 sm:items-end">
                <button class="min-h-10 flex-1 sm:flex-none rounded-full bg-brand-500 px-5 text-sm font-extrabold text-white hover:bg-brand-600 transition">Terapkan</button>
                <a href="{{ route('seller.transactions.index') }}" class="min-h-10 inline-flex flex-1 sm:flex-none items-center justify-center rounded-full border border-ink-900/15 px-5 text-sm font-extrabold hover:bg-ink-900 hover:text-white transition">Reset</a>
            </div>
        </form>
    </div>

    {{-- ===== Daftar transaksi, dikelompokkan per hari ===== --}}
    @if($transactions->isEmpty())
        <div class="mt-4 rounded-[28px] bg-white border border-dashed border-ink-900/15 p-8 sm:p-12 text-center">
            <span class="mx-auto w-14 h-14 rounded-3xl bg-cream-100 grid place-items-center text-ink-500"><x-icon name="ticket" class="w-7 h-7" /></span>
            <p class="mt-3 font-extrabold text-lg">Belum ada transaksi langsung</p>
            <p class="mt-1 text-sm font-medium text-ink-500">Catat pembelian di warung agar stok dan laporan penjualanmu selalu sesuai.</p>
            <a href="{{ route('seller.transactions.create') }}" class="mt-4 inline-flex min-h-10 items-center gap-1.5 rounded-full bg-ink-900 px-6 py-2.5 text-sm font-extrabold text-white hover:bg-brand-600 transition"><x-icon name="plus" class="w-4 h-4" /> Catat transaksi</a>
        </div>
    @else
        @php($grouped = $transactions->getCollection()->groupBy(fn ($t) => $t->created_at->format('Y-m-d')))
        <div class="mt-5 grid gap-5">
            @foreach($grouped as $day => $dayTransactions)
                @php($dayCarbon = \Carbon\Carbon::parse($day))
                <section>
                    <div class="flex items-center gap-3 mb-2.5 px-1">
                        <p class="text-[12px] font-extrabold tracking-wider text-ink-500 uppercase whitespace-nowrap">{{ $dayCarbon->isToday() ? 'Hari ini' : ($dayCarbon->isYesterday() ? 'Kemarin' : $dayCarbon->format('d M Y')) }}</p>
                        <span class="h-px flex-1 bg-ink-900/10" aria-hidden="true"></span>
                        <p class="text-[12px] font-bold text-ink-500 whitespace-nowrap">{{ $dayTransactions->count() }} transaksi • Rp {{ number_format($dayTransactions->sum('total'), 0, ',', '.') }}</p>
                    </div>
                    <div class="grid gap-2.5">
                        @foreach($dayTransactions as $transaction)
                            <article class="rounded-[24px] bg-white border border-ink-900/10 p-4 sm:p-5 transition hover:shadow-lg hover:shadow-ink-900/5 hover:-translate-y-0.5">
                                <div class="flex gap-3 sm:gap-4">
                                    <span class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl grid place-items-center shrink-0 {{ $transaction->buyer_type === 'registered' ? 'bg-brand-100 text-brand-700' : 'bg-cream-100 text-ink-600' }}">
                                        <x-icon name="{{ $transaction->buyer_type === 'registered' ? 'user' : 'shopping-bag' }}" class="w-5 h-5 sm:w-6 sm:h-6" />
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <p class="font-extrabold leading-snug break-words">{{ $transaction->buyer_name ?: 'Pembeli langsung' }}</p>
                                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $transaction->buyer_type === 'registered' ? 'bg-brand-100 text-brand-700' : 'bg-cream-100 text-ink-600' }}">{{ $transaction->buyer_type === 'registered' ? 'Pengguna terdaftar' : 'Pembeli langsung' }}</span>
                                        </div>
                                        <p class="mt-0.5 text-xs font-semibold text-ink-500 break-words">{{ $transaction->created_at->format('H:i') }}@if($transaction->buyer_email || $transaction->buyer_phone) • {{ $transaction->buyer_email }}@if($transaction->buyer_email && $transaction->buyer_phone) • @endif{{ $transaction->buyer_phone }}@endif</p>
                                        <ul class="mt-3 grid gap-1.5 text-[13px]">
                                            @foreach($transaction->items as $item)
                                                <li class="flex items-baseline gap-2 min-w-0">
                                                    <span class="font-semibold min-w-0">{{ $item->name }} <span class="font-bold text-ink-500 whitespace-nowrap">× {{ number_format($item->qty, 0, ',', '.') }}</span></span>
                                                    <span class="flex-1 border-b border-dotted border-ink-900/20 -translate-y-1 min-w-4" aria-hidden="true"></span>
                                                    <span class="font-bold whitespace-nowrap">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="mt-3 pt-3 border-t border-ink-900/10 flex flex-col sm:flex-row sm:items-center gap-2.5">
                                            <p class="font-black text-lg sm:mr-auto">Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
                                            <div class="grid grid-cols-2 gap-2 sm:flex">
                                                <a href="{{ route('seller.transactions.receipt', $transaction) }}" target="_blank" rel="noopener" class="min-h-10 inline-flex items-center justify-center gap-1.5 rounded-full border border-ink-900/15 px-4 text-[13px] font-extrabold hover:bg-ink-900 hover:text-white transition"><x-icon name="printer" class="w-4 h-4" /> Struk</a>
                                                <a href="{{ route('seller.transactions.edit', $transaction) }}" class="min-h-10 inline-flex items-center justify-center gap-1.5 rounded-full border border-ink-900/15 px-4 text-[13px] font-extrabold hover:bg-ink-900 hover:text-white transition"><x-icon name="pencil" class="w-4 h-4" /> Edit</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
        <div class="mt-6 flex justify-center">{{ $transactions->links() }}</div>
    @endif
</section>
@endsection
