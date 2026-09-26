@extends('emails.layout')

@section('subject', 'Warungmu terverifikasi!')

@section('content')
<h1 style="font-size:22px;margin:0 0 12px 0;">Selamat, {{ $user->name }}! {{ $storeName }} lolos verifikasi.</h1>
<p style="font-size:14px;line-height:1.7;margin:0 0 12px 0;">
    Admin sudah memeriksa bukti kepemilikan warungmu atas nama <strong>{{ $verification->full_name }}</strong>
    dan semuanya cocok. Warungmu sekarang <strong>bisa jualan</strong> di Warung Hebat.
</p>
<p style="font-size:14px;line-height:1.7;margin:0 0 12px 0;">
    Tambahkan produk pertamamu — setiap produk akan diperiksa admin dulu sebelum tayang (biasanya &lt; 1&times;24 jam).
</p>
<p style="margin:20px 0;">
    <a href="{{ route('seller.products.create') }}" style="display:inline-block;background-color:#FF7A29;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 24px;border-radius:999px;">Tambah Produk</a>
</p>
@endsection
