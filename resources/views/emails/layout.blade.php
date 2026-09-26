<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('subject', 'Warung Hebat')</title>
</head>
<body style="margin:0;padding:0;background-color:#FFF9EF;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1A130D;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FFF9EF;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #F0E4D3;">
                <tr>
                    <td style="background-color:#1A130D;padding:24px 32px;text-align:center;">
                        <div style="font-size:22px;font-weight:900;color:#ffffff;">Warung Hebat</div>
                        <div style="font-size:11px;font-weight:700;letter-spacing:2px;color:#FF7A29;margin-top:4px;">BELANJA DEKAT &bull; HIDUP HEBAT</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 32px 32px 32px;">
                        <p style="font-size:12px;color:#8A7B66;line-height:1.6;margin:0;">
                            Email ini dikirim otomatis oleh Warung Hebat. Balas butuh bantuan? Hubungi kami lewat halaman
                            <a href="{{ route('contact') }}" style="color:#FF7A29;">Hubungi Kami</a>.
                        </p>
                    </td>
                </tr>
            </table>
            <p style="font-size:11px;color:#B3A48D;margin-top:16px;">&copy; {{ date('Y') }} Warung Hebat. Semua hak dilindungi.</p>
        </td>
    </tr>
</table>
</body>
</html>
