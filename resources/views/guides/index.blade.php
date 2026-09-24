@extends('layouts.app')

@section('title', 'Panduan — Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-10 max-w-4xl mx-auto px-4 sm:px-6">
    <p class="reveal inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-700 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">📖 PANDUAN</p>
    <h1 class="reveal font-black tracking-tight text-3xl sm:text-5xl mt-3" style="--reveal-delay:80ms">Belanja & jualan,<br class="sm:hidden"> langkah demi langkah.</h1>
    <p class="reveal text-ink-500 font-medium text-[15px] sm:text-lg mt-3 max-w-2xl leading-relaxed" style="--reveal-delay:140ms">
        Semua cara pakai Warung Hebat dalam Bahasa Indonesia yang sederhana — untuk <strong class="text-ink-900">pembeli</strong> yang mau jajan sat-set, dan <strong class="text-ink-900">penjual</strong> yang mau warungnya tayang dalam 5 menit.
    </p>

    <div class="mt-7 grid gap-3 sm:grid-cols-2">
        <a href="{{ route('guides.buyer') }}" class="reveal group rounded-[28px] bg-ink-900 text-white p-6 sm:p-7 relative overflow-hidden hover:-translate-y-1 hover:shadow-xl transition-all" style="--reveal-delay:200ms">
            <span class="w-12 h-12 rounded-2xl bg-brand-500 grid place-items-center text-2xl">🛒</span>
            <p class="font-black text-xl sm:text-2xl mt-4">Panduan Pembeli</p>
            <p class="text-[13px] font-medium text-white/60 leading-relaxed mt-1">Daftar → cari warung terdekat → keranjang → bayar → lacak → kasih rating. Lengkap dengan cara upload bukti bayar & refund.</p>
            <ul class="mt-4 grid gap-1.5 text-[13px] font-bold text-white/80">
                <li>✓ 6 langkah + estimasi waktu tiap langkah</li>
                <li>✓ Status pesanan & arti masing-masing</li>
                <li>✓ Tanya-jawab: akun, bayar, batal, refund</li>
            </ul>
            <span class="mt-5 inline-flex items-center gap-2 bg-brand-500 group-hover:bg-brand-600 font-extrabold text-sm px-6 py-3 rounded-full transition">Baca panduan pembeli →</span>
        </a>
        <a href="{{ route('guides.seller') }}" class="reveal group rounded-[28px] bg-leaf-700 text-white p-6 sm:p-7 relative overflow-hidden hover:-translate-y-1 hover:shadow-xl transition-all" style="--reveal-delay:260ms">
            <span class="w-12 h-12 rounded-2xl bg-white/20 grid place-items-center text-2xl">🏪</span>
            <p class="font-black text-xl sm:text-2xl mt-4">Panduan Penjual</p>
            <p class="text-[13px] font-medium text-white/70 leading-relaxed mt-1">Daftar sebagai penjual → lengkapi warung → tambah produk → diverifikasi admin → terima pesanan → cairkan saldo dompet.</p>
            <ul class="mt-4 grid gap-1.5 text-[13px] font-bold text-white/85">
                <li>✓ Syarat foto, harga, stok & kategori</li>
                <li>✓ Arti status pending / disetujui / ditolak</li>
                <li>✓ Dompet, penarikan & jadwal pencairan</li>
            </ul>
            <span class="mt-5 inline-flex items-center gap-2 bg-white text-leaf-700 group-hover:bg-ink-900 group-hover:text-white font-extrabold text-sm px-6 py-3 rounded-full transition">Baca panduan penjual →</span>
        </a>
    </div>
</section>

<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16">
    <h2 class="reveal font-black tracking-tight text-2xl sm:text-3xl">Mulai cepat dalam 3 ketuk</h2>
    <div class="mt-5 grid gap-3 sm:grid-cols-2">
        <div class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6">
            <p class="text-[11px] font-extrabold tracking-[0.18em] text-brand-600">🛒 PEMBELI • ± 2 MENIT</p>
            <ol class="mt-3 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-none">
                <li class="flex gap-2.5"><span class="shrink-0 w-6 h-6 rounded-full bg-ink-900 text-white grid place-items-center text-[11px] font-black">1</span> <span><a href="{{ route('register') }}" data-open-register class="font-extrabold underline underline-offset-4 decoration-brand-500">Daftar gratis</a> — cukup nama, email & kata sandi (min. 8 karakter).</span></li>
                <li class="flex gap-2.5"><span class="shrink-0 w-6 h-6 rounded-full bg-ink-900 text-white grid place-items-center text-[11px] font-black">2</span> <span>Buka <a href="{{ route('store.index') }}" class="font-extrabold underline underline-offset-4 decoration-brand-500">warung terdekat</a>, nyalakan lokasi biar urut dari yang paling dekat.</span></li>
                <li class="flex gap-2.5"><span class="shrink-0 w-6 h-6 rounded-full bg-ink-900 text-white grid place-items-center text-[11px] font-black">3</span> <span>Masukkan ke <a href="{{ route('cart.index') }}" class="font-extrabold underline underline-offset-4 decoration-brand-500">keranjang</a> → checkout → bayar → pantau di <a href="{{ route('orders.index') }}" class="font-extrabold underline underline-offset-4 decoration-brand-500">pesanan</a>.</span></li>
            </ol>
            <a href="{{ route('guides.buyer') }}" class="mt-4 inline-flex font-extrabold text-sm text-brand-600 hover:text-brand-700">Detail lengkap pembeli →</a>
        </div>
        <div class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6" style="--reveal-delay:100ms">
            <p class="text-[11px] font-extrabold tracking-[0.18em] text-leaf-700">🏪 PENJUAL • ± 5 MENIT</p>
            <ol class="mt-3 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-none">
                <li class="flex gap-2.5"><span class="shrink-0 w-6 h-6 rounded-full bg-leaf-600 text-white grid place-items-center text-[11px] font-black">1</span> <span><a href="{{ route('register') }}" data-open-register class="font-extrabold underline underline-offset-4 decoration-leaf-500">Daftar</a> dan pilih peran <strong>Penjual</strong> saat mendaftar.</span></li>
                <li class="flex gap-2.5"><span class="shrink-0 w-6 h-6 rounded-full bg-leaf-600 text-white grid place-items-center text-[11px] font-black">2</span> <span>Lengkapi warung di <strong>Dashboard → Pengaturan Warung</strong>: nama, alamat, jam buka & foto.</span></li>
                <li class="flex gap-2.5"><span class="shrink-0 w-6 h-6 rounded-full bg-leaf-600 text-white grid place-items-center text-[11px] font-black">3</span> <span>Tambah produk di <strong>Dashboard → Produk</strong>, tunggu verifikasi admin, lalu buka warungmu.</span></li>
            </ol>
            <a href="{{ route('guides.seller') }}" class="mt-4 inline-flex font-extrabold text-sm text-leaf-700 hover:text-leaf-600">Detail lengkap penjual →</a>
        </div>
    </div>
</section>

<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16">
    <h2 class="reveal font-black tracking-tight text-2xl sm:text-3xl">Pertanyaan yang paling sering masuk</h2>
    <div class="mt-5 grid gap-2.5">
        @foreach([
            ['Apakah belanja harus daftar akun?', 'Iya, tapi gratis dan cuma butuh ±30 detik — nama, email, dan kata sandi. Tanpa daftar kamu tetap bisa jelajah katalog dan lihat warung, tapi untuk checkout wajib masuk dulu.'],
            ['Bagaimana cara bayar pesanan?', 'Setelah checkout, buka halaman detail pesanan, pilih metode pembayaran yang tersedia (QRIS, e-wallet, atau transfer bank), bayar sesuai nominal, lalu upload foto bukti transfer (JPG/PNG/WebP, maks. 2MB). Admin memverifikasi, dan status pesananmu berubah otomatis. COD dibayar tunai saat pesanan diterima.'],
            ['Berapa biaya buka warung?', 'Gratis daftar. Komisi 0% untuk 3 bulan pertama, setelah itu komisi flat kecil per transaksi — tanpa sewa dan tanpa biaya bulanan. Rinciannya selalu tampil di dashboard mitra.'],
            ['Kenapa produk saya belum tampil?', 'Setiap produk baru (dan setiap produk yang diubah) masuk status Menunggu verifikasi admin dulu. Biasanya diperiksa maksimal 1×24 jam kerja. Kalau ditolak, alasan penolakannya tampil di halaman produk — perbaiki, simpan, dan produk kembali antre verifikasi.'],
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

<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-16 sm:pb-24">
    <div class="reveal rounded-[28px] bg-ink-900 text-white p-6 sm:p-9 flex flex-col sm:flex-row sm:items-center gap-5">
        <div class="flex-1">
            <h2 class="font-black tracking-tight text-2xl">Masih bingung setelah baca panduan?</h2>
            <p class="text-sm font-medium text-white/60 mt-1.5">Ceritakan masalahmu — CS membalas maksimal 1×24 jam kerja (07.00–22.00 WIB).</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 shrink-0">
            <a href="{{ route('contact') }}" class="text-center bg-brand-500 hover:bg-brand-600 font-extrabold text-sm px-7 py-3.5 rounded-full transition">Hubungi Kami →</a>
            <a href="{{ route('terms') }}" class="text-center bg-white/10 border border-white/15 hover:bg-white/20 font-extrabold text-sm px-7 py-3.5 rounded-full transition">Syarat & Ketentuan</a>
        </div>
    </div>
</section>
@endsection

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'Panduan Warung Hebat',
    'url' => route('guides.index'),
    'description' => 'Panduan langkah demi langkah untuk pembeli dan penjual Warung Hebat: cara daftar, belanja, bayar, buka warung, tambah produk, dan cairkan saldo.',
    'inLanguage' => 'id-ID',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
