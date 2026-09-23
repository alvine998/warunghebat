@extends('layouts.app')

@section('title', 'Artikel & Tips Warung — Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-14 sm:pb-20 max-w-7xl mx-auto px-4 sm:px-6">
    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 min-h-10 text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Beranda</a>

    <div class="mt-3 mb-8 max-w-2xl">
        <p class="reveal inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-600 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">📰 ARTIKEL</p>
        <h1 class="reveal font-black tracking-tight text-3xl sm:text-5xl mt-3" style="--reveal-delay:80ms">Tips & cerita dari warung tetangga</h1>
        <p class="reveal text-ink-500 font-medium text-[15px] mt-2" style="--reveal-delay:140ms">Cara belanja lebih hemat, panduan kulakan buat pemilik warung, sampai kabar terbaru soal ongkir dan pengiriman.</p>
    </div>

    @if($articles->isEmpty())
        <div class="rounded-[24px] bg-white border border-dashed border-ink-900/15 p-10 text-center">
            <p class="text-4xl">📝</p>
            <p class="font-extrabold text-lg mt-2">Belum ada artikel</p>
            <p class="text-sm font-medium text-ink-500 mt-1">Artikel pertama sedang disiapkan. Sementara itu, lihat warung terdekat dulu.</p>
            <a href="{{ route('store.index') }}" class="mt-4 inline-block text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">Lihat warung terdekat</a>
        </div>
    @else
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($articles as $i => $article)
            <x-article-card :article="$article" :index="$i" />
            @endforeach
        </div>
    @endif

    @if($articles->hasPages())
    <div class="mt-8 flex justify-center">{{ $articles->links() }}</div>
    @endif
</section>
@endsection
