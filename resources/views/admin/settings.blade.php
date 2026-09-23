@extends('layouts.admin')

@section('title', 'Pengaturan Platform — Backoffice Warung Hebat')

@section('content')
<p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="bolt" class="w-4 h-4" /> PENGATURAN</p>
<h1 class="font-black tracking-tight text-3xl mt-1">Pengaturan platform</h1>
<p class="text-sm font-medium text-ink-500">Atur batas penarikan mitra dan komisi Warung Hebat di sini.</p>

<form method="POST" action="{{ route('admin.settings.update') }}" class="mt-5 max-w-2xl grid gap-3.5">
    @csrf
    @method('PUT')

    <div class="rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6 grid gap-4">
        <div>
            <label for="withdrawal_min" class="text-[13px] font-extrabold">Penarikan minimal (Rp) *</label>
            <input id="withdrawal_min" name="withdrawal_min" required data-numeric inputmode="numeric" value="{{ old('withdrawal_min', $withdrawalMin) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            <p class="mt-1.5 text-xs font-semibold text-ink-500">Mitra tidak bisa mengajukan penarikan di bawah nominal ini.</p>
        </div>

        <div>
            <label for="withdrawal_max" class="text-[13px] font-extrabold">Penarikan maksimal (Rp) *</label>
            <input id="withdrawal_max" name="withdrawal_max" required data-numeric inputmode="numeric" value="{{ old('withdrawal_max', $withdrawalMax) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            <p class="mt-1.5 text-xs font-semibold text-ink-500">Harus lebih besar atau sama dengan batas minimal.</p>
        </div>

        <div>
            <label for="commission_percent" class="text-[13px] font-extrabold">Komisi platform (%) *</label>
            <input id="commission_percent" name="commission_percent" type="number" min="0" max="100" step="1" required value="{{ old('commission_percent', $commissionPercent) }}" class="mt-1.5 w-full rounded-2xl border border-ink-900/15 px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
            <p class="mt-1.5 text-xs font-semibold text-ink-500">Dipotong dari setiap pesanan yang selesai, sebelum masuk ke saldo warung. Isi 0 untuk komisi 0%.</p>
        </div>
    </div>

    <div class="rounded-[24px] bg-cream-100 border border-ink-900/10 p-4">
        <p class="text-[13px] font-extrabold">Contoh perhitungan</p>
        <p class="text-[12px] font-semibold text-ink-500 mt-1">Pesanan Rp 100.000 dengan komisi {{ $commissionPercent }}% → saldo warung bertambah Rp {{ number_format(100000 - (int) round(100000 * $commissionPercent / 100), 0, ',', '.') }}.</p>
    </div>

    <button class="w-full sm:w-fit rounded-full bg-ink-900 hover:bg-brand-600 text-white font-extrabold text-sm px-8 py-3.5 transition">Simpan pengaturan</button>
</form>
@endsection
