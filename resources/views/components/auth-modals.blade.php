{{-- Auth modals shown on landing (progressive enhancement; full pages exist at /login & /register) --}}
<div id="modal-login" class="hidden fixed inset-0 z-[70] modal-backdrop">
    <div class="absolute inset-0 bg-ink-900/60 backdrop-blur-sm" data-close-modal></div>
    <div class="absolute inset-0 overflow-y-auto" data-lenis-prevent>
        <div class="min-h-full flex items-end sm:items-center justify-center p-0 sm:p-6">
            <div class="modal-card relative w-full sm:max-w-md bg-cream-50 rounded-t-[28px] sm:rounded-[28px] p-6 sm:p-8 shadow-2xl">
                <button data-close-modal class="absolute top-4 right-4 w-9 h-9 grid place-items-center rounded-full bg-ink-900/5 hover:bg-ink-900 hover:text-white transition" aria-label="Tutup">✕</button>
                <div class="flex items-center gap-2 mb-5">
                    <span class="w-9 h-9 rounded-xl bg-ink-900 grid place-items-center -rotate-3"><span class="text-brand-400 font-black">W</span></span>
                    <p class="font-extrabold">Masuk ke Warung Hebat</p>
                </div>
                <p class="text-sm text-ink-500 font-medium mb-5 -mt-3">Warung favoritmu udah nungguin. Masuk dulu yuk.</p>
                @if (($errors ?? null)?->any() && request()->routeIs('login'))
                    <div class="mb-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-[13px] font-semibold p-3">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('login') }}" class="grid gap-3">
                    @csrf
                    <label class="grid gap-1.5">
                        <span class="text-[13px] font-bold">Email</span>
                        <input name="email" type="email" required value="{{ old('email') }}" placeholder="kamu@email.com" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    </label>
                    <label class="grid gap-1.5">
                        <span class="text-[13px] font-bold">Kata sandi</span>
                        <span class="relative block">
                            <input id="modal-login-password" name="password" type="password" required placeholder="••••••••" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3 pr-12 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                            <button type="button" data-toggle-password="#modal-login-password" class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 grid place-items-center rounded-xl hover:bg-ink-900/5 text-ink-500" aria-label="Lihat sandi">
                                <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3l18 18M10.5 5.2A9.8 9.8 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 3.9M6.6 6.6A16.4 16.4 0 0 0 2 12s3.5 7 10 7a9.6 9.6 0 0 0 4.4-1.1"/></svg>
                            </button>
                        </span>
                    </label>
                    <label class="flex items-center justify-between text-[13px] font-semibold text-ink-700">
                        <span class="inline-flex items-center gap-2"><input type="checkbox" name="remember" class="w-4 h-4 accent-[#F95D0B] rounded"> Ingat saya</span>
                        <a href="#" class="text-brand-600">Lupa sandi?</a>
                    </label>
                    <button class="w-full py-3.5 rounded-2xl bg-ink-900 text-white font-extrabold text-[15px] hover:bg-brand-600 active:scale-[.99] transition">Masuk →</button>
                </form>
                <p class="text-center text-[13px] font-semibold text-ink-500 mt-4">Belum punya akun? <button data-open-register class="text-brand-600 font-extrabold">Daftar gratis</button></p>
                <p class="text-center mt-3"><a href="{{ route('login') }}" class="text-xs font-semibold text-ink-500 underline underline-offset-4">Buka halaman login penuh</a></p>
            </div>
        </div>
    </div>
</div>

<div id="modal-register" class="hidden fixed inset-0 z-[70] modal-backdrop">
    <div class="absolute inset-0 bg-ink-900/60 backdrop-blur-sm" data-close-modal></div>
    <div class="absolute inset-0 overflow-y-auto" data-lenis-prevent>
        <div class="min-h-full flex items-end sm:items-center justify-center p-0 sm:p-6">
            <div class="modal-card relative w-full sm:max-w-md bg-cream-50 rounded-t-[28px] sm:rounded-[28px] p-6 sm:p-8 shadow-2xl">
                <button data-close-modal class="absolute top-4 right-4 w-9 h-9 grid place-items-center rounded-full bg-ink-900/5 hover:bg-ink-900 hover:text-white transition" aria-label="Tutup">✕</button>
                <div class="inline-flex items-center gap-1.5 rounded-full bg-leaf-100 text-leaf-700 text-[11px] font-extrabold px-3 py-1.5 mb-4">🎉 100% GRATIS</div>
                <p class="font-extrabold text-xl tracking-tight">Bikin akun Warung Hebat</p>
                <p class="text-sm text-ink-500 font-medium mb-5">Satu akun untuk jajan & jualan.</p>
                <form method="POST" action="{{ route('register') }}" class="grid gap-3">
                    @csrf
                    <label class="grid gap-1.5">
                        <span class="text-[13px] font-bold">Nama lengkap</span>
                        <input name="name" required value="{{ old('name') }}" placeholder="cth. Sari Dewi" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    </label>
                    <label class="grid gap-1.5">
                        <span class="text-[13px] font-bold">Email</span>
                        <input name="email" type="email" required value="{{ old('email') }}" placeholder="kamu@email.com" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="grid gap-1.5">
                            <span class="text-[13px] font-bold">Kata sandi</span>
                            <input id="modal-reg-password" name="password" type="password" required placeholder="Min. 8 karakter" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                        </label>
                        <label class="grid gap-1.5">
                            <span class="text-[13px] font-bold">Konfirmasi</span>
                            <input name="password_confirmation" type="password" required placeholder="Ulangi sandi" class="w-full rounded-2xl border border-ink-900/15 bg-white px-4 py-3 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="pembeli" checked class="peer sr-only">
                            <span class="block text-center text-[13px] font-extrabold rounded-2xl border-2 border-ink-900/10 bg-white py-3 peer-checked:border-brand-500 peer-checked:bg-brand-50 transition">🛍️ Pembeli</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="penjual" class="peer sr-only">
                            <span class="block text-center text-[13px] font-extrabold rounded-2xl border-2 border-ink-900/10 bg-white py-3 peer-checked:border-leaf-500 peer-checked:bg-leaf-50 transition">🏪 Penjual</span>
                        </label>
                    </div>
                    <button class="w-full py-3.5 rounded-2xl bg-brand-500 text-white font-extrabold text-[15px] hover:bg-brand-600 active:scale-[.99] transition shadow-lg shadow-brand-500/30">Daftar Gratis →</button>
                    <p class="text-[11px] text-center text-ink-500 font-medium leading-relaxed">Dengan mendaftar, kamu setuju dengan <a href="{{ route('terms') }}" class="underline">Syarat & Ketentuan</a> dan <a href="{{ route('privacy') }}" class="underline">Kebijakan Privasi</a>.</p>
                </form>
                <p class="text-center text-[13px] font-semibold text-ink-500 mt-3">Sudah punya akun? <button data-open-login class="text-brand-600 font-extrabold">Masuk</button></p>
            </div>
        </div>
    </div>
</div>
