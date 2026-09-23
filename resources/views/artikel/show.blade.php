@extends('layouts.app')

@section('title', $article->title.' — Warung Hebat')

@push('head')
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $article->title,
    'description' => $article->summary,
    'image' => [$article->cover_url ?: asset('og-image.png')],
    'datePublished' => $article->published_at?->toIso8601String(),
    'dateModified' => $article->updated_at?->toIso8601String(),
    'author' => ['@type' => 'Organization', 'name' => 'Warung Hebat'],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'Warung Hebat',
        'logo' => ['@type' => 'ImageObject', 'url' => asset('og-image.png')],
    ],
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('articles.show', $article)],
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
<style>
    .article-body { color: var(--color-ink-700); font-size: 16px; line-height: 1.75; }
    .article-body > * + * { margin-top: 1.1em; }
    .article-body h2 { color: var(--color-ink-900); font-weight: 800; font-size: 1.35rem; line-height: 1.3; margin-top: 2em; }
    .article-body h3 { color: var(--color-ink-900); font-weight: 800; font-size: 1.1rem; margin-top: 1.6em; }
    .article-body ul, .article-body ol { padding-left: 1.35em; }
    .article-body ul { list-style: disc; }
    .article-body ol { list-style: decimal; }
    .article-body li + li { margin-top: .45em; }
    .article-body strong { color: var(--color-ink-900); font-weight: 800; }
    .article-body a { color: var(--color-brand-600); font-weight: 700; text-decoration: underline; text-underline-offset: 4px; }
    .article-body blockquote { border-left: 4px solid var(--color-brand-400); background: var(--color-cream-100); border-radius: 0 18px 18px 0; padding: 1rem 1.25rem; font-weight: 600; color: var(--color-ink-900); }
    .article-body code { background: var(--color-cream-200); border-radius: 8px; padding: .15em .45em; font-size: .9em; }
</style>
@endpush

@section('content')
<section class="pt-24 sm:pt-28 pb-14 sm:pb-20 max-w-3xl mx-auto px-4 sm:px-6">
    <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-1.5 min-h-10 text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Semua artikel</a>

    <article class="mt-3">
        <p class="inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-600 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">📰 ARTIKEL</p>
        <h1 class="font-black tracking-tight text-3xl sm:text-[42px] leading-[1.08] mt-3">{{ $article->title }}</h1>
        <p class="mt-4 text-[13px] font-bold text-ink-500">
            {{ $article->author?->name ?? 'Tim Warung Hebat' }}
            <span class="text-ink-500/60">•</span> {{ $article->published_at?->format('d M Y') }}
            <span class="text-ink-500/60">•</span> {{ $article->reading_minutes }} menit baca
        </p>

        @if($article->cover_path)
            <img src="{{ $article->cover_url }}" alt="Ilustrasi artikel {{ $article->title }}" class="mt-5 w-full rounded-[24px] border border-ink-900/10 object-cover">
        @endif

        @if($article->excerpt)
            <p class="mt-5 text-[17px] font-semibold leading-relaxed text-ink-700">{{ $article->excerpt }}</p>
        @endif

        <div class="article-body mt-5">
            {!! \Str::markdown($article->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
        </div>
    </article>

    {{-- Share: buyers mostly forward links through chat apps, so keep this one tap. --}}
    @php($shareUrl = route('articles.show', $article))
    @php($shareText = $article->title.' — Warung Hebat')
    <div class="mt-9 rounded-[24px] bg-white border border-ink-900/10 p-5">
        <p class="text-[13px] font-extrabold">Bagikan artikel ini</p>
        <div class="mt-3 flex flex-wrap gap-2 text-[13px] font-extrabold">
            <a href="https://wa.me/?text={{ urlencode($shareText.' '.$shareUrl) }}" target="_blank" rel="noopener" class="px-4 py-2.5 rounded-full bg-leaf-100 text-leaf-700 hover:bg-leaf-500 hover:text-white transition">WhatsApp</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="px-4 py-2.5 rounded-full bg-cream-100 text-ink-700 hover:bg-ink-900 hover:text-white transition">Facebook</a>
            <a href="https://x.com/intent/post?url={{ urlencode($shareUrl) }}&text={{ urlencode($article->title) }}" target="_blank" rel="noopener" class="px-4 py-2.5 rounded-full bg-cream-100 text-ink-700 hover:bg-ink-900 hover:text-white transition">X</a>
        </div>
    </div>

    @if($related->isNotEmpty())
    <div class="mt-10">
        <h2 class="font-black tracking-tight text-2xl">Baca juga</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            @foreach($related as $i => $article)
            <x-article-card :article="$article" :index="$i" />
            @endforeach
        </div>
    </div>
    @endif

    <div class="mt-10 rounded-[24px] bg-ink-900 text-white p-6 sm:p-7">
        <p class="font-extrabold text-lg leading-snug">Mau jajan dari warung dekat rumah?</p>
        <p class="text-sm font-medium text-white/60 mt-1">Lihat warung terdekat yang sedang buka, pesan dari HP, diantar atau ambil sendiri.</p>
        <a href="{{ route('store.index') }}" class="mt-4 inline-flex bg-brand-500 hover:bg-brand-600 font-extrabold text-sm px-6 py-3.5 rounded-full transition">Lihat warung terdekat →</a>
    </div>
</section>
@endsection
