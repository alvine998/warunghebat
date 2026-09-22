@extends('layouts.admin')

@section('title', 'Verifikasi Produk — Backoffice Warung Hebat')

@section('content')
<p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="package" class="w-4 h-4" /> VERIFIKASI PRODUK</p>
<h1 class="font-black tracking-tight text-3xl mt-1">Produk penjual</h1>
<p class="text-sm font-medium text-ink-500">Setujui produk yang layak tayang, tolak dengan alasan yang jelas.</p>

@if (session('success'))
    <div class="mt-4 rounded-2xl bg-leaf-50 border border-leaf-500/30 text-leaf-700 text-sm font-bold p-4">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="mt-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-bold p-4">{{ $errors->first() }}</div>
@endif

<div class="mt-5 flex flex-wrap gap-2 text-[13px] font-extrabold">
    <a href="{{ route('admin.products') }}" class="px-4 py-2 rounded-full {{ !request('status') ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}">Semua ({{ $counts['all'] }})</a>
    <a href="{{ route('admin.products', ['status' => 'pending']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full {{ request('status') === 'pending' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}"><x-icon name="clock" class="w-4 h-4" /> Pending ({{ $counts['pending'] }})</a>
    <a href="{{ route('admin.products', ['status' => 'approved']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full {{ request('status') === 'approved' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}"><x-icon name="check-circle" class="w-4 h-4" /> Tayang ({{ $counts['approved'] }})</a>
    <a href="{{ route('admin.products', ['status' => 'rejected']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full {{ request('status') === 'rejected' ? 'bg-ink-900 text-white' : 'bg-white border border-ink-900/10' }}"><x-icon name="x-mark" class="w-4 h-4" /> Ditolak ({{ $counts['rejected'] }})</a>
</div>

<form method="GET" action="{{ route('admin.products') }}" class="mt-4 flex flex-col sm:flex-row gap-2">
    @if(request('status'))
        <input type="hidden" name="status" value="{{ request('status') }}">
    @endif
    <label class="sr-only" for="product-search">Cari produk</label>
    <div class="flex flex-1 items-center gap-2 rounded-2xl bg-white border border-ink-900/10 px-4 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10">
        <x-icon name="search" class="w-5 h-5 text-ink-400" />
        <input id="product-search" name="search" value="{{ request('search') }}" type="search" placeholder="Cari nama produk, kategori, atau penjual..." class="w-full bg-transparent py-3 text-sm font-semibold outline-none placeholder:text-ink-400">
    </div>
    <button type="submit" class="rounded-2xl bg-ink-900 px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition">Cari</button>
</form>

<form id="bulk-products-form" method="POST" action="{{ route('admin.products.bulk-update') }}" class="mt-4 rounded-[24px] bg-ink-900 text-white p-4 sm:p-5" onsubmit="return confirm('Terapkan tindakan ini ke produk yang dipilih?');">
    @csrf
    @method('PATCH')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <label class="inline-flex items-center gap-2 text-sm font-extrabold">
            <input type="checkbox" id="select-all-products" class="h-4 w-4 rounded border-white/30 accent-brand-500">
            Pilih semua di halaman
        </label>
        <span id="selected-products-count" class="text-xs font-semibold text-white/60">0 dipilih</span>
        <div class="flex flex-1 flex-col gap-2 sm:flex-row sm:justify-end">
            <select name="action" id="bulk-product-action" required class="rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 text-sm font-bold text-white outline-none focus:border-brand-400">
                <option value="" class="text-ink-900">Pilih tindakan...</option>
                <option value="approve" class="text-ink-900">Setujui produk</option>
                <option value="reject" class="text-ink-900">Tolak produk</option>
            </select>
            <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold hover:bg-brand-600 transition">Terapkan</button>
        </div>
    </div>
    <div id="bulk-rejection-field" class="hidden mt-3">
        <label for="bulk-rejection-reason" class="sr-only">Alasan penolakan</label>
        <input id="bulk-rejection-reason" name="rejection_reason" maxlength="1000" placeholder="Alasan penolakan untuk semua produk yang dipilih..." class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white outline-none placeholder:text-white/50 focus:border-brand-400">
    </div>
</form>

<div class="mt-4 grid gap-2.5">
    @forelse($products as $p)
    <div class="rounded-[24px] bg-white border border-ink-900/10 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <input type="checkbox" name="product_ids[]" value="{{ $p->id }}" form="bulk-products-form" class="product-checkbox h-5 w-5 shrink-0 rounded border-ink-900/20 accent-brand-500" aria-label="Pilih {{ $p->name }}">
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

<script>
    const selectAllProducts = document.getElementById('select-all-products');
    const productCheckboxes = [...document.querySelectorAll('.product-checkbox')];
    const selectedProductsCount = document.getElementById('selected-products-count');
    const bulkProductAction = document.getElementById('bulk-product-action');
    const bulkRejectionField = document.getElementById('bulk-rejection-field');
    const bulkRejectionReason = document.getElementById('bulk-rejection-reason');

    function updateSelectedProducts() {
        const selectedCount = productCheckboxes.filter((checkbox) => checkbox.checked).length;

        selectedProductsCount.textContent = `${selectedCount} dipilih`;
        selectAllProducts.checked = productCheckboxes.length > 0 && selectedCount === productCheckboxes.length;
        selectAllProducts.indeterminate = selectedCount > 0 && selectedCount < productCheckboxes.length;
    }

    selectAllProducts.addEventListener('change', () => {
        productCheckboxes.forEach((checkbox) => {
            checkbox.checked = selectAllProducts.checked;
        });
        updateSelectedProducts();
    });

    productCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelectedProducts));

    bulkProductAction.addEventListener('change', () => {
        const rejecting = bulkProductAction.value === 'reject';

        bulkRejectionField.classList.toggle('hidden', ! rejecting);
        bulkRejectionReason.required = rejecting;
    });
</script>
@endsection
