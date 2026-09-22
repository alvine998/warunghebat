@extends('layouts.app')

@section('title', 'Warung Hebat — Belanja Dekat, Hidup Hebat')

@section('content')
{{-- ================= HERO ================= --}}
<section class="relative overflow-hidden bg-cream-50 grain pt-24 sm:pt-28 pb-10 sm:pb-16">
    <div data-parallax="0.12" class="hidden sm:block absolute -top-24 -left-24 w-96 h-96 rounded-full bg-brand-200/60 blur-[100px] pointer-events-none" aria-hidden="true"></div>
    <div data-parallax="-0.08" class="hidden sm:block absolute top-40 -right-24 w-[28rem] h-[28rem] rounded-full bg-leaf-100 blur-[100px] pointer-events-none" aria-hidden="true"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 grid gap-10 lg:grid-cols-[1.05fr_.95fr] lg:items-center">
        <div class="relative z-10">
            <div class="reveal inline-flex items-center gap-2 rounded-full bg-white border border-ink-900/10 shadow-sm pl-1.5 pr-4 py-1.5 text-[12px] font-bold mb-5">
                <span class="inline-flex items-center gap-1 bg-leaf-500 text-white rounded-full px-2.5 py-1 text-[11px]"><span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> LIVE</span>
                <span class="text-ink-700">📍 <span id="hero-location">Tebet, Jaksel</span></span>
                <span class="text-ink-500 font-semibold hidden sm:inline">• 128 warung buka</span>
            </div>

            <h1 class="reveal font-black tracking-tight leading-[0.95] text-[44px] sm:text-6xl lg:text-[76px]" style="--reveal-delay:80ms">
                Belanja dekat,<br>
                <span class="relative inline-block text-brand-500">hidup hebat.
                    <svg class="absolute -bottom-2 left-0 w-full" height="12" viewBox="0 0 200 12" fill="none" preserveAspectRatio="none"><path d="M2 9C60 2 140 2 198 9" stroke="#0D7A3B" stroke-width="5" stroke-linecap="round"/></svg>
                </span>
            </h1>

            <p class="reveal mt-5 text-[15px] sm:text-lg text-ink-500 font-medium leading-relaxed max-w-md" style="--reveal-delay:160ms">
                Marketplace digital buat semua — makanan, minuman, sembako & kebutuhan harian dari <strong class="text-ink-900">warung terdekatmu</strong>. Pesan dari HP, ambil atau diantar cepat.
            </p>

            {{-- Search (mobile-first) --}}
            <div class="reveal mt-6 bg-white rounded-[22px] border border-ink-900/10 shadow-xl shadow-ink-900/10 p-2 flex items-center gap-2 max-w-md" style="--reveal-delay:240ms">
                <span class="pl-3 text-ink-500"><x-icon name="search" class="w-5 h-5" /></span>
                <input id="hero-search" type="text" placeholder="Cari nasi goreng, kopi susu, telur..." class="flex-1 min-w-0 bg-transparent outline-none text-[15px] font-semibold placeholder:text-ink-500/60 placeholder:font-medium py-2.5">
                <a href="#warung" class="shrink-0 bg-ink-900 text-white text-sm font-extrabold px-5 py-3 rounded-2xl hover:bg-brand-600 transition">Cari</a>
            </div>
            <p id="search-hint" class="reveal mt-2.5 text-[12px] font-semibold text-ink-500" style="--reveal-delay:280ms">Coba ketik "gorengan", "kopi", atau "sembako"...</p>

            <div class="reveal mt-5 flex flex-wrap items-center gap-2.5" style="--reveal-delay:320ms">
                @guest
                <a href="{{ route('register') }}" data-open-register class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-[15px] px-7 py-3.5 rounded-full shadow-xl shadow-brand-500/30 active:scale-[.98] transition">Mulai Belanja →</a>
                <a href="#mitra" class="inline-flex items-center gap-2 bg-white border-2 border-ink-900/10 hover:border-ink-900 font-extrabold text-[15px] px-7 py-3.5 rounded-full transition">🏪 Buka Warung</a>
                @else
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 bg-ink-900 text-white font-extrabold text-[15px] px-7 py-3.5 rounded-full shadow-xl active:scale-[.98] transition">Buka Dashboard →</a>
                <a href="#warung" class="inline-flex items-center gap-2 bg-white border-2 border-ink-900/10 font-extrabold text-[15px] px-7 py-3.5 rounded-full">Lihat Warung</a>
                @endguest
            </div>

            {{-- Stats --}}
            <dl class="reveal mt-8 grid grid-cols-3 max-w-md divide-x divide-ink-900/10" style="--reveal-delay:400ms">
                <div class="pr-4">
                    <dt class="sr-only">Warung mitra</dt>
                    <dd class="font-black text-xl sm:text-2xl" data-count="2450" data-suffix="+">0</dd>
                    <dd class="text-[11px] sm:text-xs font-bold text-ink-500 uppercase tracking-wide">Warung Mitra</dd>
                </div>
                <div class="px-4">
                    <dt class="sr-only">Pesanan</dt>
                    <dd class="font-black text-xl sm:text-2xl" data-count="180" data-suffix="rb+">0</dd>
                    <dd class="text-[11px] sm:text-xs font-bold text-ink-500 uppercase tracking-wide">Pesanan</dd>
                </div>
                <div class="pl-4">
                    <dt class="sr-only">Rating</dt>
                    <dd class="font-black text-xl sm:text-2xl" data-count="4.9" data-suffix="★">0</dd>
                    <dd class="text-[11px] sm:text-xs font-bold text-ink-500 uppercase tracking-wide">Rating App</dd>
                </div>
            </dl>
        </div>

        {{-- Phone mockup --}}
        <div class="reveal-scale relative mx-auto w-full max-w-[340px] sm:max-w-[380px] lg:max-w-[400px]" style="--reveal-delay:200ms">
            <div class="phone-tilt relative rounded-[36px] bg-ink-900 p-2.5 shadow-2xl shadow-ink-900/40">
                <div class="rounded-[28px] bg-cream-50 overflow-hidden">
                    <div class="bg-ink-900 text-white px-5 pt-4 pb-5 rounded-b-[24px]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-semibold text-white/60">📍 Antar ke</p>
                                <p class="text-[13px] font-extrabold">Jl. Tebet Raya No. 12 ⌄</p>
                            </div>
                            <span class="w-9 h-9 rounded-full bg-brand-500 grid place-items-center font-black">S</span>
                        </div>
                        <div class="mt-3 bg-white/10 rounded-2xl px-4 py-2.5 text-[12px] font-semibold text-white/70">Mau jajan apa hari ini?</div>
                    </div>
                    <div class="p-4 grid gap-3">
                        <div class="flex gap-2">
                            <span class="text-[11px] font-extrabold bg-ink-900 text-white px-3 py-1.5 rounded-full">Semua</span>
                            <span class="text-[11px] font-extrabold bg-white border border-ink-900/10 px-3 py-1.5 rounded-full">🍱 Makanan</span>
                            <span class="text-[11px] font-extrabold bg-white border border-ink-900/10 px-3 py-1.5 rounded-full">🥤 Minuman</span>
                        </div>
                        @foreach([['Nasi Goreng Tek-Tek', 'Warung Bang Jago • 200m', 'Rp 15rb', 'utensils'], ['Kopi Susu Gula Aren', 'Kopi Hebat • 350m', 'Rp 12rb', 'cup'], ['Paket Sembako Hemat', 'Sembako Bu RT • 500m', 'Rp 58rb', 'basket']] as $i => $p)
                        <div class="flex items-center gap-3 bg-white rounded-2xl border border-ink-900/10 p-2.5 shadow-sm">
                            <span class="w-12 h-12 rounded-xl grid place-items-center {{ $i===0?'bg-brand-100 text-brand-600':($i===1?'bg-leaf-100 text-leaf-600':'bg-cream-200 text-ink-700') }}"><x-icon name="{{ $p[3] }}" class="w-6 h-6" /></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-extrabold truncate">{{ $p[0] }}</p>
                                <p class="text-[11px] font-semibold text-ink-500">{{ $p[1] }}</p>
                                <p class="text-[13px] font-black text-brand-600">{{ $p[2] }}</p>
                            </div>
                            <span class="w-8 h-8 rounded-full bg-ink-900 text-white grid place-items-center font-black">+</span>
                        </div>
                        @endforeach
                        <div class="bg-leaf-600 text-white rounded-2xl p-3.5 flex items-center justify-between">
                            <div><p class="text-[11px] font-semibold text-white/70">3 item • 600m</p><p class="text-[14px] font-extrabold">Rp 85.000 • Antar 12 mnt</p></div>
                            <span class="bg-white text-leaf-700 text-[12px] font-extrabold px-4 py-2 rounded-xl">Bayar →</span>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Floating cards --}}
            <div class="animate-float absolute -left-6 sm:-left-12 top-16 bg-white rounded-2xl shadow-xl border border-ink-900/10 px-3.5 py-2.5 flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-leaf-100 text-leaf-600 grid place-items-center"><x-icon name="truck" class="w-5 h-5" /></span>
                <div><p class="text-[12px] font-extrabold">Abang OTW!</p><p class="text-[11px] font-semibold text-ink-500">2 menit lagi sampai</p></div>
            </div>
            <div class="animate-float-slow absolute -right-4 sm:-right-10 bottom-24 bg-white rounded-2xl shadow-xl border border-ink-900/10 px-3.5 py-2.5 flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-brand-100 text-brand-600 grid place-items-center"><x-icon name="star-solid" class="w-5 h-5" /></span>
                <div><p class="text-[12px] font-extrabold">4.9/5.0</p><p class="text-[11px] font-semibold text-ink-500">12rb ulasan</p></div>
            </div>
        </div>
    </div>

    {{-- Trusted by --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 mt-10">
        <p class="reveal text-center text-[11px] font-extrabold tracking-[0.2em] text-ink-500">DIPERCAYA 2.450+ WARUNG DI 34 KOTA</p>
        <div class="reveal mt-4 flex flex-wrap justify-center gap-2.5" style="--reveal-delay:100ms">
            @foreach(['Warung Bang Jago','Kopi Hebat','Sembako Bu RT','Dapur Nusa','Jajan Pasar Yu Ning','Ayam Geprek Mantul'] as $w)
            <span class="inline-flex items-center gap-1.5 bg-white border border-ink-900/10 rounded-full px-4 py-2 text-[12px] font-bold text-ink-700 shadow-sm">✓ {{ $w }}</span>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= MARQUEE ================= --}}
<div class="bg-ink-900 text-white py-3.5 overflow-hidden -rotate-[0.5deg] scale-[1.01]">
    <div class="flex whitespace-nowrap animate-marquee gap-0 w-max">
        @for($r=0;$r<2;$r++)
        <div class="flex items-center gap-8 pr-8 text-[13px] font-extrabold tracking-widest">
            <span>MAKANAN</span><span class="text-brand-400">✦</span>
            <span>MINUMAN</span><span class="text-brand-400">✦</span>
            <span>SEMBAKO</span><span class="text-brand-400">✦</span>
            <span>HARIAN</span><span class="text-brand-400">✦</span>
            <span>JAJANAN</span><span class="text-brand-400">✦</span>
            <span>FROZEN</span><span class="text-brand-400">✦</span>
            <span>TERDEKAT</span><span class="text-brand-400">✦</span>
            <span>CEPAT</span><span class="text-brand-400">✦</span>
        </div>
        @endfor
    </div>
</div>

{{-- ================= KATEGORI ================= --}}
<section id="kategori" class="max-w-7xl mx-auto px-4 sm:px-6 py-14 sm:py-20">
    <div class="flex items-end justify-between gap-4 mb-7">
        <div>
            <p class="reveal inline-flex items-center gap-1.5 text-[11px] font-extrabold tracking-[0.18em] text-brand-600 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">🛍️ KATEGORI</p>
            <h2 class="reveal font-black tracking-tight text-3xl sm:text-5xl mt-3" style="--reveal-delay:80ms">Mau apa hari ini?</h2>
            <p class="reveal text-ink-500 font-medium text-[15px] mt-2 max-w-md" style="--reveal-delay:140ms">Semua kebutuhan harian ada — dijual tetanggamu sendiri, dikirim dari gang sebelah.</p>
        </div>
        <a href="#warung" class="reveal hidden sm:inline-flex font-extrabold text-sm underline underline-offset-8 decoration-brand-500 decoration-2 hover:text-brand-600">Lihat semua →</a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @foreach([
            ['utensils','Makanan','1.2rb warung','bg-brand-500','Siap saji & masakan'],
            ['cup','Minuman','860 warung','bg-leaf-500','Kopi, jus & es'],
            ['basket','Sembako','640 warung','bg-ink-900','Beras, telur, minyak'],
            ['package','Harian','520 warung','bg-amber-500','Sabun & tisu'],
            ['cookie','Jajanan','980 warung','bg-pink-500','Pasar & kekinian'],
            ['snowflake','Frozen','310 warung','bg-sky-500','Nugget & dimsum'],
        ] as $i => $c)
        <a href="#warung" class="reveal group rounded-[24px] bg-white border border-ink-900/10 p-4 sm:p-5 hover:-translate-y-1.5 hover:shadow-xl hover:shadow-ink-900/10 transition-all duration-300" style="--reveal-delay:{{ $i*70 }}ms">
            <span class="w-12 h-12 rounded-2xl {{ $c[3] }} grid place-items-center text-white shadow-lg group-hover:scale-110 group-hover:-rotate-6 transition-transform"><x-icon name="{{ $c[0] }}" class="w-6 h-6" /></span>
            <p class="font-extrabold text-[15px] mt-3">{{ $c[1] }}</p>
            <p class="text-[12px] font-bold text-leaf-600">{{ $c[2] }}</p>
            <p class="text-[12px] font-medium text-ink-500 mt-0.5">{{ $c[4] }}</p>
        </a>
        @endforeach
    </div>
</section>

{{-- ================= CARA KERJA ================= --}}
<section id="cara-kerja" class="bg-ink-900 text-white relative overflow-hidden grain">
    <div class="hidden sm:block absolute -top-32 right-0 w-96 h-96 bg-brand-500/20 blur-[120px] rounded-full pointer-events-none" aria-hidden="true"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 sm:py-20 relative">
        <p class="reveal inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-300 bg-white/10 border border-white/15 rounded-full px-3.5 py-1.5">🛵 CARA KERJA</p>
        <h2 class="reveal font-black tracking-tight text-3xl sm:text-5xl mt-3 max-w-xl" style="--reveal-delay:80ms">Dari warung sebelah sampai ke tanganmu.</h2>
        <p class="reveal text-white/60 font-medium mt-2 max-w-md" style="--reveal-delay:140ms">Tanpa antre, tanpa jauh-jauh. Semua dari HP, dioptimasi buat layar kecil & sinyal pas-pasan.</p>

        <div class="mt-9 grid gap-3 sm:grid-cols-3">
            @foreach([
                ['01','map-pin','Lokasi otomatis','Nyalakan GPS — kami tampilkan warung buka dalam radius 500m–2km, urut dari yang paling dekat.'],
                ['02','basket','Pilih & bayar','Keranjang sat-set, bayar via QRIS, e-wallet, COD atau transfer. Struk digital langsung masuk.'],
                ['03','truck','Antar / ambil','Diantar abang dalam 10–20 menit, atau ambil sendiri biar lebih hemat + dapat poin.'],
            ] as $i => $s)
            <div class="reveal rounded-[24px] bg-white/[.06] border border-white/10 p-6 hover:bg-white/[.1] hover:-translate-y-1 transition-all duration-300" style="--reveal-delay:{{ $i*100 }}ms">
                <div class="flex items-center justify-between">
                    <span class="w-12 h-12 rounded-2xl bg-brand-500 grid place-items-center text-white shadow-lg shadow-brand-500/30"><x-icon name="{{ $s[1] }}" class="w-6 h-6" /></span>
                    <span class="font-black text-4xl text-white/10">{{ $s[0] }}</span>
                </div>
                <p class="font-extrabold text-lg mt-4">{{ $s[2] }}</p>
                <p class="text-sm text-white/60 font-medium leading-relaxed mt-1.5">{{ $s[3] }}</p>
            </div>
            @endforeach
        </div>

        <div class="reveal mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 rounded-[24px] bg-leaf-600 p-5 sm:p-6" style="--reveal-delay:200ms">
            <span class="w-12 h-12 shrink-0 rounded-2xl bg-white/20 grid place-items-center text-2xl">⚡</span>
            <p class="flex-1 text-[15px] font-bold leading-snug">Rata-rata pesanan sampai dalam <strong>14 menit</strong> — karena jaraknya dekat, ongkirnya pun ringan mulai Rp 2rb.</p>
            @guest
            <a href="{{ route('register') }}" data-open-register class="text-center bg-white text-leaf-700 font-extrabold text-sm px-6 py-3.5 rounded-2xl hover:bg-ink-900 hover:text-white transition">Coba Sekarang</a>
            @else
            <a href="{{ route('dashboard') }}" class="text-center bg-white text-leaf-700 font-extrabold text-sm px-6 py-3.5 rounded-2xl hover:bg-ink-900 hover:text-white transition">Pesan Lagi</a>
            @endguest
        </div>
    </div>
</section>

{{-- ================= WARUNG TERDEKAT ================= --}}
<section id="warung" class="max-w-7xl mx-auto px-4 sm:px-6 py-14 sm:py-20">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-7">
        <div>
            <p class="reveal inline-flex text-[11px] font-extrabold tracking-[0.18em] text-leaf-700 bg-leaf-100 border border-leaf-500/20 rounded-full px-3.5 py-1.5">📍 NEARBY-FIRST</p>
            <h2 class="reveal font-black tracking-tight text-3xl sm:text-5xl mt-3" style="--reveal-delay:80ms">Warung terdekatmu</h2>
            <p class="reveal text-ink-500 font-medium text-[15px] mt-2" style="--reveal-delay:140ms">Jarak asli, ulasan asli, rasa tetangga.</p>
        </div>
        <div class="reveal flex items-center gap-2 text-[13px] font-bold" style="--reveal-delay:200ms">
            <span class="inline-flex items-center gap-1.5 bg-ink-900 text-white rounded-full pl-3 pr-4 py-2">◎ Radius 1 km <span class="text-brand-300">⌄</span></span>
            <span class="inline-flex items-center gap-1.5 bg-white border border-ink-900/15 rounded-full px-4 py-2">⭐ 4.5+</span>
        </div>
    </div>

    <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['Warung Bang Jago','Nasi goreng • Ayam geprek','200m • 8 mnt','4.9','🍳','Buka','Makanan'],
            ['Kopi Hebat Tebet','Kopi susu • Matcha • Roti','350m • 10 mnt','4.8','🧋','Buka','Minuman'],
            ['Sembako Bu RT','Beras • Telur • Minyak','500m • 12 mnt','4.9','🧺','Buka','Sembako'],
            ['Jajan Pasar Yu Ning','Klepon • Lemper • Pastel','650m • 14 mnt','4.7','🍩','Buka','Jajanan'],
            ['Dapur Nusa Frozen','Dimsum • Nugget • Kebab','800m • 15 mnt','4.8','❄️','Ramai','Frozen'],
            ['Toko Harian Berkah','Sabun • Tisu • Detergen','900m • 16 mnt','4.6','🧼','Buka','Harian'],
        ] as $i => $w)
        <article data-store-name="{{ $w[0] }}" data-store-cat="{{ $w[6] }} {{ $w[1] }}" class="reveal group rounded-[24px] bg-white border border-ink-900/10 overflow-hidden hover:shadow-xl hover:shadow-ink-900/10 hover:-translate-y-1 transition-all duration-300" style="--reveal-delay:{{ ($i%3)*90 }}ms">
            <div class="h-28 bg-gradient-to-br {{ $i%3==0?'from-brand-100 via-cream-100 to-brand-200':($i%3==1?'from-leaf-100 via-cream-100 to-leaf-100':'from-cream-200 via-cream-100 to-brand-100') }} relative flex items-center justify-center">
                <span class="text-5xl group-hover:scale-125 group-hover:-rotate-6 transition-transform duration-300">{{ $w[4] }}</span>
                <span class="absolute top-3 left-3 inline-flex items-center gap-1 text-[11px] font-extrabold bg-white/90 backdrop-blur rounded-full px-3 py-1.5">⭐ {{ $w[3] }}</span>
                <span class="absolute top-3 right-3 inline-flex items-center gap-1.5 text-[11px] font-extrabold {{ $w[5]=='Buka'?'bg-leaf-500 text-white':'bg-amber-400 text-ink-900' }} rounded-full px-3 py-1.5"><span class="w-1.5 h-1.5 rounded-full {{ $w[5]=='Buka'?'bg-white animate-pulse':'bg-ink-900' }}"></span>{{ $w[5] }}</span>
            </div>
            <div class="p-4">
                <p class="font-extrabold text-[16px]">{{ $w[0] }}</p>
                <p class="text-[13px] font-medium text-ink-500">{{ $w[1] }}</p>
                <div class="flex items-center justify-between mt-3">
                    <span class="text-[12px] font-bold text-ink-700 bg-cream-100 rounded-full px-3 py-1.5">📍 {{ $w[2] }}</span>
                    @guest
                    <button data-open-register class="text-[13px] font-extrabold bg-ink-900 text-white px-4 py-2 rounded-full group-hover:bg-brand-500 transition">+ Keranjang</button>
                    @else
                    <a href="{{ route('dashboard') }}" class="text-[13px] font-extrabold bg-ink-900 text-white px-4 py-2 rounded-full group-hover:bg-brand-500 transition">+ Keranjang</a>
                    @endguest
                </div>
            </div>
        </article>
        @endforeach
    </div>

    <p class="reveal text-center mt-7">
        <a href="#kategori" class="inline-flex items-center gap-2 font-extrabold text-sm border-2 border-ink-900/10 hover:border-ink-900 rounded-full px-7 py-3.5 transition">Lihat 128 warung lainnya →</a>
    </p>
</section>

{{-- ================= KEUNGGULAN ================= --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 pb-14 sm:pb-20">
    <div class="reveal-scale rounded-[32px] bg-cream-200/60 border border-ink-900/10 p-6 sm:p-10 grid gap-8 lg:grid-cols-2 lg:items-center overflow-hidden relative">
        <div class="hidden sm:block absolute -right-20 -top-20 w-72 h-72 bg-brand-300/40 blur-[90px] rounded-full pointer-events-none" aria-hidden="true"></div>
        <div>
            <p class="inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-700 bg-white border border-brand-200 rounded-full px-3.5 py-1.5">💡 KENAPA WARUNG HEBAT</p>
            <h2 class="font-black tracking-tight text-3xl sm:text-4xl mt-3 leading-tight">Didesain mobile-first untuk jajan sat-set.</h2>
            <ul class="mt-6 grid gap-3.5">
                @foreach([
                    ['⚡','Ringan & cepat','< 1 detik load di HP kentang, hemat kuota, bisa offline lihat katalog.'],
                    ['📍','Nearby beneran','Algoritma kedekatan — bukan yang bayar iklan paling mahal yang muncul duluan.'],
                    ['💰','Harga warung asli','Tanpa markup siluman. Yang kamu bayar = harga di etalase + ongkir transparan.'],
                    ['🤝','Pemberdayaan','Komisi 0% untuk 3 bulan pertama. 92% uangmu langsung ke pemilik warung.'],
                ] as $f)
                <li class="flex gap-3.5 bg-white/80 backdrop-blur rounded-2xl border border-ink-900/10 p-4">
                    <span class="w-11 h-11 shrink-0 rounded-2xl bg-ink-900 text-white grid place-items-center text-xl">{{ $f[0] }}</span>
                    <span><strong class="block text-[15px] font-extrabold">{{ $f[1] }}</strong><span class="block text-[13px] font-medium text-ink-500 leading-relaxed">{!! $f[2] !!}</span></span>
                </li>
                @endforeach
            </ul>
        </div>
        <div class="grid gap-3">
            <div class="rounded-3xl bg-ink-900 text-white p-6 relative overflow-hidden">
                <p class="text-[12px] font-bold text-white/50 tracking-widest">ONGKIR HEMAT</p>
                <p class="font-black text-4xl mt-1">Rp 2<span class="text-lg">rb</span></p>
                <p class="text-[13px] font-medium text-white/60 mt-1">untuk jarak &lt; 1 km. 63% lebih murah dari ojol biasa.</p>
                <div class="mt-4 h-2.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full w-[78%] rounded-full bg-gradient-to-r from-brand-400 to-leaf-500"></div></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-3xl bg-leaf-600 text-white p-5">
                    <p class="font-black text-2xl">0%</p>
                    <p class="text-[12px] font-bold text-white/80 leading-snug">komisi 3 bulan pertama buat mitra baru</p>
                </div>
                <div class="rounded-3xl bg-white border border-ink-900/10 p-5">
                    <p class="font-black text-2xl">14<span class="text-sm">mnt</span></p>
                    <p class="text-[12px] font-bold text-ink-500 leading-snug">rata-rata waktu antar pesanan</p>
                </div>
            </div>
            <div class="rounded-3xl bg-white border border-ink-900/10 p-5 flex items-center gap-4">
                <div class="flex -space-x-2.5">
                    @foreach(['S','D','R','+'] as $a)
                    <span class="w-9 h-9 rounded-full border-2 border-white grid place-items-center text-[12px] font-black text-white {{ $a=='+'?'bg-ink-900':'bg-brand-500' }}">{{ $a }}</span>
                    @endforeach
                </div>
                <p class="text-[13px] font-bold text-ink-700">Sari & 2.300+ tetanggamu udah jajan minggu ini 🧡</p>
            </div>
        </div>
    </div>
</section>

{{-- ================= CERITA KAMI ================= --}}
<section id="cerita" class="max-w-7xl mx-auto px-4 sm:px-6 pb-14 sm:pb-20">
    <div class="reveal-scale relative rounded-[32px] bg-ink-900 text-white p-7 sm:p-12 overflow-hidden grain">
        <div class="hidden sm:block absolute -top-24 -left-24 w-80 h-80 bg-brand-500/25 blur-[100px] rounded-full pointer-events-none" aria-hidden="true"></div>
        <div class="hidden sm:block absolute -bottom-28 -right-20 w-80 h-80 bg-leaf-500/20 blur-[100px] rounded-full pointer-events-none" aria-hidden="true"></div>

        <div class="relative grid gap-8 lg:grid-cols-[1.1fr_.9fr] lg:items-center">
            <div>
                <p class="reveal inline-flex items-center gap-2 text-[11px] font-extrabold tracking-[0.18em] text-brand-300 bg-white/10 border border-white/15 rounded-full px-3.5 py-1.5"><x-icon name="heart-solid" class="w-3.5 h-3.5" /> CERITA KAMI</p>
                <h2 class="reveal font-black tracking-tight text-3xl sm:text-[44px] leading-[1.02] mt-4" style="--reveal-delay:80ms">Tak perlu ruko besar untuk punya <span class="text-brand-400">warung hebat.</span></h2>
                <div class="reveal mt-5 grid gap-4 text-[15px] font-medium leading-relaxed text-white/70" style="--reveal-delay:160ms">
                    <p>Warung Hebat lahir dari gang-gang kecil Indonesia — dari <strong class="text-white">dapur Ibu</strong> yang masakannya selalu ludes di arisan, dari <strong class="text-white">teras rumah Bapak</strong> yang jadi basecamp anak komplek, dari <strong class="text-white">meja lipat depan kos</strong> yang tiap malam dipadati pembeli.</p>
                    <p>Mereka tak punya ruko besar. Tak punya modal raksasa. Yang mereka punya jauh lebih berharga: <strong class="text-white">resep keluarga, tangan yang tak kenal lelah, dan tetangga yang percaya.</strong></p>
                    <p>Kami membangun aplikasi ini dengan satu keyakinan sederhana — <strong class="text-brand-300">rumah kecil bukan halangan.</strong> Dengan satu HP, dapur dan teras rumahmu bisa menjadi toko yang dikunjungi ribuan tetangga.</p>
                </div>
            </div>

            <div class="relative grid gap-3">
                <figure class="reveal rounded-[24px] bg-white/[.07] border border-white/10 p-6 sm:p-7" style="--reveal-delay:200ms">
                    <span class="font-black text-5xl leading-none text-brand-400">“</span>
                    <blockquote class="font-bold text-[17px] sm:text-lg leading-relaxed -mt-2">Setiap pesanan di sini bukan sekadar transaksi. Ia adalah uang jajan anak, biaya sekolah, dan mimpi-mimpi kecil yang kita jaga bersama.</blockquote>
                    <figcaption class="mt-4 flex items-center gap-3">
                        <span class="w-11 h-11 rounded-full bg-brand-500 grid place-items-center"><x-icon name="store" class="w-5 h-5 text-white" /></span>
                        <span><strong class="block text-sm">Tim Warung Hebat</strong><span class="block text-xs font-semibold text-white/50">Ditulis dari sebuah teras kecil di Tebet</span></span>
                    </figcaption>
                </figure>
                <div class="reveal grid grid-cols-3 gap-2.5 text-center" style="--reveal-delay:260ms">
                    <div class="rounded-2xl bg-white/[.07] border border-white/10 py-4 px-2"><p class="font-black text-lg sm:text-xl">2.450+</p><p class="text-[10px] sm:text-[11px] font-bold text-white/50 leading-tight mt-0.5">warung rumahan</p></div>
                    <div class="rounded-2xl bg-white/[.07] border border-white/10 py-4 px-2"><p class="font-black text-lg sm:text-xl">92%</p><p class="text-[10px] sm:text-[11px] font-bold text-white/50 leading-tight mt-0.5">sampai ke penjual</p></div>
                    <div class="rounded-2xl bg-white/[.07] border border-white/10 py-4 px-2"><p class="font-black text-lg sm:text-xl">34</p><p class="text-[10px] sm:text-[11px] font-bold text-white/50 leading-tight mt-0.5">kota & terus tumbuh</p></div>
                </div>
                <a href="#mitra" class="reveal text-center bg-brand-500 hover:bg-brand-600 font-extrabold text-[15px] px-7 py-4 rounded-full shadow-xl shadow-brand-500/30 transition active:scale-[.98]" style="--reveal-delay:320ms">Rumahmu sudah cukup — buka warungmu →</a>
            </div>
        </div>
    </div>
</section>

{{-- ================= MITRA ================= --}}
<section id="mitra" class="max-w-7xl mx-auto px-4 sm:px-6 pb-14 sm:pb-20">
    <div class="grid gap-3 lg:grid-cols-[1fr_1fr] lg:gap-5">
        <div class="reveal rounded-[32px] bg-leaf-700 text-white p-7 sm:p-10 relative overflow-hidden grain">
            <div class="hidden sm:block absolute -bottom-24 -right-24 w-80 h-80 bg-white/10 blur-[80px] rounded-full pointer-events-none" aria-hidden="true"></div>
            <p class="inline-flex text-[11px] font-extrabold tracking-[0.18em] bg-white/15 border border-white/20 rounded-full px-3.5 py-1.5">🏪 BUAT PEMILIK WARUNG</p>
            <h2 class="font-black tracking-tight text-3xl sm:text-[42px] leading-[1.02] mt-3">Punya dagangan?<br>Jualan di sini, gratis.</h2>
            <p class="text-white/70 font-medium text-[15px] mt-3 max-w-sm">Foto daganganmu, tentukan jam buka, terima pesanan dari HP. Tanpa sewa, tanpa ribet.</p>
            <ul class="mt-5 grid gap-2 text-[14px] font-bold">
                <li class="flex items-center gap-2.5"><span class="w-6 h-6 rounded-full bg-white/20 grid place-items-center text-xs">✓</span> Daftar 5 menit, langsung tayang</li>
                <li class="flex items-center gap-2.5"><span class="w-6 h-6 rounded-full bg-white/20 grid place-items-center text-xs">✓</span> Dashboard WarungKu + struk otomatis</li>
                <li class="flex items-center gap-2.5"><span class="w-6 h-6 rounded-full bg-white/20 grid place-items-center text-xs">✓</span> Pelatihan digital + grup seller sedarah</li>
            </ul>
            @guest
            <a href="{{ route('register') }}" data-open-register class="mt-6 inline-flex items-center gap-2 bg-white text-leaf-700 font-extrabold px-7 py-3.5 rounded-full hover:bg-ink-900 hover:text-white transition">Daftar Jadi Mitra →</a>
            @else
            <a href="{{ route('dashboard') }}" class="mt-6 inline-flex items-center gap-2 bg-white text-leaf-700 font-extrabold px-7 py-3.5 rounded-full hover:bg-ink-900 hover:text-white transition">Buka Dashboard Mitra →</a>
            @endguest
        </div>
        <div class="grid gap-3">
            <div class="reveal rounded-[32px] bg-white border border-ink-900/10 p-7" style="--reveal-delay:100ms">
                <p class="text-3xl">💬</p>
                <p class="font-bold text-[15px] leading-relaxed mt-2">"Sejak gabung Warung Hebat, omzet gorengan saya naik 3x lipat. Yang beli orang-orang komplek sini juga, jadi kenal semua!"</p>
                <div class="flex items-center gap-3 mt-4">
                    <span class="w-11 h-11 rounded-full bg-brand-500 grid place-items-center text-white font-black">Y</span>
                    <div><p class="text-[14px] font-extrabold">Yu Ning</p><p class="text-[12px] font-semibold text-ink-500">Jajan Pasar Yu Ning • Jogja</p></div>
                    <span class="ml-auto text-[12px] font-extrabold text-leaf-600 bg-leaf-100 rounded-full px-3 py-1.5">+212% omzet</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="reveal rounded-[32px] bg-brand-500 text-white p-6" style="--reveal-delay:160ms">
                    <p class="font-black text-3xl">5<span class="text-base">mnt</span></p>
                    <p class="text-[12px] font-bold text-white/85">waktu daftar sampai warungmu tayang</p>
                </div>
                <div class="reveal rounded-[32px] bg-ink-900 text-white p-6" style="--reveal-delay:220ms">
                    <p class="font-black text-3xl">92<span class="text-base">%</span></p>
                    <p class="text-[12px] font-bold text-white/60">uang pesanan langsung ke pemilik warung</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ================= TESTIMONI ================= --}}
<section class="bg-cream-200/50 border-y border-ink-900/10 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 sm:py-16">
        <h2 class="reveal font-black tracking-tight text-3xl sm:text-4xl text-center">Kata tetangga 🧡</h2>
        <div class="mt-8 grid gap-3 sm:grid-cols-3">
            @foreach([
                ['Sari Dewi','Ibu rumah tangga • Tebet','Anak minta dimsum jam 9 malam, 15 menit sampai masih anget. Fix langganan!','⭐⭐⭐⭐⭐'],
                ['Rizky Pratama','Anak kos • Bandung','Nasi padang Rp 13rb + ongkir Rp 2rb. Dompet anak kos aman jaya.','⭐⭐⭐⭐⭐'],
                ['Mama Mecha','Seller sembako • Surabaya','Awalnya gaptek, dibantuin sampai bisa. Sekarang tiap hari ada 20+ orderan masuk.','⭐⭐⭐⭐⭐'],
            ] as $i => $t)
            <figure class="reveal rounded-[24px] bg-white border border-ink-900/10 p-6" style="--reveal-delay:{{ $i*100 }}ms">
                <p class="text-sm">{{ $t[3] }}</p>
                <blockquote class="font-bold text-[15px] leading-relaxed mt-2">"{{ $t[2] }}"</blockquote>
                <figcaption class="mt-4 text-[13px]"><strong>{{ $t[0] }}</strong> <span class="text-ink-500 font-semibold">• {{ $t[1] }}</span></figcaption>
            </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= FAQ ================= --}}
<section id="faq" class="max-w-3xl mx-auto px-4 sm:px-6 py-14 sm:py-20">
    <p class="reveal text-center inline-flex mx-auto w-fit text-[11px] font-extrabold tracking-[0.18em] text-brand-700 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">❓ FAQ</p>
    <h2 class="reveal font-black tracking-tight text-3xl sm:text-4xl text-center mt-3" style="--reveal-delay:80ms">Masih kepo? Wajar.</h2>
    <div class="mt-8 grid gap-2.5">
        @foreach([
            ['Apa itu Warung Hebat?','Marketplace digital yang menghubungkan pembeli dengan warung/toko kelontong/UMKM di sekitar mereka. Fokus kami: jarak dekat, harga jujur, dan pengalaman mobile-first yang ringan.'],
            ['Bagaimana cara pesan?','Daftar/masuk, nyalakan lokasi, pilih warung terdekat, masukkan ke keranjang, bayar via QRIS/e-wallet/COD, lalu pilih diantar atau ambil sendiri.'],
            ['Apakah harus daftar untuk belanja?','Iya, tapi gratis dan cuma butuh 30 detik — cukup nama, email & kata sandi. Tanpa daftar kamu tetap bisa jelajah katalog.'],
            ['Saya pemilik warung, berapa biayanya?','Gratis daftar + 0% komisi 3 bulan pertama. Setelah itu komisi flat kecil per transaksi, tanpa biaya sewa atau bulanan.'],
            ['Area mana saja yang dilayani?','Kami mulai dari Jabodetabek, Bandung, Jogja, Semarang, Surabaya & Denpasar — dan tiap minggu nambah kota baru. Daftarkan emailmu biar dikabari saat kotamu dibuka.'],
        ] as $i => $f)
        <div data-faq class="reveal rounded-2xl bg-white border border-ink-900/10 overflow-hidden" style="--reveal-delay:{{ $i*60 }}ms">
            <button data-faq-btn class="w-full flex items-center justify-between gap-4 text-left px-5 py-4 font-extrabold text-[15px]">
                {{ $f[0] }}
                <span data-faq-chevron class="shrink-0 w-8 h-8 grid place-items-center rounded-full bg-ink-900/5 transition-transform">⌄</span>
            </button>
            <div data-faq-panel><p class="px-5 pb-5 text-[14px] font-medium text-ink-500 leading-relaxed">{{ $f[1] }}</p></div>
        </div>
        @endforeach
    </div>
</section>

{{-- ================= FINAL CTA ================= --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 pb-16 sm:pb-24">
    <div class="reveal-scale relative rounded-[36px] bg-ink-900 text-white px-6 py-12 sm:p-14 text-center overflow-hidden grain">
        <div class="hidden sm:block absolute -top-24 left-1/2 -translate-x-1/2 w-[36rem] h-72 bg-brand-500/30 blur-[110px] rounded-full pointer-events-none" aria-hidden="true"></div>
        <p class="relative inline-flex text-[11px] font-extrabold tracking-[0.2em] text-brand-300 bg-white/10 border border-white/15 rounded-full px-4 py-1.5">🚀 GRATIS SELAMANYA BUAT PEMBELI</p>
        <h2 class="relative font-black tracking-tight text-4xl sm:text-6xl mt-4 leading-[0.95]">Laper jangan<br><span class="text-brand-400">ditunda-tunda.</span></h2>
        <p class="relative text-white/60 font-medium mt-3 max-w-md mx-auto">Gabung 48.000+ tetangga yang udah jajan hemat dari warung terdekat.</p>
        <div class="relative mt-7 flex flex-col sm:flex-row justify-center gap-2.5">
            @guest
            <a href="{{ route('register') }}" data-open-register class="bg-brand-500 hover:bg-brand-600 font-extrabold px-8 py-4 rounded-full shadow-xl shadow-brand-500/30 transition active:scale-[.98]">Daftar Gratis Sekarang →</a>
            <a href="{{ route('login') }}" data-open-login class="bg-white/10 border border-white/15 hover:bg-white/20 font-extrabold px-8 py-4 rounded-full transition">Saya Sudah Punya Akun</a>
            @else
            <a href="{{ route('dashboard') }}" class="bg-brand-500 hover:bg-brand-600 font-extrabold px-8 py-4 rounded-full shadow-xl shadow-brand-500/30 transition">Lanjut ke Dashboard →</a>
            @endguest
        </div>
        <p class="relative mt-5 text-[12px] font-semibold text-white/40">Gratis daftar • Batalkan kapan saja • CS 07.00–22.00 WIB</p>
    </div>
</section>
@endsection
