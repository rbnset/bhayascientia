<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Akses Ditolak — {{ config('app.name') }}</title>
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
            background: #fff7ed;
            margin-bottom: 1.5rem;
        }

        .icon-wrap svg {
            width: 2.5rem;
            height: 2.5rem;
            color: #f97316;
        }

        .code {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #f97316;
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
            background: linear-gradient(135deg, #f59e0b, #f97316);
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

    // Gunakan URL sebelumnya jika valid dan berbeda dari halaman ini.
    // Fallback ke /admin (dashboard Filament) jika tidak ada history.
    $backUrl = ($previous && $previous !== $current)
    ? $previous
    : url('/admin');
    @endphp

    <div class="card">

        {{-- Icon kunci --}}
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75
                     m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25
                     v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75
                     a2.25 2.25 0 0 0-2.25 2.25v6.75
                     a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>

        <div class="code">Error 403</div>

        <h1>Anda tidak memiliki akses ke halaman ini</h1>

        <p>
            @if(! empty($exception?->getMessage()))
            {{ $exception->getMessage() }}
            @else
            Halaman ini memerlukan izin khusus.
            Jika Anda merasa ini kesalahan, silakan hubungi administrator.
            @endif
        </p>

        <div class="actions">
            {{-- Kembali ke halaman tepat sebelumnya --}}
            <a href="{{ $backUrl }}" class="btn btn-secondary">
                ← Kembali
            </a>

            {{-- Selalu ke dashboard --}}
            <a href="{{ url('/admin') }}" class="btn btn-primary">
                Ke Dashboard
            </a>
        </div>

    </div>
</body>

</html>