<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Anda Telah Kami Terima – DABRAKA</title>
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
            background: #4ade80;
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
            font-size: 30px;
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
            border-left: 4px solid #22c55e;
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
            background: #22c55e;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-text {
            font-size: 13px;
            color: #374151;
            line-height: 1.5;
        }

        .status-text strong {
            color: #16a34a;
        }

        /* ===== BODY ===== */
        .body {
            background: #ffffff;
            padding: 40px 48px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
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
            background: #F0FDF4;
            border: 1px solid #BBF7D0;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            color: #16a34a;
            font-weight: 600;
        }

        /* ===== TIMELINE ===== */
        .timeline {
            margin-bottom: 32px;
        }

        .timeline-item {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 4px;
        }

        .tl-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
            width: 36px;
        }

        .tl-dot {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .tl-dot.done {
            background: #DCFCE7;
            border: 2px solid #86EFAC;
            color: #16a34a;
        }

        .tl-dot.active {
            background: #FFF7F2;
            border: 2px dashed #FCA97A;
            color: #FF6B18;
        }

        .tl-dot.pending {
            background: #F9FAFB;
            border: 2px dashed #E5E7EB;
            color: #9CA3AF;
        }

        .tl-connector {
            width: 2px;
            height: 24px;
            background: #F3F4F6;
            margin: 4px 0;
        }

        .tl-content {
            flex: 1;
            padding-top: 5px;
            padding-bottom: 20px;
        }

        .tl-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 3px;
        }

        .tl-title.done-text {
            color: #16a34a;
        }

        .tl-title.active-text {
            color: #FF6B18;
        }

        .tl-desc {
            font-size: 12.5px;
            color: #9CA3AF;
            line-height: 1.65;
        }

        .tl-desc strong {
            color: #6B7280;
        }

        /* ===== URGENT BOX ===== */
        .urgent-box {
            background: #FFFBF8;
            border: 1.5px solid #FFE2D2;
            border-radius: 12px;
            padding: 20px 22px;
            margin-bottom: 32px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .urgent-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #FF6B18, #E64627);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .urgent-content h4 {
            font-size: 13.5px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 5px;
        }

        .urgent-content p {
            font-size: 12.5px;
            color: #9CA3AF;
            line-height: 1.75;
        }

        .urgent-content a {
            color: #FF6B18;
            text-decoration: none;
            font-weight: 600;
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

        .footer-links a:hover {
            text-decoration: underline;
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
        Halo {{ $data['name'] }}, pesan Anda telah kami terima. Tim DABRAKA akan merespons dalam 1×24 jam kerja.
    </div>

    <div class="wrapper">

        {{-- ===== HEADER ===== --}}
        <div class="header">

            {{-- Logo dari hosting, diubah jadi putih via CSS filter --}}
            <div class="logo-wrap">
                <img src="{{ config('app.url') }}/assets/images/logos/logo-dark.svg" alt="DABRAKA">
            </div>

            <div class="header-badge">
                <div class="badge-dot"></div>
                <span>Konfirmasi Resmi Penerimaan Pesan</span>
            </div>

            <h1>Pesan Anda Telah<br>Kami Terima</h1>
            <p class="header-sub">
                Tim DABRAKA telah menerima pesan Anda dan<br>
                akan segera merespons sesuai jadwal operasional.
            </p>
        </div>

        {{-- ===== STATUS BANNER ===== --}}
        <div class="status-banner">
            <div class="status-banner-inner">
                <div class="status-pulse"></div>
                <div class="status-text">
                    <strong>✓ Pesan Berhasil Diterima</strong> &mdash;
                    {{ now()->setTimezone('Asia/Jakarta')->translatedFormat('l, d F Y') }},
                    pukul {{ now()->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                </div>
            </div>
        </div>

        {{-- ===== BODY ===== --}}
        <div class="body">

            <p class="greeting">Yth. {{ $data['name'] }},</p>
            <p class="intro">
                Terima kasih telah menghubungi <strong>DABRAKA</strong>.
                Pesan Anda telah tercatat dalam sistem kami dan akan segera ditinjau
                oleh tim yang berwenang. Harap simpan email ini sebagai
                <strong>bukti resmi</strong> bahwa komunikasi Anda telah diterima.
            </p>

            {{-- RINGKASAN PESAN --}}
            <div class="section-label">
                <div class="section-label-icon">📋</div>
                <span class="section-label-text">Ringkasan Pesan Anda</span>
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
                    <span class="summary-label">Pesan</span>
                    <span class="summary-value">
                        <span class="message-text">{{ $data['message'] }}</span>
                    </span>
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

            {{-- TIMELINE --}}
            <div class="section-label">
                <div class="section-label-icon">🗂️</div>
                <span class="section-label-text">Alur Penanganan Pesan</span>
            </div>

            <div class="timeline">
                <div class="timeline-item">
                    <div class="tl-left">
                        <div class="tl-dot done">✓</div>
                        <div class="tl-connector"></div>
                    </div>
                    <div class="tl-content">
                        <p class="tl-title done-text">Pesan Berhasil Diterima</p>
                        <p class="tl-desc">
                            Pesan Anda telah masuk ke sistem DABRAKA pada
                            pukul <strong>{{ now()->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</strong>.
                        </p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="tl-left">
                        <div class="tl-dot active">⏳</div>
                        <div class="tl-connector"></div>
                    </div>
                    <div class="tl-content">
                        <p class="tl-title active-text">Peninjauan oleh Tim</p>
                        <p class="tl-desc">
                            Tim kami akan meninjau dan mengklasifikasikan pesan Anda
                            sesuai jadwal operasional,
                            yaitu <strong>Senin–Jumat, 09.00–17.00 WIB</strong>.
                        </p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="tl-left">
                        <div class="tl-dot pending">💬</div>
                    </div>
                    <div class="tl-content">
                        <p class="tl-title">Respons Resmi dari Tim</p>
                        <p class="tl-desc">
                            Kami akan mengirimkan respons resmi ke
                            <strong>{{ $data['email'] }}</strong> dalam waktu
                            <strong>1×24 jam kerja</strong> sejak pesan ini diterima.
                        </p>
                    </div>
                </div>
            </div>

            <hr class="section-divider">

            {{-- URGENT BOX --}}
            <div class="urgent-box">
                <div class="urgent-icon">⚡</div>
                <div class="urgent-content">
                    <h4>Perlu Bantuan Segera?</h4>
                    <p>
                        Hubungi kami langsung melalui WhatsApp di
                        <a href="https://wa.me/6281200000000">+62 812-0000-0000</a>,
                        atau kirim email ke
                        <a href="mailto:dabraka@rbnset.me">dabraka@rbnset.me</a>.
                        Tim kami siap membantu Anda.
                    </p>
                </div>
            </div>

            {{-- CTA --}}
            <div class="cta-section">
                <a href="{{ config('app.url') }}" class="cta-btn">
                    Kunjungi Website DABRAKA &rarr;
                </a>
                <p class="cta-sub">Temukan publikasi dan informasi terbaru dari kami</p>
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
                Email ini dikirim secara otomatis sebagai konfirmasi resmi penerimaan pesan Anda.<br>
                Mohon tidak membalas email ini secara langsung gunakan kontak di atas.<br>
                &copy; {{ date('Y') }} DABRAKA. Seluruh hak dilindungi undang-undang.
            </p>

        </div>

    </div>
</body>

</html>