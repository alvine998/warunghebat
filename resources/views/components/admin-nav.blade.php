{{-- Backoffice sections. Shared by the desktop sidebar and the mobile drawer so
     both always list the same pages in the same order. --}}
@php
    $sections = [
        ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'icon' => 'chart', 'label' => 'Ringkasan'],
        ['route' => 'admin.users', 'pattern' => 'admin.users', 'icon' => 'users', 'label' => 'Pengguna'],
        ['route' => 'admin.warungs', 'pattern' => 'admin.warungs', 'icon' => 'store', 'label' => 'Warung'],
        ['route' => 'admin.products', 'pattern' => 'admin.products*', 'icon' => 'package', 'label' => 'Produk'],
        ['route' => 'admin.articles', 'pattern' => 'admin.articles*', 'icon' => 'document', 'label' => 'Artikel'],
    ];

    $finance = [
        ['route' => 'admin.finance', 'pattern' => 'admin.finance*', 'icon' => 'chart', 'label' => 'Overview'],
        ['route' => 'admin.payments', 'pattern' => 'admin.payments*', 'icon' => 'document', 'label' => 'Pembayaran'],
        ['route' => 'admin.orders', 'pattern' => 'admin.orders*', 'icon' => 'basket', 'label' => 'Pesanan'],
        ['route' => 'admin.withdrawals', 'pattern' => 'admin.withdrawals*', 'icon' => 'ticket', 'label' => 'Penarikan'],
        ['route' => 'admin.payment-methods', 'pattern' => 'admin.payment-methods*', 'icon' => 'wallet', 'label' => 'Metode Bayar'],
        ['route' => 'admin.inquiries', 'pattern' => 'admin.inquiries*', 'icon' => 'chat', 'label' => 'Pesan Masuk'],
        ['route' => 'admin.settings', 'pattern' => 'admin.settings*', 'icon' => 'bolt', 'label' => 'Pengaturan'],
    ];
@endphp
@foreach($sections as $item)
<a href="{{ route($item['route']) }}" @class([
    'flex items-center gap-3 px-4 py-3 rounded-2xl transition',
    'bg-white text-ink-900' => request()->routeIs($item['pattern']),
    'text-white/80 hover:bg-white/10 hover:text-white' => ! request()->routeIs($item['pattern']),
])><x-icon name="{{ $item['icon'] }}" class="w-5 h-5" /> {{ $item['label'] }}</a>
@endforeach

<p class="px-4 pt-5 pb-1 text-[10px] font-extrabold tracking-[0.18em] text-white/40">KEUANGAN</p>
@foreach($finance as $item)
<a href="{{ route($item['route']) }}" @class([
    'flex items-center gap-3 px-4 py-3 rounded-2xl transition',
    'bg-white text-ink-900' => request()->routeIs($item['pattern']),
    'text-white/80 hover:bg-white/10 hover:text-white' => ! request()->routeIs($item['pattern']),
])><x-icon name="{{ $item['icon'] }}" class="w-5 h-5" /> {{ $item['label'] }}</a>
@endforeach

<a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 mt-1 rounded-2xl text-white/80 hover:bg-white/10 hover:text-white transition"><x-icon name="globe" class="w-5 h-5" /> Lihat Situs</a>
