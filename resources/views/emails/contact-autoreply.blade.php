<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f0f2f5;
            padding: 30px 15px;
            color: #1A1A1A;
        }

        .wrapper {
            max-width: 600px;
            margin: auto;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
            border-radius: 16px 16px 0 0;
            padding: 40px 40px 35px;
            text-align: center;
        }

        .logo-box {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 10px 20px;
            margin-bottom: 20px;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .logo-text {
            color: white;
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 1px;
        }

        .header-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: white;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 4px 14px;
            border-radius: 999px;
            margin-bottom: 16px;
        }

        .header h1 {
            color: white;
            font-size: 26px;
            font-weight: 900;
            line-height: 1.3;
            margin-bottom: 8px;
        }

        .header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            line-height: 1.6;
        }

        /* Status Banner */
        .status-banner {
            background: #fff;
            border-left: 4px solid #22c55e;
            margin: 0 40px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .status-dot {
            width: 12px;
            height: 12px;
            background: #22c55e;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-text {
            font-size: 13px;
            color: #1A1A1A;
        }

        .status-text strong {
            color: #22c55e;
        }

        /* Body */
        .body {
            background: #fff;
            padding: 35px 40px;
        }

        .greeting {
            font-size: 16px;
            color: #1A1A1A;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .intro {
            font-size: 14px;
            color: #555;
            line-height: 1.8;
            margin-bottom: 28px;
        }

        /* Ringkasan Pesan */
        .summary-title {
            font-size: 11px;
            font-weight: bold;
            color: #737373;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .summary-box {
            background: #FFF7F2;
            border: 1.5px solid #FFE2D2;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 28px;
        }

        .summary-row {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
            align-items: flex-start;
        }

        .summary-row:last-child {
            margin-bottom: 0;
        }

        .summary-label {
            font-size: 11px;
            color: #FF6B18;
            font-weight: bold;
            text-transform: uppercase;
            min-width: 70px;
            padding-top: 1px;
        }

        .summary-value {
            font-size: 13px;
            color: #1A1A1A;
            line-height: 1.6;
            flex: 1;
        }

        .divider-line {
            border: none;
            border-top: 1px solid #EEF0F7;
            margin: 0 0 12px;
        }

        /* Timeline */
        .timeline-title {
            font-size: 11px;
            font-weight: bold;
            color: #737373;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }

        .timeline {
            margin-bottom: 28px;
        }

        .timeline-item {
            display: flex;
            gap: 14px;
            margin-bottom: 16px;
            align-items: flex-start;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .tl-dot-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .tl-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .tl-dot.done {
            background: #dcfce7;
        }

        .tl-dot.pending {
            background: #FFF7F2;
            border: 1.5px dashed #FFD4BA;
        }

        .tl-line {
            width: 2px;
            flex: 1;
            min-height: 16px;
            background: #EEF0F7;
            margin-top: 4px;
        }

        .tl-content {
            flex: 1;
        }

        .tl-title {
            font-size: 13px;
            font-weight: bold;
            color: #1A1A1A;
            margin-bottom: 2px;
        }

        .tl-desc {
            font-size: 12px;
            color: #737373;
            line-height: 1.5;
        }

        /* Info Box */
        .info-box {
            background: #F8F9FC;
            border-radius: 10px;
            padding: 18px 20px;
            margin-bottom: 28px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .info-icon {
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .info-content h4 {
            font-size: 13px;
            font-weight: bold;
            color: #1A1A1A;
            margin-bottom: 6px;
        }

        .info-content p {
            font-size: 12px;
            color: #555;
            line-height: 1.7;
        }

        .info-content a {
            color: #FF6B18;
            text-decoration: none;
        }

        /* CTA Button */
        .cta-wrap {
            text-align: center;
            margin-bottom: 28px;
        }

        .cta-btn {
            display: inline-block;
            padding: 14px 36px;
            background: linear-gradient(135deg, #FF6B18, #E64627);
            color: white !important;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .cta-sub {
            font-size: 12px;
            color: #A3A6AE;
            margin-top: 8px;
        }

        /* Footer */
        .footer {
            background: #F8F9FC;
            border-top: 1px solid #EEF0F7;
            border-radius: 0 0 16px 16px;
            padding: 28px 40px;
            text-align: center;
        }

        .footer-logo {
            font-size: 14px;
            font-weight: 900;
            color: #FF6B18;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .footer p {
            font-size: 12px;
            color: #737373;
            line-height: 1.7;
        }

        .footer a {
            color: #FF6B18;
            text-decoration: none;
        }

        .footer-divider {
            border: none;
            border-top: 1px solid #EEF0F7;
            margin: 16px 0;
        }

        .footer-note {
            font-size: 11px;
            color: #A3A6AE;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        {{-- ===== HEADER ===== --}}
        <div class="header">
            <div class="logo-box">
                <div class="logo-icon">📚</div>
                <div class="logo-text">BHAYASCIENTIA</div>
            </div>
            <div class="header-badge">✦ Konfirmasi Penerimaan Pesan</div>
            <h1>Pesan Anda<br>Telah Kami Terima!</h1>
            <p>Tim kami akan segera meninjau dan merespons pesan Anda.</p>
        </div>

        {{-- ===== STATUS BANNER ===== --}}
        <div class="status-banner">
            <div class="status-dot"></div>
            <div class="status-text">
                <strong>✓ Terkirim & Diterima</strong> —
                {{ now()->setTimezone('Asia/Jakarta')->format('l, d F Y') }}
                pukul {{ now()->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
            </div>
        </div>

        {{-- ===== BODY ===== --}}
        <div class="body">

            {{-- Greeting --}}
            <p class="greeting">Halo, {{ $data['name'] }}! 👋</p>
            <p class="intro">
                Terima kasih telah menghubungi <strong>BHAYASCIENTIA</strong>.
                Kami telah menerima pesan Anda dan akan segera meninjau serta memberikan
                respons sesegera mungkin. Harap simpan email ini sebagai konfirmasi resmi
                bahwa pesan Anda sudah masuk ke sistem kami.
            </p>

            {{-- Ringkasan Pesan --}}
            <p class="summary-title">📋 Ringkasan Pesan Anda</p>
            <div class="summary-box">
                <div class="summary-row">
                    <span class="summary-label">Nama</span>
                    <span class="summary-value">{{ $data['name'] }}</span>
                </div>
                <hr class="divider-line">
                <div class="summary-row">
                    <span class="summary-label">Email</span>
                    <span class="summary-value">{{ $data['email'] }}</span>
                </div>
                @if(!empty($data['phone']))
                <hr class="divider-line">
                <div class="summary-row">
                    <span class="summary-label">Telepon</span>
                    <span class="summary-value">{{ $data['phone'] }}</span>
                </div>
                @endif
                <hr class="divider-line">
                <div class="summary-row">
                    <span class="summary-label">Subjek</span>
                    <span class="summary-value">{{ $data['subject'] }}</span>
                </div>
                <hr class="divider-line">
                <div class="summary-row">
                    <span class="summary-label">Pesan</span>
                    <span class="summary-value" style="white-space: pre-line;">{{ $data['message'] }}</span>
                </div>
                <hr class="divider-line">
                <div class="summary-row">
                    <span class="summary-label">Dikirim</span>
                    <span class="summary-value">
                        {{ now()->setTimezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB
                    </span>
                </div>
            </div>

            {{-- Timeline --}}
            <p class="timeline-title">🕐 Proses Selanjutnya</p>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="tl-dot-wrap">
                        <div class="tl-dot done">✅</div>
                        <div class="tl-line"></div>
                    </div>
                    <div class="tl-content">
                        <p class="tl-title">Pesan Diterima</p>
                        <p class="tl-desc">Pesan Anda telah masuk ke sistem kami pada
                            {{ now()->setTimezone('Asia/Jakarta')->format('H:i') }} WIB.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="tl-dot-wrap">
                        <div class="tl-dot pending">🔍</div>
                        <div class="tl-line"></div>
                    </div>
                    <div class="tl-content">
                        <p class="tl-title">Peninjauan oleh Tim</p>
                        <p class="tl-desc">Tim kami akan meninjau pesan Anda sesuai jam operasional
                            (Senin–Jumat, 09.00–17.00 WIB).</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="tl-dot-wrap">
                        <div class="tl-dot pending">💬</div>
                    </div>
                    <div class="tl-content">
                        <p class="tl-title">Respons Tim</p>
                        <p class="tl-desc">Kami akan membalas ke <strong>{{ $data['email'] }}</strong>
                            dalam waktu <strong>1×24 jam kerja</strong>.</p>
                    </div>
                </div>
            </div>

            {{-- Info Kontak Langsung --}}
            <div class="info-box">
                <div class="info-icon">💡</div>
                <div class="info-content">
                    <h4>Butuh Respons Lebih Cepat?</h4>
                    <p>
                        Hubungi kami langsung melalui WhatsApp di
                        <a href="https://wa.me/6281200000000">+62 812-0000-0000</a>,
                        atau kirim email langsung ke
                        <a href="mailto:dabraka@rbnset.me">dabraka@rbnset.me</a>.
                    </p>
                </div>
            </div>

            {{-- CTA --}}
            <div class="cta-wrap">
                <a href="{{ config('app.url') }}/kontak" class="cta-btn">
                    Kunjungi Halaman Kontak →
                </a>
                <p class="cta-sub">atau browse publikasi terbaru kami</p>
            </div>

        </div>

        {{-- ===== FOOTER ===== --}}
        <div class="footer">
            <div class="footer-logo">BHAYASCIENTIA</div>
            <p>
                Platform publikasi ilmiah terpercaya<br>
                <a href="mailto:dabraka@rbnset.me">dabraka@rbnset.me</a>
                &nbsp;·&nbsp;
                <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
            </p>
            <hr class="footer-divider">
            <p class="footer-note">
                Email ini dikirim otomatis sebagai konfirmasi penerimaan pesan.<br>
                Mohon tidak membalas email ini secara langsung.<br>
                © {{ date('Y') }} BHAYASCIENTIA. All rights reserved.
            </p>
        </div>

    </div>
</body>

</html>