<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sesi Kedaluwarsa — {{ config('app.name') }}</title>
    <style>
        /* sama persis dengan 403, hanya ganti warna accent ke kuning */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            font-family: ui-sans-serif, system-ui, sans-serif;
            padding: 1.5rem;
        }

        .card {
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 8px 40px rgba(17, 24, 39, .08);
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }

        .icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 5rem;
            height: 5rem;
            border-radius: 50%;
            background: #fefce8;
            margin-bottom: 1.5rem;
        }

        .icon-wrap svg {
            width: 2.5rem;
            height: 2.5rem;
            color: #eab308;
        }

        .code {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #eab308;
            margin-bottom: .5rem;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: .75rem;
            line-height: 1.3;
        }

        p {
            font-size: .95rem;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            padding: .75rem 1.5rem;
            border-radius: .75rem;
            font-weight: 700;
            font-size: .9rem;
            text-decoration: none;
            transition: transform .15s ease, box-shadow .15s ease;
            cursor: pointer;
            border: none;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, .1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #f59e0b, #eab308);
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        <div class="code">Error 419</div>
        <h1>Sesi Anda telah kedaluwarsa</h1>
        <p>Halaman ini sudah terlalu lama tidak aktif. Muat ulang halaman untuk melanjutkan.</p>
        <a href="{{ url()->previous() ?: url('/') }}" class="btn btn-primary">
            ↺ Muat Ulang
        </a>
    </div>
</body>

</html>