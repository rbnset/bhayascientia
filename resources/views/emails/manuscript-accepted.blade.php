<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naskah Diterima</title>
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

        .header {
            background: linear-gradient(135deg, #14532d 0%, #16a34a 100%);
            border-radius: 12px 12px 0 0;
            padding: 36px 40px 32px;
            text-align: center;
        }

        .header .logo-text {
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #bbf7d0;
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
            color: #dcfce7;
            letter-spacing: 0.3px;
        }

        .status-bar {
            background-color: #15803d;
            padding: 14px 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-bar span {
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            color: #dcfce7;
            letter-spacing: 0.5px;
        }

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

        .info-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-left: 4px solid #16a34a;
            border-radius: 8px;
            padding: 20px 24px;
            margin: 24px 0;
        }

        .info-box .label {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #16a34a;
            margin-bottom: 6px;
        }

        .info-box .value {
            font-size: 16px;
            font-weight: bold;
            color: #14532d;
            line-height: 1.4;
        }

        .comment-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px 24px;
            margin: 20px 0;
        }

        .comment-box .comment-label {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 10px;
        }

        .comment-box .comment-text {
            font-size: 14px;
            color: #334155;
            line-height: 1.7;
        }

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
            background: #16a34a;
            color: #fff;
            box-shadow: 0 0 0 4px #bbf7d0;
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
            background: linear-gradient(to bottom, #16a34a, #e0e0e0);
        }

        .tl-line.hidden {
            visibility: hidden;
        }

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
            color: #16a34a;
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
            color: #16a34a;
            opacity: 0.8;
        }

        .tip-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 8px 0 28px;
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            color: #14532d;
            line-height: 1.6;
        }

        .tip-box .tip-head {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .cta-wrap {
            text-align: center;
            margin: 28px 0 8px;
        }

        .cta-button {
            display: inline-block;
            background-color: #16a34a;
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

        .divider {
            border: none;
            border-top: 1px solid #eee;
            margin: 28px 0;
        }

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

        <div class="header">
            <div class="logo-text">{{ config('app.name') }}</div>
            <h1>🎉 Selamat! Naskah Anda Diterima</h1>
            <div class="subtitle">Reviewer telah menyetujui naskah Anda untuk diterbitkan</div>
        </div>

        <div class="status-bar">
            <span>✅ Status diperbarui — {{ now()->timezone('Asia/Jakarta')->isoFormat('D MMMM YYYY, HH:mm') }}
                WIB</span>
        </div>

        <div class="card">
            <p class="greeting">Yth. <strong>{{ $authorName }}</strong>,</p>

            <p class="body-text">
                Kami dengan bangga menyampaikan bahwa naskah Anda telah melalui proses
                peninjauan dan <strong>dinyatakan diterima</strong> oleh reviewer.
                Ini adalah pencapaian yang luar biasa!
            </p>

            <div class="info-box">
                <div class="label">Naskah yang diterima</div>
                <div class="value">{{ $title }}</div>
            </div>

            @if($overallComment)
            <div class="comment-box">
                <div class="comment-label">💬 Catatan Akhir dari Reviewer</div>
                <div class="comment-text">{!! strip_tags($overallComment) !!}</div>
            </div>
            @endif

            <div class="timeline">
                <div class="timeline-title">Progres Publikasi Anda</div>

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
                <div class="tl-item">
                    <div class="tl-left">
                        <div class="tl-icon done">✓</div>
                        <div class="tl-line done"></div>
                    </div>
                    <div class="tl-right">
                        <div class="tl-label done">Proses Review Selesai</div>
                        <div class="tl-desc">Reviewer telah menilai naskah secara menyeluruh</div>
                    </div>
                </div>
                <div class="tl-item">
                    <div class="tl-left">
                        <div class="tl-icon active">✓</div>
                        <div class="tl-line active"></div>
                    </div>
                    <div class="tl-right">
                        <div class="tl-label active">Naskah Diterima ← Anda di sini</div>
                        <div class="tl-desc active">Naskah telah disetujui dan siap untuk diterbitkan</div>
                    </div>
                </div>
                <div class="tl-item">
                    <div class="tl-left">
                        <div class="tl-icon pending">4</div>
                        <div class="hidden tl-line"></div>
                    </div>
                    <div class="tl-right">
                        <div class="tl-label pending">Diterbitkan</div>
                        <div class="tl-desc">Naskah resmi tayang dan dapat diakses publik</div>
                    </div>
                </div>
            </div>

            <div class="tip-box">
                <div class="tip-head">🚀 Langkah selanjutnya</div>
                Tim editorial akan segera memproses penerbitan naskah Anda.
                Anda akan mendapat notifikasi kembali begitu naskah resmi tayang dan
                dapat diakses oleh publik.
            </div>

            <div class="cta-wrap">
                <a href="{{ $editUrl }}" class="cta-button">Lihat Status Naskah →</a>
            </div>
            <p class="cta-note">Pantau perkembangan penerbitan naskah Anda di sini.</p>

            <hr class="divider">

            <p class="body-text" style="font-size: 14px; color: #888;">
                Terima kasih atas kontribusi Anda. Karya Anda akan segera dapat dinikmati
                oleh banyak pembaca. Selamat!
            </p>
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem {{ config('app.name') }}.</p>
            <p>Harap tidak membalas email ini.</p>
        </div>

    </div>
</body>

</html>