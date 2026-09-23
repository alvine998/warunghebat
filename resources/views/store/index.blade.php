@extends('layouts.app')

@section('title', 'Warung Terdekat — Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-14 sm:pb-20 max-w-7xl mx-auto px-4 sm:px-6">
    <a href="{{ route('home') }}#warung" class="inline-flex items-center gap-1.5 min-h-10 text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Warung terdekat</a>

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mt-3 mb-7">
        <div>
            <p class="inline-flex text-[11px] font-extrabold tracking-[0.18em] text-leaf-700 bg-leaf-100 border border-leaf-500/20 rounded-full px-3.5 py-1.5">📍 NEARBY-FIRST</p>
            <h1 class="font-black tracking-tight text-3xl sm:text-5xl mt-3">Semua warung terdekat</h1>
            <p class="text-ink-500 font-medium text-[15px] mt-2">
                @if(isset($userLat) && isset($userLng))
                    Diurut dari jarakmu • radius {{ rtrim(rtrim(number_format($radius ?? 5, 1), '0'), '.') }} km.
                @else
                    Jarak asli, ulasan asli, rasa tetangga. Nyalakan lokasi buat urut dari yang paling dekat.
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-[13px] font-bold">
            <span class="inline-flex items-center gap-1.5 bg-ink-900 text-white rounded-full pl-3 pr-4 py-2">◎ Radius {{ rtrim(rtrim(number_format($radius ?? 5, 1), '0'), '.') }} km</span>
            <button id="locate-btn" type="button" class="inline-flex items-center gap-1.5 bg-white border border-ink-900/15 rounded-full px-4 py-2 hover:border-ink-900 transition">📍 {{ isset($userLat) ? 'Perbarui lokasiku' : 'Gunakan lokasiku' }}</button>
        </div>
    </div>
    <p id="locate-status" class="hidden mb-4 text-[13px] font-bold text-ink-500" role="status"></p>

    <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($stores as $i => $w)
        <x-store-card :store="$w" :index="$i" />
        @empty
        <div class="sm:col-span-2 lg:col-span-3 rounded-[24px] bg-white border border-dashed border-ink-900/15 p-10 text-center">
            <p class="text-4xl">🏪</p>
            <p class="font-extrabold text-lg mt-2">Belum ada warung di radius ini</p>
            <p class="text-sm font-medium text-ink-500 mt-1">Coba perbesar radius, matikan lokasi, atau cari nama warung di beranda.</p>
            <div class="mt-4 flex justify-center gap-2">
                <a href="{{ route('store.index') }}" class="text-[13px] font-extrabold bg-ink-900 text-white px-5 py-2.5 rounded-full">Lihat semua warung</a>
                @guest
                <a href="{{ route('register') }}" data-open-register class="text-[13px] font-extrabold border-2 border-ink-900/10 px-5 py-2.5 rounded-full">Buka warung pertama →</a>
                @endguest
            </div>
        </div>
        @endforelse
    </div>

    @if($stores->hasPages())
    <div class="mt-7 flex justify-center">{{ $stores->links() }}</div>
    @endif
</section>

@include('components.locate-script', ['locateHash' => ''])
@endsection
