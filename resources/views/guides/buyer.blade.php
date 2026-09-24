@extends('layouts.app')

@section('title', 'Panduan Pembeli — Cara Belanja di Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-10 max-w-4xl mx-auto px-4 sm:px-6">
    <nav class="reveal text-[13px] font-bold text-ink-500" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-ink-900">Beranda</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('guides.index') }}" class="hover:text-ink-900">Panduan</a>
        <span class="mx-1.5">/</span>
        <span class="text-ink-900">Pembeli</span>
    </nav>

    <p class="reveal mt-4 inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-700 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">🛒 PANDUAN PEMBELI</p>
    <h1 class="reveal font-black tracking-tight text-3xl sm:text-5xl mt-3" style="--reveal-delay:80ms">Cara belanja dari warung tetangga.</h1>
    <p class="reveal text-ink-500 font-medium text-[15px] sm:text-lg mt-3 max-w-2xl leading-relaxed" style="--reveal-delay:140ms">
        Dari daftar sampai pesanan sampai di tangan — 6 langkah, ±5 menit untuk pertama kali, dan ±2 menit untuk belanja berikutnya. Gratis selamanya buat pembeli.
    </p>

    <div class="reveal mt-5 flex flex-wrap gap-2 text-[13px] font-extrabold" style="--reveal-delay:200ms">
        <span class="inline-flex items-center gap-1.5 bg-ink-900 text-white rounded-full px-4 py-2">⏱️ ±5 menit pertama kali</span>
        <span class="inline-flex items-center gap-1.5 bg-white border border-ink-900/15 rounded-full px-4 py-2">📱 HP & nomor/email aktif</span>
        <span class="inline-flex items-center gap-1.5 bg-white border border-ink-900/15 rounded-full px-4 py-2">💳 QRIS / e-wallet / transfer / COD</span>
    </div>

    <div class="reveal mt-6 rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6" style="--reveal-delay:240ms">
        <p class="text-[11px] font-extrabold tracking-[0.18em] text-ink-500">DAFTAR ISI</p>
        <ol class="mt-3 grid sm:grid-cols-2 gap-2 text-[14px] font-bold">
            <li><a href="#daftar" class="hover:text-brand-600">1. Daftar & masuk akun</a></li>
            <li><a href="#cari" class="hover:text-brand-600">2. Cari warung terdekat</a></li>
            <li><a href="#keranjang" class="hover:text-brand-600">3. Isi keranjang</a></li>
            <li><a href="#bayar" class="hover:text-brand-600">4. Checkout & bayar</a></li>
            <li><a href="#lacak" class="hover:text-brand-600">5. Lacak pesanan</a></li>
            <li><a href="#rating" class="hover:text-brand-600">6. Kasih rating</a></li>
        </ol>
    </div>
</section>

<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16 grid gap-3">
    {{-- STEP 1 --}}
    <article id="daftar" class="reveal scroll-mt-28 rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-ink-900 text-white grid place-items-center font-black">1</span>
            <div>
                <h2 class="font-extrabold text-lg sm:text-xl">Daftar & masuk akun</h2>
                <p class="text-[12px] font-bold text-ink-500">⏱️ ±30 detik • gratis</p>
            </div>
        </div>
        <ol class="mt-4 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-decimal list-inside marker:font-extrabold">
            <li>Buka halaman <a href="{{ route('register') }}" data-open-register class="font-extrabold text-brand-600 underline underline-offset-4">Daftar</a>.</li>
            <li>Isi <strong>nama</strong>, <strong>email aktif</strong>, dan <strong>kata sandi minimal 8 karakter</strong> (plus konfirmasinya). Pastikan peran yang dipilih <strong>Pembeli</strong>.</li>
            <li>Klik <strong>Daftar</strong> — kamu langsung masuk dan diarahkan ke dashboard.</li>
            <li>Sudah punya akun? Masuk lewat halaman <a href="{{ route('login') }}" data-open-login class="font-extrabold text-brand-600 underline underline-offset-4">Masuk</a> dengan email + kata sandi. Centang <em>ingat saya</em> biar tidak perlu login ulang di HP sendiri.</li>
        </ol>
        <div class="mt-4 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-[13px] font-medium text-amber-900 leading-relaxed">
            <strong>Lupa kata sandi?</strong> Buka <a href="{{ route('password.request') }}" class="font-extrabold underline">Lupa Kata Sandi</a>, masukkan email, klik tautan reset yang dikirim (berlaku 60 menit, cek folder spam), lalu buat kata sandi baru.
        </div>
    </article>

    {{-- STEP 2 --}}
    <article id="cari" class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-ink-900 text-white grid place-items-center font-black">2</span>
            <div>
                <h2 class="font-extrabold text-lg sm:text-xl">Cari warung terdekat</h2>
                <p class="text-[12px] font-bold text-ink-500">⏱️ ±1 menit • tanpa daftar pun bisa lihat-lihat</p>
            </div>
        </div>
        <ol class="mt-4 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-decimal list-inside marker:font-extrabold">
            <li>Buka halaman <a href="{{ route('store.index') }}" class="font-extrabold text-brand-600 underline underline-offset-4">Warung Terdekat</a> dan klik <strong>Gunakan lokasiku</strong> — daftar warung otomatis urut dari yang paling dekat (radius default 5 km, bisa diubah).</li>
            <li>Belum tahu mau apa? Jelajah lewat <strong>Kategori</strong>: Makanan, Minuman, Sembako, Jajanan, Frozen, Harian, atau Lainnya.</li>
            <li>Klik kartu warung untuk membuka etalasenya: foto, harga, stok, jam buka, alamat, dan rating dari pembeli lain.</li>
        </ol>
        <div class="mt-4 rounded-2xl bg-cream-200/60 border border-ink-900/10 p-4 text-[13px] font-medium text-ink-700 leading-relaxed">
            💡 <strong>Tips:</strong> warung yang tutup sementara tidak bisa dipesan. Cari badge <strong>Buka</strong> dan cek estimasi jarak — makin dekat, makin cepat sampai dan makin murah ongkirnya.
        </div>
    </article>

    {{-- STEP 3 --}}
    <article id="keranjang" class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-ink-900 text-white grid place-items-center font-black">3</span>
            <div>
                <h2 class="font-extrabold text-lg sm:text-xl">Isi keranjang</h2>
                <p class="text-[12px] font-bold text-ink-500">⏱️ ±1 menit • satu keranjang = satu warung</p>
            </div>
        </div>
        <ol class="mt-4 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-decimal list-inside marker:font-extrabold">
            <li>Di halaman warung, klik <strong>+ Tambah</strong> pada produk yang kamu mau. Ulangi untuk tiap item.</li>
            <li>Buka <a href="{{ route('cart.index') }}" class="font-extrabold text-brand-600 underline underline-offset-4">Keranjang</a> untuk memeriksa: nama produk, jumlah, harga satuan, dan total.</li>
            <li>Atur jumlah dengan tombol <strong>+ / −</strong> atau hapus item yang tidak jadi dibeli. Total selalu dihitung ulang otomatis.</li>
        </ol>
        <div class="mt-4 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-[13px] font-medium text-amber-900 leading-relaxed">
            ⚠️ <strong>Penting:</strong> satu kali checkout hanya untuk <strong>satu warung</strong>. Kalau mau jajan dari dua warung berbeda, selesaikan pesanan pertama dulu, baru buat pesanan kedua.
        </div>
    </article>

    {{-- STEP 4 --}}
    <article id="bayar" class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-500 text-white grid place-items-center font-black">4</span>
            <div>
                <h2 class="font-extrabold text-lg sm:text-xl">Checkout & bayar</h2>
                <p class="text-[12px] font-bold text-ink-500">⏱️ ±2 menit • struk digital tersimpan otomatis</p>
            </div>
        </div>
        <ol class="mt-4 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-decimal list-inside marker:font-extrabold">
            <li>Dari halaman keranjang, klik <strong>Checkout</strong>. Sistem membuat pesanan dan stok produk langsung dikunci untukmu — kamu diarahkan ke halaman detail pesanan.</li>
            <li>Di halaman detail pesanan, <strong>pilih metode pembayaran</strong> yang tersedia (QRIS, e-wallet, atau transfer bank sesuai tiap warung).</li>
            <li>Bayar <strong>persis sesuai nominal total</strong> ke nomor/akun yang tertera.</li>
            <li>Foto atau screenshot bukti transfer, lalu <strong>upload di halaman yang sama</strong>: format <strong>JPG, PNG, atau WebP, maksimal 2 MB</strong>. Klik kirim.</li>
            <li>Status berubah menjadi <strong>Menunggu verifikasi</strong>. Admin memeriksa bukti bayarmu — biasanya dalam hitungan jam kerja.</li>
            <li>Kalau memilih <strong>COD</strong>, lewati upload bukti: siapkan uang tunai pas dan bayar saat pesanan diterima.</li>
        </ol>
        <div class="mt-4 overflow-x-auto rounded-2xl border border-ink-900/10">
            <table class="w-full text-[13px] min-w-[520px]">
                <thead>
                    <tr class="bg-ink-900 text-white text-left">
                        <th class="font-extrabold px-4 py-3">Status pesanan</th>
                        <th class="font-extrabold px-4 py-3">Artinya</th>
                        <th class="font-extrabold px-4 py-3">Aksi kamu</th>
                    </tr>
                </thead>
                <tbody class="font-medium text-ink-700 divide-y divide-ink-900/10">
                    <tr><td class="px-4 py-3 font-extrabold">Menunggu pembayaran</td><td class="px-4 py-3">Pesanan dibuat, belum ada bukti bayar masuk.</td><td class="px-4 py-3">Segera bayar & upload bukti.</td></tr>
                    <tr><td class="px-4 py-3 font-extrabold">Menunggu verifikasi</td><td class="px-4 py-3">Bukti bayar terkirim, antre diperiksa admin.</td><td class="px-4 py-3">Tunggu; jangan upload ulang kecuali diminta.</td></tr>
                    <tr><td class="px-4 py-3 font-extrabold">Dibayar / Diproses</td><td class="px-4 py-3">Pembayaran lolos, warung menyiapkan pesanan.</td><td class="px-4 py-3">Siap-siap terima / ambil pesanan.</td></tr>
                    <tr><td class="px-4 py-3 font-extrabold">Selesai</td><td class="px-4 py-3">Pesanan sampai & dana diteruskan ke warung.</td><td class="px-4 py-3">Kasih rating ⭐ (langkah 6).</td></tr>
                    <tr><td class="px-4 py-3 font-extrabold">Dibatalkan</td><td class="px-4 py-3">Pesanan batal (stok habis / pembayaran ditolak).</td><td class="px-4 py-3">Dana prabayar dikembalikan, lihat kebijakan refund.</td></tr>
                </tbody>
            </table>
        </div>
    </article>

    {{-- STEP 5 --}}
    <article id="lacak" class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-ink-900 text-white grid place-items-center font-black">5</span>
            <div>
                <h2 class="font-extrabold text-lg sm:text-xl">Lacak & terima pesanan</h2>
                <p class="text-[12px] font-bold text-ink-500">rata-rata sampai 14 menit untuk radius dekat</p>
            </div>
        </div>
        <ol class="mt-4 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-decimal list-inside marker:font-extrabold">
            <li>Semua pesananmu tercatat di halaman <a href="{{ route('orders.index') }}" class="font-extrabold text-brand-600 underline underline-offset-4">Pesanan Saya</a>, urut dari yang terbaru — lengkap dengan kode pesanan, warung, isi, total, dan status.</li>
            <li>Klik satu pesanan untuk melihat rincian + riwayat pembayarannya.</li>
            <li>Pilih <strong>diantar</strong> (estimasi 10–20 menit untuk radius dekat) atau <strong>ambil sendiri</strong> di alamat warung biar lebih hemat.</li>
            <li>Saat menerima: cek kelengkapan & kondisi barang di tempat. Ada yang kurang? Segera hubungi warung/CS maksimal <strong>1×24 jam</strong> setelah diterima.</li>
        </ol>
    </article>

    {{-- STEP 6 --}}
    <article id="rating" class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-ink-900 text-white grid place-items-center font-black">6</span>
            <div>
                <h2 class="font-extrabold text-lg sm:text-xl">Kasih rating buat warung</h2>
                <p class="text-[12px] font-bold text-ink-500">1 pesanan = 1 penilaian • hanya setelah pesanan selesai</p>
            </div>
        </div>
        <ol class="mt-4 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-decimal list-inside marker:font-extrabold">
            <li>Buka detail pesanan yang statusnya <strong>Selesai</strong>.</li>
            <li>Pilih <strong>1–5 bintang</strong> dan tulis komentar jujur (maks. 500 karakter) — misal soal rasa, kecepatan, dan keramahan.</li>
            <li>Klik kirim. Penilaian tidak bisa diubah, jadi pastikan sudah sesuai.</li>
        </ol>
        <div class="mt-4 rounded-2xl bg-cream-200/60 border border-ink-900/10 p-4 text-[13px] font-medium text-ink-700 leading-relaxed">
            🧡 <strong>Kenapa penting?</strong> Rating-mu menentukan reputasi warung dan membantu tetangga lain memilih. Ulasan harus jujur berdasarkan pengalaman nyata — ulasan palsu/spam bisa dihapus.
        </div>
    </article>

    {{-- Pembatalan & refund --}}
    <article id="refund" class="reveal scroll-mt-28 rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <h2 class="font-extrabold text-lg sm:text-xl">🔄 Pembatalan & pengembalian dana</h2>
        <ul class="mt-4 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-disc list-inside marker:text-brand-500">
            <li><strong>Sebelum warung memproses</strong>, kamu bisa membatalkan pesanan.</li>
            <li>Jika warung membatalkan atau barang ternyata habis, kamu ditawari <strong>barang pengganti atau refund penuh</strong>.</li>
            <li>Refund e-wallet/transfer diproses maksimal <strong>3×24 jam</strong>; COD dikembalikan tunai di tempat.</li>
            <li>Komplain kualitas (basi, salah item, rusak) disampaikan maksimal <strong>1×24 jam</strong> setelah pesanan diterima via <a href="{{ route('contact') }}" class="font-extrabold text-brand-600 underline underline-offset-4">Hubungi Kami</a> — sertakan kode pesanan + foto.</li>
        </ul>
    </article>

    {{-- Masalah umum --}}
    <article class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <h2 class="font-extrabold text-lg sm:text-xl">🛠️ Kalau ada masalah…</h2>
        <div class="mt-4 overflow-x-auto rounded-2xl border border-ink-900/10">
            <table class="w-full text-[13px] min-w-[520px]">
                <thead>
                    <tr class="bg-ink-900 text-white text-left">
                        <th class="font-extrabold px-4 py-3">Gejala</th>
                        <th class="font-extrabold px-4 py-3">Penyebab umum</th>
                        <th class="font-extrabold px-4 py-3">Solusi</th>
                    </tr>
                </thead>
                <tbody class="font-medium text-ink-700 divide-y divide-ink-900/10">
                    <tr><td class="px-4 py-3 font-extrabold">Upload bukti gagal</td><td class="px-4 py-3">File &gt; 2 MB atau bukan gambar.</td><td class="px-4 py-3">Kompres/screenshot ulang → JPG/PNG/WebP ≤ 2 MB.</td></tr>
                    <tr><td class="px-4 py-3 font-extrabold">Tombol checkout tidak jalan</td><td class="px-4 py-3">Stok habis saat bersamaan / belum login.</td><td class="px-4 py-3">Masuk dulu, kembali ke keranjang, sesuaikan jumlah.</td></tr>
                    <tr><td class="px-4 py-3 font-extrabold">Tidak ada warung dekat</td><td class="px-4 py-3">Radius terlalu kecil / lokasi mati.</td><td class="px-4 py-3">Nyalakan GPS, perbesar radius, atau cari nama warung/alamat.</td></tr>
                    <tr><td class="px-4 py-3 font-extrabold">Lupa kata sandi</td><td class="px-4 py-3">—</td><td class="px-4 py-3">Minta tautan reset di halaman Lupa Kata Sandi (berlaku 60 menit).</td></tr>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16">
    <h2 class="reveal font-black tracking-tight text-2xl sm:text-3xl">Tanya-jawab pembeli</h2>
    <div class="mt-5 grid gap-2.5">
        @foreach([
            ['Apakah harus daftar untuk belanja?', 'Iya, tapi gratis dan cuma butuh ±30 detik — nama, email, dan kata sandi minimal 8 karakter. Tanpa daftar kamu tetap bisa jelajah katalog dan warung.'],
            ['Metode pembayaran apa saja yang diterima?', 'QRIS, e-wallet, dan transfer bank (tergantung tiap warung), plus COD untuk bayar tunai di tempat. Daftar metode yang aktif selalu tampil di halaman detail pesananmu.'],
            ['Berapa lama verifikasi pembayaran?', 'Setelah upload bukti transfer yang valid (JPG/PNG/WebP ≤ 2 MB), admin memverifikasi — umumnya dalam hitungan jam kerja. Status berubah otomatis di halaman pesanan.'],
            ['Bisakah pesan dari dua warung sekaligus?', 'Belum bisa dalam satu checkout — satu keranjang hanya untuk satu warung. Selesaikan pesanan pertama dulu, baru buat pesanan kedua untuk warung lain.'],
            ['Bagaimana cara membatalkan pesanan?', 'Selama warung belum memproses, pesanan bisa dibatalkan. Jika warung yang membatalkan atau stok habis, kamu dapat refund penuh (maks. 3×24 jam untuk e-wallet/transfer).'],
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
    <div class="reveal rounded-[28px] bg-ink-900 text-white p-6 sm:p-9 grid gap-4">
        <div>
            <h2 class="font-black tracking-tight text-2xl sm:text-3xl">Siap jajan? Warung tetanggamu sudah buka. 🛒</h2>
            <p class="text-sm font-medium text-white/60 mt-2">Rata-rata pesanan sampai dalam 14 menit untuk radius dekat.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <a href="{{ route('store.index') }}" class="text-center bg-brand-500 hover:bg-brand-600 font-extrabold text-sm px-7 py-3.5 rounded-full transition">Cari warung terdekat</a>
            <a href="{{ route('guides.seller') }}" class="text-center bg-white/10 border border-white/15 hover:bg-white/20 font-extrabold text-sm px-7 py-3.5 rounded-full transition">Punya dagangan? Baca panduan penjual →</a>
            <a href="{{ route('contact') }}" class="text-center bg-white/10 border border-white/15 hover:bg-white/20 font-extrabold text-sm px-7 py-3.5 rounded-full transition">Butuh bantuan?</a>
        </div>
    </div>
</section>
@endsection

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'HowTo',
            'name' => 'Cara Belanja di Warung Hebat',
            'description' => 'Panduan pembeli: daftar, cari warung terdekat, isi keranjang, checkout dan bayar, lacak pesanan, lalu beri rating.',
            'inLanguage' => 'id-ID',
            'step' => [
                ['@type' => 'HowToStep', 'position' => 1, 'name' => 'Daftar akun pembeli', 'text' => 'Daftar gratis dengan nama, email aktif, dan kata sandi minimal 8 karakter.'],
                ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Cari warung terdekat', 'text' => 'Nyalakan lokasi, buka halaman warung terdekat, atau jelajah lewat kategori.'],
                ['@type' => 'HowToStep', 'position' => 3, 'name' => 'Isi keranjang', 'text' => 'Tambahkan produk dari satu warung, atur jumlah, dan cek total di keranjang.'],
                ['@type' => 'HowToStep', 'position' => 4, 'name' => 'Checkout dan bayar', 'text' => 'Checkout untuk membuat pesanan, pilih metode pembayaran, bayar sesuai nominal, lalu upload bukti transfer JPG/PNG/WebP maksimal 2MB.'],
                ['@type' => 'HowToStep', 'position' => 5, 'name' => 'Lacak pesanan', 'text' => 'Pantau status di halaman Pesanan Saya; pilih diantar atau ambil sendiri.'],
                ['@type' => 'HowToStep', 'position' => 6, 'name' => 'Beri rating', 'text' => 'Setelah pesanan selesai, beri 1-5 bintang dan komentar maksimal 500 karakter.'],
            ],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Panduan', 'item' => route('guides.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Panduan Pembeli', 'item' => route('guides.buyer')],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
