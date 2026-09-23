{{-- Expects $storeStats (array) and $lowStockProducts (collection). --}}
{{-- 2-up on phones: four stacked cards burn a full screen of scroll. --}}
<div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-2.5">
    @foreach([
        ['📦', 'Total produk', number_format($storeStats['total'] ?? 0, 0, ',', '.'), 'bg-ink-900 text-white'],
        ['✅', 'Tayang', number_format($storeStats['approved'] ?? 0, 0, ',', '.'), 'bg-white border border-ink-900/10'],
        ['⏳', 'Pending', number_format($storeStats['pending'] ?? 0, 0, ',', '.'), 'bg-white border border-ink-900/10'],
        ['❌', 'Ditolak', number_format($storeStats['rejected'] ?? 0, 0, ',', '.'), 'bg-white border border-ink-900/10'],
    ] as $s)
    <div class="rounded-2xl p-4 min-w-0 {{ $s[3] }}">
        <p class="text-xl">{{ $s[0] }}</p>
        <p class="font-black text-xl sm:text-2xl mt-1 tabular-nums">{{ $s[2] }}</p>
        <p class="text-[11px] font-bold opacity-70 truncate">{{ $s[1] }}</p>
    </div>
    @endforeach
</div>

<div class="mt-2.5 grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-2.5">
    @foreach([
        ['📊', 'Total stok', number_format($storeStats['stock'] ?? 0, 0, ',', '.') . ' pcs'],
        ['💰', 'Nilai inventaris', 'Rp ' . number_format($storeStats['inventory_value'] ?? 0, 0, ',', '.')],
        ['⚠️', 'Stok menipis', number_format($storeStats['low_stock'] ?? 0, 0, ',', '.') . ' produk'],
        ['⛔', 'Stok habis', number_format($storeStats['out_of_stock'] ?? 0, 0, ',', '.') . ' produk'],
    ] as $s)
    <div class="rounded-2xl bg-cream-100 border border-ink-900/10 p-4 min-w-0">
        <p class="text-xl">{{ $s[0] }}</p>
        <p class="font-black text-sm sm:text-lg mt-1 leading-snug tabular-nums break-words">{{ $s[2] }}</p>
        <p class="text-[11px] font-bold text-ink-500 truncate">{{ $s[1] }}</p>
    </div>
    @endforeach
</div>

@if(($lowStockProducts ?? collect())->isNotEmpty())
<div class="mt-2.5 rounded-2xl border border-amber-200 bg-amber-50 p-4">
    <p class="text-[13px] font-extrabold text-amber-900">⚠️ Stok menipis — segera restock</p>
    <div class="mt-2 grid gap-2">
        @foreach($lowStockProducts as $p)
        <div class="flex items-center gap-3 rounded-xl bg-white border border-amber-200/60 px-3 py-2.5">
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-extrabold truncate">{{ $p->name }}</p>
                <p class="text-[11px] font-bold {{ $p->stock === 0 ? 'text-red-700' : 'text-amber-800' }}">Sisa {{ $p->stock }} pcs</p>
            </div>
            <a href="{{ route('seller.products.edit', $p) }}" class="shrink-0 text-[12px] font-extrabold px-4 py-2 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">Restock</a>
        </div>
        @endforeach
    </div>
</div>
@endif
