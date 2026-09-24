@extends('layouts.app')

@section('title', 'Produk Saya — Warung Hebat')

@section('bare', true)

@section('content')
<section class="pt-6 sm:pt-8 pb-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between gap-2 mb-5 flex-wrap">
        <a href="{{ route('dashboard') }}" class="text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Dashboard</a>
        <div class="flex items-center gap-2 flex-wrap">
            @include('seller.store._open_toggle', ['store' => $store])
            <a href="{{ route('seller.store.edit') }}" class="text-[13px] font-extrabold px-5 py-2.5 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">⚙️ Pengaturan Warung</a>
            <a href="{{ route('seller.products.create') }}" class="text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition">+ Tambah Produk</a>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <p class="text-[11px] font-extrabold tracking-[0.2em] text-leaf-700">🏪 PRODUK SAYA</p>
            <h1 class="font-black tracking-tight text-2xl sm:text-3xl lg:text-4xl mt-1">Kelola produk</h1>
            <p class="text-sm font-medium text-ink-500 mt-1">Produk baru & hasil edit menunggu verifikasi admin sebelum tayang.</p>
        </div>
        <div class="hidden lg:flex items-center gap-2 text-[13px] font-bold text-ink-500">
            <span class="rounded-full bg-white border border-ink-900/10 px-4 py-2">Total: {{ $counts['all'] }} produk</span>
            <span class="rounded-full bg-leaf-100 text-leaf-700 px-4 py-2">Tayang: {{ $counts['approved'] }}</span>
        </div>
    </div>

    <div class="mt-5 flex flex-wrap gap-2 text-[11px] sm:text-[13px] font-extrabold">
        <a href="{{ route('seller.products.index') }}" class="px-3 sm:px-4 py-2 rounded-full {{ !request('status') ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">Semua ({{ $counts['all'] }})</a>
        <a href="{{ route('seller.products.index', ['status' => 'pending']) }}" class="px-3 sm:px-4 py-2 rounded-full {{ request('status') === 'pending' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">⏳ Pending ({{ $counts['pending'] }})</a>
        <a href="{{ route('seller.products.index', ['status' => 'approved']) }}" class="px-3 sm:px-4 py-2 rounded-full {{ request('status') === 'approved' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">✅ Tayang ({{ $counts['approved'] }})</a>
        <a href="{{ route('seller.products.index', ['status' => 'rejected']) }}" class="px-3 sm:px-4 py-2 rounded-full {{ request('status') === 'rejected' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">❌ Ditolak ({{ $counts['rejected'] }})</a>
    </div>

    @if($products->count())
    {{-- ===== Desktop table (lg+) ===== --}}
    <div class="mt-4 hidden lg:block overflow-hidden rounded-[24px] bg-white border border-ink-900/10">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-ink-900/10 bg-cream-50/60 text-[11px] font-extrabold uppercase tracking-wider text-ink-500">
                    <th class="px-5 py-3.5 w-[38%]">Produk</th>
                    <th class="px-4 py-3.5">Harga</th>
                    <th class="px-4 py-3.5">Stok</th>
                    <th class="px-4 py-3.5">Status</th>
                    <th class="px-4 py-3.5">Diperbarui</th>
                    <th class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-900/5">
                @foreach($products as $p)
                <tr class="hover:bg-cream-50/50 transition align-top">
                    <td class="px-5 py-4">
                        <div class="flex gap-3.5">
                            @if($p->image_path)
                                <img src="{{ $p->image_url }}" alt="Foto {{ $p->name }}" class="w-16 h-16 rounded-2xl object-cover border border-ink-900/10 shrink-0">
                            @else
                                <div class="w-16 h-16 rounded-2xl bg-cream-100 border border-dashed border-ink-900/15 grid place-items-center text-2xl shrink-0">📷</div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-extrabold leading-snug">{{ $p->name }}</p>
                                <p class="mt-0.5 text-xs font-bold text-ink-500">{{ $p->category }}</p>
                                @if($p->description)
                                    <p class="mt-1 text-[13px] font-medium text-ink-500 leading-snug line-clamp-2">{{ $p->description }}</p>
                                @endif
                                @if($p->status === 'rejected' && $p->rejection_reason)
                                    <p class="mt-1.5 text-[12px] font-semibold text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-1.5">Alasan admin: {{ $p->rejection_reason }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 font-black whitespace-nowrap">Rp {{ number_format($p->price, 0, ',', '.') }}
                        @if($p->hasActivePromo())
                            <span class="mt-1 block text-[11px] font-extrabold text-white bg-brand-500 rounded-full px-2.5 py-1 w-fit">⚡ Rp {{ number_format($p->discount_price, 0, ',', '.') }} (−{{ $p->discountPercent() }}%)</span>
                        @elseif($p->discount_price !== null)
                            <span class="mt-1 block text-[11px] font-extrabold text-amber-800 bg-amber-100 rounded-full px-2.5 py-1 w-fit">Promo tidak aktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 font-bold whitespace-nowrap">
                        {{ number_format($p->stock, 0, ',', '.') }}
                        @if($p->stock <= 5)
                            <span class="ml-1 text-[11px] font-extrabold text-red-700 bg-red-50 border border-red-200 rounded-full px-2 py-0.5">Menipis</span>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-block text-[11px] font-extrabold rounded-full px-3 py-1.5 whitespace-nowrap {{ $p->status === 'approved' ? 'bg-leaf-100 text-leaf-700' : ($p->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-700') }}">{{ $p->status }}</span>
                        @if($p->status === 'pending')
                            <p class="mt-1 text-[11px] font-semibold text-amber-800">⏳ Menunggu verifikasi</p>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-xs font-semibold text-ink-500 whitespace-nowrap">{{ $p->updated_at?->diffForHumans() }}</td>
                    <td class="px-5 py-4">
                        <div class="flex gap-2 justify-end">
                            <a href="{{ route('seller.products.edit', $p) }}" class="text-[13px] font-extrabold px-4 py-2 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">Edit</a>
                            <form method="POST" action="{{ route('seller.products.destroy', $p) }}" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-[13px] font-extrabold px-4 py-2 rounded-full bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ===== Mobile / tablet cards (<lg) ===== --}}
    <div class="mt-4 grid gap-2.5 sm:grid-cols-2 lg:hidden">
        @foreach($products as $p)
        <div class="rounded-[24px] bg-white border border-ink-900/10 overflow-hidden flex flex-col">
            @if($p->image_path)
                <img src="{{ $p->image_url }}" alt="Foto {{ $p->name }}" class="w-full h-44 sm:h-40 object-cover">
            @else
                <div class="w-full h-44 sm:h-40 bg-cream-100 border-b border-dashed border-ink-900/15 grid place-items-center text-4xl">📷</div>
            @endif
            <div class="p-4 flex flex-col gap-2 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="font-extrabold leading-snug flex-1 min-w-0">{{ $p->name }}</p>
                    <span class="text-[11px] font-extrabold rounded-full px-3 py-1 {{ $p->status === 'approved' ? 'bg-leaf-100 text-leaf-700' : ($p->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-700') }}">{{ $p->status }}</span>
                </div>
                <p class="text-[11px] font-bold text-ink-500">{{ $p->category }} • {{ $p->updated_at?->diffForHumans() }}</p>
                <p class="text-sm font-black">Rp {{ number_format($p->price, 0, ',', '.') }} <span class="font-bold text-ink-500">• Stok: {{ $p->stock }}</span></p>
                @if($p->hasActivePromo())
                <p class="text-[12px] font-extrabold text-white bg-brand-500 rounded-full px-3 py-1 w-fit">⚡ Promo Rp {{ number_format($p->discount_price, 0, ',', '.') }} (−{{ $p->discountPercent() }}%)</p>
                @elseif($p->discount_price !== null)
                <p class="text-[12px] font-extrabold text-amber-800 bg-amber-100 rounded-full px-3 py-1 w-fit">Promo tidak aktif</p>
                @endif
                @if($p->status === 'rejected' && $p->rejection_reason)
                <p class="text-[13px] font-semibold text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Alasan admin: {{ $p->rejection_reason }}</p>
                @endif
                @if($p->status === 'pending')
                <p class="text-xs font-semibold text-amber-800">⏳ Menunggu verifikasi admin.</p>
                @endif
                <div class="flex gap-2 mt-auto pt-1">
                    <a href="{{ route('seller.products.edit', $p) }}" class="flex-1 text-center text-[13px] font-extrabold px-4 py-2.5 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">Edit</a>
                    <form method="POST" action="{{ route('seller.products.destroy', $p) }}" onsubmit="return confirm('Hapus produk ini?')" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button class="w-full text-[13px] font-extrabold px-4 py-2.5 rounded-full bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="mt-4 rounded-[24px] bg-white border border-dashed border-ink-900/15 p-8 lg:p-14 text-center">
        <p class="text-3xl">📦</p>
        <p class="mt-2 font-extrabold text-lg">Belum ada produk</p>
        <p class="text-sm font-medium text-ink-500">Tambahkan produk pertamamu, admin akan verifikasi sebelum tayang.</p>
        <a href="{{ route('seller.products.create') }}" class="mt-4 inline-block text-sm font-extrabold bg-ink-900 text-white px-6 py-3 rounded-full">+ Tambah Produk</a>
    </div>
    @endif

    <div class="mt-6 flex justify-center">{{ $products->links() }}</div>
</section>
@endsection
