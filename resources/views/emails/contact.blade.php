<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #FF6B18, #E64627);
            padding: 28px 30px;
            text-align: center;
        }

        .header h1 {
            color: white;
            font-size: 20px;
        }

        .header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 13px;
            margin-top: 6px;
        }

        .body {
            padding: 30px;
        }

        .field {
            margin-bottom: 18px;
        }

        .label {
            font-size: 11px;
            color: #737373;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .value {
            font-size: 14px;
            color: #1A1A1A;
            padding: 10px 14px;
            background: #F8F9FC;
            border-radius: 8px;
            border-left: 3px solid #FF6B18;
            line-height: 1.6;
        }

        .divider {
            border: none;
            border-top: 1px solid #EEF0F7;
            margin: 20px 0;
        }

        .reply-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 28px;
            background: linear-gradient(135deg, #FF6B18, #E64627);
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            background: #F8F9FC;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #737373;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📩 Pesan Baru Masuk</h1>
            <p>Ada pesan baru dari form kontak DABRAKA</p>
        </div>
        <div class="body">
            <div class="field">
                <div class="label">Nama Pengirim</div>
                <div class="value">{{ $data['name'] }}</div>
            </div>
            <div class="field">
                <div class="label">Email</div>
                <div class="value">{{ $data['email'] }}</div>
            </div>
            @if(!empty($data['phone']))
            <div class="field">
                <div class="label">Nomor Telepon</div>
                <div class="value">{{ $data['phone'] }}</div>
            </div>
            @endif
            <div class="field">
                <div class="label">Subjek</div>
                <div class="value">{{ $data['subject'] }}</div>
            </div>
            <hr class="divider">
            <div class="field">
                <div class="label">Isi Pesan</div>
                <div class="value" style="white-space: pre-line;">{{ $data['message'] }}</div>
            </div>
            <a href="mailto:{{ $data['email'] }}" class="reply-btn">Balas Pesan →</a>
        </div>
        <div class="footer">
            Dikirim otomatis dari form kontak DABRAKA<br>
            {{ now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
        </div>
    </div>
</body>

</html>