@extends('layouts.app')

@section('title', 'Saldo Warung — Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-10 sm:pb-14 max-w-4xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between gap-2 flex-wrap">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 min-h-10 text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Dashboard</a>
        <a href="{{ route('seller.products.index') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">Kelola produk →</a>
    </div>

    <h1 class="mt-3 font-black tracking-tight text-[26px] sm:text-3xl">Saldo & penarikan</h1>
    <p class="text-[13px] font-semibold text-ink-500 mt-1">Dana pesanan <span class="font-extrabold text-ink-900">{{ $store->name }}</span> yang sudah dilepas Warung Hebat.</p>

    {{-- ===== BALANCE ===== --}}
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
        <div class="rounded-[28px] bg-ink-900 text-white p-5 sm:p-6 relative overflow-hidden grain">
            <div class="absolute -top-16 -right-16 w-48 h-48 bg-brand-500/30 blur-[70px] rounded-full pointer-events-none"></div>
            <p class="relative flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-300"><x-icon name="wallet" class="w-4 h-4" /> SALDO TERSEDIA</p>
            <p class="relative font-black text-3xl sm:text-4xl mt-2">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</p>
            <p class="relative mt-1 text-[12px] font-semibold text-white/60">Bisa ditarik kapan saja, minimal Rp {{ number_format($min, 0, ',', '.') }}.</p>
        </div>
        <div class="rounded-[28px] bg-cream-100 border border-ink-900/10 p-5 sm:p-6">
            <p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-ink-500"><x-icon name="clock" class="w-4 h-4" /> DANA DITAHAN</p>
            <p class="font-black text-3xl sm:text-4xl mt-2">Rp {{ number_format($held, 0, ',', '.') }}</p>
            <p class="mt-1 text-[12px] font-semibold text-ink-500">Dari pesanan yang sudah dibayar tapi belum selesai.</p>
        </div>
    </div>

    <div class="mt-3.5 grid gap-3.5 lg:grid-cols-2">
        {{-- ===== WITHDRAWAL FORM ===== --}}
        <div class="rounded-[28px] bg-white border border-ink-900/10 p-5 sm:p-6">
            <p class="font-extrabold">Tarik saldo</p>
            <p class="text-[13px] font-medium text-ink-500 mt-0.5">Minimal Rp {{ number_format($min, 0, ',', '.') }}, maksimal Rp {{ number_format($max, 0, ',', '.') }} per penarikan.</p>

            <form method="POST" action="{{ route('seller.wallet.withdraw') }}" class="mt-4 grid gap-3">
                @csrf

                <div>
                    <label for="amount" class="text-[13px] font-extrabold">Nominal (Rp) *</label>
                    <input id="amount" name="amount" required data-numeric data-max="{{ $max }}" inputmode="numeric" value="{{ old('amount') }}" placeholder="50.000" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                </div>

                <div>
                    <label for="bank_name" class="text-[13px] font-extrabold">Bank / e-wallet *</label>
                    <input id="bank_name" name="bank_name" required maxlength="80" value="{{ old('bank_name', $lastWithdrawal?->bank_name) }}" placeholder="cth. BCA, GoPay" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="account_number" class="text-[13px] font-extrabold">No. rekening *</label>
                        <input id="account_number" name="account_number" required maxlength="50" value="{{ old('account_number', $lastWithdrawal?->account_number) }}" placeholder="1234567890" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    </div>
                    <div>
                        <label for="account_name" class="text-[13px] font-extrabold">Nama pemilik *</label>
                        <input id="account_name" name="account_name" required maxlength="80" value="{{ old('account_name', $lastWithdrawal?->account_name ?? $store->user?->name) }}" placeholder="Nama sesuai rekening" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    </div>
                </div>

                <div>
                    <label for="store_note" class="text-[13px] font-extrabold">Catatan (opsional)</label>
                    <input id="store_note" name="store_note" maxlength="255" value="{{ old('store_note') }}" placeholder="cth. tolong transfer hari ini" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                </div>

                <button class="w-full min-h-12 rounded-full bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-sm transition disabled:opacity-40" @disabled($wallet->balance < $min)>
                    @if($wallet->balance < $min)
                        Saldo belum cukup (min Rp {{ number_format($min, 0, ',', '.') }})
                    @else
                        Ajukan penarikan
                    @endif
                </button>
            </form>
        </div>

        {{-- ===== WITHDRAWAL HISTORY ===== --}}
        <div class="rounded-[28px] bg-white border border-ink-900/10 p-5 sm:p-6">
            <p class="font-extrabold">Riwayat penarikan</p>
            <div class="mt-3 grid gap-2">
                @forelse($withdrawals as $withdrawal)
                    <div class="rounded-2xl border border-ink-900/10 p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-black text-[15px]">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</p>
                                <p class="text-[11px] font-semibold text-ink-500 truncate">{{ $withdrawal->bank_name }} • {{ $withdrawal->account_number }}</p>
                            </div>
                            <span class="shrink-0 text-[11px] font-extrabold rounded-full px-2.5 py-1 {{ $withdrawal->status === 'paid' ? 'bg-leaf-100 text-leaf-700' : ($withdrawal->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800') }}">{{ $withdrawal->statusLabel() }}</span>
                        </div>
                        <p class="mt-1 text-[11px] font-semibold text-ink-500">{{ $withdrawal->created_at->diffForHumans() }}@if($withdrawal->admin_note) • {{ $withdrawal->admin_note }}@endif</p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-ink-900/15 p-5 text-center">
                        <p class="text-sm font-extrabold">Belum ada penarikan</p>
                        <p class="text-xs font-semibold text-ink-500 mt-0.5">Dana yang sudah dilepas bisa kamu tarik ke rekening.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ===== LEDGER ===== --}}
    <div class="mt-3.5 rounded-[28px] bg-white border border-ink-900/10 p-5 sm:p-6">
        <p class="font-extrabold">Mutasi saldo</p>
        <p class="text-[13px] font-medium text-ink-500 mt-0.5">Semua uang masuk dari pesanan selesai dan keluar untuk penarikan.</p>

        @if($transactions->isEmpty())
            <div class="mt-3 rounded-2xl border border-dashed border-ink-900/15 p-6 text-center">
                <p class="text-sm font-extrabold">Belum ada mutasi</p>
                <p class="text-xs font-semibold text-ink-500 mt-0.5">Saldo bertambah setiap pesanan selesai diverifikasi.</p>
            </div>
        @else
            <div class="mt-3 overflow-x-auto rounded-2xl border border-ink-900/10">
                <table class="w-full text-left text-sm min-w-[520px]">
                    <thead>
                        <tr class="bg-cream-50/60 text-[11px] font-extrabold uppercase tracking-wider text-ink-500">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Keterangan</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-900/5">
                        @foreach($transactions as $trx)
                            <tr>
                                <td class="px-4 py-3 font-bold whitespace-nowrap">{{ $trx->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-semibold break-words">{{ $trx->description }}</td>
                                <td class="px-4 py-3 font-black text-right whitespace-nowrap {{ $trx->isCredit() ? 'text-leaf-700' : 'text-red-700' }}">
                                    {{ $trx->isCredit() ? '+' : '−' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 font-bold text-right whitespace-nowrap">Rp {{ number_format($trx->balance_after, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $transactions->links() }}</div>
        @endif
    </div>
</section>
@endsection
