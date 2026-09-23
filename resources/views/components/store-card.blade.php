@php
    $dist = $store->distance_km ?? null;
    $distLabel = $dist !== null ? ($dist < 1 ? round($dist * 1000).'m' : number_format($dist, 1).' km') : null;
    $etaLabel = $dist !== null ? max(5, (int) round($dist * 8 + 5)).' mnt' : null;
    $topCats = $store->products->pluck('category')->filter()->unique()->take(2)->values();
    $catLine = $topCats->isNotEmpty() ? $topCats->join(' • ') : ($store->description ? \Str::limit($store->description, 42) : 'Warung tetangga');
@endphp
<article data-store-name="{{ $store->name }}" data-store-cat="{{ $catLine }} {{ $store->address }}" class="reveal group rounded-[24px] bg-white border border-ink-900/10 overflow-hidden hover:shadow-xl hover:shadow-ink-900/10 hover:-translate-y-1 transition-all duration-300" style="--reveal-delay:{{ ($index % 3) * 90 }}ms">
    <div class="h-28 relative flex items-center justify-center {{ $store->image_path ? '' : 'bg-gradient-to-br '.($index % 3 == 0 ? 'from-brand-100 via-cream-100 to-brand-200' : ($index % 3 == 1 ? 'from-leaf-100 via-cream-100 to-leaf-100' : 'from-cream-200 via-cream-100 to-brand-100')) }}">
        @if($store->image_path)
            <img src="{{ $store->image_url }}" alt="Foto {{ $store->name }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-ink-900/40 to-transparent"></div>
        @else
            <span class="text-5xl group-hover:scale-125 group-hover:-rotate-6 transition-transform duration-300">🏪</span>
        @endif
        <span class="absolute top-3 left-3 inline-flex items-center gap-1 text-[11px] font-extrabold bg-white/90 backdrop-blur rounded-full px-3 py-1.5">🛍️ {{ $store->approved_products_count }} produk</span>
        <span class="absolute top-3 right-3 inline-flex items-center gap-1.5 text-[11px] font-extrabold {{ $store->is_open ? 'bg-leaf-500 text-white' : 'bg-amber-400 text-ink-900' }} rounded-full px-3 py-1.5"><span class="w-1.5 h-1.5 rounded-full {{ $store->is_open ? 'bg-white animate-pulse' : 'bg-ink-900' }}"></span>{{ $store->is_open ? 'Buka' : 'Tutup' }}</span>
    </div>
    <div class="p-4">
        <p class="font-extrabold text-[16px]">{{ $store->name }}</p>
        <p class="text-[13px] font-medium text-ink-500">{{ $catLine }}</p>
        <div class="flex items-center justify-between mt-3">
            <span class="text-[12px] font-bold text-ink-700 bg-cream-100 rounded-full px-3 py-1.5">📍 {{ $distLabel ? $distLabel.($etaLabel ? ' • '.$etaLabel : '') : ($store->address ? \Str::limit($store->address, 24) : 'Lokasi menyusul') }}</span>
            <a href="{{ route('store.show', $store) }}" class="text-[13px] font-extrabold bg-ink-900 text-white px-4 py-2 rounded-full group-hover:bg-brand-500 transition">Lihat</a>
        </div>
    </div>
</article>
