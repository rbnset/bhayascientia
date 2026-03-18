<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Anda Telah Kami Terima – DABRAKA</title>
</head>

<body
    style="margin:0;padding:0;background-color:#F0F2F5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;-webkit-font-smoothing:antialiased;">

    {{-- Preheader --}}
    <div style="display:none;max-height:0;overflow:hidden;font-size:1px;color:#F0F2F5;">
        Halo {{ $data['name'] }}, pesan Anda telah kami terima. Tim DABRAKA akan merespons dalam 1×24 jam kerja.
    </div>

    {{-- Wrapper --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F0F2F5;padding:40px 16px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

                    {{-- ===== HEADER ===== --}}
                    <tr>
                        <td
                            style="background-color:#FF6B18;border-radius:20px 20px 0 0;padding:48px 48px 44px;text-align:center;">

                            {{-- Logo --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom:28px;">
                                        <img src="{{ config('app.url') }}/assets/images/logos/logo-dark.svg"
                                            alt="DABRAKA"
                                            style="height:40px;width:auto;filter:brightness(0) invert(1);">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-bottom:22px;">
                                        <table cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td
                                                    style="background-color:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.35);border-radius:999px;padding:5px 18px;">
                                                    <table cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td
                                                                style="width:7px;height:7px;background-color:#4ade80;border-radius:50%;vertical-align:middle;">
                                                                &nbsp;</td>
                                                            <td
                                                                style="padding-left:7px;color:rgba(255,255,255,0.95);font-size:10.5px;font-weight:700;letter-spacing:1.3px;text-transform:uppercase;vertical-align:middle;">
                                                                Konfirmasi Resmi Penerimaan Pesan
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <h1
                                            style="color:#ffffff;font-size:30px;font-weight:900;line-height:1.25;margin:0 0 10px 0;">
                                            Pesan Anda Telah<br>Kami Terima
                                        </h1>
                                        <p
                                            style="color:rgba(255,255,255,0.85);font-size:14px;line-height:1.75;margin:0;">
                                            Tim DABRAKA telah menerima pesan Anda dan<br>
                                            akan segera merespons sesuai jadwal operasional.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ===== STATUS BANNER ===== --}}
                    <tr>
                        <td style="background-color:#ffffff;border-left:4px solid #22c55e;padding:14px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td
                                        style="width:10px;height:10px;background-color:#22c55e;border-radius:50%;vertical-align:middle;">
                                        &nbsp;</td>
                                    <td
                                        style="padding-left:10px;font-size:13px;color:#374151;line-height:1.5;vertical-align:middle;">
                                        <strong style="color:#16a34a;">✓ Pesan Berhasil Diterima</strong> &mdash;
                                        {{ now()->setTimezone('Asia/Jakarta')->translatedFormat('l, d F Y') }},
                                        pukul {{ now()->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ===== BODY ===== --}}
                    <tr>
                        <td style="background-color:#ffffff;padding:40px 48px;">

                            {{-- Greeting --}}
                            <p style="font-size:18px;font-weight:700;color:#111827;margin:0 0 10px 0;">
                                Yth. {{ $data['name'] }},
                            </p>
                            <p
                                style="font-size:14px;color:#6B7280;line-height:1.85;margin:0 0 32px 0;border-left:3px solid #FFD4BA;padding-left:16px;">
                                Terima kasih telah menghubungi <strong style="color:#374151;">DABRAKA</strong>.
                                Pesan Anda telah tercatat dalam sistem kami dan akan segera ditinjau
                                oleh tim yang berwenang. Harap simpan email ini sebagai
                                <strong style="color:#374151;">bukti resmi</strong> bahwa komunikasi Anda telah
                                diterima.
                            </p>

                            {{-- Section Label --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:14px;">
                                <tr>
                                    <td
                                        style="width:26px;height:26px;background-color:#FFF7F2;border-radius:7px;text-align:center;font-size:13px;vertical-align:middle;">
                                        📋</td>
                                    <td
                                        style="padding-left:8px;font-size:10.5px;font-weight:800;color:#9CA3AF;text-transform:uppercase;letter-spacing:1.3px;vertical-align:middle;">
                                        Ringkasan Pesan Anda
                                    </td>
                                </tr>
                            </table>

                            {{-- Summary Box --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="1"
                                style="border-color:#F3F4F6;border-collapse:collapse;border-radius:12px;margin-bottom:32px;">
                                <tr style="background-color:#FAFAFA;">
                                    <td
                                        style="padding:13px 20px;border-bottom:1px solid #F3F4F6;width:80px;font-size:10.5px;font-weight:800;color:#FF6B18;text-transform:uppercase;letter-spacing:0.8px;vertical-align:top;">
                                        Nama
                                    </td>
                                    <td
                                        style="padding:13px 20px;border-bottom:1px solid #F3F4F6;font-size:13.5px;color:#374151;line-height:1.65;">
                                        {{ $data['name'] }}
                                    </td>
                                </tr>
                                <tr style="background-color:#FAFAFA;">
                                    <td
                                        style="padding:13px 20px;border-bottom:1px solid #F3F4F6;font-size:10.5px;font-weight:800;color:#FF6B18;text-transform:uppercase;letter-spacing:0.8px;vertical-align:top;">
                                        Email
                                    </td>
                                    <td
                                        style="padding:13px 20px;border-bottom:1px solid #F3F4F6;font-size:13.5px;color:#374151;line-height:1.65;">
                                        {{ $data['email'] }}
                                    </td>
                                </tr>
                                @if(!empty($data['phone']))
                                <tr style="background-color:#FAFAFA;">
                                    <td
                                        style="padding:13px 20px;border-bottom:1px solid #F3F4F6;font-size:10.5px;font-weight:800;color:#FF6B18;text-transform:uppercase;letter-spacing:0.8px;vertical-align:top;">
                                        Telepon
                                    </td>
                                    <td
                                        style="padding:13px 20px;border-bottom:1px solid #F3F4F6;font-size:13.5px;color:#374151;line-height:1.65;">
                                        {{ $data['phone'] }}
                                    </td>
                                </tr>
                                @endif
                                <tr style="background-color:#FAFAFA;">
                                    <td
                                        style="padding:13px 20px;border-bottom:1px solid #F3F4F6;font-size:10.5px;font-weight:800;color:#FF6B18;text-transform:uppercase;letter-spacing:0.8px;vertical-align:top;">
                                        Subjek
                                    </td>
                                    <td
                                        style="padding:13px 20px;border-bottom:1px solid #F3F4F6;font-size:13.5px;color:#374151;line-height:1.65;">
                                        {{ $data['subject'] }}
                                    </td>
                                </tr>
                                <tr style="background-color:#FAFAFA;">
                                    <td
                                        style="padding:13px 20px;border-bottom:1px solid #F3F4F6;font-size:10.5px;font-weight:800;color:#FF6B18;text-transform:uppercase;letter-spacing:0.8px;vertical-align:top;">
                                        Pesan
                                    </td>
                                    <td style="padding:13px 20px;border-bottom:1px solid #F3F4F6;">
                                        <span
                                            style="display:block;white-space:pre-line;background-color:#ffffff;border:1px solid #F3F4F6;border-radius:8px;padding:10px 14px;font-size:13px;color:#4B5563;line-height:1.7;">{{
                                            $data['message'] }}</span>
                                    </td>
                                </tr>
                                <tr style="background-color:#FAFAFA;">
                                    <td
                                        style="padding:13px 20px;font-size:10.5px;font-weight:800;color:#FF6B18;text-transform:uppercase;letter-spacing:0.8px;vertical-align:top;">
                                        Dikirim
                                    </td>
                                    <td style="padding:13px 20px;">
                                        <span
                                            style="display:inline-block;background-color:#F0FDF4;border:1px solid #BBF7D0;border-radius:6px;padding:4px 10px;font-size:12px;color:#16a34a;font-weight:600;">
                                            ● {{ now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <hr style="border:none;border-top:1px solid #F3F4F6;margin:32px 0;">

                            {{-- Timeline Label --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:14px;">
                                <tr>
                                    <td
                                        style="width:26px;height:26px;background-color:#FFF7F2;border-radius:7px;text-align:center;font-size:13px;vertical-align:middle;">
                                        🗂️</td>
                                    <td
                                        style="padding-left:8px;font-size:10.5px;font-weight:800;color:#9CA3AF;text-transform:uppercase;letter-spacing:1.3px;vertical-align:middle;">
                                        Alur Penanganan Pesan
                                    </td>
                                </tr>
                            </table>

                            {{-- Timeline --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:32px;">
                                {{-- Step 1 --}}
                                <tr>
                                    <td style="width:36px;text-align:center;vertical-align:top;padding-top:5px;">
                                        <div
                                            style="width:36px;height:36px;border-radius:50%;background-color:#DCFCE7;border:2px solid #86EFAC;text-align:center;line-height:32px;font-size:14px;font-weight:700;color:#16a34a;display:inline-block;">
                                            ✓</div>
                                    </td>
                                    <td
                                        style="padding-left:16px;padding-bottom:20px;vertical-align:top;padding-top:5px;">
                                        <p style="font-size:13.5px;font-weight:700;color:#16a34a;margin:0 0 3px 0;">
                                            Pesan Berhasil Diterima</p>
                                        <p style="font-size:12.5px;color:#9CA3AF;line-height:1.65;margin:0;">
                                            Pesan Anda telah masuk ke sistem DABRAKA pada pukul <strong
                                                style="color:#6B7280;">{{
                                                now()->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</strong>.
                                        </p>
                                    </td>
                                </tr>
                                {{-- Step 2 --}}
                                <tr>
                                    <td style="width:36px;text-align:center;vertical-align:top;padding-top:5px;">
                                        <div
                                            style="width:36px;height:36px;border-radius:50%;background-color:#FFF7F2;border:2px dashed #FCA97A;text-align:center;line-height:32px;font-size:14px;font-weight:700;color:#FF6B18;display:inline-block;">
                                            ⏳</div>
                                    </td>
                                    <td
                                        style="padding-left:16px;padding-bottom:20px;vertical-align:top;padding-top:5px;">
                                        <p style="font-size:13.5px;font-weight:700;color:#FF6B18;margin:0 0 3px 0;">
                                            Peninjauan oleh Tim</p>
                                        <p style="font-size:12.5px;color:#9CA3AF;line-height:1.65;margin:0;">
                                            Tim kami akan meninjau dan mengklasifikasikan pesan Anda sesuai jadwal
                                            operasional, yaitu <strong style="color:#6B7280;">Senin–Jumat, 09.00–17.00
                                                WIB</strong>.
                                        </p>
                                    </td>
                                </tr>
                                {{-- Step 3 --}}
                                <tr>
                                    <td style="width:36px;text-align:center;vertical-align:top;padding-top:5px;">
                                        <div
                                            style="width:36px;height:36px;border-radius:50%;background-color:#F9FAFB;border:2px dashed #E5E7EB;text-align:center;line-height:32px;font-size:14px;font-weight:700;color:#9CA3AF;display:inline-block;">
                                            💬</div>
                                    </td>
                                    <td style="padding-left:16px;vertical-align:top;padding-top:5px;">
                                        <p style="font-size:13.5px;font-weight:700;color:#111827;margin:0 0 3px 0;">
                                            Respons Resmi dari Tim</p>
                                        <p style="font-size:12.5px;color:#9CA3AF;line-height:1.65;margin:0;">
                                            Kami akan mengirimkan respons resmi ke <strong style="color:#6B7280;">{{
                                                $data['email'] }}</strong> dalam waktu <strong
                                                style="color:#6B7280;">1×24 jam kerja</strong> sejak pesan ini diterima.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <hr style="border:none;border-top:1px solid #F3F4F6;margin:32px 0;">

                            {{-- Urgent Box --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background-color:#FFFBF8;border:1.5px solid #FFE2D2;border-radius:12px;margin-bottom:32px;">
                                <tr>
                                    <td style="padding:20px 22px;vertical-align:top;width:56px;">
                                        <div
                                            style="width:40px;height:40px;background-color:#FF6B18;border-radius:10px;text-align:center;line-height:40px;font-size:18px;">
                                            ⚡</div>
                                    </td>
                                    <td style="padding:20px 22px 20px 0;vertical-align:top;">
                                        <p style="font-size:13.5px;font-weight:700;color:#1F2937;margin:0 0 5px 0;">
                                            Perlu Bantuan Segera?</p>
                                        <p style="font-size:12.5px;color:#9CA3AF;line-height:1.75;margin:0;">
                                            Hubungi kami langsung melalui WhatsApp di
                                            <a href="https://wa.me/6281200000000"
                                                style="color:#FF6B18;text-decoration:none;font-weight:600;">+62
                                                812-0000-0000</a>,
                                            atau kirim email ke
                                            <a href="mailto:dabraka@rbnset.me"
                                                style="color:#FF6B18;text-decoration:none;font-weight:600;">dabraka@rbnset.me</a>.
                                            Tim kami siap membantu Anda.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- CTA --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom:8px;">
                                        <a href="{{ config('app.url') }}"
                                            style="display:inline-block;padding:15px 44px;background-color:#FF6B18;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:14px;letter-spacing:0.3px;">
                                            Kunjungi Website DABRAKA &rarr;
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <p style="font-size:12px;color:#D1D5DB;margin:10px 0 0 0;">Temukan publikasi dan
                                            informasi terbaru dari kami</p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    {{-- ===== FOOTER ===== --}}
                    <tr>
                        <td
                            style="background-color:#111827;border-radius:0 0 20px 20px;padding:36px 48px 32px;text-align:center;">

                            <img src="{{ config('app.url') }}/assets/images/logos/logo-dark.svg" alt="DABRAKA"
                                style="height:28px;width:auto;filter:brightness(0) invert(1);opacity:0.85;margin-bottom:14px;">

                            <p style="font-size:12px;color:#9CA3AF;margin:0 0 20px 0;line-height:1.6;">
                                Platform Publikasi Ilmiah Terpercaya
                            </p>

                            <p style="margin:0 0 20px 0;">
                                <a href="{{ config('app.url') }}"
                                    style="font-size:12px;color:#FF6B18;text-decoration:none;margin:0 10px;font-weight:500;">Website</a>
                                <a href="{{ config('app.url') }}/kontak"
                                    style="font-size:12px;color:#FF6B18;text-decoration:none;margin:0 10px;font-weight:500;">Kontak</a>
                                <a href="mailto:dabraka@rbnset.me"
                                    style="font-size:12px;color:#FF6B18;text-decoration:none;margin:0 10px;font-weight:500;">dabraka@rbnset.me</a>
                            </p>

                            <hr style="border:none;border-top:1px solid rgba(255,255,255,0.07);margin:18px 0;">

                            <p style="font-size:11px;color:#6B7280;line-height:1.8;margin:0;">
                                Email ini dikirim secara otomatis sebagai konfirmasi resmi penerimaan pesan Anda.<br>
                                Mohon tidak membalas email ini secara langsung — gunakan kontak di atas.<br>
                                &copy; {{ date('Y') }} DABRAKA. Seluruh hak dilindungi undang-undang.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>