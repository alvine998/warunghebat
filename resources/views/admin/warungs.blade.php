@extends('layouts.admin')

@section('title', 'Warung — Backoffice Warung Hebat')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
        <p class="flex items-center gap-2 text-[11px] font-extrabold tracking-[0.2em] text-brand-600"><x-icon name="store" class="w-4 h-4" /> WARUNG MITRA</p>
        <h1 class="font-black tracking-tight text-3xl mt-1">{{ $warungs->count() }} warung terdaftar</h1>
        <p class="text-sm font-medium text-ink-500">Verifikasi status warung: Aktif, Review, Nonaktif.</p>
    </div>
    <span class="text-sm font-extrabold bg-leaf-600 text-white px-5 py-2.5 rounded-full w-fit">+ Tambah Warung</span>
</div>

<div class="mt-5 grid gap-3 sm:grid-cols-2">
    @foreach($warungs as $w)
    <article class="rounded-[24px] bg-white border border-ink-900/10 p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="font-extrabold text-lg">{{ $w['name'] }}</p>
                <p class="flex items-center gap-1 text-[13px] font-semibold text-ink-500">{{ $w['owner'] }} • {{ $w['cat'] }} • <x-icon name="map-pin" class="w-3.5 h-3.5" /> {{ $w['distance'] }} • <x-icon name="star-solid" class="w-3.5 h-3.5 text-amber-500" /> {{ $w['rating'] }}</p>
            </div>
            <span class="shrink-0 text-[11px] font-extrabold rounded-full px-3 py-1.5 {{ $w['status'] === 'Aktif' ? 'bg-leaf-100 text-leaf-700' : ($w['status'] === 'Review' ? 'bg-amber-100 text-amber-800' : 'bg-ink-900/10 text-ink-500') }}">{{ $w['status'] }}</span>
        </div>
        <div class="mt-4 grid grid-cols-3 gap-2 text-[12px] font-extrabold">
            <button class="py-2.5 rounded-xl bg-cream-100 hover:bg-ink-900 hover:text-white transition">Detail</button>
            <button class="py-2.5 rounded-xl bg-leaf-600 text-white hover:bg-leaf-700 transition">Setujui</button>
            <button class="py-2.5 rounded-xl bg-ink-900/5 hover:bg-red-500 hover:text-white transition">Nonaktifkan</button>
        </div>
    </article>
    @endforeach
</div>
@endsection
