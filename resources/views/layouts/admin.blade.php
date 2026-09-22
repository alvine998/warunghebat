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
</head>
<body class="antialiased bg-cream-50">
<div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
    {{-- Sidebar --}}
    <aside class="bg-ink-900 text-white lg:min-h-screen lg:sticky lg:top-0 flex lg:flex-col">
        <div class="flex items-center justify-between lg:justify-start gap-2.5 px-4 sm:px-6 lg:p-6 py-4 w-full">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-2xl bg-brand-500 grid place-items-center -rotate-3"><span class="font-black text-xl text-white">W</span></span>
                <span class="leading-none">
                    <span class="block font-extrabold">Warung Hebat</span>
                    <span class="block text-[10px] font-bold tracking-[0.2em] text-brand-300">BACKOFFICE</span>
                </span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                @csrf
                <button class="text-xs font-bold bg-white/10 rounded-full px-4 py-2">Keluar</button>
            </form>
        </div>
        <nav class="hidden lg:grid gap-1 px-4 text-sm font-bold">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-3 rounded-2xl {{ request()->routeIs('admin.dashboard') ? 'bg-white text-ink-900' : 'hover:bg-white/10 text-white/80' }}">📊 Ringkasan</a>
            <a href="{{ route('admin.users') }}" class="px-4 py-3 rounded-2xl {{ request()->routeIs('admin.users') ? 'bg-white text-ink-900' : 'hover:bg-white/10 text-white/80' }}">👥 Pengguna</a>
            <a href="{{ route('admin.warungs') }}" class="px-4 py-3 rounded-2xl {{ request()->routeIs('admin.warungs') ? 'bg-white text-ink-900' : 'hover:bg-white/10 text-white/80' }}">🏪 Warung</a>
            <a href="{{ route('home') }}" class="px-4 py-3 rounded-2xl hover:bg-white/10 text-white/80">🌐 Lihat Situs</a>
        </nav>
        <div class="hidden lg:block mt-auto p-4">
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

    {{-- Mobile top tabs --}}
    <div class="lg:hidden bg-ink-900 text-white px-4 pb-4 -mt-1">
        <nav class="grid grid-cols-3 gap-1.5 text-[13px] font-extrabold">
            <a href="{{ route('admin.dashboard') }}" class="text-center py-2.5 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-white text-ink-900' : 'bg-white/10' }}">📊 Ringkasan</a>
            <a href="{{ route('admin.users') }}" class="text-center py-2.5 rounded-xl {{ request()->routeIs('admin.users') ? 'bg-white text-ink-900' : 'bg-white/10' }}">👥 Pengguna</a>
            <a href="{{ route('admin.warungs') }}" class="text-center py-2.5 rounded-xl {{ request()->routeIs('admin.warungs') ? 'bg-white text-ink-900' : 'bg-white/10' }}">🏪 Warung</a>
        </nav>
    </div>

    <main class="p-4 sm:p-8 max-w-6xl w-full">
        @yield('content')
    </main>
</div>
</body>
</html>
