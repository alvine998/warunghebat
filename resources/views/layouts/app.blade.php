<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="Warung Hebat — marketplace digital mobile-first. Belanja makanan, minuman & kebutuhan harian dari warung terdekat.">
    <meta name="theme-color" content="#1A130D">
    <meta name="color-scheme" content="light">
    <meta name="format-detection" content="telephone=no">
    <meta property="og:title" content="Warung Hebat — Belanja Dekat, Hidup Hebat">
    <meta property="og:description" content="Marketplace digital mobile-first. Makanan, minuman & kebutuhan harian dari warung terdekat.">
    <meta property="og:type" content="website">
    <title>@yield('title', 'Warung Hebat — Belanja Dekat, Hidup Hebat')</title>
    {{-- Fonts + CSS/JS are self-hosted via Vite (hashed, immutable).
         Module scripts are deferred by default; font CSS uses font-display: swap. --}}
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='24' fill='%231A130D'/><text x='50' y='68' font-size='52' text-anchor='middle' fill='%23FF7A29' font-weight='900'>W</text></svg>">
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
        <div class="nav-inner max-w-7xl mx-auto flex items-center justify-between gap-3 px-4 sm:px-6 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0" aria-label="Warung Hebat">
                <span class="w-10 h-10 rounded-2xl bg-ink-900 grid place-items-center shadow-lg shadow-ink-900/20 -rotate-3">
                    <span class="text-brand-400 font-black text-xl leading-none translate-y-[-1px]">W</span>
                </span>
                <span class="leading-none">
                    <span class="block font-extrabold tracking-tight text-[17px]">Warung Hebat</span>
                    <span class="block text-[11px] font-semibold text-leaf-600 tracking-wide">BELANJA DEKAT • HIDUP HEBAT</span>
                </span>
            </a>

            <nav class="hidden lg:flex items-center gap-1 text-[14px] font-semibold text-ink-700">
                <a href="#kategori" class="px-4 py-2 rounded-full hover:bg-ink-900/5 transition">Kategori</a>
                <a href="#cara-kerja" class="px-4 py-2 rounded-full hover:bg-ink-900/5 transition">Cara Kerja</a>
                <a href="#warung" class="px-4 py-2 rounded-full hover:bg-ink-900/5 transition">Warung Terdekat</a>
                <a href="#cerita" class="px-4 py-2 rounded-full hover:bg-ink-900/5 transition">Cerita Kami</a>
                <a href="#mitra" class="px-4 py-2 rounded-full hover:bg-ink-900/5 transition">Jadi Mitra</a>
                <a href="#faq" class="px-4 py-2 rounded-full hover:bg-ink-900/5 transition">FAQ</a>
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex text-sm font-extrabold px-4 py-2.5 rounded-full bg-brand-500 text-white hover:bg-brand-600 transition">🛠️ Backoffice</a>
                    @endif
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="hidden sm:inline-flex items-center gap-2 text-sm font-bold bg-ink-900 text-white pl-1.5 pr-4 py-1.5 rounded-full hover:bg-black transition">
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
                <button id="menu-btn" class="lg:hidden w-10 h-10 grid place-items-center rounded-full border border-ink-900/15 bg-white" aria-label="Menu">
                    <svg id="menu-icon-open" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h10"/></svg>
                    <svg id="menu-icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="hidden lg:hidden mx-4 mb-4 rounded-3xl bg-ink-900 text-white p-3 shadow-2xl">
            <nav class="grid text-[15px] font-bold">
                <a href="#kategori" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="grid" class="w-5 h-5 text-brand-300" /> Kategori</a>
                <a href="#cara-kerja" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="truck" class="w-5 h-5 text-brand-300" /> Cara Kerja</a>
                <a href="#warung" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="map-pin" class="w-5 h-5 text-brand-300" /> Warung Terdekat</a>
                <a href="#cerita" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="heart-solid" class="w-5 h-5 text-brand-300" /> Cerita Kami</a>
                <a href="#mitra" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="store" class="w-5 h-5 text-brand-300" /> Jadi Mitra</a>
                <a href="#faq" class="px-4 py-3 rounded-2xl hover:bg-white/10 flex items-center gap-2.5"><x-icon name="question" class="w-5 h-5 text-brand-300" /> FAQ</a>
            </nav>
            @guest
            <div class="grid grid-cols-2 gap-2 p-2">
                <a href="{{ route('login') }}" class="text-center py-3 rounded-2xl bg-white/10 font-bold text-sm">Masuk</a>
                <a href="{{ route('register') }}" class="text-center py-3 rounded-2xl bg-brand-500 font-bold text-sm">Daftar Gratis</a>
            </div>
            @else
            <div class="p-2 grid gap-2">
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
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 grid place-items-center hover:bg-brand-500 transition" aria-label="Instagram"><x-icon name="instagram" class="w-5 h-5" /></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 grid place-items-center hover:bg-brand-500 transition" aria-label="TikTok"><x-icon name="logo-tiktok" class="w-[18px] h-[18px]" /></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 grid place-items-center hover:bg-brand-500 transition" aria-label="X"><x-icon name="logo-x" class="w-4 h-4" /></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 grid place-items-center hover:bg-brand-500 transition" aria-label="WhatsApp"><x-icon name="phone" class="w-5 h-5" /></a>
                    </div>
                </div>
                <div>
                    <p class="font-extrabold text-sm tracking-widest text-white/40 mb-4">BELANJA</p>
                    <ul class="space-y-2.5 text-sm font-semibold text-white/80">
                        <li><a href="#kategori" class="hover:text-brand-300">Makanan</a></li>
                        <li><a href="#kategori" class="hover:text-brand-300">Minuman</a></li>
                        <li><a href="#kategori" class="hover:text-brand-300">Sembako</a></li>
                        <li><a href="#kategori" class="hover:text-brand-300">Kebutuhan Harian</a></li>
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
                <a href="#warung" class="flex flex-col items-center gap-1 py-3"><x-icon name="map-pin" class="w-5 h-5" />Terdekat</a>
                <a href="#kategori" class="flex flex-col items-center gap-1 py-3"><x-icon name="grid" class="w-5 h-5" />Kategori</a>
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

    @stack('scripts')
</body>
</html>
