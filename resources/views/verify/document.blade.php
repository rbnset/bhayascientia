<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verifikasi Dokumen — {{ config('app.name') }}</title>
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
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            font-family: ui-sans-serif, system-ui, sans-serif;
            padding: 1.5rem;
            gap: 1.5rem;
        }

        /* ── Brand ── */
        .brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1rem;
            font-weight: 900;
            color: #111827;
            letter-spacing: -0.01em;
        }

        .brand span {
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
        }

        /* ── Card ── */
        .card {
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 8px 40px rgba(17, 24, 39, 0.08);
            padding: 2.5rem 2rem;
            max-width: 520px;
            width: 100%;
        }

        /* ── Result header ── */
        .result-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 0.75rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            margin-bottom: 1.5rem;
        }

        .icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 50%;
        }

        .icon-wrap.valid {
            background: #f0fdf4;
        }

        .icon-wrap.invalid {
            background: #fef2f2;
        }

        .icon-wrap svg {
            width: 2.25rem;
            height: 2.25rem;
        }

        .icon-wrap.valid svg {
            color: #22c55e;
        }

        .icon-wrap.invalid svg {
            color: #ef4444;
        }

        .result-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #111827;
        }

        .result-subtitle {
            font-size: 0.9rem;
            color: #6b7280;
            line-height: 1.5;
        }

        /* ── Code badge ── */
        .code-badge {
            display: inline-block;
            background: #f3f4f6;
            border-radius: 0.5rem;
            padding: 0.3rem 0.75rem;
            font-family: ui-monospace, monospace;
            font-size: 0.85rem;
            font-weight: 700;
            color: #374151;
            letter-spacing: 0.05em;
        }

        /* ── Meta table ── */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .meta-table tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .meta-table tr:last-child {
            border-bottom: none;
        }

        .meta-table td {
            padding: 0.65rem 0;
            vertical-align: top;
        }

        .meta-table td:first-child {
            color: #9ca3af;
            font-weight: 600;
            width: 40%;
            padding-right: 1rem;
        }

        .meta-table td:last-child {
            color: #111827;
            font-weight: 500;
        }

        /* ── Status badge ── */
        .status-badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-draft {
            background: #f3f4f6;
            color: #374151;
        }

        .status-submitted {
            background: #fef3c7;
            color: #92400e;
        }

        .status-in_review {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-revision_required {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-accepted {
            background: #dcfce7;
            color: #166534;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-published {
            background: #d1fae5;
            color: #065f46;
        }

        /* ── Footer note ── */
        .footer-note {
            font-size: 0.75rem;
            color: #9ca3af;
            text-align: center;
            line-height: 1.6;
        }

        /* ── Manual verify form ── */
        .verify-form {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .verify-form input {
            flex: 1;
            min-width: 0;
            padding: 0.6rem 0.9rem;
            border: 1px solid #d1d5db;
            border-radius: 0.6rem;
            font-size: 0.875rem;
            font-family: ui-monospace, monospace;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            outline: none;
            color: #111827;
        }

        .verify-form input:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }

        .verify-form button {
            padding: 0.6rem 1.2rem;
            background: linear-gradient(135deg, #f59e0b, #f97316);
            color: #fff;
            border: none;
            border-radius: 0.6rem;
            font-weight: 700;
            font-size: 0.875rem;
            cursor: pointer;
            transition: transform 0.15s ease;
        }

        .verify-form button:hover {
            transform: translateY(-1px);
        }
    </style>
</head>

<body>

    {{-- Brand ─────────────────────────────────────────────────── --}}
    <div class="brand">
        @if(file_exists(public_path('images/dabraka-logo.png')))
        <img src="{{ asset('images/dabraka-logo.png') }}" alt="Logo" height="28">
        @endif
        DABRAKA
        <span>Darma Brata Buana Cendekia</span>
    </div>

    {{-- Card ──────────────────────────────────────────────────── --}}
    <div class="card">

        @if($valid)
        {{-- ✅ VALID ─────────────────────────────────────────── --}}
        <div class="result-header">
            <div class="icon-wrap valid">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <div class="result-title">Dokumen Terverifikasi</div>
                <div class="result-subtitle">
                    Dokumen ini asli dan terdaftar di sistem DABRAKA.
                </div>
            </div>
            <div class="code-badge">{{ $code }}</div>
        </div>

        <table class="meta-table">
            <tr>
                <td>Judul</td>
                <td>{{ $pub->title }}</td>
            </tr>
            <tr>
                <td>Tipe</td>
                <td>{{ $pub->publicationType?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td>Versi Dokumen</td>
                <td>Versi {{ $version->version_number }}</td>
            </tr>
            <tr>
                <td>Tanggal Submit</td>
                <td>{{ $version->submitted_at?->translatedFormat('d F Y, H:i') ?? '—' }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>
                    <span class="status-badge status-{{ $pub->status }}">
                        {{ str($pub->status)->headline() }}
                    </span>
                </td>
            </tr>
            <tr>
                <td>Penulis</td>
                <td>
                    {{ $pub->authors->sortBy('pivot.order')->pluck('name')->filter()->implode(', ') ?: '—' }}
                </td>
            </tr>
            @if($pub->published_at)
            <tr>
                <td>Diterbitkan</td>
                <td>{{ $pub->published_at->translatedFormat('d F Y') }}</td>
            </tr>
            @endif
        </table>

        @else
        {{-- ❌ INVALID ─────────────────────────────────────────── --}}
        <div class="result-header">
            <div class="icon-wrap invalid">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <div>
                <div class="result-title">Dokumen Tidak Valid</div>
                <div class="result-subtitle">
                    Kode <span class="code-badge">{{ $code }}</span> tidak ditemukan
                    atau tidak cocok dengan dokumen manapun di sistem kami.
                </div>
            </div>
        </div>

        <p style="font-size:0.875rem;color:#6b7280;margin-bottom:0.5rem;">
            Coba verifikasi dengan kode lain:
        </p>
        @endif

        {{-- Form verifikasi manual ──────────────────────────── --}}
        <form method="GET" action="" class="verify-form"
            onsubmit="event.preventDefault(); window.location='/verify/'+encodeURIComponent(this.code.value.toUpperCase())">
            <input type="text" name="code" placeholder="DBK-0001-V1-A3F8C1" value="{{ $valid ? '' : $code }}"
                maxlength="30" autocomplete="off" spellcheck="false" />
            <button type="submit">Cek</button>
        </form>
    </div>

    {{-- Footer note ─────────────────────────────────────────── --}}
    <p class="footer-note">
        Sistem verifikasi dokumen DABRAKA.<br>
        Kode tertera di pojok kanan atas setiap halaman PDF.
    </p>

</body>

</html>