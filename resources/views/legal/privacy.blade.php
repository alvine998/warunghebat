@extends('layouts.app')

@section('title', 'Kebijakan Privasi — Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-14 max-w-4xl mx-auto px-4 sm:px-6">
    <p class="reveal inline-flex text-[11px] font-extrabold tracking-[0.18em] text-leaf-700 bg-leaf-100 border border-leaf-500/20 rounded-full px-3.5 py-1.5">🔒 KEBIJAKAN PRIVASI</p>
    <h1 class="reveal font-black tracking-tight text-3xl sm:text-5xl mt-3" style="--reveal-delay:80ms">Datamu aman, titik.</h1>
    <p class="reveal text-ink-500 font-medium text-[15px] mt-2" style="--reveal-delay:140ms">Terakhir diperbarui: 23 September 2026 • Kami hanya meminta data yang benar-benar dibutuhkan agar jajanmu lancar.</p>

    <div class="reveal mt-6 rounded-[24px] bg-leaf-50 border border-leaf-500/25 p-5 text-[14px] font-medium text-leaf-900 leading-relaxed" style="--reveal-delay:200ms">
        <strong>Komitmen kami:</strong> datamu tidak dijual. Tidak ke pengiklan, tidak ke broker data, tidak ke siapa pun. Data hanya dipakai untuk menjalankan pesanan dan meningkatkan layanan.
    </div>

    <div class="mt-6 grid gap-3">
        @foreach([
            ['01', 'Data yang kami kumpulkan', 'Akun: nama, email, dan kata sandi (tersimpan terenkripsi/hash). Transaksi: riwayat pesanan, alamat antar, dan metode pembayaran (detail kartu tidak pernah kami simpan — diproses payment gateway). Teknis: lokasi (hanya saat kamu mengizinkan, untuk mencari warung terdekat), tipe perangkat, dan log penggunaan untuk keamanan.'],
            ['02', 'Untuk apa datamu dipakai', 'Memproses dan mengantar pesanan; menampilkan warung terdekat dari lokasimu; mencegah penipuan dan penyalahgunaan voucher; mengirim notifikasi pesanan dan info penting; serta analisis agregat (misalnya “jam tersibuk”) untuk meningkatkan layanan. Kami tidak menggunakannya untuk profiling iklan pihak ketiga.'],
            ['03', 'Dengan siapa data dibagikan', 'Hanya seperlunya: (a) Mitra warung — nama, alamat antar, dan isi pesanan agar bisa diproses; (b) penyedia pembayaran & kurir — sebatas menyelesaikan transaksi; (c) penegak hukum — hanya bila diwajibkan peraturan. Semua pihak terikat kewajiban kerahasiaan.'],
            ['04', 'Lokasi & izin perangkat', 'Akses lokasi bersifat opsional dan bisa dimatikan kapan saja dari pengaturan HP. Tanpa izin lokasi, kamu tetap bisa belanja dengan mengetik alamat manual — hanya fitur “terdekat otomatis” yang tidak aktif.'],
            ['05', 'Cookie & penyimpanan lokal', 'Kami memakai cookie sesi (wajib, agar kamu tetap masuk), preferensi (bahasa, keranjang), dan analitik agregat. Kamu bisa menghapusnya lewat pengaturan browser; sebagian fitur (misalnya tetap masuk) akan terpengaruh.'],
            ['06', 'Keamanan', 'Kata sandi di-hash (bcrypt), trafik dienkripsi (HTTPS), akses internal dibatasi peran, dan sesi kedaluwarsa otomatis. Tidak ada sistem yang 100% kebal — jika menemukan celah, laporkan ke '.$officialEmail.' dan kami menindaklanjuti maksimal 3×24 jam.'],
            ['07', 'Penyimpanan & penghapusan', 'Data akun disimpan selama akun aktif. Kamu bisa meminta salinan atau penghapusan data lewat Hubungi Kami — kami proses maksimal 14 hari kerja, kecuali data transaksi yang wajib disimpan menurut ketentuan perpajakan.'],
            ['08', 'Hak kamu', 'Kamu berhak: melihat dan memperbaiki data; menarik persetujuan (misalnya lokasi); meminta penghapusan; serta menolak pesan promosi (setiap promo selalu ada tombol berhenti). Cukup kirim email dari alamat terdaftar agar verifikasi cepat.'],
            ['09', 'Anak di bawah umur', 'Platform ditujukan untuk usia 13 tahun ke atas. Pesanan oleh anak di bawah umur harus melalui orang tua/wali. Jika kami mengetahui data anak terkumpul tanpa persetujuan wali, data tersebut akan dihapus.'],
            ['10', 'Perubahan & kontak', 'Kebijakan ini dapat diperbarui; versi terbaru selalu ada di halaman ini dengan tanggal revisi. Pertanyaan privasi? Email: '.$officialEmail.' dengan subjek “Privasi”, atau gunakan halaman Hubungi Kami.'],
        ] as $i => $s)
        <article class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6" style="--reveal-delay:{{ ($i%4)*60 }}ms">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 shrink-0 rounded-xl bg-leaf-600 text-white grid place-items-center text-[13px] font-black">{{ $s[0] }}</span>
                <h2 class="font-extrabold text-[17px]">{{ $s[1] }}</h2>
            </div>
            <p class="text-[14px] font-medium text-ink-500 leading-relaxed mt-2.5">{{ $s[2] }}</p>
        </article>
        @endforeach
    </div>

    <div class="reveal mt-6 flex flex-col sm:flex-row gap-2.5">
        <a href="{{ route('terms') }}" class="flex-1 text-center font-extrabold text-sm border-2 border-ink-900/10 hover:border-ink-900 rounded-full px-7 py-3.5 transition">← Baca Syarat & Ketentuan</a>
        <a href="{{ route('contact') }}" class="flex-1 text-center font-extrabold text-sm bg-ink-900 text-white rounded-full px-7 py-3.5 hover:bg-brand-600 transition">Minta Hapus Data Saya →</a>
    </div>
</section>
@endsection
