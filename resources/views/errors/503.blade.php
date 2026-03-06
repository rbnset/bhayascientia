<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sedang Maintenance — {{ config('app.name') }}</title>
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
            background: #f0fdf4;
            margin-bottom: 1.5rem;
        }

        .icon-wrap svg {
            width: 2.5rem;
            height: 2.5rem;
            color: #22c55e;
        }

        .code {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #22c55e;
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
            margin-bottom: 1.5rem;
        }

        .retry-box {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: .75rem;
            padding: .6rem 1rem;
            font-size: .85rem;
            color: #15803d;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        .dot {
            width: .5rem;
            height: .5rem;
            border-radius: 50%;
            background: #22c55e;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .3
            }
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
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, .1);
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.65-4.654 4.654-5.654a2.548 2.548 0 0 1 3.586 3.586l-5.654 4.654m-4.654 5.65-.008.002-.009.002a.75.75 0 0 1-.878-.878l.002-.009.002-.008" />
            </svg>
        </div>

        <div class="code">Error 503</div>
        <h1>Sedang dalam pemeliharaan</h1>
        <p>
            {{ $exception?->getMessage() ?: config('app.name') . ' sedang dalam pemeliharaan singkat untuk meningkatkan
            layanan. Kami akan segera kembali.' }}
        </p>

        {{-- Retry-After jika tersedia --}}
        @if(isset($exception) && method_exists($exception, 'retryAfter') && $exception->retryAfter())
        <div class="retry-box">
            <span class="dot"></span>
            Estimasi selesai: {{ $exception->retryAfter() }}
        </div>
        @else
        <div class="retry-box">
            <span class="dot"></span>
            Silakan coba beberapa menit lagi
        </div>
        @endif

        <a href="javascript:location.reload()" class="btn">
            ↺ Coba Lagi
        </a>
    </div>
</body>

</html>