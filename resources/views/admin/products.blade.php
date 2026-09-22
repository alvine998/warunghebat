@extends('layouts.admin')

@section('title', 'Verifikasi Produk — Backoffice Warung Hebat')

@section('content')
<p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="package" class="w-4 h-4" /> VERIFIKASI PRODUK</p>
<h1 class="font-black tracking-tight text-3xl mt-1">Produk penjual</h1>
<p class="text-sm font-medium text-ink-500">Setujui produk yang layak tayang, tolak dengan alasan yang jelas.</p>

@if (session('success'))
    <div class="mt-4 rounded-2xl bg-leaf-50 border border-leaf-500/30 text-leaf-700 text-sm font-bold p-4">{{ session('success') }}</div>
@endif

<div class="mt-5 flex flex-wrap gap-2 text-[13px] font-extrabold">
    <a href="{{ route('admin.products') }}" class="px-4 py-2 rounded-full {{ !request('status') ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">Semua ({{ $counts['all'] }})</a>
    <a href="{{ route('admin.products', ['status' => 'pending']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full {{ request('status') === 'pending' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}"><x-icon name="clock" class="w-4 h-4" /> Pending ({{ $counts['pending'] }})</a>
    <a href="{{ route('admin.products', ['status' => 'approved']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full {{ request('status') === 'approved' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}"><x-icon name="check-circle" class="w-4 h-4" /> Tayang ({{ $counts['approved'] }})</a>
    <a href="{{ route('admin.products', ['status' => 'rejected']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full {{ request('status') === 'rejected' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}"><x-icon name="x-mark" class="w-4 h-4" /> Ditolak ({{ $counts['rejected'] }})</a>
</div>

<div class="mt-4 grid gap-2.5">
    @forelse($products as $p)
    <div class="rounded-[24px] bg-white border border-ink-900/10 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            @if($p->image_path)
                <img src="{{ $p->image_url }}" alt="Foto {{ $p->name }}" class="w-full sm:w-16 h-40 sm:h-16 rounded-2xl object-cover border border-ink-900/10 shrink-0">
            @else
                <div class="w-full sm:w-16 h-40 sm:h-16 rounded-2xl bg-cream-100 border border-dashed border-ink-900/15 grid place-items-center shrink-0"><x-icon name="document" class="w-7 h-7 text-ink-400" /></div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="font-extrabold">{{ $p->name }}</p>
                    <span class="text-[11px] font-extrabold rounded-full px-3 py-1 {{ $p->status === 'approved' ? 'bg-leaf-100 text-leaf-700' : ($p->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-700') }}">{{ $p->status }}</span>
                </div>
                <p class="text-xs font-semibold text-ink-500 mt-0.5">{{ $p->user->name }} • {{ $p->user->email }} • {{ $p->category }} • Rp {{ number_format($p->price, 0, ',', '.') }} • Stok {{ $p->stock }}</p>
                @if($p->description)
                <p class="text-[13px] font-medium text-ink-700 mt-1">{{ $p->description }}</p>
                @endif
                @if($p->status === 'rejected' && $p->rejection_reason)
                <p class="mt-1 text-[13px] font-semibold text-red-700">Alasan: {{ $p->rejection_reason }}</p>
                @endif
            </div>
            <div class="flex gap-2 shrink-0">
                @if($p->status !== 'approved')
                <form method="POST" action="{{ route('admin.products.approve', $p) }}">
                    @csrf
                    @method('PATCH')
                    <button class="text-[13px] font-extrabold px-4 py-2 rounded-full bg-leaf-600 text-white hover:bg-leaf-700 transition">Setujui</button>
                </form>
                @endif
            </div>
        </div>
        @if($p->status !== 'rejected')
        <form method="POST" action="{{ route('admin.products.reject', $p) }}" class="mt-3 flex flex-col sm:flex-row gap-2">
            @csrf
            @method('PATCH')
            <input name="rejection_reason" required maxlength="1000" placeholder="Alasan penolakan (wajib jika menolak)..." value="{{ old('rejection_reason') }}" class="flex-1 rounded-2xl border border-ink-900/15 px-4 py-2.5 text-sm font-medium outline-none focus:border-red-400 focus:ring-4 focus:ring-red-500/10 transition">
            <button class="text-[13px] font-extrabold px-4 py-2.5 rounded-full bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition">Tolak</button>
        </form>
        @endif
    </div>
    @empty
    <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-8 text-center font-semibold text-ink-500">Tidak ada produk pada filter ini.</div>
    @endforelse
</div>

<div class="mt-4">{{ $products->links() }}</div>
@endsection
