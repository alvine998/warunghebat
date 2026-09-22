@extends('layouts.app')

@section('title', 'Hubungi Kami — Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-14 max-w-7xl mx-auto px-4 sm:px-6">
    <div class="max-w-2xl">
        <p class="reveal inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-700 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">✉️ HUBUNGI KAMI</p>
        <h1 class="reveal font-black tracking-tight text-3xl sm:text-5xl mt-3" style="--reveal-delay:80ms">Cerita aja, kami dengerin.</h1>
        <p class="reveal text-ink-500 font-medium text-[15px] mt-2" style="--reveal-delay:140ms">Pesanan bermasalah, mau jadi mitra, atau sekadar kasih masukan — balas maksimal 1×24 jam kerja.</p>
    </div>

    <div class="mt-8 grid gap-3.5 lg:grid-cols-[380px_1fr]">
        {{-- Info channels --}}
        <div class="grid gap-3 content-start">
            <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" class="reveal rounded-[24px] bg-leaf-600 text-white p-5 flex items-center gap-4 hover:-translate-y-1 hover:shadow-xl transition-all">
                <span class="w-12 h-12 shrink-0 rounded-2xl bg-white/20 grid place-items-center text-2xl">✆</span>
                <span><strong class="block">WhatsApp CS</strong><span class="block text-[13px] font-semibold text-white/75">+62 812-3456-7890 • 07.00–22.00 WIB</span></span>
            </a>
            <a href="mailto:halo@warunghebat.id" class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 flex items-center gap-4 hover:-translate-y-1 hover:shadow-xl transition-all" style="--reveal-delay:80ms">
                <span class="w-12 h-12 shrink-0 rounded-2xl bg-brand-100 grid place-items-center text-2xl">✉️</span>
                <span><strong class="block text-[15px]">halo@warunghebat.id</strong><span class="block text-[13px] font-semibold text-ink-500">Email resmi • balasan 1×24 jam</span></span>
            </a>
            <div class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 flex items-center gap-4" style="--reveal-delay:140ms">
                <span class="w-12 h-12 shrink-0 rounded-2xl bg-cream-200 grid place-items-center text-2xl">📍</span>
                <span><strong class="block text-[15px]">Kantor Kami</strong><span class="block text-[13px] font-semibold text-ink-500">Jl. Tebet Raya No. 12, Jakarta Selatan</span></span>
            </div>
            <div class="reveal rounded-[24px] bg-ink-900 text-white p-5" style="--reveal-delay:200ms">
                <p class="text-[11px] font-extrabold tracking-[0.2em] text-white/40">JAM OPERASIONAL</p>
                <p class="font-extrabold mt-1">Senin–Sabtu • 07.00–22.00 WIB</p>
                <p class="text-[13px] font-semibold text-white/60">Minggu & tanggal merah: slow response 🙏</p>
            </div>
        </div>

        {{-- Form --}}
        <div class="reveal rounded-[28px] bg-white border border-ink-900/10 p-6 sm:p-8" style="--reveal-delay:120ms">
            <h2 class="font-extrabold text-xl">Kirim pesan langsung 📝</h2>
            <p class="text-[13px] font-medium text-ink-500 mt-1">Form ini tersimpan aman dan diteruskan ke tim yang tepat.</p>
            <form method="POST" action="{{ route('contact.send') }}" class="mt-5 grid gap-3.5">
                @csrf
                <div class="grid sm:grid-cols-2 gap-3.5">
                    <label class="grid gap-1.5">
                        <span class="text-[13px] font-bold">Nama</span>
                        <input name="name" required value="{{ old('name') }}" placeholder="Nama kamu" class="w-full rounded-2xl border border-ink-900/15 bg-cream-50 px-4 py-3.5 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    </label>
                    <label class="grid gap-1.5">
                        <span class="text-[13px] font-bold">Email</span>
                        <input name="email" type="email" required value="{{ old('email') }}" placeholder="kamu@email.com" class="w-full rounded-2xl border border-ink-900/15 bg-cream-50 px-4 py-3.5 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                    </label>
                </div>
                <label class="grid gap-1.5">
                    <span class="text-[13px] font-bold">Topik</span>
                    <select name="subject" required class="w-full rounded-2xl border border-ink-900/15 bg-cream-50 px-4 py-3.5 text-[15px] font-semibold outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition">
                        @foreach(['Pesanan','Pembayaran','Mitra','Bantuan Teknis','Lainnya'] as $t)
                            <option value="{{ $t }}" @selected(old('subject') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-1.5">
                    <span class="text-[13px] font-bold">Pesan <span class="font-medium text-ink-500">(min. 10 karakter)</span></span>
                    <textarea name="message" rows="5" required minlength="10" maxlength="2000" placeholder="Ceritakan masalah atau kebutuhanmu sedetail mungkin..." class="w-full rounded-2xl border border-ink-900/15 bg-cream-50 px-4 py-3.5 text-[15px] font-medium outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 transition resize-y">{{ old('message') }}</textarea>
                </label>
                <button class="w-full py-4 rounded-2xl bg-brand-500 text-white font-extrabold text-[15px] hover:bg-brand-600 active:scale-[.99] transition shadow-xl shadow-brand-500/30">Kirim Pesan →</button>
                <p class="text-[11px] text-center text-ink-500 font-medium">Dengan mengirim, kamu setuju data diproses sesuai <a href="{{ route('privacy') }}" class="underline font-bold">Kebijakan Privasi</a>.</p>
            </form>
        </div>
    </div>
</section>
@endsection
