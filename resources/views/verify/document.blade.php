<!DOCTYPE html>
<html lang="id" class="antialiased">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen — {{ config('app.name') }}</title>
    <meta name="robots" content="noindex, nofollow">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #F5F3EE;
            --surface: #FDFCF9;
            --border: #E2DDD4;
            --text-main: #1C1917;
            --text-muted: #78716C;
            --text-faint: #A8A29E;
            --valid-bg: #F0FDF4;
            --valid-ring: #16A34A;
            --valid-text: #14532D;
            --valid-acc: #22C55E;
            --invalid-bg: #FFF7F7;
            --invalid-ring: #DC2626;
            --invalid-text: #7F1D1D;
            --invalid-acc: #EF4444;
            --gold: #B45309;
            --gold-light: #FEF3C7;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Noise texture overlay ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        /* ── Header ── */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(245, 243, 238, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0 clamp(1rem, 5vw, 3rem);
        }

        .header-inner {
            max-width: 960px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
        }

        .site-logo {
            font-family: 'Instrument Serif', serif;
            font-size: 1.2rem;
            color: var(--text-main);
            text-decoration: none;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gold);
        }

        .header-badge {
            font-size: 0.7rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ── Main ── */
        main {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: clamp(2rem, 6vw, 5rem) clamp(1rem, 5vw, 2rem);
            position: relative;
            z-index: 1;
        }

        .container {
            width: 100%;
            max-width: 680px;
        }

        /* ── Page title ── */
        .page-eyebrow {
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--text-faint);
            font-weight: 500;
            margin-bottom: 10px;
        }

        .page-title {
            font-family: 'Instrument Serif', serif;
            font-size: clamp(2rem, 5vw, 2.8rem);
            line-height: 1.1;
            color: var(--text-main);
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .page-title em {
            font-style: italic;
            color: var(--gold);
        }

        .page-subtitle {
            font-size: 0.92rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            max-width: 480px;
            line-height: 1.6;
        }

        /* ── Code display ── */
        .code-display {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 16px;
            margin-bottom: 2rem;
        }

        .code-label {
            font-size: 0.72rem;
            color: var(--text-faint);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .code-value {
            font-family: 'DM Mono', 'Courier New', monospace;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            letter-spacing: 0.06em;
        }

        .code-sep {
            color: var(--border);
            font-size: 1rem;
        }

        /* ── Result card ── */
        .result-card {
            background: var(--surface);
            border-radius: 20px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.04);
            animation: cardIn 0.5s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ── Status banner ── */
        .status-banner {
            padding: 24px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .status-banner.valid {
            background: var(--valid-bg);
            border-bottom: 1px solid #BBF7D0;
        }

        .status-banner.invalid {
            background: var(--invalid-bg);
            border-bottom: 1px solid #FECACA;
        }

        .status-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .status-icon.valid {
            background: var(--valid-acc);
            color: #fff;
        }

        .status-icon.invalid {
            background: var(--invalid-acc);
            color: #fff;
        }

        .status-icon svg {
            width: 22px;
            height: 22px;
        }

        .status-text {
            flex: 1;
        }

        .status-title {
            font-family: 'Instrument Serif', serif;
            font-size: 1.25rem;
            margin-bottom: 2px;
        }

        .status-title.valid {
            color: var(--valid-text);
        }

        .status-title.invalid {
            color: var(--invalid-text);
        }

        .status-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* ── Info grid ── */
        .info-grid {
            padding: 24px 28px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-item.full {
            grid-column: 1 / -1;
        }

        .info-key {
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-faint);
            font-weight: 500;
        }

        .info-val {
            font-size: 0.95rem;
            color: var(--text-main);
            font-weight: 500;
            line-height: 1.4;
        }

        .info-val.muted {
            color: var(--text-muted);
            font-weight: 400;
        }

        /* ── Status badge ── */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .status-pill.published {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-pill.draft {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-pill.review {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .status-pill-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        /* ── Divider ── */
        .card-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 0 28px;
        }

        /* ── Scan counter ── */
        .scan-strip {
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #FAF9F6;
        }

        .scan-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .scan-icon {
            color: var(--text-faint);
        }

        .scan-text {
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .scan-count {
            font-size: 0.82rem;
            font-weight: 600;
            background: var(--gold-light);
            color: var(--gold);
            padding: 2px 10px;
            border-radius: 999px;
        }

        /* ── Actions ── */
        .card-actions {
            padding: 20px 28px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            border-top: 1px solid var(--border);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 0.87rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: var(--text-main);
            color: #fff;
        }

        .btn-primary:hover {
            background: #292524;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        .btn-ghost:hover {
            background: var(--bg);
            color: var(--text-main);
        }

        .btn svg {
            width: 16px;
            height: 16px;
        }

        /* ── Invalid tips ── */
        .tips-list {
            padding: 20px 28px;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            border-top: 1px solid #FECACA;
        }

        .tips-list li {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .tips-list li::before {
            content: '→';
            color: var(--invalid-acc);
            font-size: 0.8rem;
            margin-top: 1px;
            flex-shrink: 0;
        }

        /* ── Footer ── */
        .site-footer {
            text-align: center;
            padding: 24px;
            font-size: 0.78rem;
            color: var(--text-faint);
            border-top: 1px solid var(--border);
            position: relative;
            z-index: 1;
        }

        .site-footer a {
            color: var(--text-muted);
            text-decoration: none;
        }

        .site-footer a:hover {
            color: var(--text-main);
        }

        /* ── Responsive ── */
        @media (max-width: 520px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .status-banner {
                padding: 18px 20px;
            }

            .card-actions {
                padding: 16px 20px;
            }

            .card-divider {
                margin: 0 20px;
            }

            .scan-strip,
            .tips-list,
            .info-grid {
                padding-left: 20px;
                padding-right: 20px;
            }
        }
    </style>
</head>

<body>

    {{-- ── Header ── --}}
    <header class="site-header">
        <div class="header-inner">
            <a href="{{ url('/') }}" class="site-logo">
                <span class="logo-dot"></span>
                {{ config('app.name') }}
            </a>
            <span class="header-badge">Portal Verifikasi</span>
        </div>
    </header>

    {{-- ── Main ── --}}
    <main>
        <div class="container">

            <p class="page-eyebrow">Sistem Verifikasi Dokumen</p>
            <h1 class="page-title">Keaslian <em>Dokumen</em></h1>
            <p class="page-subtitle">
                Hasil pengecekan kode verifikasi untuk memastikan dokumen yang Anda terima adalah dokumen resmi dan
                tidak dimanipulasi.
            </p>

            {{-- Kode yang dicek --}}
            <div class="code-display">
                <span class="code-label">Kode</span>
                <span class="code-sep">|</span>
                <span class="code-value">{{ $code }}</span>
            </div>

            {{-- Result card --}}
            <div class="result-card">

                @if($valid && $pub && $version)
                {{-- ✅ VALID --}}

                <div class="status-banner valid">
                    <div class="status-icon valid">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                    </div>
                    <div class="status-text">
                        <p class="status-title valid">Dokumen Terverifikasi</p>
                        <p class="status-desc">Dokumen ini sah dan dikeluarkan secara resmi oleh {{ config('app.name')
                            }}.</p>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item full">
                        <span class="info-key">Judul Publikasi</span>
                        <span class="info-val">{{ $pub->title }}</span>
                    </div>

                    @if($pub->author)
                    <div class="info-item">
                        <span class="info-key">Penulis / Author</span>
                        <span class="info-val">{{ $pub->author }}</span>
                    </div>
                    @endif

                    <div class="info-item">
                        <span class="info-key">Versi Dokumen</span>
                        <span class="info-val">Versi {{ $version->version_number }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-key">Tanggal Diterbitkan</span>
                        <span class="info-val muted">
                            {{ $version->created_at?->translatedFormat('d F Y') ?? '-' }}
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-key">Status</span>
                        <span class="info-val">
                            @php
                            $status = strtolower($pub->status ?? 'published');
                            $labels = ['published' => 'Diterbitkan', 'draft' => 'Draft', 'review' => 'Dalam Review'];
                            @endphp
                            <span class="status-pill {{ $status }}">
                                <span class="status-pill-dot"></span>
                                {{ $labels[$status] ?? ucfirst($status) }}
                            </span>
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-key">ID Publikasi</span>
                        <span class="info-val muted">#{{ str_pad($pub->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                @if($verification)
                <hr class="card-divider">
                <div class="scan-strip">
                    <div class="scan-info">
                        <svg class="scan-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <span class="scan-text">
                            Terakhir discan {{ $verification->last_scanned_at?->diffForHumans() ?? 'baru saja' }}
                        </span>
                    </div>
                    <span class="scan-count">{{ number_format($verification->scan_count) }}× discan</span>
                </div>
                @endif

                <div class="card-actions">
                    @auth
                    @if($version->pdf_file_path)
                    <a href="{{ route('manuscripts.view', $version) }}" target="_blank" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        Lihat Dokumen
                    </a>
                    <a href="{{ route('manuscripts.download', $version) }}" class="btn btn-ghost">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        Unduh
                    </a>
                    @endif
                    @endauth
                    <a href="{{ url('/') }}" class="btn btn-ghost">
                        ← Kembali
                    </a>
                </div>

                @else
                {{-- ❌ INVALID --}}

                <div class="status-banner invalid">
                    <div class="status-icon invalid">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </div>
                    <div class="status-text">
                        <p class="status-title invalid">Dokumen Tidak Valid</p>
                        <p class="status-desc">Kode verifikasi <strong>{{ $code }}</strong> tidak ditemukan atau tidak
                            sesuai.</p>
                    </div>
                </div>

                <ul class="tips-list">
                    <li>Pastikan kode disalin secara lengkap dan benar dari dokumen asli.</li>
                    <li>Format kode yang valid: <code>DBK-XXXX-VX-XXXXXX</code> (huruf kapital semua).</li>
                    <li>Dokumen mungkin telah dimodifikasi atau kode sudah tidak berlaku.</li>
                    <li>Jika Anda yakin dokumen ini asli, hubungi penerbit untuk konfirmasi lebih lanjut.</li>
                </ul>

                <div class="card-actions">
                    <a href="{{ url('/') }}" class="btn btn-primary">← Kembali ke Beranda</a>
                </div>

                @endif
            </div>

        </div>
    </main>

    {{-- ── Footer ── --}}
    <footer class="site-footer">
        &copy; {{ date('Y') }} <a href="{{ url('/') }}">{{ config('app.name') }}</a>
        &nbsp;·&nbsp; Sistem Verifikasi Dokumen Resmi
    </footer>

</body>

</html>