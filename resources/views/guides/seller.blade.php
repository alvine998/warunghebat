@extends('layouts.app')

@section('title', 'Panduan Penjual — Cara Jualan di Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-10 max-w-4xl mx-auto px-4 sm:px-6">
    <nav class="reveal text-[13px] font-bold text-ink-500" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-ink-900">Beranda</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('guides.index') }}" class="hover:text-ink-900">Panduan</a>
        <span class="mx-1.5">/</span>
        <span class="text-ink-900">Penjual</span>
    </nav>

    <p class="reveal mt-4 inline-flex text-[11px] font-extrabold tracking-[0.18em] text-leaf-700 bg-leaf-100 border border-leaf-500/20 rounded-full px-3.5 py-1.5">🏪 PANDUAN PENJUAL</p>
    <h1 class="reveal font-black tracking-tight text-3xl sm:text-5xl mt-3" style="--reveal-delay:80ms">Buka warung online dalam 5 menit.</h1>
    <p class="reveal text-ink-500 font-medium text-[15px] sm:text-lg mt-3 max-w-2xl leading-relaxed" style="--reveal-delay:140ms">
        Daftar gratis, lengkapi warung, tambah produk, lolos verifikasi admin — dan terima pesanan dari tetangga sekitar. <strong class="text-ink-900">Komisi 0% untuk 3 bulan pertama</strong>, tanpa sewa, tanpa biaya bulanan.
    </p>

    <div class="reveal mt-5 flex flex-wrap gap-2 text-[13px] font-extrabold" style="--reveal-delay:200ms">
        <span class="inline-flex items-center gap-1.5 bg-leaf-600 text-white rounded-full px-4 py-2">⏱️ ±5 menit sampai tayang*</span>
        <span class="inline-flex items-center gap-1.5 bg-white border border-ink-900/15 rounded-full px-4 py-2">📱 1 HP + foto dagangan</span>
        <span class="inline-flex items-center gap-1.5 bg-white border border-ink-900/15 rounded-full px-4 py-2">💰 92% ke pemilik warung</span>
    </div>
    <p class="reveal mt-2 text-[12px] font-semibold text-ink-500" style="--reveal-delay:220ms">*Pengisian formulir ±5 menit; verifikasi produk oleh admin maksimal 1×24 jam kerja.</p>

    <div class="reveal mt-6 rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6" style="--reveal-delay:240ms">
        <p class="text-[11px] font-extrabold tracking-[0.18em] text-ink-500">DAFTAR ISI</p>
        <ol class="mt-3 grid sm:grid-cols-2 gap-2 text-[14px] font-bold">
            <li><a href="#daftar" class="hover:text-leaf-700">1. Daftar sebagai penjual</a></li>
            <li><a href="#warung" class="hover:text-leaf-700">2. Lengkapi profil warung</a></li>
            <li><a href="#produk" class="hover:text-leaf-700">3. Tambah produk pertama</a></li>
            <li><a href="#verifikasi" class="hover:text-leaf-700">4. Lolos verifikasi admin</a></li>
            <li><a href="#kelola" class="hover:text-leaf-700">5. Kelola warung harian</a></li>
            <li><a href="#dompet" class="hover:text-leaf-700">6. Dompet & pencairan dana</a></li>
        </ol>
    </div>
</section>

<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16 grid gap-3">
    {{-- STEP 1 --}}
    <article id="daftar" class="reveal scroll-mt-28 rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-leaf-600 text-white grid place-items-center font-black">1</span>
            <div>
                <h2 class="font-extrabold text-lg sm:text-xl">Daftar sebagai penjual</h2>
                <p class="text-[12px] font-bold text-ink-500">⏱️ ±1 menit • gratis</p>
            </div>
        </div>
        <ol class="mt-4 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-decimal list-inside marker:font-extrabold">
            <li>Buka halaman <a href="{{ route('register') }}" data-open-register class="font-extrabold text-leaf-700 underline underline-offset-4">Daftar</a>.</li>
            <li>Isi <strong>nama</strong>, <strong>email aktif</strong>, dan <strong>kata sandi minimal 8 karakter</strong> (plus konfirmasinya).</li>
            <li>Pada pilihan peran, pilih <strong>Penjual</strong> — bukan Pembeli. Ini yang membuka menu warung, produk, dan dompet di dashboard.</li>
            <li>Klik <strong>Daftar</strong> — kamu langsung masuk dan diarahkan ke halaman <strong>Verifikasi Warung (KYC)</strong>.</li>
        </ol>
        <div class="mt-4 rounded-2xl bg-leaf-50 border border-leaf-500/30 p-4 text-[13px] font-medium text-leaf-700 leading-relaxed">
            🛡️ <strong>Wajib KYC:</strong> penjual harus membuktikan warung miliknya (NIK + nama KTP + foto KTP + selfie pegang KTP + foto depan warung). Admin memeriksa maks. 1×24 jam — <strong>warung baru bisa jualan setelah KYC disetujui</strong>.
        </div>
        <div class="mt-4 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-[13px] font-medium text-amber-900 leading-relaxed">
            ⚠️ <strong>Salah pilih peran?</strong> Akun yang sudah terdaftar sebagai pembeli tidak bisa diubah sendiri — hubungi CS via <a href="{{ route('contact') }}" class="font-extrabold underline">Hubungi Kami</a> dengan menyertakan email akunmu.
        </div>
    </article>

    {{-- STEP 2 --}}
    <article id="warung" class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-leaf-600 text-white grid place-items-center font-black">2</span>
            <div>
                <h2 class="font-extrabold text-lg sm:text-xl">Lengkapi profil warung</h2>
                <p class="text-[12px] font-bold text-ink-500">⏱️ ±2 menit • Dashboard → Pengaturan Warung</p>
            </div>
        </div>
        <ol class="mt-4 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-decimal list-inside marker:font-extrabold">
            <li>Dari dashboard penjual, buka <strong>Pengaturan Warung</strong> (atau menu <strong>Warung Saya</strong>).</li>
            <li>Isi <strong>nama warung</strong> (maks. 80 karakter — mis. “Gorengan Bang Jago”), <strong>deskripsi</strong> singkat yang menjual (maks. 1.000 karakter), dan <strong>alamat lengkap</strong> dengan patokan.</li>
            <li>Isi <strong>titik lokasi (latitude & longitude) — wajib</strong> — ini yang menentukan warungmu muncul di “terdekat” siapa dan tampil di peta. Klik peta atau tekan “Gunakan lokasi saya”. Makin akurat, makin tepat sasaran.</li>
            <li>Isi <strong>nomor HP/WA aktif</strong>, <strong>jam buka & jam tutup</strong> (format JJ:MM, mis. 07:00–21:00).</li>
            <li>Upload <strong>foto warung</strong>: JPG, PNG, atau WebP, <strong>maks. 2 MB</strong>. Foto terang dari depan paling dipercaya pembeli.</li>
            <li>Klik <strong>Simpan</strong>. Link warungmu (slug) dibuat otomatis dari nama warung dan bisa dibagikan ke mana saja.</li>
        </ol>
        <div class="mt-4 rounded-2xl bg-cream-200/60 border border-ink-900/10 p-4 text-[13px] font-medium text-ink-700 leading-relaxed">
            💡 <strong>Checklist warung laris:</strong> nama jelas + foto terang + alamat berpatokan + jam buka akurat + nomor WA yang fast-respons. Warung dengan profil lengkap dipesan 3× lebih sering.
        </div>
    </article>

    {{-- STEP 3 --}}
    <article id="produk" class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-leaf-600 text-white grid place-items-center font-black">3</span>
            <div>
                <h2 class="font-extrabold text-lg sm:text-xl">Tambah produk pertama</h2>
                <p class="text-[12px] font-bold text-ink-500">⏱️ ±1 menit per produk • Dashboard → Produk → Tambah</p>
            </div>
        </div>
        <ol class="mt-4 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-decimal list-inside marker:font-extrabold">
            <li>Dari dashboard, buka <strong>Produk → Tambah Produk</strong>.</li>
            <li>Isi <strong>nama produk</strong> (maks. 120 karakter — spesifik lebih laku, mis. “Nasi Goreng Tek-Tek Telur” bukan sekadar “Nasi Goreng”).</li>
            <li>Isi <strong>harga</strong> (angka Rupiah, tanpa titik — mis. 15000) dan <strong>stok</strong> awal yang jujur. Stok 0 = tidak bisa dipesan.</li>
            <li>Pilih <strong>kategori</strong>: Makanan, Minuman, Sembako, Jajanan, Frozen, Harian, atau Lainnya. Kategori yang tepat bikin produkmu ketemu di filter.</li>
            <li>Tulis <strong>deskripsi</strong> seperlunya (maks. 2.000 karakter): isi, porsi, level pedas, atau catatan alergi.</li>
            <li>Upload <strong>1 foto produk</strong> — <strong>wajib</strong>, format JPG/PNG/WebP <strong>maks. 2 MB</strong>. Foto asli daganganmu, bukan dari internet.</li>
            <li>Klik <strong>Simpan</strong>. Produk masuk antre verifikasi (langkah 4).</li>
            <li>Mau ikut <strong>flash sale</strong>? Isi <strong>Harga promo</strong> (harus lebih kecil dari harga normal) dan opsional isi <strong>Mulai/Berakhir promo</strong>. Produk yang disetujui otomatis tampil di Flash Sale beranda dengan label −% dan hitung mundur.</li>
        </ol>
        <div class="mt-4 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-[13px] font-medium text-amber-900 leading-relaxed">
            📸 <strong>Foto yang lolos & laku:</strong> terang, fokus ke makanan, tanpa watermark, tanpa teks promo menutupi produk. Produk difoto ulang tiap ada perubahan tampilan — sekalian update stoknya.
        </div>
    </article>

    {{-- STEP 4 --}}
    <article id="verifikasi" class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-brand-500 text-white grid place-items-center font-black">4</span>
            <div>
                <h2 class="font-extrabold text-lg sm:text-xl">Lolos verifikasi admin</h2>
                <p class="text-[12px] font-bold text-ink-500">maks. 1×24 jam kerja • pantau di halaman Produk</p>
            </div>
        </div>
        <p class="mt-4 text-[14px] font-medium text-ink-700 leading-relaxed">Setiap produk baru — dan setiap produk yang kamu <strong>ubah</strong> — kembali ke antre verifikasi. Ini yang menjaga katalog tetap jujur dan aman buat pembeli.</p>
        <div class="mt-4 overflow-x-auto rounded-2xl border border-ink-900/10">
            <table class="w-full text-[13px] min-w-[520px]">
                <thead>
                    <tr class="bg-ink-900 text-white text-left">
                        <th class="font-extrabold px-4 py-3">Status</th>
                        <th class="font-extrabold px-4 py-3">Artinya</th>
                        <th class="font-extrabold px-4 py-3">Aksi kamu</th>
                    </tr>
                </thead>
                <tbody class="font-medium text-ink-700 divide-y divide-ink-900/10">
                    <tr><td class="px-4 py-3 font-extrabold text-amber-600">⏳ Menunggu</td><td class="px-4 py-3">Antre diperiksa admin.</td><td class="px-4 py-3">Tunggu; pastikan foto & deskripsi sudah benar.</td></tr>
                    <tr><td class="px-4 py-3 font-extrabold text-leaf-600">✅ Disetujui</td><td class="px-4 py-3">Tayang di etalase & bisa dipesan.</td><td class="px-4 py-3">Jaga stok & jam buka. Promosikan link warungmu!</td></tr>
                    <tr><td class="px-4 py-3 font-extrabold text-red-600">❌ Ditolak</td><td class="px-4 py-3">Tidak lolos — alasan tampil di halaman produk.</td><td class="px-4 py-3">Perbaiki sesuai alasan, simpan → kembali antre.</td></tr>
                </tbody>
            </table>
        </div>
        <ul class="mt-4 grid gap-2 text-[13px] font-medium text-ink-700 leading-relaxed list-disc list-inside marker:text-brand-500">
            <li><strong>Alasan penolakan umum:</strong> foto blur/gelap, foto bukan dagangan sendiri, harga tidak wajar, deskripsi menyesatkan, atau barang terlarang.</li>
            <li><strong>Dilarang keras:</strong> barang ilegal, penipuan, harga berubah sepihak setelah pesanan dibayar. Pelanggaran berulang = warung dinonaktifkan.</li>
        </ul>
    </article>

    {{-- STEP 5 --}}
    <article id="kelola" class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-leaf-600 text-white grid place-items-center font-black">5</span>
            <div>
                <h2 class="font-extrabold text-lg sm:text-xl">Kelola warung harian</h2>
                <p class="text-[12px] font-bold text-ink-500">rutinitas 2 menit tiap pagi</p>
            </div>
        </div>
        <ul class="mt-4 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-disc list-inside marker:text-leaf-600">
            <li><strong>Buka/tutup warung</strong> dari dashboard setiap hari. Tutup saat libur atau stok habis — jangan biarkan pembeli memesan warung yang sebenarnya tutup.</li>
            <li><strong>Update stok</strong> sebelum jam ramai. Stok menipis? Kecilkan angkanya; habis? Nol-kan biar tombol beli mati otomatis.</li>
            <li><strong>Proses pesanan masuk tepat waktu.</strong> Jika barang habis mendadak, tawarkan pengganti atau batalkan jujur — pembeli mendapat refund penuh.</li>
            <li><strong>Balas rating & jaga kebersihan.</strong> Rating tinggi menaikkan posisi warungmu; makanan harus aman dan sesuai deskripsi.</li>
        </ul>
    </article>

    {{-- STEP 6 --}}
    <article id="dompet" class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-7">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-2xl bg-ink-900 text-white grid place-items-center font-black">6</span>
            <div>
                <h2 class="font-extrabold text-lg sm:text-xl">Dompet & pencairan dana</h2>
                <p class="text-[12px] font-bold text-ink-500">Dashboard → Dompet • transparan per transaksi</p>
            </div>
        </div>
        <ol class="mt-4 grid gap-2.5 text-[14px] font-medium text-ink-700 leading-relaxed list-decimal list-inside marker:font-extrabold">
            <li><strong>Pahami alurnya:</strong> pembeli bayar → dana ditahan platform (status <em>ditahan</em>) → setelah pesanan selesai, dana masuk <strong>saldo dompet</strong> warung.</li>
            <li>Buka halaman <strong>Dompet</strong> untuk melihat: saldo tersedia, dana yang masih ditahan, riwayat transaksi, dan riwayat penarikan.</li>
            <li>Klik <strong>Ajukan Penarikan</strong>, isi <strong>nominal</strong> (dalam batas minimal–maksimal yang tampil di halaman), <strong>nama bank/e-wallet</strong>, <strong>nomor rekening</strong>, <strong>nama pemilik rekening</strong>, dan catatan (opsional).</li>
            <li>Klik kirim — saldo langsung <strong>ditahan</strong> sebesar nominal pengajuan. Admin memproses ke rekeningmu; status berubah menjadi <strong>Dibayar</strong> (atau <strong>Ditolak</strong> dengan alasan jika data salah).</li>
        </ol>
        <div class="mt-4 rounded-2xl bg-cream-200/60 border border-ink-900/10 p-4 text-[13px] font-medium text-ink-700 leading-relaxed">
            💰 <strong>Biaya:</strong> daftar gratis + <strong>0% komisi 3 bulan pertama</strong>. Setelah itu komisi flat kecil per transaksi — tanpa sewa, tanpa biaya bulanan. Skema yang berlaku selalu tampil di dashboard mitra.
        </div>
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
                    <tr><td class="px-4 py-3 font-extrabold">Produk tidak tampil</td><td class="px-4 py-3">Masih menunggu / ditolak / stok 0.</td><td class="px-4 py-3">Cek status di halaman Produk; perbaiki alasan penolakan; isi stok &gt; 0.</td></tr>
                    <tr><td class="px-4 py-3 font-extrabold">Upload foto gagal</td><td class="px-4 py-3">File &gt; 2 MB atau bukan JPG/PNG/WebP.</td><td class="px-4 py-3">Kompres di bawah 2 MB dan simpan sebagai JPG/PNG/WebP.</td></tr>
                    <tr><td class="px-4 py-3 font-extrabold">Warung tidak ditemukan</td><td class="px-4 py-3">Warung dalam posisi tutup / lokasi kosong.</td><td class="px-4 py-3">Aktifkan status Buka; lengkapi latitude & longitude.</td></tr>
                    <tr><td class="px-4 py-3 font-extrabold">Penarikan ditolak</td><td class="px-4 py-3">Nominal di luar batas / data rekening salah / saldo kurang.</td><td class="px-4 py-3">Samakan nominal dengan batas di halaman Dompet; cek nama & nomor rekening.</td></tr>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16">
    <h2 class="reveal font-black tracking-tight text-2xl sm:text-3xl">Tanya-jawab penjual</h2>
    <div class="mt-5 grid gap-2.5">
        @foreach([
            ['Berapa modal untuk mulai jualan?', 'Nol rupiah. Pendaftaran gratis, tidak ada sewa dan tidak ada biaya bulanan. Kamu hanya butuh HP, foto dagangan, dan alamat warung yang jelas.'],
            ['Kapan warung saya bisa menerima pesanan?', 'Begitu minimal satu produk berstatus Disetujui dan status warung Buka. Pengisian profil ±5 menit; verifikasi produk oleh admin maksimal 1×24 jam kerja.'],
            ['Kenapa setiap edit produk harus verifikasi ulang?', 'Agar katalog tetap jujur — harga, foto, dan deskripsi yang berubah dicek ulang admin sebelum tayang. Jadi pastikan setiap perubahan sudah benar sebelum disimpan.'],
            ['Bagaimana dan kapan dana cair?', 'Dana pesanan yang selesai masuk ke saldo dompet warung. Ajukan penarikan dari halaman Dompet ke bank/e-wallet-mu; saldo ditahan saat pengajuan dan dicairkan setelah admin memproses. Batas minimal–maksimal tampil di halaman Dompet.'],
            ['Bolehkah menaikkan harga setelah pesanan dibayar?', 'Tidak boleh. Harga yang tampil saat checkout adalah harga final. Menaikkan harga sepihak setelah dibayar termasuk pelanggaran dan bisa membuat warung dinonaktifkan.'],
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
    <div class="reveal rounded-[28px] bg-leaf-700 text-white p-6 sm:p-9 grid gap-4 relative overflow-hidden">
        <div>
            <h2 class="font-black tracking-tight text-2xl sm:text-3xl">Dapur dan terasmu sudah cukup. Buka warungmu hari ini. 🏪</h2>
            <p class="text-sm font-medium text-white/70 mt-2">Gratis daftar • 0% komisi 3 bulan pertama • Grup seller + pelatihan digital.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <a href="{{ route('register') }}" data-open-register class="text-center bg-white text-leaf-700 hover:bg-ink-900 hover:text-white font-extrabold text-sm px-7 py-3.5 rounded-full transition">Daftar Jadi Mitra →</a>
            <a href="{{ route('guides.buyer') }}" class="text-center bg-white/15 border border-white/20 hover:bg-white/25 font-extrabold text-sm px-7 py-3.5 rounded-full transition">Lihat panduan pembeli →</a>
            <a href="{{ route('contact') }}" class="text-center bg-white/15 border border-white/20 hover:bg-white/25 font-extrabold text-sm px-7 py-3.5 rounded-full transition">Butuh bantuan?</a>
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
            'name' => 'Cara Jualan di Warung Hebat',
            'description' => 'Panduan penjual: daftar sebagai penjual, lengkapi warung, tambah produk, lolos verifikasi admin, kelola warung, dan cairkan saldo dompet.',
            'inLanguage' => 'id-ID',
            'step' => [
                ['@type' => 'HowToStep', 'position' => 1, 'name' => 'Daftar sebagai penjual', 'text' => 'Daftar gratis dengan nama, email, kata sandi minimal 8 karakter, dan pilih peran Penjual.'],
                ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Lengkapi profil warung', 'text' => 'Isi nama, deskripsi, alamat, titik lokasi, nomor HP/WA, jam buka, dan foto warung maksimal 2MB.'],
                ['@type' => 'HowToStep', 'position' => 3, 'name' => 'Tambah produk', 'text' => 'Isi nama, harga, stok, kategori, deskripsi, dan 1 foto produk JPG/PNG/WebP maksimal 2MB.'],
                ['@type' => 'HowToStep', 'position' => 4, 'name' => 'Lolos verifikasi admin', 'text' => 'Pantau status menunggu, disetujui, atau ditolak di halaman Produk; perbaiki sesuai alasan jika ditolak.'],
                ['@type' => 'HowToStep', 'position' => 5, 'name' => 'Kelola warung harian', 'text' => 'Atur status buka/tutup, update stok, dan proses pesanan tepat waktu.'],
                ['@type' => 'HowToStep', 'position' => 6, 'name' => 'Cairkan dana', 'text' => 'Ajukan penarikan dari halaman Dompet ke bank atau e-wallet dengan data rekening yang benar.'],
            ],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Panduan', 'item' => route('guides.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Panduan Penjual', 'item' => route('guides.seller')],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
