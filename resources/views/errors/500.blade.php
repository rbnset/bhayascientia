<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kesalahan Server — {{ config('app.name') }}</title>
    <style>
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
            background: #fef2f2;
            margin-bottom: 1.5rem;
        }

        .icon-wrap svg {
            width: 2.5rem;
            height: 2.5rem;
            color: #ef4444;
        }

        .code {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #ef4444;
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

        .actions {
            display: flex;
            flex-direction: column;
            gap: .75rem;
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
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        @media (min-width:400px) {
            .actions {
                flex-direction: row;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </div>
        <div class="code">Error 500</div>
        <h1>Terjadi kesalahan pada server</h1>
        <p>
            Kami sedang memperbaiki masalah ini. Silakan coba beberapa saat lagi.
            Jika masalah berlanjut, hubungi administrator.
        </p>
        <div class="actions">
            <a href="{{ url()->previous() ?: url('/') }}" class="btn btn-secondary">← Kembali</a>
            <a href="{{ url('/admin') }}" class="btn btn-primary">Ke Dashboard</a>
        </div>
    </div>
</body>

</html>