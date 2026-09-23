@extends('layouts.app')

@section('title', 'Tentang Warung Hebat — Belanja Dekat, Hidup Hebat')

@section('content')
<section class="relative overflow-hidden pt-24 sm:pt-28 pb-12 sm:pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 min-h-10 text-[13px] font-extrabold text-ink-500 hover:text-ink-900">← Beranda</a>

        <p class="reveal mt-3 inline-flex text-[11px] font-extrabold tracking-[0.18em] text-brand-600 bg-brand-50 border border-brand-200 rounded-full px-3.5 py-1.5">🏪 TENTANG KAMI</p>
        <h1 class="reveal font-black tracking-tight text-[36px] sm:text-[56px] leading-[1.02] mt-3" style="--reveal-delay:80ms">Warung kecil,<br><span class="text-brand-500">dampak besar.</span></h1>
        <p class="reveal mt-5 text-[16px] sm:text-lg text-ink-500 font-medium leading-relaxed max-w-2xl" style="--reveal-delay:140ms">
            Warung Hebat adalah marketplace digital yang menghubungkan pembeli dengan warung, toko kelontong, dan UMKM di sekitar mereka. Kami percaya belanja harian tidak perlu jauh-jauh — dan uangnya sebaiknya berputar di lingkungan sendiri.
        </p>

        <dl class="reveal mt-8 grid grid-cols-3 gap-3 max-w-lg" style="--reveal-delay:200ms">
            <div class="rounded-2xl bg-white border border-ink-900/10 p-4">
                <dt class="sr-only">Warung mitra</dt>
                <dd class="font-black text-xl sm:text-2xl">2.450+</dd>
                <dd class="text-[11px] font-bold text-ink-500 leading-tight">warung rumahan</dd>
            </div>
            <div class="rounded-2xl bg-white border border-ink-900/10 p-4">
                <dt class="sr-only">Bagian untuk penjual</dt>
                <dd class="font-black text-xl sm:text-2xl">92%</dd>
                <dd class="text-[11px] font-bold text-ink-500 leading-tight">sampai ke penjual</dd>
            </div>
            <div class="rounded-2xl bg-white border border-ink-900/10 p-4">
                <dt class="sr-only">Kota</dt>
                <dd class="font-black text-xl sm:text-2xl">34</dd>
                <dd class="text-[11px] font-bold text-ink-500 leading-tight">kota & terus tumbuh</dd>
            </div>
        </dl>
    </div>
</section>

<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16">
    <div class="reveal rounded-[28px] bg-white border border-ink-900/10 p-6 sm:p-9 grid gap-4 text-[15px] font-medium leading-relaxed text-ink-700">
        <h2 class="font-black tracking-tight text-2xl sm:text-3xl text-ink-900">Cerita kami</h2>
        <p>Warung Hebat lahir dari gang-gang kecil Indonesia — dari <strong class="text-ink-900">dapur Ibu</strong> yang masakannya selalu ludes di arisan, dari <strong class="text-ink-900">teras rumah Bapak</strong> yang jadi basecamp anak komplek, dari <strong class="text-ink-900">meja lipat depan kos</strong> yang tiap malam dipadati pembeli.</p>
        <p>Mereka tak punya ruko besar. Tak punya modal raksasa. Yang mereka punya jauh lebih berharga: resep keluarga, tangan yang tak kenal lelah, dan tetangga yang percaya.</p>
        <p>Kami membangun aplikasi ini dengan satu keyakinan sederhana — <strong class="text-ink-900">rumah kecil bukan halangan.</strong> Dengan satu HP, dapur dan teras rumahmu bisa menjadi toko yang dikunjungi ribuan tetangga.</p>
        <p>Karena itu setiap rupiah yang masuk ke warung mitra kami jaga betul: komisi flat tanpa biaya bulanan, tanpa iklan berbayar yang menenggelamkan warung kecil, dan pencairan yang jelas.</p>
    </div>
</section>

<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16">
    <h2 class="reveal font-black tracking-tight text-2xl sm:text-3xl">Yang kami pegang</h2>
    <div class="mt-5 grid gap-3 sm:grid-cols-2">
        @foreach([
            ['⚡','Ringan & cepat','Halaman warung harus terbuka di bawah satu detik, bahkan di HP sederhana dengan sinyal pas-pasan.'],
            ['📍','Terdekat dulu, bukan yang bayar iklan','Urutan warung mengikuti jarak sungguhan dari lokasimu. Tidak ada slot berbayar yang menyalip.'],
            ['💰','Harga warung asli','Tanpa markup tersembunyi. Yang kamu bayar adalah harga di etalase plus ongkir yang transparan.'],
            ['🤝','Adil untuk pemilik warung','Umumnya 92% nilai pesanan sampai ke pemilik warung, dan komisi flat — bukan potongan yang naik diam-diam.'],
        ] as $i => $value)
        <div class="reveal rounded-[24px] bg-white border border-ink-900/10 p-5" style="--reveal-delay:{{ $i*80 }}ms">
            <span class="w-11 h-11 rounded-2xl bg-ink-900 text-white grid place-items-center text-xl">{{ $value[0] }}</span>
            <p class="font-extrabold text-[15px] mt-3">{{ $value[1] }}</p>
            <p class="text-[13px] font-medium text-ink-500 leading-relaxed mt-1">{{ $value[2] }}</p>
        </div>
        @endforeach
    </div>
</section>

<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16">
    <div class="reveal rounded-[28px] bg-ink-900 text-white p-6 sm:p-9 grid gap-6 relative overflow-hidden grain">
        <div>
            <h2 class="font-black tracking-tight text-2xl sm:text-3xl">Bagaimana kami bekerja</h2>
            <p class="text-sm font-medium text-white/60 mt-2 max-w-xl">Tiga langkah sederhana untuk pembeli, dan proses yang sama ringkasnya untuk pemilik warung.</p>
        </div>
        <ol class="grid gap-4 sm:grid-cols-3">
            @foreach([
                ['01','Pembeli buka aplikasi','Nyalakan lokasi, lihat warung terdekat yang sedang buka, pilih produknya.'],
                ['02','Warung siapkan pesanan','Pesanan masuk ke HP pemilik warung lengkap dengan daftar belanja dan alamat.'],
                ['03','Pesanan sampai, dana diteruskan','Pembeli atau kurir mengambil pesanan. Setelah selesai, saldo masuk ke dompet warung dan bisa dicairkan.'],
            ] as $i => $step)
            <li class="rounded-[20px] bg-white/[.06] border border-white/10 p-5" style="--reveal-delay:{{ $i*80 }}ms">
                <p class="font-black text-2xl text-brand-400">{{ $step[0] }}</p>
                <p class="font-extrabold mt-2">{{ $step[1] }}</p>
                <p class="text-[13px] font-medium text-white/60 leading-relaxed mt-1">{!! $step[2] !!}</p>
            </li>
            @endforeach
        </ol>
        <div class="flex flex-col sm:flex-row gap-2">
            <a href="{{ route('store.index') }}" class="text-center bg-brand-500 hover:bg-brand-600 font-extrabold text-sm px-7 py-3.5 rounded-full transition">Belanja dari warung terdekat</a>
            <a href="{{ route('contact') }}" class="text-center bg-white/10 border border-white/15 hover:bg-white/20 font-extrabold text-sm px-7 py-3.5 rounded-full transition">Hubungi kami</a>
        </div>
    </div>
</section>

<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-16 sm:pb-24">
    <div class="reveal rounded-[28px] bg-leaf-600 text-white p-6 sm:p-9">
        <p class="inline-flex text-[11px] font-extrabold tracking-[0.18em] bg-white/15 border border-white/20 rounded-full px-3.5 py-1.5">🏪 BUAT PEMILIK WARUNG</p>
        <h2 class="font-black tracking-tight text-2xl sm:text-3xl mt-3">Punya dagangan? Jualan di sini, gratis.</h2>
        <p class="text-white/70 font-medium text-[15px] mt-2 max-w-xl">Daftar lima menit, foto daganganmu, dan mulai terima pesanan dari tetangga sekitar. Tanpa sewa, tanpa biaya bulanan.</p>
        <a href="{{ route('register') }}" data-open-register class="mt-5 inline-flex bg-white text-leaf-700 hover:bg-ink-900 hover:text-white font-extrabold text-sm px-7 py-3.5 rounded-full transition">Daftar jadi mitra →</a>
    </div>
</section>
@endsection

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'AboutPage',
    'name' => 'Tentang Warung Hebat',
    'url' => route('about'),
    'mainEntity' => [
        '@type' => 'Organization',
        'name' => 'Warung Hebat',
        'url' => route('home'),
        'logo' => asset('og-image.png'),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
