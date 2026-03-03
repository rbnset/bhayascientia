<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi DABRAKA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #F0F2F5;
            padding: 40px 16px;
            color: #111827;
            -webkit-font-smoothing: antialiased;
        }

        .wrapper {
            max-width: 560px;
            margin: 0 auto;
        }

        .preheader {
            display: none;
            max-height: 0;
            overflow: hidden;
            font-size: 1px;
            color: #F0F2F5;
        }

        /* HEADER */
        .header {
            background: linear-gradient(145deg, #FF6B18 0%, #D63A1F 100%);
            border-radius: 20px 20px 0 0;
            padding: 44px 48px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.07);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -40px;
            width: 260px;
            height: 260px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }

        .logo-wrap {
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }

        .logo-wrap img {
            height: 38px;
            width: auto;
            filter: brightness(0) invert(1);
            position: relative;
            z-index: 1;
        }

        .header-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 999px;
            padding: 5px 18px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .badge-dot {
            width: 7px;
            height: 7px;
            background: #facc15;
            border-radius: 50%;
        }

        .header-badge span {
            color: rgba(255, 255, 255, 0.95);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }

        .header h1 {
            color: #fff;
            font-size: 26px;
            font-weight: 900;
            line-height: 1.3;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .header-sub {
            color: rgba(255, 255, 255, 0.85);
            font-size: 13px;
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }

        /* BODY */
        .body {
            background: #fff;
            padding: 40px 48px;
        }

        .greeting {
            font-size: 17px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .intro {
            font-size: 14px;
            color: #6B7280;
            line-height: 1.85;
            margin-bottom: 32px;
            border-left: 3px solid #FFD4BA;
            padding-left: 16px;
        }

        .intro strong {
            color: #374151;
        }

        /* OTP BOX */
        .otp-box {
            background: linear-gradient(135deg, #FFF7F2 0%, #FFE8DC 100%);
            border: 2px solid #FCA97A;
            border-radius: 16px;
            padding: 32px 24px;
            text-align: center;
            margin-bottom: 28px;
        }

        .otp-label {
            font-size: 11px;
            font-weight: 800;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 16px;
        }

        .otp-code {
            font-size: 48px;
            font-weight: 900;
            letter-spacing: 12px;
            color: #FF6B18;
            font-family: 'Courier New', monospace;
            margin-bottom: 16px;
            display: block;
        }

        .otp-expires {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff;
            border: 1px solid #FCA97A;
            border-radius: 999px;
            padding: 5px 16px;
            font-size: 12px;
            color: #D97706;
            font-weight: 600;
        }

        /* INFO BOX */
        .info-box {
            background: #F0FDF4;
            border: 1.5px solid #BBF7D0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 28px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-icon {
            font-size: 14px;
            flex-shrink: 0;
        }

        .info-text {
            font-size: 12.5px;
            color: #374151;
            line-height: 1.6;
        }

        .info-text strong {
            color: #16a34a;
        }

        /* WARNING */
        .warning-box {
            background: #FFFBEB;
            border: 1.5px solid #FDE68A;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 28px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .warning-icon {
            font-size: 18px;
            flex-shrink: 0;
        }

        .warning-text {
            font-size: 12.5px;
            color: #92400E;
            line-height: 1.7;
        }

        .section-divider {
            border: none;
            border-top: 1px solid #F3F4F6;
            margin: 28px 0;
        }

        /* FOOTER */
        .footer {
            background: linear-gradient(160deg, #1a1f2e 0%, #111827 100%);
            border-radius: 0 0 20px 20px;
            padding: 32px 48px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 180px;
            height: 180px;
            background: rgba(255, 107, 24, 0.06);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }

        .footer::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -30px;
            width: 150px;
            height: 150px;
            background: rgba(255, 107, 24, 0.04);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }

        .footer-logo {
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }

        .footer-logo img {
            height: 26px;
            width: auto;
            filter: brightness(0) invert(1);
            opacity: 0.8;
            position: relative;
            z-index: 1;
        }

        .footer-tagline {
            font-size: 12px;
            color: #9CA3AF;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .footer-divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            margin: 16px 0;
            position: relative;
            z-index: 1;
        }

        .footer-legal {
            font-size: 11px;
            color: #6B7280;
            line-height: 1.8;
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body>

    <div class="preheader">
        Kode OTP Anda: {{ $otpCode->code }} — Berlaku 10 menit. Jangan bagikan ke siapapun.
    </div>

    <div class="wrapper">

        {{-- HEADER --}}
        <div class="header">
            <div class="logo-wrap">
                <img src="{{ config('app.url') }}/assets/images/logos/logo-dark.svg" alt="DABRAKA">
            </div>
            <div class="header-badge">
                <div class="badge-dot"></div>
                <span>Verifikasi Akun</span>
            </div>
            <h1>🔐 Kode Verifikasi Anda</h1>
            <p class="header-sub">
                Masukkan kode berikut untuk mengaktifkan<br>
                akun DABRAKA Anda
            </p>
        </div>

        {{-- BODY --}}
        <div class="body">

            <p class="greeting">Halo, {{ $otpCode->user->name }}! 👋</p>
            <p class="intro">
                Terima kasih telah mendaftar di <strong>DABRAKA</strong>.
                Untuk mengaktifkan akun Anda, masukkan kode verifikasi berikut
                di halaman konfirmasi.
            </p>

            {{-- OTP CODE --}}
            <div class="otp-box">
                <p class="otp-label">🔑 Kode Verifikasi OTP</p>
                <span class="otp-code">{{ $otpCode->code }}</span>
                <div class="otp-expires">
                    ⏱ Berlaku hingga {{ $otpCode->expires_at->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                    (10 menit)
                </div>
            </div>

            {{-- INFO --}}
            <div class="info-box">
                <div class="info-row">
                    <span class="info-icon">✅</span>
                    <span class="info-text">Kode berlaku selama <strong>10 menit</strong> sejak email ini dikirim</span>
                </div>
                <div class="info-row">
                    <span class="info-icon">🔄</span>
                    <span class="info-text">Anda dapat meminta <strong>kirim ulang</strong> maksimal 3 kali</span>
                </div>
                <div class="info-row">
                    <span class="info-icon">📧</span>
                    <span class="info-text">Kode dikirim ke <strong>{{ $otpCode->user->email }}</strong></span>
                </div>
            </div>

            {{-- WARNING --}}
            <div class="warning-box">
                <span class="warning-icon">⚠️</span>
                <p class="warning-text">
                    <strong>Jangan bagikan kode ini kepada siapapun</strong> — termasuk tim DABRAKA.
                    Kami tidak pernah meminta kode OTP Anda. Jika Anda tidak merasa mendaftar,
                    abaikan email ini.
                </p>
            </div>

            <hr class="section-divider">

            <p style="font-size: 12.5px; color: #9CA3AF; text-align: center; line-height: 1.8;">
                Dikirim pada {{ now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB<br>
                dari sistem DABRAKA secara otomatis
            </p>

        </div>

        {{-- FOOTER --}}
        <div class="footer">
            <div class="footer-logo">
                <img src="{{ config('app.url') }}/assets/images/logos/logo-dark.svg" alt="DABRAKA">
            </div>
            <p class="footer-tagline">Platform Publikasi Ilmiah Terpercaya</p>
            <hr class="footer-divider">
            <p class="footer-legal">
                Email ini dikirim otomatis — mohon tidak membalas email ini.<br>
                Jika ada pertanyaan, hubungi <a href="mailto:dabraka@rbnset.me"
                    style="color:#FF6B18;">dabraka@rbnset.me</a><br>
                &copy; {{ date('Y') }} DABRAKA. Seluruh hak dilindungi undang-undang.
            </p>
        </div>

    </div>
</body>

</html>