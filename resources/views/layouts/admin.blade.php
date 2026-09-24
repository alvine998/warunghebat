<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1A130D">
    <title>@yield('title', 'Backoffice — Warung Hebat')</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='24' fill='%231A130D'/><text x='50' y='68' font-size='52' text-anchor='middle' fill='%23FF7A29' font-weight='900'>W</text></svg>">
    <style>
        /* Mobile nav drawer. Transforms only, so it stays smooth on cheap phones. */
        #admin-drawer { transition: transform .3s cubic-bezier(.22,1,.36,1); }
        #admin-drawer-backdrop { transition: opacity .3s ease; }
        @media (prefers-reduced-motion: reduce) {
            #admin-drawer, #admin-drawer-backdrop { transition: none; }
        }
    </style>
</head>
@php($adminSection = trim(\Str::before($__env->yieldContent('title', 'Backoffice'), ' — ')) ?: 'Backoffice')
<body class="antialiased bg-cream-50">
<div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">

    {{-- ===== MOBILE TOPBAR ===== --}}
    <header class="lg:hidden sticky top-0 z-40 bg-ink-900 text-white border-b border-white/10">
        <div class="flex items-center gap-3 px-4 py-3">
            <button id="admin-nav-btn" type="button" aria-controls="admin-drawer" aria-expanded="false" aria-label="Buka menu backoffice" class="w-11 h-11 shrink-0 grid place-items-center rounded-2xl bg-white/10 hover:bg-white/20 active:scale-95 transition">
                <x-icon name="menu" class="w-5 h-5" />
            </button>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 min-w-0 flex-1">
                <span class="w-9 h-9 shrink-0 rounded-xl bg-brand-500 grid place-items-center -rotate-3"><span class="font-black text-lg text-white">W</span></span>
                <span class="min-w-0 leading-tight">
                    <span class="block text-[10px] font-extrabold tracking-[0.2em] text-brand-300">BACKOFFICE</span>
                    <span class="block text-[13px] font-extrabold truncate">{{ $adminSection }}</span>
                </span>
            </a>
            <a href="{{ route('home') }}" class="w-11 h-11 shrink-0 grid place-items-center rounded-2xl bg-white/10 hover:bg-white/20 transition" aria-label="Lihat situs" title="Lihat situs">
                <x-icon name="globe" class="w-5 h-5" />
            </a>
        </div>
    </header>

    {{-- ===== DESKTOP SIDEBAR ===== --}}
    <aside class="hidden lg:flex lg:flex-col bg-ink-900 text-white lg:h-screen lg:overflow-y-auto lg:sticky lg:top-0">
        <div class="p-6">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-2xl bg-brand-500 grid place-items-center -rotate-3"><span class="font-black text-xl text-white">W</span></span>
                <span class="leading-none">
                    <span class="block font-extrabold">Warung Hebat</span>
                    <span class="block text-[10px] font-bold tracking-[0.2em] text-brand-300">BACKOFFICE</span>
                </span>
            </a>
        </div>
        <nav class="grid gap-1 px-4 text-[13px] font-bold">
            @include('components.admin-nav')
        </nav>
        <div class="mt-auto p-4">
            <div class="rounded-2xl bg-white/[.07] border border-white/10 p-4">
                <p class="text-sm font-extrabold">{{ auth()->user()->name }}</p>
                <p class="text-xs font-semibold text-white/50">{{ auth()->user()->email }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button class="w-full py-2.5 rounded-xl bg-brand-500 text-sm font-extrabold hover:bg-brand-600 transition">Keluar</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ===== MOBILE DRAWER ===== --}}
    <div id="admin-drawer-backdrop" class="hidden lg:hidden fixed inset-0 z-[70] bg-ink-900/60 backdrop-blur-sm opacity-0" aria-hidden="true"></div>
    <aside id="admin-drawer" class="lg:hidden fixed inset-y-0 left-0 z-[80] w-[17.5rem] max-w-[85vw] bg-ink-900 text-white flex flex-col -translate-x-full" aria-hidden="true" aria-label="Menu backoffice">
        <div class="flex items-center justify-between gap-2 p-4 border-b border-white/10">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 min-w-0">
                <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-500 grid place-items-center -rotate-3"><span class="font-black text-xl text-white">W</span></span>
                <span class="min-w-0 leading-none">
                    <span class="block font-extrabold truncate">Warung Hebat</span>
                    <span class="block text-[10px] font-bold tracking-[0.2em] text-brand-300">BACKOFFICE</span>
                </span>
            </a>
            <button id="admin-nav-close" type="button" aria-label="Tutup menu" class="w-10 h-10 shrink-0 grid place-items-center rounded-2xl bg-white/10 hover:bg-white/20 active:scale-95 transition">
                <x-icon name="x-mark" class="w-5 h-5" />
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-4 grid gap-1 text-[13px] font-bold">
            @include('components.admin-nav')
        </nav>

        <div class="p-4 border-t border-white/10">
            <p class="text-sm font-extrabold truncate">{{ auth()->user()->name }}</p>
            <p class="text-xs font-semibold text-white/50 truncate">{{ auth()->user()->email }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button class="w-full py-2.5 rounded-xl bg-brand-500 text-sm font-extrabold hover:bg-brand-600 transition">Keluar</button>
            </form>
        </div>
    </aside>

    <main class="p-4 sm:p-8 max-w-6xl w-full">
        @yield('content')
    </main>
</div>
@include('components.toasts')

<script>
(function () {
    var drawer = document.getElementById('admin-drawer');
    var backdrop = document.getElementById('admin-drawer-backdrop');
    var openBtn = document.getElementById('admin-nav-btn');
    var closeBtn = document.getElementById('admin-nav-close');
    if (!drawer || !backdrop || !openBtn) return;

    function isOpen() { return !drawer.classList.contains('-translate-x-full'); }

    function openDrawer() {
        drawer.classList.remove('-translate-x-full');
        drawer.setAttribute('aria-hidden', 'false');
        openBtn.setAttribute('aria-expanded', 'true');
        backdrop.classList.remove('hidden');
        requestAnimationFrame(function () { backdrop.classList.remove('opacity-0'); });
        document.body.classList.add('overflow-hidden');
    }

    function closeDrawer() {
        drawer.classList.add('-translate-x-full');
        drawer.setAttribute('aria-hidden', 'true');
        openBtn.setAttribute('aria-expanded', 'false');
        backdrop.classList.add('opacity-0');
        document.body.classList.remove('overflow-hidden');
        setTimeout(function () { if (!isOpen()) backdrop.classList.add('hidden'); }, 300);
    }

    openBtn.addEventListener('click', function () { isOpen() ? closeDrawer() : openDrawer(); });
    closeBtn?.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && isOpen()) closeDrawer(); });
    // The drawer is mobile-only; drop it when the viewport grows into the sidebar.
    window.addEventListener('resize', function () { if (window.innerWidth >= 1024 && isOpen()) closeDrawer(); });
})();
</script>
@stack('scripts')
</body>
</html>
