@extends('layouts.admin')

@section('title', 'Artikel — Backoffice Warung Hebat')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
        <p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="document" class="w-4 h-4" /> ARTIKEL</p>
        <h1 class="font-black tracking-tight text-3xl mt-1">Blog dan tips warung</h1>
        <p class="text-sm font-medium text-ink-500">Artikel yang terbit tampil di halaman /artikel, ikut masuk sitemap, dan pratinjau tautannya muncul saat dibagikan ke media sosial.</p>
    </div>
    <a href="{{ route('admin.articles.create') }}" class="text-sm font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full hover:bg-brand-600 transition w-fit">+ Tulis artikel</a>
</div>

<form method="GET" action="{{ route('admin.articles') }}" class="mt-4 flex flex-col sm:flex-row gap-2">
    <label class="sr-only" for="article-search">Cari artikel</label>
    <div class="flex flex-1 items-center gap-2 rounded-2xl bg-white border border-ink-900/10 px-4 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10">
        <x-icon name="search" class="w-5 h-5 text-ink-400" />
        <input id="article-search" name="search" value="{{ request('search') }}" type="search" placeholder="Cari judul atau isi artikel..." class="w-full bg-transparent py-3 text-sm font-semibold outline-none placeholder:text-ink-400">
    </div>
    <button type="submit" class="rounded-2xl bg-ink-900 px-5 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition">Cari</button>
</form>

<div class="mt-5 grid gap-2.5">
    @forelse($articles as $article)
        <div class="rounded-[24px] bg-white border border-ink-900/10 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                @if($article->cover_path)
                    <img src="{{ $article->cover_url }}" alt="Cover {{ $article->title }}" class="w-full sm:w-24 h-40 sm:h-20 rounded-2xl object-cover border border-ink-900/10 shrink-0">
                @else
                    <div class="w-full sm:w-24 h-40 sm:h-20 rounded-2xl bg-cream-100 border border-dashed border-ink-900/15 grid place-items-center shrink-0"><x-icon name="document" class="w-7 h-7 text-ink-500" /></div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-extrabold">{{ $article->title }}</p>
                        <span class="text-[11px] font-extrabold rounded-full px-3 py-1 {{ $article->isPublished() ? 'bg-leaf-100 text-leaf-700' : 'bg-amber-100 text-amber-800' }}">{{ $article->isPublished() ? 'Terbit' : 'Draft' }}</span>
                        @if($article->published_at)
                            <span class="text-[11px] font-bold text-ink-500">{{ $article->published_at->format('d M Y') }}</span>
                        @endif
                    </div>
                    <p class="mt-1 text-[13px] font-medium text-ink-700 line-clamp-2">{{ $article->summary }}</p>
                    <p class="mt-1 text-[12px] font-semibold text-ink-500">{{ $article->author?->name ?? 'Tanpa penulis' }} • /artikel/{{ $article->slug }} • {{ $article->reading_minutes }} menit baca</p>
                </div>

                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    @if($article->isPublished())
                        <a href="{{ route('articles.show', $article) }}" target="_blank" rel="noopener" class="text-[13px] font-extrabold px-4 py-2 rounded-full bg-cream-100 hover:bg-ink-900 hover:text-white transition">Lihat</a>
                    @endif
                    <form method="POST" action="{{ route('admin.articles.toggle', $article) }}">
                        @csrf
                        @method('PATCH')
                        <button class="text-[13px] font-extrabold px-4 py-2 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">{{ $article->isPublished() ? 'Tarik jadi draft' : 'Terbitkan' }}</button>
                    </form>
                    <a href="{{ route('admin.articles.edit', $article) }}" class="text-[13px] font-extrabold px-4 py-2 rounded-full bg-cream-100 hover:bg-ink-900 hover:text-white transition">Edit</a>
                    <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Hapus artikel ini? Tautan yang sudah dibagikan tidak akan bisa dibuka lagi.');">
                        @csrf
                        @method('DELETE')
                        <button class="text-[13px] font-extrabold px-4 py-2 rounded-full bg-red-50 text-red-700 border border-red-200 hover:bg-red-600 hover:text-white transition">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-8 text-center">
            @if(request('search'))
                <p class="font-extrabold">Tidak ada artikel yang cocok dengan "{{ request('search') }}"</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Coba kata kunci lain seperti judul, topik, atau isi artikel.</p>
            @else
                <p class="text-3xl">📰</p>
                <p class="font-extrabold mt-2">Belum ada artikel</p>
                <p class="text-sm font-medium text-ink-500 mt-1">Tulis artikel pertama untuk mengisi halaman /artikel dan sitemap.</p>
                <a href="{{ route('admin.articles.create') }}" class="mt-4 inline-block text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">+ Tulis artikel</a>
            @endif
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $articles->links() }}</div>
@endsection
