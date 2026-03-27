<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Review Naskah</title>
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
            background: linear-gradient(135deg, #1e1b4b 0%, #4338ca 100%);
            border-radius: 12px 12px 0 0;
            padding: 36px 40px 32px;
            text-align: center;
        }

        .header .logo-text {
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #c7d2fe;
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
            color: #e0e7ff;
            letter-spacing: 0.3px;
        }

        .status-bar {
            background-color: #3730a3;
            padding: 14px 40px;
        }

        .status-bar span {
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            color: #c7d2fe;
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
            background: #eef2ff;
            border: 1px solid #a5b4fc;
            border-left: 4px solid #4338ca;
            border-radius: 8px;
            padding: 20px 24px;
            margin: 24px 0;
        }

        .info-box .label {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #4338ca;
            margin-bottom: 6px;
        }

        .info-box .value {
            font-size: 16px;
            font-weight: bold;
            color: #1e1b4b;
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

        .encourage-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 20px 0 28px;
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            color: #78350f;
            line-height: 1.6;
        }

        .encourage-box .enc-head {
            font-weight: bold;
            margin-bottom: 4px;
            color: #92400e;
        }

        .cta-wrap {
            text-align: center;
            margin: 28px 0 8px;
        }

        .cta-button {
            display: inline-block;
            background-color: #4338ca;
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
            <h1>Hasil Review Naskah Anda</h1>
            <div class="subtitle">Proses peninjauan telah selesai dilakukan oleh reviewer</div>
        </div>

        <div class="status-bar">
            <span>📋 Status diperbarui — {{ now()->timezone('Asia/Jakarta')->isoFormat('D MMMM YYYY, HH:mm') }}
                WIB</span>
        </div>

        <div class="card">
            <p class="greeting">Yth. <strong>{{ $authorName }}</strong>,</p>

            <p class="body-text">
                Terima kasih telah mengirimkan karya Anda dan bersabar selama proses peninjauan.
                Setelah melalui evaluasi yang cermat, reviewer kami memutuskan bahwa naskah
                berikut <strong>belum dapat diterima</strong> pada tahap ini.
            </p>

            <div class="info-box">
                <div class="label">Naskah yang ditinjau</div>
                <div class="value">{{ $title }}</div>
            </div>

            @if($overallComment)
            <div class="comment-box">
                <div class="comment-label">💬 Catatan dari Reviewer</div>
                <div class="comment-text">{!! strip_tags($overallComment) !!}</div>
            </div>
            @endif

            <div class="encourage-box">
                <div class="enc-head">💡 Jangan menyerah — ini bukan akhir</div>
                Penolakan adalah bagian dari proses kreatif dan ilmiah. Banyak karya terbaik
                melewati beberapa kali revisi sebelum akhirnya diterima. Pelajari catatan
                reviewer dengan seksama, perbaiki naskah Anda, dan jangan ragu untuk
                mengirimkan karya berikutnya.
            </div>

            <div class="cta-wrap">
                <a href="{{ $editUrl }}" class="cta-button">Lihat Detail Naskah →</a>
            </div>
            <p class="cta-note">Anda dapat melihat catatan lengkap reviewer melalui tautan di atas.</p>

            <hr class="divider">

            <p class="body-text" style="font-size: 14px; color: #888;">
                Jika Anda memiliki pertanyaan mengenai keputusan ini, jangan ragu untuk
                menghubungi tim editorial kami. Kami menghargai setiap kontribusi yang
                Anda berikan.
            </p>
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem {{ config('app.name') }}.</p>
            <p>Harap tidak membalas email ini.</p>
        </div>

    </div>
</body>

</html>