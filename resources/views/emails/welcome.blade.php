@extends('emails.layout')

@section('subject', 'Selamat datang di Warung Hebat!')

@section('content')
<h1 style="font-size:22px;margin:0 0 12px 0;">Halo, {{ $user->name }}! Selamat datang di Warung Hebat.</h1>
<p style="font-size:14px;line-height:1.7;margin:0 0 12px 0;">
    Akunmu sudah jadi. Belanja kebutuhan harian dari warung tetangga — jarak dekat, harga warung asli.
</p>
@if ($user->role === 'penjual')
    <p style="font-size:14px;line-height:1.7;margin:0 0 12px 0;">
        Kamu daftar sebagai <strong>penjual</strong>. Langkah berikutnya: verifikasi kepemilikan warungmu
        (KTP + selfie + foto depan warung) agar bisa mulai jualan.
    </p>
    <p style="margin:20px 0;">
        <a href="{{ route('seller.verification.show') }}" style="display:inline-block;background-color:#FF7A29;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 24px;border-radius:999px;">Verifikasi Warungku</a>
    </p>
@else
    <p style="margin:20px 0;">
        <a href="{{ route('home') }}" style="display:inline-block;background-color:#FF7A29;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 24px;border-radius:999px;">Mulai Belanja</a>
    </p>
@endif
<p style="font-size:13px;color:#8A7B66;line-height:1.6;margin:16px 0 0 0;">
    Masuk kapan saja dengan email ini: {{ $user->email }}
</p>
@endsection
