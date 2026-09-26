@extends('emails.layout')

@section('subject', 'Produkmu perlu perbaikan')

@section('content')
<h1 style="font-size:22px;margin:0 0 12px 0;">Produk &ldquo;{{ $product->name }}&rdquo; perlu diperbaiki.</h1>
<p style="font-size:14px;line-height:1.7;margin:0 0 12px 0;">
    Admin sudah memeriksa <strong>{{ $product->name }}</strong>, tapi belum bisa ditayangkan.
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FFF3EC;border:1px solid #F5C9AC;border-radius:12px;margin:16px 0;">
    <tr>
        <td style="padding:16px 20px;">
            <div style="font-size:12px;font-weight:700;color:#B3541E;margin-bottom:6px;">ALASAN PENOLAKAN</div>
            <div style="font-size:14px;line-height:1.6;">{{ $product->rejection_reason }}</div>
        </td>
    </tr>
</table>
<p style="font-size:14px;line-height:1.7;margin:0 0 12px 0;">
    Perbaiki datanya (nama, foto, harga, atau deskripsi) lalu simpan ulang — produkmu otomatis masuk antrean verifikasi lagi.
</p>
<p style="margin:20px 0;">
    <a href="{{ route('seller.products.index') }}" style="display:inline-block;background-color:#FF7A29;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 24px;border-radius:999px;">Perbaiki Produk</a>
</p>
@endsection
