@extends('layouts.admin')

@section('title', 'Penarikan Saldo — Backoffice Warung Hebat')

@section('content')
<p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="wallet" class="w-4 h-4" /> PENARIKAN SALDO</p>
<h1 class="font-black tracking-tight text-3xl mt-1">Penarikan mitra</h1>
<p class="text-sm font-medium text-ink-500">Transfer manual ke rekening mitra, lalu tandai sudah dibayar. Menolak akan mengembalikan saldo.</p>

<div class="mt-5 flex flex-wrap gap-2 text-[13px] font-extrabold">
    <a href="{{ route('admin.withdrawals', ['status' => 'all', 'search' => request('search')]) }}" class="px-4 py-2 rounded-full {{ ! $status ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">Semua ({{ $counts['all'] }})</a>
    @foreach(\App\Models\Withdrawal::STATUSES as $value)
        <a href="{{ route('admin.withdrawals', ['status' => $value, 'search' => request('search')]) }}" class="px-4 py-2 rounded-full {{ $status === $value ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">
            {{ \App\Models\Withdrawal::STATUS_LABELS[$value] }} ({{ $counts[$value] }})
        </a>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.withdrawals') }}" class="mt-4 flex flex-col sm:flex-row gap-2">
    @if(request('status'))
        <input type="hidden" name="status" value="{{ request('status') }}">
    @endif
    <label class="sr-only" for="withdrawal-search">Cari penarikan</label>
    <div class="flex flex-1 items-center gap-2 rounded-2xl bg-white border border-ink-900/10 px-4 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10">
        <x-icon name="search" class="w-5 h-5 text-ink-400" />
        <input id="withdrawal-search" name="search" value="{{ request('search') }}" type="search" placeholder="Cari warung, bank, atau nomor rekening..." class="w-full bg-transparent py-3 text-sm font-semibold outline-none placeholder:text-ink-400">
    </div>
    <button type="submit" class="rounded-2xl bg-ink-900 px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition">Cari</button>
</form>

<div class="mt-4 grid gap-2.5">
    @forelse($withdrawals as $withdrawal)
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-extrabold">#{{ $withdrawal->id }} • {{ $withdrawal->wallet->store?->name ?? 'Warung dihapus' }}</p>
                        <span class="text-[11px] font-extrabold rounded-full px-3 py-1 {{ $withdrawal->isPaid() ? 'bg-leaf-100 text-leaf-700' : ($withdrawal->isRejected() ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800') }}">{{ $withdrawal->statusLabel() }}</span>
                    </div>

                    <p class="mt-1 font-black text-xl">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</p>

                    <p class="mt-1 text-[13px] font-bold">{{ $withdrawal->bank_name }} • {{ $withdrawal->account_number }}</p>
                    <p class="text-[13px] font-semibold text-ink-500">a/n {{ $withdrawal->account_name }}</p>

                    @if($withdrawal->store_note)
                        <p class="mt-1 text-[12px] font-semibold text-ink-700">Catatan mitra: {{ $withdrawal->store_note }}</p>
                    @endif
                    @if($withdrawal->admin_note)
                        <p class="mt-1 text-[12px] font-semibold text-ink-700">Catatan admin: {{ $withdrawal->admin_note }}</p>
                    @endif

                    <p class="mt-1 text-[12px] font-semibold text-ink-500">
                        Diajukan {{ $withdrawal->created_at->format('d M Y, H:i') }}
                        @if($withdrawal->processed_at)
                            • diproses {{ $withdrawal->processor?->name }} {{ $withdrawal->processed_at->diffForHumans() }}
                        @endif
                    </p>
                </div>

                @if($withdrawal->isPending())
                    <div class="flex sm:flex-col gap-2 shrink-0 sm:w-44">
                        <form method="POST" action="{{ route('admin.withdrawals.paid', $withdrawal) }}" class="flex-1" onsubmit="return confirm('Tandai penarikan ini sudah dibayar? Pastikan transfer sudah dilakukan.');">
                            @csrf
                            @method('PATCH')
                            <button class="w-full text-[13px] font-extrabold px-4 py-2.5 rounded-full bg-leaf-600 text-white hover:bg-leaf-700 transition">Tandai dibayar</button>
                        </form>
                    </div>
                @endif
            </div>

            @if($withdrawal->isPending())
                <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}" class="mt-3 flex flex-col sm:flex-row gap-2">
                    @csrf
                    @method('PATCH')
                    <label class="sr-only" for="admin-note-{{ $withdrawal->id }}">Alasan penolakan</label>
                    <input id="admin-note-{{ $withdrawal->id }}" name="admin_note" required maxlength="500" placeholder="Alasan penolakan (wajib) — saldo dikembalikan ke warung..." class="flex-1 rounded-2xl border border-ink-900/15 px-4 py-2.5 text-sm font-medium outline-none focus:border-red-400 focus:ring-4 focus:ring-red-500/10 transition">
                    <button class="text-[13px] font-extrabold px-4 py-2.5 rounded-full bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition">Tolak</button>
                </form>
            @endif
        </div>
    @empty
        <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-8 text-center">
            @if(request('search'))
                <p class="font-extrabold">Tidak ada penarikan yang cocok dengan "{{ request('search') }}"</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Coba kata kunci lain atau pilih tab Semua.</p>
            @else
                <p class="font-extrabold">Tidak ada penarikan di status ini</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Permintaan penarikan mitra akan muncul di sini.</p>
            @endif
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $withdrawals->links() }}</div>
@endsection
