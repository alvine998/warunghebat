@extends('layouts.admin')

@section('title', 'Pesanan — Backoffice Warung Hebat')

@section('content')
<p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="basket" class="w-4 h-4" /> PESANAN</p>
<h1 class="font-black tracking-tight text-3xl mt-1">Pesanan marketplace</h1>
<p class="text-sm font-medium text-ink-500">Selesaikan pesanan yang sudah dibayar untuk melepas dana ke saldo warung.</p>

<div class="mt-5 flex flex-wrap gap-2 text-[13px] font-extrabold">
    <a href="{{ route('admin.orders', ['search' => request('search')]) }}" class="px-4 py-2 rounded-full {{ ! $status ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">Semua ({{ $counts['all'] }})</a>
    @foreach(\App\Models\Order::STATUSES as $value)
        <a href="{{ route('admin.orders', ['status' => $value, 'search' => request('search')]) }}" class="px-4 py-2 rounded-full {{ $status === $value ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">
            {{ \App\Models\Order::STATUS_LABELS[$value] }} ({{ $counts[$value] }})
        </a>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.orders') }}" class="mt-4 flex flex-col sm:flex-row gap-2">
    @if(request('status'))
        <input type="hidden" name="status" value="{{ request('status') }}">
    @endif
    <label class="sr-only" for="order-search">Cari pesanan</label>
    <div class="flex flex-1 items-center gap-2 rounded-2xl bg-white border border-ink-900/10 px-4 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10">
        <x-icon name="search" class="w-5 h-5 text-ink-400" />
        <input id="order-search" name="search" value="{{ request('search') }}" type="search" placeholder="Cari kode pesanan, pembeli, warung, atau produk..." class="w-full bg-transparent py-3 text-sm font-semibold outline-none placeholder:text-ink-400">
    </div>
    <button type="submit" class="rounded-2xl bg-ink-900 px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition">Cari</button>
</form>

<div class="mt-4 grid gap-2.5">
    @forelse($orders as $order)
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-extrabold">{{ $order->code() }}</p>
                        <span class="text-[11px] font-extrabold rounded-full px-3 py-1
                            @if($order->isCompleted()) bg-leaf-100 text-leaf-700
                            @elseif($order->isPaid()) bg-leaf-100 text-leaf-700
                            @elseif($order->isCancelled()) bg-red-100 text-red-700
                            @elseif($order->status === 'waiting_verification') bg-amber-100 text-amber-800
                            @else bg-brand-100 text-brand-700 @endif">{{ $order->statusLabel() }}</span>
                    </div>

                    <p class="mt-1 text-[13px] font-semibold text-ink-500">
                        {{ $order->user?->name }} • {{ $order->warung_name }} • {{ $order->created_at->format('d M Y, H:i') }}
                    </p>

                    <div class="mt-2 grid gap-1">
                        @foreach($order->items as $item)
                            <p class="text-[13px] font-semibold text-ink-700">{{ $item->qty }} × {{ $item->name }} — Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                        @endforeach
                    </div>

                    <p class="mt-2 font-black">Rp {{ number_format($order->total, 0, ',', '.') }}
                        @if($order->commission_amount > 0)
                            <span class="text-[12px] font-bold text-ink-500">• komisi Rp {{ number_format($order->commission_amount, 0, ',', '.') }}</span>
                        @endif
                    </p>
                </div>

                <div class="flex sm:flex-col gap-2 shrink-0 sm:w-44">
                    @if($order->isPaid())
                        <form method="POST" action="{{ route('admin.orders.complete', $order) }}" class="flex-1" onsubmit="return confirm('Selesaikan pesanan ini dan lepas dananya ke saldo warung?');">
                            @csrf
                            @method('PATCH')
                            <button class="w-full text-[13px] font-extrabold px-4 py-2.5 rounded-full bg-leaf-600 text-white hover:bg-leaf-700 transition">Selesaikan & lepas dana</button>
                        </form>
                    @endif

                    @if($order->canBeCancelled())
                        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="flex-1" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan ke warung.');">
                            @csrf
                            @method('PATCH')
                            <button class="w-full text-[13px] font-extrabold px-4 py-2.5 rounded-full bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition">Batalkan</button>
                        </form>
                    @endif

                    @if($order->isCompleted() && $order->completed_at)
                        <p class="text-[11px] font-semibold text-ink-500 sm:text-center">Dilepas {{ $order->completed_at->diffForHumans() }}</p>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-8 text-center">
            @if(request('search'))
                <p class="font-extrabold">Tidak ada pesanan yang cocok dengan "{{ request('search') }}"</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Coba kata kunci lain atau pilih tab Semua.</p>
            @else
                <p class="font-extrabold">Tidak ada pesanan di status ini</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Pesanan pembeli akan muncul di sini.</p>
            @endif
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection
