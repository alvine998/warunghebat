@extends('emails.layout')

@section('subject', 'Verifikasi warung perlu perbaikan')

@section('content')
<h1 style="font-size:22px;margin:0 0 12px 0;">Halo {{ $user->name }}, berkas verifikasimu perlu diperbaiki.</h1>
<p style="font-size:14px;line-height:1.7;margin:0 0 12px 0;">
    Admin sudah memeriksa pengajuan atas nama <strong>{{ $verification->full_name }}</strong>, tapi belum bisa disetujui.
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FFF3EC;border:1px solid #F5C9AC;border-radius:12px;margin:16px 0;">
    <tr>
        <td style="padding:16px 20px;">
            <div style="font-size:12px;font-weight:700;color:#B3541E;margin-bottom:6px;">ALASAN PENOLAKAN</div>
            <div style="font-size:14px;line-height:1.6;">{{ $verification->rejection_reason }}</div>
        </td>
    </tr>
</table>
<p style="font-size:14px;line-height:1.7;margin:0 0 12px 0;">
    Perbaiki berkasmu lalu kirim ulang — foto lama tetap tersimpan, cukup unggah ulang yang bermasalah.
</p>
<p style="margin:20px 0;">
    <a href="{{ route('seller.verification.show') }}" style="display:inline-block;background-color:#FF7A29;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 24px;border-radius:999px;">Perbaiki Verifikasi</a>
</p>
@endsection
