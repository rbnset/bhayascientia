<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naskah Sedang Direview</title>
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
            background: linear-gradient(135deg, #1a3c5e 0%, #1e5f8e 100%);
            border-radius: 12px 12px 0 0;
            padding: 36px 40px 32px;
            text-align: center;
        }

        .header .logo-text {
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #90caf9;
            font-family: 'Arial', sans-serif;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .header h1 {
            font-size: 22px;
            color: #ffffff;
            font-weight: normal;
            letter-spacing: 0.3px;
            margin-bottom: 8px;
        }

        .header .subtitle {
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            color: #bbdefb;
            letter-spacing: 0.3px;
        }

        /* ── Status Bar ── */
        .status-bar {
            background-color: #1565c0;
            padding: 14px 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-bar .pulse-wrap {
            position: relative;
            width: 12px;
            height: 12px;
            flex-shrink: 0;
        }

        .status-bar .dot {
            width: 10px;
            height: 10px;
            background: #64b5f6;
            border-radius: 50%;
            position: absolute;
            top: 1px;
            left: 1px;
        }

        .status-bar span {
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            color: #bbdefb;
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

        .body-text {
            font-size: 15px;
            color: #3d3d3d;
            margin-bottom: 16px;
        }

        /* ── Manuscript Info Box ── */
        .info-box {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            border-left: 4px solid #1565c0;
            border-radius: 8px;
            padding: 20px 24px;
            margin: 24px 0;
        }

        .info-box .label {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #1976d2;
            margin-bottom: 6px;
        }

        .info-box .value {
            font-size: 16px;
            font-weight: bold;
            color: #0d2137;
            line-height: 1.4;
        }

        /* ── Timeline ── */
        .timeline {
            margin: 32px 0;
        }

        .timeline-title {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 20px;
        }

        .tl-item {
            display: flex;
            gap: 0;
            align-items: stretch;
            margin-bottom: 0;
        }

        /* Left column: icon + line */
        .tl-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 36px;
            flex-shrink: 0;
        }

        .tl-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            font-family: 'Arial', sans-serif;
            font-weight: bold;
            z-index: 1;
        }

        .tl-icon.done {
            background: #c8e6c9;
            color: #2e7d32;
        }

        .tl-icon.active {
            background: #1565c0;
            color: #fff;
            box-shadow: 0 0 0 4px #bbdefb;
        }

        .tl-icon.pending {
            background: #f5f5f5;
            color: #bbb;
            border: 1.5px dashed #ddd;
        }

        .tl-line {
            width: 2px;
            flex: 1;
            min-height: 20px;
            background: #e0e0e0;
            margin: 2px 0;
        }

        .tl-line.done {
            background: #a5d6a7;
        }

        .tl-line.active {
            background: linear-gradient(to bottom, #1565c0, #e0e0e0);
        }

        .tl-line.hidden {
            visibility: hidden;
        }

        /* Right column: text */
        .tl-right {
            padding: 4px 0 24px 14px;
            flex: 1;
        }

        .tl-right .tl-label {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            line-height: 1.3;
        }

        .tl-right .tl-label.done {
            color: #2e7d32;
        }

        .tl-right .tl-label.active {
            color: #1565c0;
        }

        .tl-right .tl-label.pending {
            color: #aaa;
        }

        .tl-right .tl-desc {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #999;
            margin-top: 2px;
            line-height: 1.5;
        }

        .tl-right .tl-desc.active {
            color: #5c8fbf;
        }

        /* ── Tip Box ── */
        .tip-box {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 8px 0 28px;
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            color: #6d4c00;
            line-height: 1.6;
        }

        .tip-box .tip-head {
            font-weight: bold;
            margin-bottom: 4px;
            color: #5d4037;
        }

        /* ── CTA Button ── */
        .cta-wrap {
            text-align: center;
            margin: 28px 0 8px;
        }

        .cta-button {
            display: inline-block;
            background-color: #1565c0;
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
            margin: 28px 0;
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
            <h1>Naskah Anda Sedang Direview</h1>
            <div class="subtitle">Proses peninjauan telah dimulai oleh tim reviewer kami</div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <div class="pulse-wrap">
                <div class="dot"></div>
            </div>
            <span>Status diperbarui &mdash; {{ now()->timezone('Asia/Jakarta')->isoFormat('D MMMM YYYY, HH:mm') }}
                WIB</span>
        </div>

        <!-- Card -->
        <div class="card">
            <p class="greeting">Yth. <strong>{{ $authorName }}</strong>,</p>

            <p class="body-text">
                Kabar baik! Naskah Anda telah diambil oleh reviewer dan proses peninjauan
                resmi telah dimulai. Berikut adalah naskah yang sedang ditinjau:
            </p>

            <!-- Info Box -->
            <div class="info-box">
                <div class="label">Naskah dalam proses review</div>
                <div class="value">{{ $title }}</div>
            </div>

            <!-- Timeline -->
            <div class="timeline">
                <div class="timeline-title">Progres Publikasi Anda</div>

                {{-- Step 1: Submitted --}}
                <div class="tl-item">
                    <div class="tl-left">
                        <div class="tl-icon done">✓</div>
                        <div class="tl-line done"></div>
                    </div>
                    <div class="tl-right">
                        <div class="tl-label done">Naskah Dikirim</div>
                        <div class="tl-desc">Naskah berhasil masuk ke sistem editorial</div>
                    </div>
                </div>

                {{-- Step 2: In Review (ACTIVE) --}}
                <div class="tl-item">
                    <div class="tl-left">
                        <div class="tl-icon active">&#9679;</div>
                        <div class="tl-line active"></div>
                    </div>
                    <div class="tl-right">
                        <div class="tl-label active">Proses Review &nbsp;← Anda di sini</div>
                        <div class="tl-desc active">Reviewer sedang membaca dan menilai naskah Anda secara menyeluruh
                        </div>
                    </div>
                </div>

                {{-- Step 3: Keputusan --}}
                <div class="tl-item">
                    <div class="tl-left">
                        <div class="tl-icon pending">3</div>
                        <div class="tl-line pending"></div>
                    </div>
                    <div class="tl-right">
                        <div class="tl-label pending">Keputusan Editorial</div>
                        <div class="tl-desc">Diterima, revisi diminta, atau ditolak</div>
                    </div>
                </div>

                {{-- Step 4: Revisi (opsional) --}}
                <div class="tl-item">
                    <div class="tl-left">
                        <div class="tl-icon pending">4</div>
                        <div class="tl-line pending"></div>
                    </div>
                    <div class="tl-right">
                        <div class="tl-label pending">Revisi (jika diperlukan)</div>
                        <div class="tl-desc">Anda dapat mengunggah revisi sesuai catatan reviewer</div>
                    </div>
                </div>

                {{-- Step 5: Published --}}
                <div class="tl-item">
                    <div class="tl-left">
                        <div class="tl-icon pending">5</div>
                        <div class="hidden tl-line"></div>
                    </div>
                    <div class="tl-right">
                        <div class="tl-label pending">Diterbitkan</div>
                        <div class="tl-desc">Naskah resmi tayang dan dapat diakses publik</div>
                    </div>
                </div>
            </div>

            <!-- Tip -->
            <div class="tip-box">
                <div class="tip-head">💡 Yang perlu Anda tahu selama proses review:</div>
                Naskah tidak dapat diubah saat sedang direview. Anda akan mendapat notifikasi
                segera setelah reviewer selesai dan memberikan keputusan. Harap bersabar —
                reviewer kami bekerja seteliti mungkin demi kualitas publikasi.
            </div>

            <!-- CTA -->
            <div class="cta-wrap">
                <a href="{{ $editUrl }}" class="cta-button">Pantau Status Naskah →</a>
            </div>
            <p class="cta-note">Halaman ini menampilkan status terkini naskah Anda.</p>

            <hr class="divider">

            <p class="body-text" style="font-size: 14px; color: #888;">
                Jika ada pertanyaan mengenai proses review, jangan ragu menghubungi
                tim editorial kami. Terima kasih atas kepercayaan Anda.
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