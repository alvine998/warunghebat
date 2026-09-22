@extends('layouts.app')

@section('title', 'Daftar — Warung Hebat')

@section('content')
<section class="min-h-screen grid lg:grid-cols-2 pt-[72px]">
    <div class="hidden lg:flex relative bg-leaf-700 text-white overflow-hidden flex-col justify-between p-12 grain">
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-brand-400/30 blur-[120px] rounded-full"></div>
        <a href="{{ route('home') }}" class="relative flex items-center gap-2.5">
            <span class="w-10 h-10 rounded-2xl bg-white grid place-items-center -rotate-3"><span class="font-black text-xl text-leaf-700">W</span></span>
            <span class="font-extrabold text-lg">Warung Hebat</span>
        </a>
        <div class="relative">
            <p class="inline-flex text-[11px] font-extrabold tracking-[0.2em] bg-white/15 border border-white/20 rounded-full px-4 py-1.5">🎉 100% GRATIS</p>
            <h1 class="font-black tracking-tight text-5xl leading-[1.0] mt-4">Satu akun,<br>semua warung <span class="text-brand-200">dekatmu.</span></h1>
            <ul class="mt-6 grid gap-2.5 max-w-sm text-[14px] font-bold">
                <li class="flex items-center gap-3 bg-white/10 border border-white/15 rounded-2xl p-3.5"><span class="w-9 h-9 rounded-xl bg-white/20 grid place-items-center">🛍️</span> Belanja dari 2.450+ warung terdekat</li>
                <li class="flex items-center gap-3 bg-white/10 border border-white/15 rounded-2xl p-3.5"><span class="w-9 h-9 rounded-xl bg-white/20 grid place-items-center">🏪</span> Atau buka warungmu sendiri, 0% komisi</li>
                <li class="flex items-center gap-3 bg-white/10 border border-white/15 rounded-2xl p-3.5"><span class="w-9 h-9 rounded-xl bg-white/20 grid place-items-center">🎁</span> Bonus 100 poin Hebat buat pendaftar baru</li>
            </ul>
        </div>
        <div class="relative flex items-center gap-3">
            <div class="flex -space-x-2.5">
                @foreach(['A','B','C'] as $a)<span class="w-9 h-9 rounded-full bg-ink-900 border-2 border-leaf-700 grid place-items-center text-xs font-black">{{ $a }}</span>@endforeach
            </div>
            <p class="text-xs font-bold text-white/70">48.000+ tetangga udah gabung bulan ini</p>
        </div>
    </div>

    <div class="flex items-start sm:items-center justify-center px-4 sm:px-8 py-10 bg-cream-50">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="lg:hidden flex items-center gap-2 mb-6">
                <span class="w-9 h-9 rounded-xl bg-ink-900 grid place-items-center -rotate-3"><span class="text-brand-400 font-black">W</span></span>
                <span class="font-extrabold">Warung Hebat</span>
            </a>
            <h2 class="font-black tracking-tight text-3xl sm:text-4xl">Bikin akun gratis 🎉</h2>
            <p class="text-ink-500 font-medium text-[15px] mt-1.5">Sudah punya akun? <a href="{{ route('login') }}" class="text-brand-600 font-extrabold">Masuk</a></p>

            @if ($errors->any())
                <div class="mt-5 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-[13px] font-bold p-4">
                    <ul class="list-disc pl-4 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="mt-6 grid gap-3.5">
                @csrf
                <label class="grid gap-1.5">
                    <span class="text-[13px] font-bold">Nama lengkap</span>
                    <input name="name" required autofocus value="{{ old('name') }}" placeholder="cth. Sari Dewi" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3.5 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                </label>
                <label class="grid gap-1.5">
                    <span class="text-[13px] font-bold">Email</span>
                    <input name="email" type="email" required value="{{ old('email') }}" placeholder="kamu@email.com" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3.5 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                </label>
                <div class="grid sm:grid-cols-2 gap-3.5">
                    <label class="grid gap-1.5">
                        <span class="text-[13px] font-bold">Kata sandi</span>
                        <input name="password" type="password" required placeholder="Min. 8 karakter" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3.5 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    </label>
                    <label class="grid gap-1.5">
                        <span class="text-[13px] font-bold">Konfirmasi sandi</span>
                        <input name="password_confirmation" type="password" required placeholder="Ulangi sandi" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3.5 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    </label>
                </div>
                <div>
                    <p class="text-[13px] font-bold mb-2">Daftar sebagai</p>
                    <div class="grid grid-cols-2 gap-2.5">
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="pembeli" checked class="peer sr-only">
                            <span class="block rounded-2xl border-2 border-ink-900/10 bg-white p-4 text-center peer-checked:border-brand-500 peer-checked:bg-brand-50 transition">
                                <span class="text-2xl">🛍️</span>
                                <strong class="block text-sm font-extrabold mt-1">Pembeli</strong>
                                <span class="block text-[11px] font-semibold text-ink-500">Jajan & belanja</span>
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="penjual" class="peer sr-only">
                            <span class="block rounded-2xl border-2 border-ink-900/10 bg-white p-4 text-center peer-checked:border-leaf-500 peer-checked:bg-leaf-50 transition">
                                <span class="text-2xl">🏪</span>
                                <strong class="block text-sm font-extrabold mt-1">Penjual</strong>
                                <span class="block text-[11px] font-semibold text-ink-500">Buka warung</span>
                            </span>
                        </label>
                    </div>
                </div>
                <button class="w-full py-4 rounded-2xl bg-brand-500 text-white font-extrabold text-[15px] hover:bg-brand-600 active:scale-[.99] transition shadow-xl shadow-brand-500/30">Daftar Gratis →</button>
                <p class="text-[11px] text-center text-ink-500 font-medium leading-relaxed">Dengan mendaftar, kamu setuju dengan <a href="{{ route('terms') }}" class="underline font-bold">Syarat & Ketentuan</a> dan <a href="{{ route('privacy') }}" class="underline font-bold">Kebijakan Privasi</a> Warung Hebat.</p>
            </form>
            <p class="text-center mt-6"><a href="{{ route('home') }}" class="text-[13px] font-bold text-ink-500 hover:text-ink-900">← Kembali ke beranda</a></p>
        </div>
    </div>
</section>
@endsection
