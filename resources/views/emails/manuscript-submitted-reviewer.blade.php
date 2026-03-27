<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publikasi Baru Menunggu Review</title>
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
            background-color: #2c2c54;
            border-radius: 12px 12px 0 0;
            padding: 36px 40px 32px;
            text-align: center;
        }

        .header .logo-text {
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #a29bfe;
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

        /* ── Urgency Bar ── */
        .urgency-bar {
            background-color: #4a4a8a;
            padding: 14px 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .urgency-bar .dot {
            width: 10px;
            height: 10px;
            background: #a29bfe;
            border-radius: 50%;
            flex-shrink: 0;
            animation: pulse 2s infinite;
        }

        .urgency-bar span {
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            color: #dcd7fe;
            letter-spacing: 0.5px;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
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

        .body-text {
            font-size: 15px;
            color: #3d3d3d;
            margin-bottom: 16px;
        }

        /* ── Manuscript Info Box ── */
        .info-box {
            background: #f5f3ff;
            border: 1px solid #d4cffe;
            border-left: 4px solid #2c2c54;
            border-radius: 8px;
            padding: 20px 24px;
            margin: 28px 0;
        }

        .info-row {
            margin-bottom: 12px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-row .label {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 3px;
        }

        .info-row .value {
            font-size: 15px;
            color: #1a1a1a;
            font-weight: 600;
            line-height: 1.4;
        }

        /* ── Responsibility Note ── */
        .note-box {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 24px 0;
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            color: #795548;
            line-height: 1.6;
        }

        .note-box strong {
            color: #5d4037;
        }

        /* ── CTA Button ── */
        .cta-wrap {
            text-align: center;
            margin: 32px 0 8px;
        }

        .cta-button {
            display: inline-block;
            background-color: #2c2c54;
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
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <div class="logo-text">{{ config('app.name') }}</div>
            <h1>Ada Naskah Baru untuk Direview</h1>
        </div>

        <!-- Urgency Bar -->
        <div class="urgency-bar">
            <div class="dot"></div>
            <span>Diterima {{ now()->timezone('Asia/Jakarta')->isoFormat('D MMMM YYYY, HH:mm') }} WIB &mdash; Menunggu
                penugasan reviewer</span>
        </div>

        <!-- Card -->
        <div class="card">
            <p class="greeting">Yth. <strong>Tim Reviewer</strong>,</p>

            <p class="body-text">
                Sebuah naskah baru telah masuk ke sistem dan siap untuk ditinjau.
                Harap segera buka dashboard untuk mengambil tugas review ini.
            </p>

            <!-- Info Box -->
            <div class="info-box">
                <div class="info-row">
                    <div class="label">Judul Naskah</div>
                    <div class="value">{{ $title }}</div>
                </div>

                <div class="info-row">
                    <div class="label">Jenis Publikasi</div>
                    <div class="value">{{ $type }}</div>
                </div>

                <div class="info-row">
                    <div class="label">Penulis</div>
                    <div class="value">{{ $authorNames }}</div>
                </div>

                <div class="info-row">
                    <div class="label">Tanggal Masuk</div>
                    <div class="value">{{ now()->timezone('Asia/Jakarta')->isoFormat('D MMMM YYYY, HH:mm') }} WIB</div>
                </div>
            </div>

            <!-- Note -->
            <div class="note-box">
                <strong>Catatan:</strong> Untuk menjaga kualitas dan kepercayaan penulis, mohon segera lakukan
                peninjauan dalam waktu yang wajar. Akses naskah lengkap tersedia di dashboard editorial.
            </div>

            <!-- CTA -->
            <div class="cta-wrap">
                <a href="{{ $reviewUrl }}" class="cta-button">Buka Halaman Review →</a>
            </div>
            <p class="cta-note">Anda perlu login terlebih dahulu jika sesi sudah berakhir.</p>

            <hr class="divider">

            <p class="body-text" style="font-size: 14px; color: #888;">
                Email ini dikirim karena Anda terdaftar sebagai reviewer aktif.
                Jika ada pertanyaan teknis, silakan hubungi administrator sistem.
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