<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Terlalu Banyak Permintaan — {{ config('app.name') }}</title>
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
            background: #fdf4ff;
            margin-bottom: 1.5rem;
        }

        .icon-wrap svg {
            width: 2.5rem;
            height: 2.5rem;
            color: #a855f7;
        }

        .code {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #a855f7;
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

        /* Countdown timer */
        .countdown-wrap {
            margin-bottom: 2rem;
        }

        .countdown-label {
            font-size: .8rem;
            color: #9ca3af;
            margin-bottom: .5rem;
        }

        .countdown {
            font-size: 2.5rem;
            font-weight: 900;
            color: #a855f7;
            font-variant-numeric: tabular-nums;
            line-height: 1;
        }

        .countdown-unit {
            font-size: .75rem;
            color: #9ca3af;
            font-weight: 600;
            margin-top: .15rem;
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
            background: linear-gradient(135deg, #a855f7, #7c3aed);
            color: #fff;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, .1);
        }

        .btn:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
            </svg>
        </div>

        <div class="code">Error 429</div>
        <h1>Terlalu banyak permintaan</h1>
        <p>
            Anda melakukan terlalu banyak aksi dalam waktu singkat.
            Tunggu sebentar sebelum mencoba lagi.
        </p>

        {{-- Countdown otomatis jika Retry-After tersedia --}}
        @php
        $retryAfter = request()->header('Retry-After')
        ?? (isset($exception) && method_exists($exception, 'getHeaders')
        ? ($exception->getHeaders()['Retry-After'] ?? 60)
        : 60);
        @endphp

        <div class="countdown-wrap">
            <div class="countdown-label">Coba lagi dalam</div>
            <div class="countdown" id="countdown">{{ $retryAfter }}</div>
            <div class="countdown-unit">detik</div>
        </div>

        <button class="btn" id="retry-btn" disabled onclick="location.reload()">
            ↺ Coba Lagi
        </button>
    </div>

    <script>
        let seconds = parseInt(document.getElementById('countdown').textContent) || 60;
    const countdownEl = document.getElementById('countdown');
    const retryBtn    = document.getElementById('retry-btn');

    const timer = setInterval(() => {
        seconds--;
        countdownEl.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(timer);
            countdownEl.textContent = '0';
            retryBtn.disabled = false;
            retryBtn.textContent = '↺ Coba Lagi Sekarang';
        }
    }, 1000);
    </script>
</body>

</html>