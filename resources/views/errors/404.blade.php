<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Halaman Tidak Ditemukan — {{ config('app.name') }}</title>
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
            box-shadow: 0 8px 40px rgba(17, 24, 39, 0.08);
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
            background: #eff6ff;
            margin-bottom: 1.5rem;
        }

        .icon-wrap svg {
            width: 2.5rem;
            height: 2.5rem;
            color: #3b82f6;
        }

        .code {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #3b82f6;
            margin-bottom: 0.5rem;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        p {
            font-size: 0.95rem;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .url-box {
            display: inline-block;
            background: #f3f4f6;
            border-radius: 0.5rem;
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
            font-family: ui-monospace, monospace;
            color: #6b7280;
            word-break: break-all;
            margin-bottom: 1.75rem;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            cursor: pointer;
            border: none;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: #fff;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        @media (min-width: 400px) {
            .actions {
                flex-direction: row;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    @php
    $previous = url()->previous();
    $current = url()->current();
    $backUrl = ($previous && $previous !== $current)
    ? $previous
    : url('/admin');
    @endphp

    <div class="card">

        {{-- Icon --}}
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5
                     A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0
                     0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12
                     M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25
                     c0 .621.504 1.125 1.125 1.125h12.75c.621 0
                     1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
        </div>

        <div class="code">Error 404</div>

        <h1>Halaman tidak ditemukan</h1>

        <p>
            Halaman yang Anda cari tidak ada, sudah dipindahkan,
            atau URL yang dimasukkan tidak tepat.
        </p>

        {{-- Tampilkan URL yang dicoba --}}
        <div class="url-box">{{ $current }}</div>

        <div class="actions">
            <a href="{{ $backUrl }}" class="btn btn-secondary">
                ← Kembali
            </a>
            <a href="{{ url('/admin') }}" class="btn btn-primary">
                Ke Dashboard
            </a>
        </div>

    </div>
</body>

</html>