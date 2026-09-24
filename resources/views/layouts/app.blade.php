<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1A130D">
    <meta name="color-scheme" content="light">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Warung Hebat — Belanja Dekat, Hidup Hebat')</title>
    @include('components.seo')
    {{-- Fonts + CSS/JS are self-hosted via Vite (hashed, immutable).
         Module scripts are deferred by default; font CSS uses font-display: swap. --}}
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='24' fill='%231A130D'/><text x='50' y='68' font-size='52' text-anchor='middle' fill='%23FF7A29' font-weight='900'>W</text></svg>">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon-64.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Warung Hebat">
    <style>
        #navbar { transition: all .35s cubic-bezier(.22,1,.36,1); }
        #navbar.is-scrolled { background: rgba(255,249,239,.88); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); box-shadow: 0 8px 32px -12px rgba(26,19,13,.18); }
        #navbar.is-scrolled .nav-inner { padding-top: .65rem; padding-bottom: .65rem; }
        .nav-inner { transition: padding .35s cubic-bezier(.22,1,.36,1); }
        [data-faq-panel] { max-height: 0; overflow: hidden; transition: max-height .4s cubic-bezier(.22,1,.36,1); }
        .modal-backdrop { opacity: 0; transition: opacity .2s ease; }
        .modal-card { transform: translateY(16px) scale(.98); opacity: 0; transition: all .25s cubic-bezier(.22,1,.36,1); }
        .modal-open.modal-backdrop, .modal-open .modal-backdrop { opacity: 1; }
        .modal-open .modal-card { transform: translateY(0) scale(1); opacity: 1; }
        #hero-location { transition: opacity .25s ease; }
        #mobile-menu { animation: slideDown .3s cubic-bezier(.22,1,.36,1); }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-12px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    @stack('head')
</head>
<body class="antialiased">

    @hasSection('bare')
    @else
    {{-- ===== NAVBAR ===== --}}
    <header id="navbar" class="fixed top-0 inset-x-0 z-50">
        @php($onHome = request()->routeIs('home'))
        <div class="nav-inner max-w-7xl mx-auto flex items-center justify-between gap-2 px-4 sm:px-6 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0" aria-label="Warung Hebat">
                <span class="w-10 h-10 rounded-2xl bg-ink-900 grid place-items-center shadow-lg shadow-ink-900/20 -rotate-3">
                    <span class="text-brand-400 font-black text-xl leading-none translate-y-[-1px]">W</span>
                </span>
                <span class="leading-none">
                    <span class="block font-extrabold tracking-tight text-[17px]">Warung Hebat</span>
                    <span class="block text-[11px] font-semibold text-leaf-600 tracking-wide">BELANJA DEKAT • HIDUP HEBAT</span>
                </span>
            </a>

            <nav class="hidden min-w-0 flex-1 items-center justify-center gap-0.5 text-[13px] font-semibold text-ink-700 whitespace-nowrap xl:flex 2xl:gap-1 2xl:text-sm" aria-label="Navigasi utama">
                <a href="{{ $onHome ? '#kategori' : route('home').'#kategori' }}" class="px-3 py-2 rounded-full hover:bg-ink-900/5 transition">Kategori</a>
                <a href="{{ $onHome ? '#warung' : route('home').'#warung' }}" class="px-3 py-2 rounded-full hover:bg-ink-900/5 transition">Warung Terdekat</a>
                <a href="{{ route('promo.index') }}" class="px-3 py-2 rounded-full hover:bg-ink-900/5 transition">Promo</a>
                <a href="{{ route('articles.index') }}" class="px-3 py-2 rounded-full hover:bg-ink-900/5 transition">Artikel</a>
                <a href="{{ route('guides.index') }}" class="px-3 py-2 rounded-full hover:bg-ink-900/5 transition">Panduan</a>
                <a href="{{ $onHome ? '#faq' : route('home').'#faq' }}" class="px-3 py-2 rounded-full hover:bg-ink-900/5 transition">FAQ</a>
            </nav>

            <div class="flex shrink-0 items-center gap-2">
                @auth
                    @php($cartCount = (int) collect(session('cart.items', []))->sum('qty'))
                    <a href="{{ route('cart.index') }}" class="hidden sm:inline-flex items-center gap-1.5 text-sm font-bold px-4 py-2.5 rounded-full hover:bg-ink-900/5 transition">
                        🛒 Keranjang
                        @if($cartCount > 0)
                            <span class="grid place-items-center min-w-5 h-5 px-1 rounded-full bg-brand-500 text-white text-[11px] font-black">{{ $cartCount }}</span>
                        @endif
                    </a>
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="hidden sm:inline-flex items-center gap-2 text-sm font-bold bg-ink-900 text-white pl-1.5 pr-4 py-1.5 rounded-full hover:bg-black transition whitespace-nowrap">
                        <span class="w-8 h-8 rounded-full bg-brand-500 grid place-items-center text-white text-sm font-extrabold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        {{ Str::limit(auth()->user()->name, 12) }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button class="text-sm font-bold px-4 py-2.5 rounded-full border border-ink-900/15 hover:bg-ink-900 hover:text-white transition">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" data-open-login class="hidden sm:inline-flex text-sm font-bold px-5 py-2.5 rounded-full hover:bg-ink-900/5 transition">Masuk</a>
                    <a href="{{ route('register') }}" data-open-register class="hidden sm:inline-flex text-sm font-bold px-5 py-2.5 rounded-full bg-ink-900 text-white hover:bg-brand-600 transition shadow-lg shadow-ink-900/20">Daftar Gratis</a>
                    <a href="{{ route('register') }}" data-open-register class="sm:hidden inline-flex text-[13px] font-bold px-4 py-2 rounded-full bg-ink-900 text-white">Daftar</a>
                @endauth
                <button id="menu-btn" class="xl:hidden w-10 h-10 grid place-items-center rounded-full border border-ink-900/15 bg-white" aria-label="Menu">
                    <svg id="menu-icon-open" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h10"/></svg>
                    <svg id="menu-icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="hidden xl:hidden mx-4 mb-4 rounded-3xl bg-ink-900 text-white p-3 shadow-2xl">
            <nav class="grid text-[15px] font-bold">
                <a href="{{ $onHome ? '#kategori' : route('home').'#kategori' }}" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="grid" class="w-5 h-5 text-brand-300" /> Kategori</a>
                <a href="{{ $onHome ? '#cara-kerja' : route('home').'#cara-kerja' }}" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="truck" class="w-5 h-5 text-brand-300" /> Cara Kerja</a>
                <a href="{{ $onHome ? '#warung' : route('home').'#warung' }}" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="map-pin" class="w-5 h-5 text-brand-300" /> Warung Terdekat</a>
                <a href="{{ $onHome ? '#cerita' : route('home').'#cerita' }}" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="heart-solid" class="w-5 h-5 text-brand-300" /> Cerita Kami</a>
                <a href="{{ $onHome ? '#mitra' : route('home').'#mitra' }}" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="store" class="w-5 h-5 text-brand-300" /> Jadi Mitra</a>
                <a href="{{ route('articles.index') }}" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="document" class="w-5 h-5 text-brand-300" /> Artikel</a>
                <a href="{{ route('guides.index') }}" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="question" class="w-5 h-5 text-brand-300" /> Panduan</a>
                <a href="{{ route('promo.index') }}" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="star-solid" class="w-5 h-5 text-brand-300" /> Promo</a>
                <a href="{{ $onHome ? '#faq' : route('home').'#faq' }}" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="question" class="w-5 h-5 text-brand-300" /> FAQ</a>
            </nav>
            @guest
            <div class="grid grid-cols-2 gap-2 p-2">
                <a href="{{ route('login') }}" class="text-center py-3 rounded-2xl bg-white/10 font-bold text-sm">Masuk</a>
                <a href="{{ route('register') }}" class="text-center py-3 rounded-2xl bg-brand-500 font-bold text-sm">Daftar Gratis</a>
            </div>
            @else
            <div class="p-2 grid gap-2">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('cart.index') }}" class="text-center py-3 rounded-2xl bg-white/10 font-bold text-sm">🛒 Keranjang</a>
                    <a href="{{ route('orders.index') }}" class="text-center py-3 rounded-2xl bg-white/10 font-bold text-sm">🧾 Pesanan</a>
                </div>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-center py-3 rounded-2xl bg-brand-500 font-bold text-sm">🛠️ Buka Backoffice</a>
                @else
                    <a href="{{ route('dashboard') }}" class="text-center py-3 rounded-2xl bg-brand-500 font-bold text-sm">Buka Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full py-3 rounded-2xl bg-white/10 font-bold text-sm">Keluar</button>
                </form>
            </div>
            @endguest
        </div>
    </header>
    @endif

    <main>
        @yield('content')
    </main>

    @hasSection('bare')
    @else
    {{-- ===== FOOTER ===== --}}
    <footer class="bg-ink-900 text-white mt-0 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-14 pb-28 sm:pb-10">
            <div class="grid gap-10 lg:grid-cols-[1.3fr_.7fr_.7fr_.7fr]">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <span class="w-10 h-10 rounded-2xl bg-brand-500 grid place-items-center -rotate-3"><span class="font-black text-xl text-white">W</span></span>
                        <span class="font-extrabold text-lg">Warung Hebat</span>
                    </div>
                    <p class="text-white/60 text-sm leading-relaxed max-w-xs">Marketplace digital mobile-first untuk makanan, minuman & kebutuhan harian dari warung-warung hebat di sekitarmu.</p>
                    <div class="flex gap-2 mt-5">
                        @if(($socialLinks['instagram'] ?? '') !== '')
                            <a href="{{ $socialLinks['instagram'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-white/10 grid place-items-center hover:bg-brand-500 transition" aria-label="Instagram"><x-icon name="instagram" class="w-5 h-5" /></a>
                        @endif
                        @if(($socialLinks['tiktok'] ?? '') !== '')
                            <a href="{{ $socialLinks['tiktok'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-white/10 grid place-items-center hover:bg-brand-500 transition" aria-label="TikTok"><x-icon name="logo-tiktok" class="w-[18px] h-[18px]" /></a>
                        @endif
                        @if(($socialLinks['x'] ?? '') !== '')
                            <a href="{{ $socialLinks['x'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-white/10 grid place-items-center hover:bg-brand-500 transition" aria-label="X"><x-icon name="logo-x" class="w-4 h-4" /></a>
                        @endif
                        <a href="{{ $socialLinks['whatsapp'] ?? 'https://wa.me/6281234567890' }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-white/10 grid place-items-center hover:bg-brand-500 transition" aria-label="WhatsApp"><x-icon name="phone" class="w-5 h-5" /></a>
                        @if(($socialLinks['email'] ?? '') !== '')
                            <a href="mailto:{{ $socialLinks['email'] }}" class="w-10 h-10 rounded-full bg-white/10 grid place-items-center hover:bg-brand-500 transition" aria-label="Email"><x-icon name="mail" class="w-5 h-5" /></a>
                        @endif
                    </div>
                </div>
                <div>
                    <p class="font-extrabold text-sm tracking-widest text-white/40 mb-4">BELANJA</p>
                    <ul class="space-y-2.5 text-sm font-semibold text-white/80">
                        <li><a href="{{ route('promo.index') }}" class="hover:text-brand-300">⚡ Promo & Flash Sale</a></li>
                        <li><a href="{{ route('category.show', ['category' => 'makanan']) }}" class="hover:text-brand-300">Makanan</a></li>
                        <li><a href="{{ route('category.show', ['category' => 'minuman']) }}" class="hover:text-brand-300">Minuman</a></li>
                        <li><a href="{{ route('category.show', ['category' => 'sembako']) }}" class="hover:text-brand-300">Sembako</a></li>
                        <li><a href="{{ route('category.show', ['category' => 'harian']) }}" class="hover:text-brand-300">Kebutuhan Harian</a></li>
                        <li><a href="{{ route('store.index') }}" class="hover:text-brand-300">Semua warung terdekat</a></li>
                    </ul>
                </div>
                <div>
                    <p class="font-extrabold text-sm tracking-widest text-white/40 mb-4">MITRA</p>
                    <ul class="space-y-2.5 text-sm font-semibold text-white/80">
                        <li><a href="#mitra" class="hover:text-brand-300">Buka Warung</a></li>
                        <li><a href="#mitra" class="hover:text-brand-300">WarungKu App</a></li>
                        <li><a href="#" class="hover:text-brand-300">Biaya & Komisi</a></li>
                        <li><a href="#" class="hover:text-brand-300">Cerita Mitra</a></li>
                    </ul>
                </div>
                <div>
                    <p class="font-extrabold text-sm tracking-widest text-white/40 mb-4">BANTUAN</p>
                    <ul class="space-y-2.5 text-sm font-semibold text-white/80">
                        <li><a href="{{ route('guides.index') }}" class="hover:text-brand-300">Panduan</a></li>
                        <li><a href="{{ route('guides.buyer') }}" class="hover:text-brand-300">Panduan Pembeli</a></li>
                        <li><a href="{{ route('guides.seller') }}" class="hover:text-brand-300">Panduan Penjual</a></li>
                        <li><a href="{{ route('articles.index') }}" class="hover:text-brand-300">Artikel</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-brand-300">Tentang Kami</a></li>
                        <li><a href="#faq" class="hover:text-brand-300">FAQ</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-brand-300">Hubungi Kami</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-brand-300">Syarat & Ketentuan</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-brand-300">Kebijakan Privasi</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-white/40 font-semibold">
                <p>© 2026 Warung Hebat.</p>
                <p>Lebih Mudah dan Terjangkau</p>
            </div>
        </div>

        {{-- Mobile bottom nav --}}
        <nav class="sm:hidden fixed bottom-0 inset-x-0 z-40 safe-bottom">
            <div class="mx-3 mb-3 rounded-3xl bg-ink-900 border border-white/10 shadow-2xl grid grid-cols-4 text-[10px] font-bold text-white/70">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 py-3 text-brand-300"><x-icon name="home" class="w-5 h-5" />Beranda</a>
                <a href="{{ $onHome ? '#warung' : route('home').'#warung' }}" class="flex flex-col items-center gap-1 py-3"><x-icon name="map-pin" class="w-5 h-5" />Terdekat</a>
                <a href="{{ $onHome ? '#kategori' : route('home').'#kategori' }}" class="flex flex-col items-center gap-1 py-3"><x-icon name="grid" class="w-5 h-5" />Kategori</a>
                @auth
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 py-3"><x-icon name="user" class="w-5 h-5" />Akun</a>
                @else
                <a href="{{ route('login') }}" data-open-login class="flex flex-col items-center gap-1 py-3"><x-icon name="user" class="w-5 h-5" />Masuk</a>
                @endauth
            </div>
        </nav>
    </footer>
    @endif

    @guest
        @include('components.auth-modals')
    @endguest

    {{-- PWA install banner (Android/Chrome only — shown on beforeinstallprompt). --}}
    <div id="pwa-install" class="hidden fixed z-40 inset-x-3 bottom-20 sm:inset-x-auto sm:right-6 sm:bottom-6 sm:w-[380px] rounded-3xl bg-ink-900 text-white border border-white/10 shadow-2xl p-4">
        <div class="flex items-center gap-3">
            <img src="{{ asset('icons/icon-192.png') }}" alt="" width="44" height="44" class="w-11 h-11 rounded-2xl shrink-0">
            <div class="flex-1 min-w-0">
                <p class="text-[14px] font-extrabold">Pasang Warung Hebat</p>
                <p class="text-[12px] font-semibold text-white/60">Buka kayak aplikasi, tetap ringan.</p>
            </div>
            <button id="pwa-install-dismiss" class="shrink-0 text-white/50 hover:text-white font-black px-2" aria-label="Tutup">✕</button>
        </div>
        <button id="pwa-install-btn" class="mt-3 w-full py-3 rounded-2xl bg-brand-500 text-sm font-extrabold hover:bg-brand-600 transition">Pasang Sekarang</button>
    </div>

    @include('components.toasts')
    @include('components.push-notifications')

    @stack('scripts')
</body>
</html>
