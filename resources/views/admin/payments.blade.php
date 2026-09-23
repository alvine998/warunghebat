@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran — Backoffice Warung Hebat')

@section('content')
<p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="document" class="w-4 h-4" /> VERIFIKASI PEMBAYARAN</p>
<h1 class="font-black tracking-tight text-3xl mt-1">Pembayaran pembeli</h1>
<p class="text-sm font-medium text-ink-500">Periksa bukti transfer, lalu verifikasi agar dana ditahan untuk pesanan warung.</p>

<div class="mt-5 flex flex-wrap gap-2 text-[13px] font-extrabold">
    <a href="{{ route('admin.payments', ['status' => 'all', 'search' => request('search')]) }}" class="px-4 py-2 rounded-full {{ ! $status ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">Semua ({{ $counts['all'] }})</a>
    @foreach(['pending', 'verified', 'rejected'] as $value)
        <a href="{{ route('admin.payments', ['status' => $value, 'search' => request('search')]) }}" class="px-4 py-2 rounded-full {{ $status === $value ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">
            {{ \App\Models\Payment::STATUS_LABELS[$value] }} ({{ $counts[$value] }})
        </a>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.payments') }}" class="mt-4 flex flex-col sm:flex-row gap-2">
    @if(request('status'))
        <input type="hidden" name="status" value="{{ request('status') }}">
    @endif
    <label class="sr-only" for="payment-search">Cari pembayaran</label>
    <div class="flex flex-1 items-center gap-2 rounded-2xl bg-white border border-ink-900/10 px-4 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10">
        <x-icon name="search" class="w-5 h-5 text-ink-400" />
        <input id="payment-search" name="search" value="{{ request('search') }}" type="search" placeholder="Cari kode pesanan, pembeli, warung, atau metode..." class="w-full bg-transparent py-3 text-sm font-semibold outline-none placeholder:text-ink-400">
    </div>
    <button type="submit" class="rounded-2xl bg-ink-900 px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition">Cari</button>
</form>

<div class="mt-4 grid gap-2.5">
    @forelse($payments as $payment)
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row gap-4">
                @if($payment->proof_path)
                    <a href="{{ $payment->proof_url }}" target="_blank" rel="noopener" class="shrink-0">
                        <img src="{{ $payment->proof_url }}" alt="Bukti transfer {{ $payment->order->code() }}" class="w-full sm:w-28 h-56 sm:h-28 rounded-2xl object-cover border border-ink-900/10">
                    </a>
                @else
                    <div class="w-full sm:w-28 h-56 sm:h-28 rounded-2xl bg-cream-100 border border-dashed border-ink-900/15 grid place-items-center shrink-0"><x-icon name="document" class="w-7 h-7 text-ink-500" /></div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-extrabold">{{ $payment->order->code() }}</p>
                        <span class="text-[11px] font-extrabold rounded-full px-3 py-1 {{ $payment->isPending() ? 'bg-amber-100 text-amber-800' : ($payment->isVerified() ? 'bg-leaf-100 text-leaf-700' : 'bg-red-100 text-red-700') }}">{{ $payment->statusLabel() }}</span>
                    </div>
                    <p class="mt-1 text-[13px] font-semibold text-ink-500">
                        {{ $payment->order->user?->name }} • {{ $payment->order->warung_name }} • {{ $payment->created_at->diffForHumans() }}
                    </p>
                    <p class="mt-1 text-[13px] font-bold">Metode: <span class="font-extrabold">{{ $payment->paymentMethod?->name ?? 'Metode dihapus' }}</span></p>
                    <p class="mt-0.5 font-black text-lg">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>

                    @if($payment->isRejected() && $payment->rejection_reason)
                        <p class="mt-1 text-[13px] font-semibold text-red-700">Alasan: {{ $payment->rejection_reason }}</p>
                    @endif
                    @if($payment->isVerified())
                        <p class="mt-1 text-[12px] font-semibold text-leaf-700">Diverifikasi {{ $payment->verifier?->name }} • {{ $payment->verified_at?->diffForHumans() }}</p>
                    @endif
                </div>

                @if($payment->isPending())
                    <div class="flex sm:flex-col gap-2 shrink-0 sm:w-40">
                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="flex-1" onsubmit="return confirm('Verifikasi pembayaran ini? Dana akan ditahan untuk pesanan.');">
                            @csrf
                            @method('PATCH')
                            <button class="w-full text-[13px] font-extrabold px-4 py-2.5 rounded-full bg-leaf-600 text-white hover:bg-leaf-700 transition">Verifikasi</button>
                        </form>
                    </div>
                @endif
            </div>

            @if($payment->isPending())
                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="mt-3 flex flex-col sm:flex-row gap-2">
                    @csrf
                    @method('PATCH')
                    <label class="sr-only" for="rejection-{{ $payment->id }}">Alasan penolakan</label>
                    <input id="rejection-{{ $payment->id }}" name="rejection_reason" required maxlength="500" placeholder="Alasan penolakan (wajib) — pembeli akan mengunggah ulang..." class="flex-1 rounded-2xl border border-ink-900/15 px-4 py-2.5 text-sm font-medium outline-none focus:border-red-400 focus:ring-4 focus:ring-red-500/10 transition">
                    <button class="text-[13px] font-extrabold px-4 py-2.5 rounded-full bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition">Tolak</button>
                </form>
            @endif
        </div>
    @empty
        <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-8 text-center">
            @if(request('search'))
                <p class="font-extrabold">Tidak ada pembayaran yang cocok dengan "{{ request('search') }}"</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Coba kata kunci lain atau pilih tab Semua.</p>
            @else
                <p class="font-extrabold">Tidak ada pembayaran di status ini</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Bukti transfer pembeli akan muncul di sini untuk diverifikasi.</p>
            @endif
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $payments->links() }}</div>
@endsection
