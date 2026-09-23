{{-- Overrides the framework default: Indonesian copy + the Warung Hebat pill style. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-[12px] font-semibold text-ink-500">
            Menampilkan
            <span class="font-black text-ink-900">{{ $paginator->firstItem() ?? 0 }}</span>–<span class="font-black text-ink-900">{{ $paginator->lastItem() ?? 0 }}</span>
            dari <span class="font-black text-ink-900">{{ $paginator->total() }}</span> data
        </p>

        <div class="flex flex-wrap items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="Halaman sebelumnya" class="grid place-items-center w-9 h-9 rounded-full bg-white border border-ink-900/10 text-ink-400 cursor-not-allowed">
                    <x-icon name="chevron-left" class="w-4 h-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya" class="grid place-items-center w-9 h-9 rounded-full bg-white border border-ink-900/10 hover:bg-ink-900 hover:text-white transition">
                    <x-icon name="chevron-left" class="w-4 h-4" />
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true" class="grid place-items-center w-9 h-9 rounded-full text-[13px] font-extrabold text-ink-500">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="grid place-items-center w-9 h-9 rounded-full bg-ink-900 text-white text-[13px] font-extrabold">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="grid place-items-center w-9 h-9 rounded-full bg-white border border-ink-900/10 text-[13px] font-extrabold hover:bg-ink-900 hover:text-white transition">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya" class="grid place-items-center w-9 h-9 rounded-full bg-white border border-ink-900/10 hover:bg-ink-900 hover:text-white transition">
                    <x-icon name="chevron-right" class="w-4 h-4" />
                </a>
            @else
                <span aria-disabled="true" aria-label="Halaman berikutnya" class="grid place-items-center w-9 h-9 rounded-full bg-white border border-ink-900/10 text-ink-400 cursor-not-allowed">
                    <x-icon name="chevron-right" class="w-4 h-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
