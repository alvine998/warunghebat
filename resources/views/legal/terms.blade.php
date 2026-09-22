@extends('layouts.app')

@section('title', 'Syarat & Ketentuan — Warung Hebat')

@section('content')
<section class="pt-24 sm:pt-28 pb-14 max-w-4xl mx-auto px-4 sm:px-6">
    <p class="reveal inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-700 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">📜 SYARAT & KETENTUAN</p>
    <h1 class="reveal font-black tracking-tight text-3xl sm:text-5xl mt-3" style="--reveal-delay:80ms">Aturan main yang adil buat semua.</h1>
    <p class="reveal text-ink-500 font-medium text-[15px] mt-2" style="--reveal-delay:140ms">Terakhir diperbarui: 23 September 2026 • Berlaku untuk pembeli, penjual, dan pengunjung Warung Hebat.</p>

    <div class="reveal mt-6 rounded-[24px] bg-amber-50 border border-amber-200 p-5 text-[14px] font-medium text-amber-900 leading-relaxed" style="--reveal-delay:200ms">
        <strong>Ringkasan 30 detik:</strong> jajan & jualan dengan jujur, bayar sesuai yang disepakati, dan hormati warung tetanggamu. Sisanya adalah detail hukum di bawah ini.
    </div>

    <div class="mt-6 grid gap-3">
        @foreach([
            ['01', 'Definisi', '“Platform” adalah situs dan aplikasi Warung Hebat. “Pembeli” adalah pengguna yang berbelanja. “Mitra/Penjual” adalah pemilik warung atau toko yang menjual melalui Platform. “Pesanan” adalah transaksi pembelian yang dibuat Pembeli kepada Mitra melalui Platform.'],
            ['02', 'Akun Pengguna', 'Untuk berbelanja atau berjualan kamu wajib membuat akun dengan data yang benar (nama dan email aktif). Kamu bertanggung jawab menjaga kerahasiaan kata sandi. Satu orang hanya boleh memiliki satu akun pembeli. Akun yang terbukti fiktif, mencuri identitas, atau disalahgunakan dapat ditangguhkan tanpa pemberitahuan.'],
            ['03', 'Pemesanan & Harga', 'Harga yang tampil di etalase adalah harga final dari Mitra, belum termasuk ongkos kirim dan biaya layanan (jika ada) yang ditampilkan transparan sebelum bayar. Pesanan dianggap sah setelah pembayaran terkonfirmasi. Stok mengikuti ketersediaan di warung — jika barang habis, Mitra wajib menawarkan pengganti atau pengembalian dana.'],
            ['04', 'Pembayaran', 'Kami menerima QRIS, e-wallet, transfer bank, dan COD (bayar di tempat) sesuai ketersediaan di tiap warung. Dana untuk pesanan prabayar diteruskan ke Mitra setelah pesanan selesai. Bukti pembayaran digital (struk) tersimpan di akunmu.'],
            ['05', 'Pengiriman & Pengambilan', 'Pesanan dapat diantar kurir (estimasi 10–20 menit untuk radius dekat) atau diambil sendiri di warung. Risiko keterlambatan akibat cuaca, lalu lintas, atau force majeure di luar tanggung jawab kami, namun kamu berhak atas pembatalan dan refund sesuai kebijakan.'],
            ['06', 'Pembatalan & Pengembalian Dana', 'Pembeli dapat membatalkan sebelum warung memproses pesanan. Jika warung membatalkan atau barang tidak tersedia, dana dikembalikan penuh maksimal 3×24 jam (e-wallet/transfer) atau tunai di tempat untuk COD. Komplain kualitas disampaikan maksimal 1×24 jam setelah pesanan diterima.'],
            ['07', 'Aturan untuk Mitra', 'Mitra wajib: (a) menjual barang legal, aman, dan sesuai deskripsi; (b) menjaga kebersihan dan keamanan pangan; (c) memproses pesanan tepat waktu dan jujur soal stok; (d) tidak menaikkan harga sepihak setelah pesanan dibayar. Komisi 0% berlaku 3 bulan pertama, setelahnya mengikuti skema yang diinformasikan di dashboard Mitra. Pelanggaran berulang berakibat penonaktifan warung.'],
            ['08', 'Larangan', 'Dilarang menggunakan Platform untuk: barang ilegal (narkoba, senjata, barang curian), penipuan, ujaran kebencian, spam/ulasan palsu, peretasan, atau penyalahgunaan voucher/promo. Kami dapat membatasi atau menutup akun yang melanggar.'],
            ['09', 'Ulasan & Konten', 'Ulasan harus jujur berdasarkan pengalaman nyata. Dengan mengunggah foto/ulasan, kamu memberi kami lisensi non-eksklusif untuk menampilkannya di Platform. Konten yang melanggar akan dihapus.'],
            ['10', 'Batasan Tanggung Jawab', 'Platform berperan sebagai perantara antara Pembeli dan Mitra. Kualitas dan keamanan barang menjadi tanggung jawab Mitra sebagai penjual. Tanggung jawab kami terbatas pada nilai transaksi yang bersangkutan, kecuali diwajibkan lain oleh hukum yang berlaku.'],
            ['11', 'Perubahan Ketentuan', 'Kami dapat memperbarui halaman ini sewaktu-waktu. Perubahan penting akan diumumkan lewat email atau notifikasi aplikasi. Penggunaan berkelanjutan setelah perubahan berarti kamu menyetujuinya.'],
            ['12', 'Hukum & Kontak', 'Syarat ini tunduk pada hukum Republik Indonesia. Sengketa diselesaikan musyawarah dulu; jika buntu, melalui pengadilan yang berwenang. Pertanyaan? Hubungi kami via halaman Hubungi Kami atau email halo@warunghebat.id.'],
        ] as $i => $s)
        <article class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5 sm:p-6" style="--reveal-delay:{{ ($i%4)*60 }}ms">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 shrink-0 rounded-xl bg-ink-900 text-white grid place-items-center text-[13px] font-black">{{ $s[0] }}</span>
                <h2 class="font-extrabold text-[17px]">{{ $s[1] }}</h2>
            </div>
            <p class="text-[14px] font-medium text-ink-500 leading-relaxed mt-2.5">{{ $s[2] }}</p>
        </article>
        @endforeach
    </div>

    <div class="reveal mt-6 flex flex-col sm:flex-row gap-2.5">
        <a href="{{ route('privacy') }}" class="flex-1 text-center font-extrabold text-sm border-2 border-ink-900/10 hover:border-ink-900 rounded-full px-7 py-3.5 transition">🔒 Baca Kebijakan Privasi →</a>
        <a href="{{ route('contact') }}" class="flex-1 text-center font-extrabold text-sm bg-ink-900 text-white rounded-full px-7 py-3.5 hover:bg-brand-600 transition">Ada pertanyaan? Hubungi Kami →</a>
    </div>
</section>
@endsection
