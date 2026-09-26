{{-- Product card for listing pages. Expects $product (with user.store + distance_km) and $index. --}}
@php($item = $product ?? $p ?? null)
@php($store = $item->user->store)
@php($dist = $item->distance_km ?? null)
<article class="reveal group flex flex-col rounded-[20px] sm:rounded-[24px] bg-white border border-ink-900/10 overflow-hidden hover:shadow-xl hover:shadow-ink-900/10 hover:-translate-y-1 transition-all duration-300" style="--reveal-delay:{{ (($index ?? 0) % 4) * 70 }}ms">
    <a href="{{ route('store.show', $store) }}" class="relative block">
        @if($item->image_path)
            <img src="{{ $item->image_url }}" alt="Foto {{ $item->name }}" loading="lazy" class="w-full h-28 sm:h-36 object-cover">
        @else
            <div class="w-full h-28 sm:h-36 bg-cream-100 border-b border-dashed border-ink-900/15 grid place-items-center text-3xl">📷</div>
        @endif
        @if($item->hasActivePromo())
            <span class="absolute top-2 left-2 text-[11px] sm:text-[12px] font-black text-white bg-brand-500 rounded-full px-2.5 py-1 shadow-lg">−{{ $item->discountPercent() }}%</span>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-2.5 sm:p-4">
        <a href="{{ route('store.show', $store) }}" class="block">
            <p class="font-extrabold text-[13px] sm:text-[15px] leading-snug break-words line-clamp-2 min-h-[2.1em] sm:min-h-0">{{ $item->name }}</p>
            <p class="mt-1 flex items-center gap-1.5 flex-wrap">
                <span class="min-w-0 truncate text-[11px] font-extrabold bg-cream-100 text-ink-700 rounded-full px-2 py-0.5">🏪 {{ $store->name }}</span>
                @if($item->brand)
                    <span class="shrink-0 text-[11px] font-extrabold bg-ink-900 text-white rounded-full px-2 py-0.5">🏷️ {{ $item->brand->name }}</span>
                @endif
                @unless($store->is_open)
                    <span class="shrink-0 text-[11px] font-extrabold bg-amber-100 text-amber-800 rounded-full px-2 py-0.5">Tutup</span>
                @endunless
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1.5">
                @if($item->hasActivePromo())
                    <p class="font-black text-[13px] sm:text-[16px] text-brand-600 leading-tight">Rp {{ number_format($item->effectivePrice(), 0, ',', '.') }}</p>
                    <p class="text-[11px] sm:text-[12px] font-bold text-ink-500 line-through leading-tight">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                @else
                    <p class="font-black text-[13px] sm:text-[16px] text-brand-600 leading-tight">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                @endif
                @if($item->stock > 0)
                    <span class="text-[10px] sm:text-[11px] font-extrabold text-leaf-700 bg-leaf-100 rounded-full px-2 py-0.5 whitespace-nowrap">Stok {{ $item->stock }}</span>
                @else
                    <span class="text-[10px] sm:text-[11px] font-extrabold text-amber-800 bg-amber-100 rounded-full px-2 py-0.5 whitespace-nowrap">Habis</span>
                @endif
            </div>
            <p class="mt-2 sm:mt-3 text-[11px] sm:text-[12px] font-bold text-ink-500 truncate">📍 {{ $dist !== null ? ($dist < 1 ? round($dist * 1000).'m' : number_format($dist, 1).' km') : ($store->address ? \Str::limit($store->address, 20) : 'Lokasi menyusul') }}</p>
        </a>

        @if($store->is_open && $item->stock > 0)
        <div class="mt-auto pt-2.5">
            @auth
            <form method="POST" action="{{ route('cart.store') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $item->id }}">
                <button type="submit" class="w-full min-h-11 py-2.5 px-2 rounded-full bg-ink-900 text-white text-[12px] sm:text-[13px] font-extrabold hover:bg-brand-500 active:scale-[.98] transition">+ Keranjang</button>
            </form>
            @else
            <button type="button" data-open-login class="w-full min-h-11 py-2.5 px-2 rounded-full bg-ink-900 text-white text-[12px] sm:text-[13px] font-extrabold hover:bg-brand-500 active:scale-[.98] transition">+ Keranjang</button>
            @endauth
        </div>
        @endif
    </div>
</article>
