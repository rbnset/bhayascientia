<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karya Anda Telah Diterbitkan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background-color: #f4f2ee;
            color: #1a1a1a;
            line-height: 1.7;
        }

        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 16px 48px;
        }

        /* ── Hero Header ── */
        .hero {
            background: linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%);
            border-radius: 12px 12px 0 0;
            padding: 48px 40px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 160px;
            height: 160px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        .hero .confetti {
            font-size: 36px;
            display: block;
            margin-bottom: 12px;
            line-height: 1;
        }

        .hero .logo-text {
            font-size: 12px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #6ee7b7;
            font-family: 'Arial', sans-serif;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .hero h1 {
            font-size: 26px;
            color: #ffffff;
            font-weight: normal;
            letter-spacing: 0.3px;
            line-height: 1.3;
            margin-bottom: 8px;
        }

        .hero .tagline {
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            color: #a7f3d0;
            letter-spacing: 0.3px;
        }

        /* ── Live Badge ── */
        .live-bar {
            background-color: #064e3b;
            padding: 13px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: #34d399;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .live-bar span {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #6ee7b7;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 700;
        }

        /* ── Card ── */
        .card {
            background: #ffffff;
            border-left: 1px solid #e0ddd7;
            border-right: 1px solid #e0ddd7;
            border-bottom: 1px solid #e0ddd7;
            border-radius: 0 0 12px 12px;
            padding: 40px 40px 36px;
        }

        .greeting {
            font-size: 17px;
            margin-bottom: 16px;
            color: #1a1a1a;
        }

        .body-text {
            font-size: 15px;
            color: #3d3d3d;
            margin-bottom: 16px;
        }

        /* ── Publication Card ── */
        .pub-card {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #059669;
            border-radius: 10px;
            padding: 22px 24px;
            margin: 28px 0;
        }

        .pub-card .pub-type {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #059669;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .pub-card .pub-title {
            font-size: 17px;
            font-weight: bold;
            color: #064e3b;
            line-height: 1.4;
            margin-bottom: 12px;
        }

        .pub-card .pub-meta {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #6b7280;
        }

        .pub-card .pub-meta span {
            color: #059669;
            font-weight: 600;
        }

        /* ── URL Box ── */
        .url-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 14px 18px;
            margin: 0 0 28px;
            word-break: break-all;
        }

        .url-box .url-label {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 5px;
        }

        .url-box .url-text {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #334155;
            line-height: 1.5;
        }

        /* ── CTA Button ── */
        .cta-wrap {
            text-align: center;
            margin: 8px 0 24px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #059669, #047857);
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 44px;
            border-radius: 50px;
            font-family: 'Arial', sans-serif;
            font-size: 15px;
            letter-spacing: 0.5px;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35);
        }

        .cta-note {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #aaa;
            text-align: center;
            margin-top: 10px;
        }

        /* ── Tips Box ── */
        .tips-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 18px 22px;
            margin: 28px 0;
        }

        .tips-box .tips-title {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            font-weight: 700;
            color: #92400e;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .tips-box ul {
            padding-left: 18px;
            margin: 0;
        }

        .tips-box li {
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            color: #78350f;
            line-height: 1.7;
        }

        /* ── Divider ── */
        .divider {
            border: none;
            border-top: 1px solid #eee;
            margin: 28px 0;
        }

        /* ── Footer ── */
        .footer {
            text-align: center;
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #bbb;
            margin-top: 28px;
            line-height: 1.9;
        }

        .footer a {
            color: #6ee7b7;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <!-- Hero Header -->
        <div class="hero">
            <span class="confetti">🎉</span>
            <div class="logo-text">{{ config('app.name') }}</div>
            <h1>Karya Anda Kini Dapat<br>Diakses Publik!</h1>
            <div class="tagline">Selamat — publikasi Anda resmi tayang di website kami</div>
        </div>

        <!-- Live Badge -->
        <div class="live-bar">
            <div class="live-dot"></div>
            <span>Live sejak {{ $publishedAt }} WIB</span>
        </div>

        <!-- Card -->
        <div class="card">

            <p class="greeting">Selamat!</p>

            <p class="body-text">
                Proses panjang mulai dari penulisan, pengiriman, hingga review telah selesai.
                Karya Anda kini resmi diterbitkan dan dapat ditemukan, dibaca, serta diunduh
                oleh siapa saja yang mengunjungi website kami.
            </p>

            <!-- Publication Info -->
            <div class="pub-card">
                <div class="pub-type">{{ $type }}</div>
                <div class="pub-title">{{ $title }}</div>
                <div class="pub-meta">
                    Diterbitkan pada <span>{{ $publishedAt }} WIB</span>
                </div>
            </div>

            <!-- URL Box -->
            <div class="url-box">
                <div class="url-label">Tautan Publikasi</div>
                <div class="url-text">{{ $publicUrl }}</div>
            </div>

            <!-- CTA -->
            <div class="cta-wrap">
                <a href="{{ $publicUrl }}" class="cta-button">Lihat Karya Saya →</a>
            </div>
            <p class="cta-note">Bagikan tautan ini kepada rekan, kolega, atau media sosial Anda.</p>

            <!-- Tips -->
            <div class="tips-box">
                <div class="tips-title">💡 Tingkatkan jangkauan karya Anda</div>
                <ul>
                    <li>Bagikan tautan publikasi ke media sosial dan grup akademik</li>
                    <li>Sertakan tautan ini di profil akademik atau CV Anda</li>
                    <li>Informasikan kepada rekan peneliti untuk meningkatkan sitasi</li>
                    <li>Pantau statistik pembaca melalui dashboard publikasi Anda</li>
                </ul>
            </div>

            <hr class="divider">

            <p class="body-text" style="font-size: 14px; color: #888;">
                Terima kasih telah mempercayakan karya Anda kepada kami.
                Kontribusi Anda sangat berarti bagi perkembangan ilmu pengetahuan dan komunitas akademik.
            </p>

        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem {{ config('app.name') }}.</p>
            <p>Harap tidak membalas email ini.</p>
            <p style="margin-top: 6px;">
                <a href="{{ url('/') }}">{{ url('/') }}</a>
            </p>
        </div>

    </div>
</body>

</html>