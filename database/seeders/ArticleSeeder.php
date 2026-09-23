<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Editorial content for the public blog (/artikel) and the landing page.
     * Idempotent: articles are matched by slug, so re-running keeps one copy.
     */
    public function run(): void
    {
        $author = User::where('email', 'admin@warunghebat.id')->first() ?? User::orderBy('id')->first();

        if (! $author) {
            return;
        }

        foreach ($this->articles() as $article) {
            if (Article::where('slug', $article['slug'])->exists()) {
                continue;
            }

            Article::create([
                'user_id' => $author->id,
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => $article['excerpt'],
                'body' => $article['body'],
                'status' => Article::STATUS_PUBLISHED,
                'published_at' => now()->subDays($article['days_ago']),
            ]);
        }
    }

    /**
     * @return list<array{title: string, slug: string, excerpt: string, body: string, days_ago: int}>
     */
    private function articles(): array
    {
        return [
            [
                'title' => 'Cara Memilih Sembako Segar di Warung Tetangga',
                'slug' => 'cara-memilih-sembako-segar-di-warung-tetangga',
                'excerpt' => 'Beras, telur, minyak, sampai bumbu dapur — ini ciri sembako yang masih bagus saat kamu belanja di warung dekat rumah.',
                'days_ago' => 9,
                'body' => <<<'MD'
                Belanja sembako di warung tetangga punya satu keunggulan yang sulit ditandingi minimarket: **perputaran barangnya cepat**. Warung yang ramai hampir selalu punya stok baru, karena barangnya habis setiap hari.

                ## Cek sebelum bayar

                - **Beras**: ambil segenggam, pastikan butirannya tidak berbau apek dan tidak berkutu. Karung yang terbuka lama biasanya lembap di bagian dalam.
                - **Telur**: pilih yang cangkangnya utuh dan tidak lengket. Telur segar tenggelam saat direndam air; yang mengapung sebaiknya jangan dibeli.
                - **Minyak goreng**: perhatikan tanggal kedaluwarsa di leher kemasan. Minyak yang sudah keruh dan berbau tengik sebaiknya dilewati.
                - **Bumbu dapur**: bawang merah dan bawang putih yang bagus terasa padat di tangan, kulitnya tidak keriput, dan tidak bertunas.

                ## Kenali jam ramai

                Stok baru biasanya datang pagi. Kalau kamu belanja setelah subuh atau sebelum jam makan siang, pilihan sayur dan telur masih lengkap.

                > Tips: pemilik warung biasanya tahu barang mana yang baru datang. Tanya saja langsung — mereka hampir selalu jujur soal kondisi stoknya.

                ## Simpan dengan benar di rumah

                Beras sebaiknya dipindah ke wadah kedap udara, telur **tidak** perlu dicuci sebelum disimpan, dan minyak goreng dijauhkan dari sinar matahari langsung. Dengan cara ini, belanjaanmu tahan berhari-hari tanpa berubah rasa.
                MD,
            ],
            [
                'title' => 'Belanja di Warung Dekat vs Minimarket: Mana yang Lebih Hemat?',
                'slug' => 'belanja-warung-dekat-vs-minimarket',
                'excerpt' => 'Kami bandingkan harga, ongkos, dan waktu tempuh belanja kebutuhan harian di warung sekitar dengan minimarket terdekat.',
                'days_ago' => 6,
                'body' => <<<'MD'
                Pertanyaan ini sering muncul: kalau minimarketnya lebih lengkap, kenapa harus belanja di warung? Jawabannya ada di **biaya total**, bukan hanya harga di rak.

                ## Tiga hal yang sering lupa dihitung

                1. **Ongkos perjalanan.** Minimarket biasanya berada di jalan besar, sekitar 1–3 km dari rumah. Pulang-pergi dengan ojek online berarti Rp 8.000–15.000 yang tidak kamu keluarkan bila belanja di warung depan gang.
                2. **Waktu.** Antre di kasir minimarket bisa 10 menit, sementara di warung tetangga kamu sering langsung dilayani.
                3. **Jumlah minimal belanja.** Aplikasi retail besar biasanya punya minimum pembelian agar gratis ongkir. Di warung, beli satu telur pun dilayani.

                ## Harga satuan

                Untuk barang curah — beras, minyak, gula — harga warung sering **lebih murah per liter atau per kilogram**, karena tidak ada biaya distribusi ke gerai ber-AC.

                ## Kapan minimarket menang?

                Untuk merek tertentu, produk impor, dan promo mingguan, minimarket memang bisa lebih murah. Jadi cara paling hemat adalah **belanja rutin di warung, sisanya di minimarket** saat ada kebutuhan khusus.

                Belanja harian di warung juga berarti uangnya berputar di lingkunganmu sendiri — tetangga yang jualan bisa kulakan lagi besok pagi.
                MD,
            ],
            [
                'title' => '5 Tips Warung Rumahan Biar Kebanjiran Pesanan Online',
                'slug' => 'tips-warung-rumahan-kebanjiran-pesanan-online',
                'excerpt' => 'Dari foto produk yang rapi sampai jam buka yang konsisten — ini yang paling berpengaruh saat warungmu mulai jualan lewat aplikasi.',
                'days_ago' => 4,
                'body' => <<<'MD'
                Banyak warung menjual barang bagus tapi sepi pesanan online. Biasanya bukan karena produknya, tapi karena pembeli **tidak punya cukup informasi** untuk memutuskan.

                ## 1. Foto produk, bukan foto barangnya saja

                Ambil foto dengan cahaya dari jendela, latar polos, dan produk memenuhi sekitar dua pertiga bingkai. Foto gelap dan miring membuat pembeli ragu, sedangkan foto yang jelas langsung menaikkan jumlah klik.

                ## 2. Tulis nama produk seperti pembeli mencarinya

                "Sembako Paket Hemat" kurang dicari orang. "Beras Setra Ramos 5 kg", "Telur Ayam 1 kg", dan "Minyak Goreng 2 Liter" jauh lebih mudah ditemukan dan lebih cepat dipahami.

                ## 3. Jaga jam buka tetap konsisten

                Tentukan jam buka yang benar-benar sanggup kamu layani, lalu patuhi setiap hari. Pembeli akan kembali pada jam yang mereka ingat — dan algoritma kami mengurutkan warung yang sedang buka lebih dulu.

                ## 4. Siapkan stok untuk jam ramai

                Kalau pesananmu menumpuk antara pukul 11.00–13.00 dan 17.00–20.00, pastikan stok produk terlaris sudah ditambah sebelum jam itu.

                ## 5. Balas cepat dan catat pesanan

                Kecepatan membalas chat berpengaruh besar pada keputusan pembeli. Pakai satu buku catatan atau aplikasi sederhana untuk mencatat pesanan yang harus dikemas, supaya tidak ada yang terlewat saat warung sedang ramai.

                > Mulai dari satu perubahan kecil hari ini: perbaiki tiga foto produk terlarismu. Efeknya biasanya terasa dalam sepekan.
                MD,
            ],
            [
                'title' => 'Panduan Menyimpan Frozen Food agar Tetap Aman Sampai Rumah',
                'slug' => 'panduan-menyimpan-frozen-food',
                'excerpt' => 'Nugget, dimsum, dan bakso bisa rusak sebelum sampai dapur kalau salah di perjalanan. Ikuti aturan suhu dan waktu ini.',
                'days_ago' => 2,
                'body' => <<<'MD'
                Makanan beku aman selama suhunya tetap di bawah **-18°C**. Masalahnya muncul saat perjalanan pulang, ketika suhu mulai naik dan kristal es mencair.

                ## Aturan perjalanan

                - Bawa tas pendingin atau setidaknya tas berlapis bila perjalanan lebih dari 15 menit.
                - Jangan menaruh frozen food di bagasi motor yang panas; taruh di depan bersama barang lain yang tidak berat.
                - Belanja frozen food **terakhir**, setelah sayur dan barang kering.

                ## Tanda produk sudah tidak layak

                1. Kemasan menggembung atau berisi banyak bunga es besar di dalamnya.
                2. Produk terasa lunak saat ditekan, bukan keras membeku.
                3. Ada bau asam atau warna yang berubah setelah dimasak.

                ## Setelah sampai rumah

                Masukkan segera ke freezer, bukan ke kulkas bawah. Kalau produk sudah mulai mencair, jangan dibekukan ulang — masak langsung dan habiskan hari itu.

                Untuk kebutuhan mingguan, beli dalam porsi kecil yang habis dalam 3–4 hari. Freezer yang tidak penuh berlebihan juga bekerja lebih efisien dan lebih hemat listrik.
                MD,
            ],
        ];
    }
}
