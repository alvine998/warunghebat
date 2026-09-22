{{-- Expects $finance (array from User::financialOverview). --}}
<div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-2.5">
    <div class="rounded-2xl bg-ink-900 text-white p-4">
        <p class="text-xl">💰</p>
        <p class="font-black text-lg mt-1 leading-snug">Rp {{ number_format($finance['inventory_value'] ?? 0, 0, ',', '.') }}</p>
        <p class="text-[11px] font-bold opacity-70">Nilai inventaris</p>
    </div>
    <div class="rounded-2xl bg-white border border-ink-900/10 p-4">
        <p class="text-xl">🏷️</p>
        <p class="font-black text-lg mt-1 leading-snug">Rp {{ number_format($finance['avg_price'] ?? 0, 0, ',', '.') }}</p>
        <p class="text-[11px] font-bold text-ink-500">Rata-rata harga</p>
    </div>
    <div class="rounded-2xl bg-white border border-ink-900/10 p-4">
        <p class="text-xl">🗂️</p>
        <p class="font-black text-2xl mt-1">{{ number_format(count($finance['categories'] ?? []), 0, ',', '.') }}</p>
        <p class="text-[11px] font-bold text-ink-500">Kategori aktif</p>
    </div>
    <div class="rounded-2xl bg-white border border-ink-900/10 p-4">
        <p class="text-xl">📦</p>
        <p class="font-black text-2xl mt-1">{{ number_format($finance['total'] ?? 0, 0, ',', '.') }}</p>
        <p class="text-[11px] font-bold text-ink-500">Total SKU</p>
    </div>
</div>

@if(($finance['total'] ?? 0) > 0)
<div class="mt-2.5 overflow-x-auto rounded-2xl border border-ink-900/10">
    <table class="w-full text-left text-sm min-w-[480px]">
        <thead>
            <tr class="bg-cream-50/60 text-[11px] font-extrabold uppercase tracking-wider text-ink-500">
                <th class="px-4 py-3">Kategori</th>
                <th class="px-4 py-3">Produk</th>
                <th class="px-4 py-3">Stok</th>
                <th class="px-4 py-3 text-right">Nilai</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-ink-900/5 bg-white">
            @foreach($finance['categories'] as $c)
            <tr>
                <td class="px-4 py-3 font-extrabold">{{ $c->category }}</td>
                <td class="px-4 py-3 font-bold">{{ number_format($c->products, 0, ',', '.') }}</td>
                <td class="px-4 py-3 font-bold">{{ number_format($c->stock, 0, ',', '.') }} pcs</td>
                <td class="px-4 py-3 font-black text-right whitespace-nowrap">Rp {{ number_format($c->value, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-2.5 rounded-2xl border border-ink-900/10 bg-white p-4">
    <p class="text-[13px] font-extrabold">💎 Produk bernilai tertinggi</p>
    <div class="mt-2 grid gap-2">
        @foreach($finance['top_products'] as $i => $p)
        <div class="flex items-center gap-3">
            <span class="w-7 h-7 shrink-0 rounded-lg bg-cream-100 grid place-items-center text-[12px] font-black">{{ $i + 1 }}</span>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-extrabold truncate">{{ $p->name }}</p>
                <p class="text-[11px] font-semibold text-ink-500">{{ number_format($p->stock, 0, ',', '.') }} pcs × Rp {{ number_format($p->price, 0, ',', '.') }}</p>
            </div>
            <p class="shrink-0 text-[13px] font-black">Rp {{ number_format($p->stock_value, 0, ',', '.') }}</p>
        </div>
        @endforeach
    </div>
</div>
@else
<div class="mt-2.5 rounded-2xl border border-dashed border-ink-900/15 p-6 text-center">
    <p class="text-sm font-extrabold">Belum ada produk</p>
    <p class="text-xs font-semibold text-ink-500 mt-0.5">Tambahkan produk untuk melihat ringkasan keuangan warungmu.</p>
    <a href="{{ route('seller.products.create') }}" class="mt-3 inline-block text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">+ Tambah Produk</a>
</div>
@endif
