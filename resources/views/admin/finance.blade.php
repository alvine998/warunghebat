@extends('layouts.admin')

@section('title', 'Overview Keuangan — Backoffice Warung Hebat')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
        <p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="chart" class="w-4 h-4" /> KEUANGAN</p>
        <h1 class="font-black tracking-tight text-3xl mt-1">Overview keuangan</h1>
        <p class="text-sm font-medium text-ink-500">Uang pembeli yang ditahan, dilepas ke mitra, dan dibayarkan Warung Hebat.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.withdrawals') }}" class="text-sm font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition">Penarikan →</a>
        <a href="{{ route('admin.settings') }}" class="text-sm font-extrabold border border-ink-900/15 px-5 py-2.5 rounded-full hover:bg-ink-900 hover:text-white transition">Pengaturan →</a>
    </div>
</div>

{{-- ===== POSITION ===== --}}
<h2 class="mt-6 font-extrabold text-lg">Posisi uang saat ini</h2>
<div class="mt-3 grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="rounded-[24px] p-5 bg-ink-900 text-white">
        <p class="text-xl">💰</p>
        <p class="font-black text-xl sm:text-2xl mt-1 tabular-nums break-words">Rp {{ number_format($position['escrow'], 0, ',', '.') }}</p>
        <p class="text-[12px] font-bold opacity-70">Dana ditahan</p>
        <p class="text-[11px] font-semibold opacity-60">{{ number_format($position['escrow_count'], 0, ',', '.') }} pesanan belum selesai</p>
    </div>
    <div class="rounded-[24px] p-5 bg-white border border-ink-900/10">
        <p class="text-xl">🏦</p>
        <p class="font-black text-xl sm:text-2xl mt-1 tabular-nums break-words">Rp {{ number_format($position['wallets'], 0, ',', '.') }}</p>
        <p class="text-[12px] font-bold text-ink-500">Saldo warung</p>
        <p class="text-[11px] font-semibold text-ink-500/80">Siap ditarik mitra</p>
    </div>
    <div class="rounded-[24px] p-5 bg-white border border-ink-900/10">
        <p class="text-xl">⏳</p>
        <p class="font-black text-xl sm:text-2xl mt-1 tabular-nums break-words">Rp {{ number_format($position['withdrawals_pending'], 0, ',', '.') }}</p>
        <p class="text-[12px] font-bold text-ink-500">Penarikan menunggu</p>
        <p class="text-[11px] font-semibold text-ink-500/80">{{ number_format($position['withdrawals_pending_count'], 0, ',', '.') }} permintaan</p>
    </div>
    <div class="rounded-[24px] p-5 bg-brand-500 text-white">
        <p class="text-xl">📈</p>
        <p class="font-black text-xl sm:text-2xl mt-1 tabular-nums break-words">Rp {{ number_format($position['commission'], 0, ',', '.') }}</p>
        <p class="text-[12px] font-bold opacity-80">Pendapatan platform</p>
        <p class="text-[11px] font-semibold opacity-70">Komisi dari {{ number_format($position['commission_count'], 0, ',', '.') }} pesanan selesai</p>
    </div>
</div>
<p class="mt-2 text-[12px] font-semibold text-ink-500">
    Kewajiban ke mitra (saldo warung + penarikan menunggu): <span class="font-black text-ink-900">Rp {{ number_format($position['liability'], 0, ',', '.') }}</span>.
    Dana ditahan belum menjadi hak mitra sampai pesanannya selesai.
</p>

{{-- ===== LIFETIME ===== --}}
<h2 class="mt-6 font-extrabold text-lg">Sepanjang waktu</h2>
<div class="mt-3 grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="rounded-[24px] p-5 bg-cream-100 border border-ink-900/10">
        <p class="text-xl">🧾</p>
        <p class="font-black text-xl sm:text-2xl mt-1 tabular-nums break-words">Rp {{ number_format($lifetime['orders_value'], 0, ',', '.') }}</p>
        <p class="text-[12px] font-bold text-ink-500">Nilai pesanan</p>
        <p class="text-[11px] font-semibold text-ink-500/80">{{ number_format($lifetime['orders_count'], 0, ',', '.') }} pesanan (di luar yang dibatalkan)</p>
    </div>
    <div class="rounded-[24px] p-5 bg-cream-100 border border-ink-900/10">
        <p class="text-xl">📤</p>
        <p class="font-black text-xl sm:text-2xl mt-1 tabular-nums break-words">Rp {{ number_format($position['released'], 0, ',', '.') }}</p>
        <p class="text-[12px] font-bold text-ink-500">Dilepas ke warung</p>
        <p class="text-[11px] font-semibold text-ink-500/80">Masuk ke saldo mitra</p>
    </div>
    <div class="rounded-[24px] p-5 bg-cream-100 border border-ink-900/10">
        <p class="text-xl">🏧</p>
        <p class="font-black text-xl sm:text-2xl mt-1 tabular-nums break-words">Rp {{ number_format($lifetime['paid_out'], 0, ',', '.') }}</p>
        <p class="text-[12px] font-bold text-ink-500">Sudah dibayarkan</p>
        <p class="text-[11px] font-semibold text-ink-500/80">Penarikan yang sudah ditransfer</p>
    </div>
    <div class="rounded-[24px] p-5 bg-white border border-ink-900/10">
        <p class="text-xl">❌</p>
        <p class="font-black text-xl sm:text-2xl mt-1 tabular-nums break-words">Rp {{ number_format($lifetime['cancelled_value'], 0, ',', '.') }}</p>
        <p class="text-[12px] font-bold text-ink-500">Dibatalkan</p>
        <p class="text-[11px] font-semibold text-ink-500/80">{{ number_format($lifetime['cancelled_count'], 0, ',', '.') }} pesanan batal</p>
    </div>
</div>

{{-- ===== QUEUES ===== --}}
<h2 class="mt-6 font-extrabold text-lg">Perlu tindakan</h2>
<div class="mt-3 grid gap-2.5 sm:grid-cols-3">
    <a href="{{ route('admin.payments') }}" class="rounded-[24px] bg-white border border-ink-900/10 p-5 hover:shadow-xl hover:shadow-ink-900/5 transition">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="font-extrabold">{{ number_format($queues['payments']['count'], 0, ',', '.') }} bukti transfer</p>
                <p class="text-[12px] font-semibold text-ink-500 mt-0.5">Menunggu diverifikasi</p>
            </div>
            <span class="shrink-0 text-[11px] font-extrabold rounded-full px-3 py-1.5 bg-amber-100 text-amber-800">Pending</span>
        </div>
        <p class="mt-2 font-black tabular-nums">Rp {{ number_format($queues['payments']['total'], 0, ',', '.') }}</p>
    </a>
    <a href="{{ route('admin.withdrawals') }}" class="rounded-[24px] bg-white border border-ink-900/10 p-5 hover:shadow-xl hover:shadow-ink-900/5 transition">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="font-extrabold">{{ number_format($position['withdrawals_pending_count'], 0, ',', '.') }} penarikan</p>
                <p class="text-[12px] font-semibold text-ink-500 mt-0.5">Belum ditransfer ke mitra</p>
            </div>
            <span class="shrink-0 text-[11px] font-extrabold rounded-full px-3 py-1.5 bg-amber-100 text-amber-800">Pending</span>
        </div>
        <p class="mt-2 font-black tabular-nums">Rp {{ number_format($position['withdrawals_pending'], 0, ',', '.') }}</p>
    </a>
    <a href="{{ route('admin.orders', ['status' => 'paid']) }}" class="rounded-[24px] bg-white border border-ink-900/10 p-5 hover:shadow-xl hover:shadow-ink-900/5 transition">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="font-extrabold">{{ number_format($position['escrow_count'], 0, ',', '.') }} pesanan dibayar</p>
                <p class="text-[12px] font-semibold text-ink-500 mt-0.5">Siap dilepas dananya</p>
            </div>
            <span class="shrink-0 text-[11px] font-extrabold rounded-full px-3 py-1.5 bg-leaf-100 text-leaf-700">Escrow</span>
        </div>
        <p class="mt-2 font-black tabular-nums">Rp {{ number_format($position['escrow'], 0, ',', '.') }}</p>
    </a>
</div>
@if($queues['payments_rejected']['count'] > 0)
<p class="mt-2 text-[12px] font-semibold text-ink-500">
    {{ number_format($queues['payments_rejected']['count'], 0, ',', '.') }} bukti transfer ditolak dan menunggu pembeli mengunggah ulang
    <a href="{{ route('admin.payments', ['status' => 'rejected']) }}" class="font-extrabold text-brand-600">lihat →</a>
</p>
@endif

{{-- ===== CHARTS: INFLOW + POSITION ===== --}}
<div class="mt-4 grid gap-3.5 lg:grid-cols-[1.6fr_1fr]">
    <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div>
                <h2 class="font-extrabold text-lg">Uang masuk 7 hari terakhir</h2>
                <p class="text-[12px] font-medium text-ink-500">Dari bukti transfer yang sudah diverifikasi admin.</p>
            </div>
            <p class="font-black text-lg whitespace-nowrap tabular-nums">Rp {{ number_format($inflow['total'], 0, ',', '.') }}</p>
        </div>
        <div id="chart-inflow" class="mt-3"></div>
    </div>

    <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6">
        <h2 class="font-extrabold text-lg">Posisi uang</h2>
        <p class="text-[12px] font-medium text-ink-500">Ke mana uang yang sedang dipegang platform.</p>

        @if(($position['escrow'] + $position['wallets'] + $position['withdrawals_pending']) > 0)
            <div id="chart-position" class="mt-1"></div>
        @else
            <div class="mt-4 rounded-2xl border border-dashed border-ink-900/15 p-6 text-center">
                <p class="text-sm font-extrabold">Belum ada uang dipegang</p>
                <p class="text-xs font-semibold text-ink-500 mt-0.5">Muncul setelah ada pembeli yang transfer.</p>
            </div>
        @endif
    </div>
</div>

{{-- ===== CHARTS: STORES + ORDER STATUS ===== --}}
<div class="mt-4 grid gap-3.5 lg:grid-cols-[1.6fr_1fr]">
    <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6">
        <h2 class="font-extrabold text-lg">Nilai pesanan selesai per warung</h2>
        <p class="text-[12px] font-medium text-ink-500">Enam warung dengan nilai pesanan selesai tertinggi.</p>

        @if(count($charts['stores']['categories']) > 0)
            <div id="chart-stores" class="mt-3"></div>
        @else
            <div class="mt-4 rounded-2xl border border-dashed border-ink-900/15 p-8 text-center">
                <p class="text-sm font-extrabold">Belum ada pesanan selesai</p>
                <p class="text-xs font-semibold text-ink-500 mt-0.5">Grafik muncul setelah ada pesanan yang dana nya dilepas.</p>
            </div>
        @endif
    </div>

    <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6">
        <h2 class="font-extrabold text-lg">Status pesanan</h2>
        <p class="text-[12px] font-medium text-ink-500">Sebaran pesanan di seluruh alur.</p>

        @if(array_sum($charts['statuses']['series']) > 0)
            <div id="chart-statuses" class="mt-1"></div>
        @else
            <div class="mt-4 rounded-2xl border border-dashed border-ink-900/15 p-6 text-center">
                <p class="text-sm font-extrabold">Belum ada pesanan</p>
                <p class="text-xs font-semibold text-ink-500 mt-0.5">Data muncul setelah pembeli memesan.</p>
            </div>
        @endif
    </div>
</div>

{{-- ===== PER STORE ===== --}}
<div class="mt-4 rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6">
    <h2 class="font-extrabold text-lg">Rincian per warung</h2>
    <p class="text-[12px] font-medium text-ink-500">Pesanan selesai, saldo yang masih dipegang platform, dan yang sudah ditransfer.</p>

    @if($stores->isEmpty())
        <div class="mt-4 rounded-2xl border border-dashed border-ink-900/15 p-8 text-center">
            <p class="font-extrabold">Belum ada warung</p>
            <p class="text-sm font-medium text-ink-500 mt-1">Data keuangan muncul setelah ada mitra yang berjualan.</p>
        </div>
    @else
        <div class="mt-4 overflow-x-auto rounded-2xl border border-ink-900/10">
            <table class="w-full text-left text-sm min-w-[760px]">
                <thead>
                    <tr class="bg-cream-50/60 text-[11px] font-extrabold uppercase tracking-wider text-ink-500">
                        <th class="px-4 py-3">Warung</th>
                        <th class="px-4 py-3 text-right">Selesai</th>
                        <th class="px-4 py-3 text-right">Nilai pesanan</th>
                        <th class="px-4 py-3 text-right">Komisi</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                        <th class="px-4 py-3 text-right">Ditahan</th>
                        <th class="px-4 py-3 text-right">Ditarik</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-900/5">
                    @foreach($stores as $row)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-extrabold">{{ $row['name'] }}</p>
                            <p class="text-[11px] font-semibold text-ink-500">{{ $row['owner'] ?? 'Tanpa pemilik' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right font-bold tabular-nums">{{ number_format($row['completed_count'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-black tabular-nums whitespace-nowrap">Rp {{ number_format($row['completed_total'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold tabular-nums whitespace-nowrap">Rp {{ number_format($row['commission_total'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-black tabular-nums whitespace-nowrap">Rp {{ number_format($row['balance'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right tabular-nums whitespace-nowrap {{ $row['held_total'] > 0 ? 'font-black text-amber-800' : 'font-bold text-ink-500' }}">Rp {{ number_format($row['held_total'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold tabular-nums whitespace-nowrap">Rp {{ number_format($row['paid_out_total'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- ===== PER PAYMENT METHOD ===== --}}
<div class="mt-4 rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6">
    <div class="flex items-center justify-between gap-2">
        <div>
            <h2 class="font-extrabold text-lg">Uang masuk per metode</h2>
            <p class="text-[12px] font-medium text-ink-500">Rekening tujuan transfer yang dipakai pembeli.</p>
        </div>
        <a href="{{ route('admin.payment-methods') }}" class="text-[13px] font-extrabold text-brand-600 whitespace-nowrap">Kelola →</a>
    </div>

    @if($methods->isEmpty())
        <div class="mt-4 rounded-2xl border border-dashed border-ink-900/15 p-8 text-center">
            <p class="font-extrabold">Belum ada metode pembayaran</p>
            <p class="text-sm font-medium text-ink-500 mt-1">Tambahkan rekening tujuan supaya pembeli bisa membayar.</p>
        </div>
    @else
        <div class="mt-4 overflow-x-auto rounded-2xl border border-ink-900/10">
            <table class="w-full text-left text-sm min-w-[560px]">
                <thead>
                    <tr class="bg-cream-50/60 text-[11px] font-extrabold uppercase tracking-wider text-ink-500">
                        <th class="px-4 py-3">Metode</th>
                        <th class="px-4 py-3 text-right">Terverifikasi</th>
                        <th class="px-4 py-3 text-right">Uang masuk</th>
                        <th class="px-4 py-3 text-right">Menunggu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-900/5">
                    @foreach($methods as $method)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-extrabold">{{ $method->name }}</p>
                            <p class="text-[11px] font-semibold text-ink-500">{{ $method->typeLabel() }}@unless($method->is_active) • nonaktif @endunless</p>
                        </td>
                        <td class="px-4 py-3 text-right font-bold tabular-nums">{{ number_format($method->verified_count, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-black tabular-nums whitespace-nowrap">Rp {{ number_format((int) $method->verified_total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold tabular-nums whitespace-nowrap {{ $method->pending_count > 0 ? 'text-amber-800' : 'text-ink-500' }}">{{ number_format($method->pending_count, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@push('scripts')
<script>window.financeCharts = {{ Js::from($charts) }};</script>
@vite('resources/js/charts.js')
@endpush
@endsection
