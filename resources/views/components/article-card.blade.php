{{-- Article teaser used by /artikel, the landing page and related-post lists. --}}
@php($cover = $article->cover_path)
<article class="reveal group flex flex-col rounded-[24px] bg-white border border-ink-900/10 overflow-hidden hover:shadow-xl hover:shadow-ink-900/10 hover:-translate-y-1 transition-all duration-300" style="--reveal-delay:{{ ($index % 3) * 90 }}ms">
    <a href="{{ route('articles.show', $article) }}" class="block">
        @if($cover)
            <img src="{{ $article->cover_url }}" alt="Ilustrasi artikel {{ $article->title }}" loading="lazy" class="w-full h-40 object-cover">
        @else
            <div class="w-full h-40 bg-gradient-to-br from-brand-100 via-cream-100 to-leaf-100 grid place-items-center text-4xl">📰</div>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-5">
        <p class="text-[11px] font-extrabold tracking-wide text-ink-500">{{ $article->published_at?->format('d M Y') }} • {{ $article->reading_minutes }} menit baca</p>
        <a href="{{ route('articles.show', $article) }}" class="mt-2 block">
            <h3 class="font-extrabold text-[17px] leading-snug group-hover:text-brand-600 transition">{{ $article->title }}</h3>
            <p class="text-[13px] font-medium text-ink-500 leading-relaxed mt-2 line-clamp-3">{{ $article->summary }}</p>
        </a>
        <a href="{{ route('articles.show', $article) }}" class="mt-auto pt-4 text-[13px] font-extrabold text-brand-600">Baca selengkapnya →</a>
    </div>
</article>
