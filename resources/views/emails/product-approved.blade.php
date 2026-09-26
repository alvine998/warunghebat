@extends('emails.layout')

@section('subject', 'Produkmu sudah tayang!')

@section('content')
<h1 style="font-size:22px;margin:0 0 12px 0;">Produk &ldquo;{{ $product->name }}&rdquo; disetujui!</h1>
<p style="font-size:14px;line-height:1.7;margin:0 0 12px 0;">
    Kabar baik — produkmu <strong>{{ $product->name }}</strong>
    (Rp{{ number_format($product->price, 0, ',', '.') }} &bull; stok {{ $product->stock }})
    sudah lolos pemeriksaan admin dan <strong>sedang tayang</strong> di etalase warungmu.
</p>
<p style="margin:20px 0;">
    <a href="{{ route('seller.products.index') }}" style="display:inline-block;background-color:#FF7A29;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 24px;border-radius:999px;">Lihat Produkku</a>
</p>
@endsection
