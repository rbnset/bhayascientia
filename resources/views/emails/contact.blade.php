<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Baru Masuk – DABRAKA</title>
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
            max-width: 600px;
            margin: 0 auto;
        }

        .preheader {
            display: none;
            max-height: 0;
            overflow: hidden;
            font-size: 1px;
            color: #F0F2F5;
        }

        /* ===== HEADER ===== */
        .header {
            background: linear-gradient(145deg, #FF6B18 0%, #D63A1F 100%);
            border-radius: 20px 20px 0 0;
            padding: 48px 48px 44px;
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
            margin-bottom: 28px;
            position: relative;
            z-index: 1;
        }

        .logo-wrap img {
            height: 40px;
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
            margin-bottom: 22px;
            position: relative;
            z-index: 1;
        }

        .badge-dot {
            width: 7px;
            height: 7px;
            background: #facc15;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .header-badge span {
            color: rgba(255, 255, 255, 0.95);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }

        .header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 900;
            line-height: 1.25;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .header-sub {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            line-height: 1.75;
            position: relative;
            z-index: 1;
        }

        /* ===== STATUS BANNER ===== */
        .status-banner {
            background: #ffffff;
            border-left: 4px solid #facc15;
            padding: 14px 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .status-banner-inner {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-pulse {
            width: 10px;
            height: 10px;
            background: #facc15;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-text {
            font-size: 13px;
            color: #374151;
            line-height: 1.5;
        }

        .status-text strong {
            color: #b45309;
        }

        /* ===== BODY ===== */
        .body {
            background: #ffffff;
            padding: 40px 48px;
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

        .section-divider {
            border: none;
            border-top: 1px solid #F3F4F6;
            margin: 32px 0;
        }

        .section-label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }

        .section-label-icon {
            width: 26px;
            height: 26px;
            background: #FFF7F2;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .section-label-text {
            font-size: 10.5px;
            font-weight: 800;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: 1.3px;
        }

        /* ===== SUMMARY BOX ===== */
        .summary-box {
            background: #FAFAFA;
            border: 1.5px solid #F3F4F6;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 32px;
        }

        .summary-row {
            display: flex;
            align-items: flex-start;
            padding: 13px 20px;
            border-bottom: 1px solid #F3F4F6;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            font-size: 10.5px;
            font-weight: 800;
            color: #FF6B18;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            min-width: 80px;
            padding-top: 2px;
            flex-shrink: 0;
        }

        .summary-value {
            font-size: 13.5px;
            color: #374151;
            line-height: 1.65;
            flex: 1;
        }

        .message-text {
            display: block;
            white-space: pre-line;
            background: #ffffff;
            border: 1px solid #F3F4F6;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #4B5563;
            line-height: 1.7;
        }

        .summary-timestamp {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #FEFCE8;
            border: 1px solid #FDE68A;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            color: #b45309;
            font-weight: 600;
        }

        /* ===== ALERT BOX ===== */
        .alert-box {
            background: #FFFBEB;
            border: 1.5px solid #FDE68A;
            border-radius: 12px;
            padding: 20px 22px;
            margin-bottom: 32px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .alert-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #F59E0B, #D97706);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .alert-content h4 {
            font-size: 13.5px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 5px;
        }

        .alert-content p {
            font-size: 12.5px;
            color: #9CA3AF;
            line-height: 1.75;
        }

        .alert-content strong {
            color: #6B7280;
        }

        /* ===== CTA ===== */
        .cta-section {
            text-align: center;
            margin-bottom: 8px;
        }

        .cta-btn {
            display: inline-block;
            padding: 15px 44px;
            background: linear-gradient(135deg, #FF6B18 0%, #D63A1F 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 16px rgba(255, 107, 24, 0.3);
        }

        .cta-sub {
            font-size: 12px;
            color: #D1D5DB;
            margin-top: 10px;
        }

        /* ===== FOOTER ===== */
        .footer {
            background: linear-gradient(160deg, #1a1f2e 0%, #111827 100%);
            border-radius: 0 0 20px 20px;
            padding: 36px 48px 32px;
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

        .footer-logo-wrap {
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
        }

        .footer-logo-wrap img {
            height: 28px;
            width: auto;
            filter: brightness(0) invert(1);
            opacity: 0.85;
            position: relative;
            z-index: 1;
        }

        .footer-tagline {
            font-size: 12px;
            color: #9CA3AF;
            margin-bottom: 20px;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .footer-links {
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .footer-links a {
            font-size: 12px;
            color: #FF6B18;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 500;
        }

        .footer-divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            margin: 18px 0;
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
        Pesan baru masuk dari {{ $data['name'] }} melalui form kontak DABRAKA. Segera tinjau dan berikan respons.
    </div>

    <div class="wrapper">

        {{-- ===== HEADER ===== --}}
        <div class="header">
            <div class="logo-wrap">
                <img src="{{ config('app.url') }}/assets/images/logos/logo-dark.svg" alt="DABRAKA">
            </div>

            <div class="header-badge">
                <div class="badge-dot"></div>
                <span>Notifikasi Pesan Baru</span>
            </div>

            <h1>📩 Pesan Baru Masuk</h1>
            <p class="header-sub">
                Ada pesan baru dari form kontak DABRAKA<br>
                yang memerlukan perhatian Anda.
            </p>
        </div>

        {{-- ===== STATUS BANNER ===== --}}
        <div class="status-banner">
            <div class="status-banner-inner">
                <div class="status-pulse"></div>
                <div class="status-text">
                    <strong>⚡ Pesan Baru Diterima</strong> &mdash;
                    {{ now()->setTimezone('Asia/Jakarta')->translatedFormat('l, d F Y') }},
                    pukul {{ now()->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                </div>
            </div>
        </div>

        {{-- ===== BODY ===== --}}
        <div class="body">

            <p class="intro">
                Seseorang telah mengirimkan pesan melalui form kontak
                <strong>DABRAKA</strong>. Berikut adalah detail lengkap pesan yang masuk.
                Segera tinjau dan berikan respons dalam waktu <strong>1×24 jam kerja</strong>.
            </p>

            {{-- DETAIL PENGIRIM --}}
            <div class="section-label">
                <div class="section-label-icon">👤</div>
                <span class="section-label-text">Informasi Pengirim</span>
            </div>

            <div class="summary-box">
                <div class="summary-row">
                    <span class="summary-label">Nama</span>
                    <span class="summary-value">{{ $data['name'] }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Email</span>
                    <span class="summary-value">{{ $data['email'] }}</span>
                </div>
                @if(!empty($data['phone']))
                <div class="summary-row">
                    <span class="summary-label">Telepon</span>
                    <span class="summary-value">{{ $data['phone'] }}</span>
                </div>
                @endif
                <div class="summary-row">
                    <span class="summary-label">Subjek</span>
                    <span class="summary-value">{{ $data['subject'] }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Dikirim</span>
                    <span class="summary-value">
                        <span class="summary-timestamp">
                            ● {{ now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                        </span>
                    </span>
                </div>
            </div>

            <hr class="section-divider">

            {{-- ISI PESAN --}}
            <div class="section-label">
                <div class="section-label-icon">💬</div>
                <span class="section-label-text">Isi Pesan</span>
            </div>

            <div class="summary-box">
                <div class="summary-row">
                    <span class="summary-value">
                        <span class="message-text">{{ $data['message'] }}</span>
                    </span>
                </div>
            </div>

            <hr class="section-divider">

            {{-- ALERT REMINDER --}}
            <div class="alert-box">
                <div class="alert-icon">⏰</div>
                <div class="alert-content">
                    <h4>Segera Berikan Respons</h4>
                    <p>
                        Pengirim telah mendapatkan konfirmasi bahwa pesan mereka diterima.
                        Berikan respons ke <strong>{{ $data['email'] }}</strong> sesegera
                        mungkin, maksimal <strong>1×24 jam kerja</strong>.
                    </p>
                </div>
            </div>

            {{-- CTA --}}
            <div class="cta-section">
                <a href="mailto:{{ $data['email'] }}?subject=Re: {{ $data['subject'] }}" class="cta-btn">
                    Balas Pesan Sekarang &rarr;
                </a>
                <p class="cta-sub">Klik untuk membuka email client dan membalas langsung</p>
            </div>

        </div>

        {{-- ===== FOOTER ===== --}}
        <div class="footer">
            <div class="footer-logo-wrap">
                <img src="{{ config('app.url') }}/assets/images/logos/logo-dark.svg" alt="DABRAKA">
            </div>

            <p class="footer-tagline">Platform Publikasi Ilmiah Terpercaya</p>

            <div class="footer-links">
                <a href="{{ config('app.url') }}">Website</a>
                <a href="{{ config('app.url') }}/kontak">Kontak</a>
                <a href="mailto:dabraka@rbnset.me">dabraka@rbnset.me</a>
            </div>

            <hr class="footer-divider">

            <p class="footer-legal">
                Email ini dikirim otomatis saat ada pesan masuk dari form kontak DABRAKA.<br>
                Hanya dapat dilihat oleh admin yang berwenang.<br>
                &copy; {{ date('Y') }} DABRAKA. Seluruh hak dilindungi undang-undang.
            </p>
        </div>

    </div>
</body>

</html>