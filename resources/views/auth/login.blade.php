@extends('layouts.app')

@section('title', 'Masuk — Warung Hebat')

@section('content')
<section class="min-h-screen grid lg:grid-cols-2 pt-[72px]">
    {{-- Left: brand panel (hidden on small, visual on desktop) --}}
    <div class="hidden lg:flex relative bg-ink-900 text-white overflow-hidden flex-col justify-between p-12 grain">
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-brand-500/30 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-0 -left-24 w-80 h-80 bg-leaf-500/20 blur-[100px] rounded-full"></div>
        <a href="{{ route('home') }}" class="relative flex items-center gap-2.5">
            <span class="w-10 h-10 rounded-2xl bg-brand-500 grid place-items-center -rotate-3"><span class="font-black text-xl text-white">W</span></span>
            <span class="font-extrabold text-lg">Warung Hebat</span>
        </a>
        <div class="relative">
            <p class="inline-flex text-[11px] font-extrabold tracking-[0.2em] text-brand-300 bg-white/10 border border-white/15 rounded-full px-4 py-1.5">👋 SELAMAT DATANG KEMBALI</p>
            <h1 class="font-black tracking-tight text-5xl leading-[1.0] mt-4">Warung langgananmu <span class="text-brand-400">kangen.</span></h1>
            <p class="text-white/60 font-medium mt-3 max-w-sm">Masuk dan lanjutkan jajan — keranjangmu, poinmu, dan warung favoritmu masih tersimpan rapi.</p>
            <div class="mt-8 grid gap-2.5 max-w-sm">
                <div class="flex items-center gap-3 bg-white/[.07] border border-white/10 rounded-2xl p-3.5">
                    <span class="w-10 h-10 rounded-xl bg-brand-500 grid place-items-center">🛵</span>
                    <p class="text-sm font-bold">Pesanan terakhir: Nasi Goreng Tek-Tek <span class="text-white/50">• pesan lagi 1 klik</span></p>
                </div>
                <div class="flex items-center gap-3 bg-white/[.07] border border-white/10 rounded-2xl p-3.5">
                    <span class="w-10 h-10 rounded-xl bg-leaf-500 grid place-items-center">🎁</span>
                    <p class="text-sm font-bold">240 poin Hebat <span class="text-white/50">= Rp 24rb voucher</span></p>
                </div>
            </div>
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
            <h2 class="font-black tracking-tight text-3xl sm:text-4xl">Masuk dulu yuk 👋</h2>
            <p class="text-ink-500 font-medium text-[15px] mt-1.5">Belum punya akun? <a href="{{ route('register') }}" class="text-brand-600 font-extrabold">Daftar gratis</a></p>

            @if ($errors->any())
                <div class="mt-5 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-[13px] font-bold p-4">{{ $errors->first() }}</div>
            @endif
            @if (session('success'))
                <div class="mt-5 rounded-2xl bg-leaf-50 border border-leaf-500/30 text-leaf-700 text-[13px] font-bold p-4">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 grid gap-3.5">
                @csrf
                <label class="grid gap-1.5">
                    <span class="text-[13px] font-bold">Email</span>
                    <input name="email" type="email" required autofocus value="{{ old('email') }}" placeholder="kamu@email.com" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3.5 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                </label>
                <label class="grid gap-1.5">
                    <span class="text-[13px] font-bold">Kata sandi</span>
                    <span class="relative block">
                        <input id="login-password" name="password" type="password" required placeholder="••••••••" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3.5 pr-12 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                        <button type="button" data-toggle-password="#login-password" class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 grid place-items-center rounded-xl hover:bg-ink-900/5 text-ink-500" aria-label="Lihat sandi">👁</button>
                    </span>
                </label>
                <label class="flex items-center justify-between text-[13px] font-bold text-ink-700">
                    <span class="inline-flex items-center gap-2"><input type="checkbox" name="remember" class="w-4 h-4 accent-[#F95D0B]"> Ingat saya</span>
                    <a href="#" class="text-brand-600">Lupa kata sandi?</a>
                </label>
                <button class="w-full py-4 rounded-2xl bg-ink-900 text-white font-extrabold text-[15px] hover:bg-brand-600 active:scale-[.99] transition shadow-xl shadow-ink-900/20">Masuk →</button>
                <div class="flex items-center gap-3 text-[11px] font-extrabold text-ink-500 tracking-widest"><span class="flex-1 h-px bg-ink-900/10"></span>ATAU<span class="flex-1 h-px bg-ink-900/10"></span></div>
                <div class="grid grid-cols-2 gap-2.5">
                    <button type="button" class="py-3 rounded-2xl bg-white border-2 border-ink-900/10 font-extrabold text-sm hover:border-ink-900 transition">G Google</button>
                    <button type="button" class="py-3 rounded-2xl bg-white border-2 border-ink-900/10 font-extrabold text-sm hover:border-ink-900 transition">◉ WhatsApp</button>
                </div>
            </form>
            <p class="text-center mt-6"><a href="{{ route('home') }}" class="text-[13px] font-bold text-ink-500 hover:text-ink-900">← Kembali ke beranda</a></p>
        </div>
    </div>
</section>
@endsection
