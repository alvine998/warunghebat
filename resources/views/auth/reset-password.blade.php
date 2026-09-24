@extends('layouts.app')

@section('title', 'Buat Kata Sandi Baru — Warung Hebat')

@section('content')
<section class="min-h-screen grid lg:grid-cols-2 pt-[72px]">
    {{-- Left: brand panel (hidden on small, visual on desktop) --}}
    <div class="hidden lg:flex relative bg-leaf-700 text-white overflow-hidden flex-col justify-between p-12 grain">
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-brand-400/30 blur-[120px] rounded-full"></div>
        <a href="{{ route('home') }}" class="relative flex items-center gap-2.5">
            <span class="w-10 h-10 rounded-2xl bg-white grid place-items-center -rotate-3"><span class="font-black text-xl text-leaf-700">W</span></span>
            <span class="font-extrabold text-lg">Warung Hebat</span>
        </a>
        <div class="relative">
            <p class="inline-flex text-[11px] font-extrabold tracking-[0.2em] bg-white/15 border border-white/20 rounded-full px-4 py-1.5">✨ HAMPIR SELESAI</p>
            <h1 class="font-black tracking-tight text-5xl leading-[1.0] mt-4">Bikin sandi baru<br>yang <span class="text-brand-200">gampang diingat.</span></h1>
            <p class="text-white/70 font-medium mt-3 max-w-sm">Minimal 8 karakter. Jangan pakai tanggal lahir atau "password123" ya.</p>
        </div>
        <p class="relative text-xs font-semibold text-white/40">© 2026 Warung Hebat • Belanja Dekat, Hidup Hebat</p>
    </div>

    {{-- Right: form --}}
    <div class="flex items-start sm:items-center justify-center px-4 sm:px-8 py-10 bg-cream-50">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="lg:hidden flex items-center gap-2 mb-6">
                <span class="w-9 h-9 rounded-xl bg-ink-900 grid place-items-center -rotate-3"><span class="text-brand-400 font-black">W</span></span>
                <span class="font-extrabold">Warung Hebat</span>
            </a>
            <h2 class="font-black tracking-tight text-3xl sm:text-4xl">Sandi baru ✨</h2>
            <p class="text-ink-500 font-medium text-[15px] mt-1.5">Untuk <strong class="text-ink-900">{{ $email }}</strong></p>

            <form method="POST" action="{{ route('password.update') }}" class="mt-6 grid gap-3.5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <label class="grid gap-1.5">
                    <span class="text-[13px] font-bold">Kata sandi baru</span>
                    <span class="relative block">
                        <input id="reset-password" name="password" type="password" required autofocus placeholder="Min. 8 karakter" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3.5 pr-12 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                        <button type="button" data-toggle-password="#reset-password" class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 grid place-items-center rounded-xl hover:bg-ink-900/5 text-ink-500" aria-label="Lihat sandi">👁</button>
                    </span>
                </label>
                @error('password')
                    <p class="text-[13px] font-bold text-red-600 -mt-1.5">{{ $message }}</p>
                @enderror
                <label class="grid gap-1.5">
                    <span class="text-[13px] font-bold">Konfirmasi sandi baru</span>
                    <input name="password_confirmation" type="password" required placeholder="Ulangi sandi baru" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3.5 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                </label>
                @error('email')
                    <p class="text-[13px] font-bold text-red-600">{{ $message }}</p>
                @enderror
                <button class="w-full py-4 rounded-2xl bg-brand-500 text-white font-extrabold text-[15px] hover:bg-brand-600 active:scale-[.99] transition shadow-xl shadow-brand-500/30">Ubah Kata Sandi →</button>
            </form>
            <p class="text-center mt-6 text-[13px] font-semibold text-ink-500">Tautan kedaluwarsa? <a href="{{ route('password.request') }}" class="text-brand-600 font-extrabold">Minta tautan baru</a></p>
        </div>
    </div>
</section>
@endsection
