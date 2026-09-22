@extends('layouts.app')

@section('title', 'Produk Saya — Warung Hebat')

@section('bare', true)

@section('content')
<section class="pt-6 sm:pt-8 pb-10 max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between mb-5">
        <a href="{{ route('dashboard') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Dashboard</a>
        <a href="{{ route('seller.products.create') }}" class="text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition">+ Tambah Produk</a>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-2xl bg-leaf-50 border border-leaf-500/30 text-leaf-700 text-sm font-bold p-4">{{ session('success') }}</div>
    @endif

    <p class="text-[11px] font-extrabold tracking-[0.2em] text-leaf-700">🏪 PRODUK SAYA</p>
    <h1 class="font-black tracking-tight text-3xl mt-1">Kelola produk</h1>
    <p class="text-sm font-medium text-ink-500">Produk baru & hasil edit menunggu verifikasi admin sebelum tayang.</p>

    <div class="mt-5 flex flex-wrap gap-2 text-[13px] font-extrabold">
        <a href="{{ route('seller.products.index') }}" class="px-4 py-2 rounded-full {{ !request('status') ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">Semua ({{ $counts['all'] }})</a>
        <a href="{{ route('seller.products.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-full {{ request('status') === 'pending' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">⏳ Pending ({{ $counts['pending'] }})</a>
        <a href="{{ route('seller.products.index', ['status' => 'approved']) }}" class="px-4 py-2 rounded-full {{ request('status') === 'approved' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">✅ Tayang ({{ $counts['approved'] }})</a>
        <a href="{{ route('seller.products.index', ['status' => 'rejected']) }}" class="px-4 py-2 rounded-full {{ request('status') === 'rejected' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">❌ Ditolak ({{ $counts['rejected'] }})</a>
    </div>

    <div class="mt-4 grid gap-2.5">
        @forelse($products as $p)
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-4 flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="font-extrabold truncate">{{ $p->name }}</p>
                    <span class="text-[11px] font-extrabold rounded-full px-3 py-1 {{ $p->status === 'approved' ? 'bg-leaf-100 text-leaf-700' : ($p->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-700') }}">{{ $p->status }}</span>
                    <span class="text-[11px] font-bold text-ink-500">{{ $p->category }}</span>
                </div>
                <p class="text-sm font-bold mt-1">Rp {{ number_format($p->price, 0, ',', '.') }} • Stok: {{ $p->stock }}</p>
                @if($p->status === 'rejected' && $p->rejection_reason)
                <p class="mt-1 text-[13px] font-semibold text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Alasan admin: {{ $p->rejection_reason }}</p>
                @endif
                @if($p->status === 'pending')
                <p class="mt-1 text-xs font-semibold text-amber-800">⏳ Menunggu verifikasi admin.</p>
                @endif
            </div>
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('seller.products.edit', $p) }}" class="text-[13px] font-extrabold px-4 py-2 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">Edit</a>
                <form method="POST" action="{{ route('seller.products.destroy', $p) }}" onsubmit="return confirm('Hapus produk ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-[13px] font-extrabold px-4 py-2 rounded-full bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition">Hapus</button>
                </form>
            </div>
        </div>
        @empty
        <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-8 text-center">
            <p class="text-3xl">📦</p>
            <p class="mt-2 font-extrabold">Belum ada produk</p>
            <p class="text-sm font-medium text-ink-500">Tambahkan produk pertamamu, admin akan verifikasi sebelum tayang.</p>
            <a href="{{ route('seller.products.create') }}" class="mt-4 inline-block text-sm font-extrabold bg-ink-900 text-white px-6 py-3 rounded-full">+ Tambah Produk</a>
        </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</section>
@endsection
