@extends('layouts.app')

@section('title', 'Pesanan Saya — Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-10 sm:pb-14 max-w-2xl mx-auto px-4 sm:px-6">
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 min-h-10 text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Dashboard</a>

    <h1 class="mt-3 font-black tracking-tight text-[26px] sm:text-3xl">Pesanan saya</h1>
    <p class="text-[13px] font-semibold text-ink-500 mt-1">Pantau pembayaran dan status pesananmu di sini.</p>

    <div class="mt-4 grid gap-2.5">
        @forelse($orders as $order)
            @php
                $tone = match ($order->status) {
                    'completed', 'paid' => 'bg-leaf-100 text-leaf-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                    'waiting_verification' => 'bg-amber-100 text-amber-800',
                    default => 'bg-brand-100 text-brand-700',
                };
            @endphp
            <a href="{{ route('orders.show', $order) }}" class="block rounded-[24px] bg-white border border-ink-900/10 p-4 hover:shadow-xl hover:shadow-ink-900/10 transition">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-extrabold tracking-[0.15em] text-ink-500">{{ $order->code() }}</p>
                        <p class="font-extrabold mt-0.5 truncate">{{ $order->warung_name }}</p>
                    </div>
                    <span class="shrink-0 text-[11px] font-extrabold rounded-full px-3 py-1.5 {{ $tone }}">{{ $order->statusLabel() }}</span>
                </div>

                <p class="mt-2 text-[13px] font-semibold text-ink-500 truncate">{{ $order->item_name }}</p>

                <div class="mt-2.5 flex items-center justify-between gap-3">
                    <p class="text-[12px] font-bold text-ink-500">{{ $order->created_at->diffForHumans() }}</p>
                    <p class="font-black whitespace-nowrap">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                </div>
            </a>
        @empty
            <div class="rounded-[28px] bg-white border border-dashed border-ink-900/15 p-7 sm:p-10 text-center">
                <p class="text-4xl">🧾</p>
                <p class="font-extrabold text-base sm:text-lg mt-2">Belum ada pesanan</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Yuk pesan dari warung terdekat dan kumpulkan poin Hebat.</p>
                <a href="{{ route('home') }}#warung" class="mt-4 inline-block text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">Cari Warung →</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</section>
@endsection
