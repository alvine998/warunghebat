@extends('layouts.app')

@section('title', 'Kamu sedang offline — Warung Hebat')

@section('content')
<section class="pt-28 sm:pt-32 pb-16 max-w-xl mx-auto px-4 sm:px-6 text-center">
    <p class="inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-700 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">📴 OFFLINE</p>
    <h1 class="font-black tracking-tight text-3xl sm:text-5xl mt-3">Sinyal lagi hilang.</h1>
    <p class="text-ink-500 font-medium text-[15px] mt-2">Tenang, begitu koneksi balik kamu bisa lanjut jajan. Katalog yang sempat dibuka tetap bisa dilihat dari cache.</p>

    <div class="mt-8 grid gap-3">
        <a href="{{ route('home') }}" class="w-full py-4 rounded-2xl bg-brand-500 text-white font-extrabold text-[15px] hover:bg-brand-600 active:scale-[.99] transition shadow-xl shadow-brand-500/30">Coba Lagi →</a>
        <a href="{{ route('store.index') }}" class="w-full py-4 rounded-2xl bg-white border-2 border-ink-900/10 font-extrabold text-[15px] hover:border-ink-900 transition">Lihat Warung Tersimpan</a>
    </div>

    <p class="mt-5 text-[12px] font-semibold text-ink-500">Tips: tambah Warung Hebat ke layar utama biar kebuka kayak aplikasi.</p>
</section>
@endsection
