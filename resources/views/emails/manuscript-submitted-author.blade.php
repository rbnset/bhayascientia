<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naskah Berhasil Dikirim</title>
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

        /* ── Header ── */
        .header {
            background-color: #1b3a4b;
            border-radius: 12px 12px 0 0;
            padding: 36px 40px 32px;
            text-align: center;
        }

        .header .logo-text {
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #7ec8e3;
            font-family: 'Arial', sans-serif;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .header h1 {
            font-size: 22px;
            color: #ffffff;
            font-weight: normal;
            letter-spacing: 0.3px;
        }

        /* ── Status Badge ── */
        .status-bar {
            background-color: #2d6a4f;
            padding: 14px 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-bar .dot {
            width: 10px;
            height: 10px;
            background: #74c69d;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-bar span {
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            color: #d8f3dc;
            letter-spacing: 0.5px;
        }

        /* ── Body Card ── */
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

        .greeting strong {
            color: #1b3a4b;
        }

        .body-text {
            font-size: 15px;
            color: #3d3d3d;
            margin-bottom: 16px;
        }

        /* ── Manuscript Info Box ── */
        .info-box {
            background: #f8f6f1;
            border: 1px solid #ddd8cc;
            border-left: 4px solid #1b3a4b;
            border-radius: 8px;
            padding: 20px 24px;
            margin: 28px 0;
        }

        .info-box .label {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 6px;
        }

        .info-box .value {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
            line-height: 1.4;
        }

        .info-box .meta {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #999;
            margin-top: 8px;
        }

        /* ── Timeline Steps ── */
        .steps {
            margin: 28px 0;
        }

        .steps-title {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 16px;
        }

        .step {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .step-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .step-icon.done {
            background: #d8f3dc;
            color: #2d6a4f;
        }

        .step-icon.active {
            background: #1b3a4b;
            color: #fff;
        }

        .step-icon.next {
            background: #f0ede8;
            color: #aaa;
            border: 1px dashed #ccc;
        }

        .step-text {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            color: #555;
            padding-top: 5px;
        }

        .step-text.done {
            color: #2d6a4f;
        }

        .step-text.active {
            color: #1b3a4b;
            font-weight: bold;
        }

        /* ── CTA Button ── */
        .cta-wrap {
            text-align: center;
            margin: 32px 0 8px;
        }

        .cta-button {
            display: inline-block;
            background-color: #1b3a4b;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 8px;
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .cta-note {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #aaa;
            text-align: center;
            margin-top: 10px;
        }

        /* ── Divider ── */
        .divider {
            border: none;
            border-top: 1px solid #eee;
            margin: 32px 0;
        }

        /* ── Footer ── */
        .footer {
            text-align: center;
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #bbb;
            margin-top: 28px;
            line-height: 1.8;
        }

        .footer a {
            color: #7ec8e3;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <div class="logo-text">{{ config('app.name') }}</div>
            <h1>Konfirmasi Pengiriman Naskah</h1>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <div class="dot"></div>
            <span>Naskah diterima sistem &mdash; {{ now()->timezone('Asia/Jakarta')->isoFormat('D MMMM YYYY, HH:mm') }}
                WIB</span>
        </div>

        <!-- Card -->
        <div class="card">
            <p class="greeting">Yth. <strong>{{ $authorName }}</strong>,</p>

            <p class="body-text">
                Naskah Anda telah berhasil dikirim ke sistem kami dan sedang menunggu proses peninjauan oleh reviewer.
                Berikut adalah ringkasan pengiriman Anda:
            </p>

            <!-- Info Box -->
            <div class="info-box">
                <div class="label">Judul Naskah</div>
                <div class="value">{{ $title }}</div>
                <div class="meta">Dikirim pada {{ now()->timezone('Asia/Jakarta')->isoFormat('D MMMM YYYY, HH:mm') }}
                    WIB</div>
            </div>

            <!-- Timeline -->
            <div class="steps">
                <div class="steps-title">Alur Proses Publikasi</div>

                <div class="step">
                    <div class="step-icon done">✓</div>
                    <div class="step-text done">Naskah dikirim</div>
                </div>

                <div class="step">
                    <div class="step-icon active">2</div>
                    <div class="step-text active">Menunggu reviewer</div>
                </div>

                <div class="step">
                    <div class="step-icon next">3</div>
                    <div class="step-text">Proses review</div>
                </div>

                <div class="step">
                    <div class="step-icon next">4</div>
                    <div class="step-text">Keputusan editorial</div>
                </div>

                <div class="step">
                    <div class="step-icon next">5</div>
                    <div class="step-text">Publikasi diterbitkan</div>
                </div>
            </div>

            <p class="body-text">
                Selama proses review berlangsung, naskah <strong>tidak dapat diubah</strong>.
                Anda akan mendapat notifikasi segera setelah reviewer memberikan keputusan.
            </p>

            <!-- CTA -->
            <div class="cta-wrap">
                <a href="{{ $editUrl }}" class="cta-button">Pantau Status Naskah →</a>
            </div>
            <p class="cta-note">Tombol di atas akan membuka halaman detail publikasi Anda.</p>

            <hr class="divider">

            <p class="body-text" style="font-size: 14px; color: #888;">
                Jika Anda memiliki pertanyaan, jangan ragu menghubungi tim editorial kami.
                Terima kasih telah mempercayakan karya Anda kepada kami.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem {{ config('app.name') }}.</p>
            <p>Harap tidak membalas email ini.</p>
        </div>
    </div>
</body>

</html>