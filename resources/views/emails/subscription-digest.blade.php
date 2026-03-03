<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update DABRAKA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #F0F2F5;
            padding: 40px 16px;
            color: #111827;
            -webkit-font-smoothing: antialiased;
        }

        .wrapper {
            max-width: 600px;
            margin: 0 auto;
        }

        .preheader {
            display: none;
            max-height: 0;
            overflow: hidden;
            font-size: 1px;
            color: #F0F2F5;
        }

        /* HEADER */
        .header {
            background: linear-gradient(145deg, #FF6B18 0%, #D63A1F 100%);
            border-radius: 20px 20px 0 0;
            padding: 44px 48px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.07);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -40px;
            width: 260px;
            height: 260px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }

        .logo-wrap {
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }

        .logo-wrap img {
            height: 38px;
            width: auto;
            filter: brightness(0) invert(1);
        }

        .header-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 999px;
            padding: 5px 18px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .badge-dot {
            width: 7px;
            height: 7px;
            background: #4ade80;
            border-radius: 50%;
        }

        .header-badge span {
            color: rgba(255, 255, 255, 0.95);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }

        .header h1 {
            color: #fff;
            font-size: 26px;
            font-weight: 900;
            line-height: 1.3;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .header-sub {
            color: rgba(255, 255, 255, 0.85);
            font-size: 13px;
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }

        /* STATUS BANNER */
        .status-banner {
            background: #fff;
            border-left: 4px solid #22c55e;
            padding: 13px 24px;
        }

        .status-inner {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            background: #22c55e;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-text {
            font-size: 13px;
            color: #374151;
        }

        .status-text strong {
            color: #16a34a;
        }

        /* BODY */
        .body {
            background: #fff;
            padding: 36px 48px;
        }

        .greeting {
            font-size: 17px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .intro {
            font-size: 14px;
            color: #6B7280;
            line-height: 1.85;
            margin-bottom: 28px;
            border-left: 3px solid #FFD4BA;
            padding-left: 16px;
        }

        .intro strong {
            color: #374151;
        }

        .section-divider {
            border: none;
            border-top: 1px solid #F3F4F6;
            margin: 28px 0;
        }

        .section-label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }

        .section-label-icon {
            width: 26px;
            height: 26px;
            background: #FFF7F2;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .section-label-text {
            font-size: 10.5px;
            font-weight: 800;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: 1.3px;
        }

        /* PUBLICATION CARD */
        .pub-card {
            border: 1.5px solid #F3F4F6;
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 12px;
            text-decoration: none;
            display: block;
            transition: border-color 0.2s;
        }

        .pub-card:hover {
            border-color: #FF6B18;
        }

        .pub-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .pub-type {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 10px;
            background: #FFF7F2;
            color: #FF6B18;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .pub-category {
            font-size: 10px;
            color: #9CA3AF;
            background: #F9FAFB;
            padding: 3px 10px;
            border-radius: 999px;
        }

        .pub-date {
            font-size: 10px;
            color: #D1D5DB;
            margin-left: auto;
        }

        .pub-title {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            line-height: 1.5;
            margin-bottom: 6px;
        }

        .pub-authors {
            font-size: 12px;
            color: #9CA3AF;
            margin-bottom: 8px;
        }

        .pub-abstract {
            font-size: 12.5px;
            color: #6B7280;
            line-height: 1.7;
            margin-bottom: 10px;
        }

        .pub-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .pub-read-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 700;
            color: #FF6B18;
            text-decoration: none;
        }

        .pub-views {
            font-size: 11px;
            color: #D1D5DB;
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 32px 24px;
        }

        .empty-state p {
            font-size: 14px;
            color: #9CA3AF;
        }

        /* CTA */
        .cta-section {
            text-align: center;
            margin-top: 8px;
        }

        .cta-btn {
            display: inline-block;
            padding: 14px 40px;
            background: linear-gradient(135deg, #FF6B18 0%, #D63A1F 100%);
            color: #fff !important;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 4px 16px rgba(255, 107, 24, 0.3);
        }

        .cta-sub {
            font-size: 12px;
            color: #D1D5DB;
            margin-top: 10px;
        }

        /* FOOTER */
        .footer {
            background: linear-gradient(160deg, #1a1f2e 0%, #111827 100%);
            border-radius: 0 0 20px 20px;
            padding: 32px 48px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 180px;
            height: 180px;
            background: rgba(255, 107, 24, 0.06);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }

        .footer::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -30px;
            width: 150px;
            height: 150px;
            background: rgba(255, 107, 24, 0.04);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }

        .footer-logo {
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }

        .footer-logo img {
            height: 26px;
            width: auto;
            filter: brightness(0) invert(1);
            opacity: 0.8;
            position: relative;
            z-index: 1;
        }

        .footer-tagline {
            font-size: 12px;
            color: #9CA3AF;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .footer-links {
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .footer-links a {
            font-size: 12px;
            color: #FF6B18;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 500;
        }

        .footer-divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            margin: 16px 0;
            position: relative;
            z-index: 1;
        }

        .footer-legal {
            font-size: 11px;
            color: #6B7280;
            line-height: 1.8;
            position: relative;
            z-index: 1;
        }

        .footer-legal a {
            color: #FF6B18;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="preheader">
        {{ $periodLabel }} – {{ $publications->count() }} publikasi baru dari DABRAKA sesuai minat Anda.
    </div>

    <div class="wrapper">

        {{-- HEADER --}}
        <div class="header">
            <div class="logo-wrap">
                <img src="{{ config('app.url') }}/assets/images/logos/logo-dark.svg" alt="DABRAKA">
            </div>
            <div class="header-badge">
                <div class="badge-dot"></div>
                <span>
                    @if($digestType === 'instant') Notifikasi Instan
                    @elseif($digestType === 'daily') Ringkasan Harian
                    @elseif($digestType === 'weekly_new') Mingguan · Terbaru
                    @elseif($digestType === 'weekly_popular') Mingguan · Terpopuler
                    @else Bulanan · Terpopuler
                    @endif
                </span>
            </div>
            <h1>
                @if($digestType === 'instant') 🔔 Publikasi Baru Tersedia
                @elseif($digestType === 'daily') 🌅 Ringkasan Harian
                @elseif($digestType === 'weekly_new') 📅 Publikasi Terbaru<br>Minggu Ini
                @elseif($digestType === 'weekly_popular') 🔥 Publikasi Terpopuler<br>Minggu Ini
                @else ⭐ Publikasi Terbaik<br>Bulan Ini
                @endif
            </h1>
            <p class="header-sub">
                {{ $publications->count() }} publikasi baru sesuai minat Anda<br>
                {{ $periodLabel }}
            </p>
        </div>

        {{-- STATUS BANNER --}}
        <div class="status-banner">
            <div class="status-inner">
                <div class="status-dot"></div>
                <div class="status-text">
                    <strong>{{ $publications->count() }} Publikasi Ditemukan</strong> &mdash;
                    {{ now()->setTimezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}
                </div>
            </div>
        </div>

        {{-- BODY --}}
        <div class="body">

            <p class="greeting">Halo, {{ $subscription->user->name }}! 👋</p>
            <p class="intro">
                Berikut adalah <strong>{{ $publications->count() }} publikasi terbaru</strong>
                yang sesuai dengan preferensi langganan Anda di <strong>DABRAKA</strong>.
                Klik judul untuk membaca selengkapnya.
            </p>

            {{-- DAFTAR PUBLIKASI --}}
            <div class="section-label">
                <div class="section-label-icon">📚</div>
                <span class="section-label-text">Publikasi untuk Anda</span>
            </div>

            @forelse($publications as $publication)
            <a href="{{ route('publikasi.show', $publication->slug) }}" class="pub-card">
                <div class="pub-meta">
                    @if($publication->publicationType)
                    <span class="pub-type">{{ $publication->publicationType->name }}</span>
                    @endif
                    @if($publication->categories->isNotEmpty())
                    <span class="pub-category">{{ $publication->categories->first()->name }}</span>
                    @endif
                    <span class="pub-date">
                        {{ $publication->published_at?->setTimezone('Asia/Jakarta')->format('d M Y') }}
                    </span>
                </div>
                <p class="pub-title">{{ $publication->title }}</p>
                @if($publication->authors->isNotEmpty())
                <p class="pub-authors">
                    ✍️ {{ $publication->authors->pluck('name')->take(3)->join(', ') }}
                    @if($publication->authors->count() > 3)
                    + {{ $publication->authors->count() - 3 }} lainnya
                    @endif
                </p>
                @endif
                @if($publication->abstract)
                <p class="pub-abstract">
                    {{ Str::limit(strip_tags($publication->abstract), 120) }}
                </p>
                @endif
                <div class="pub-footer">
                    <span class="pub-read-btn">Baca Selengkapnya →</span>
                    @if(isset($publication->views_count))
                    <span class="pub-views">👁 {{ number_format($publication->views_count) }} views</span>
                    @endif
                </div>
            </a>
            @empty
            <div class="empty-state">
                <p>Tidak ada publikasi baru untuk periode ini.</p>
            </div>
            @endforelse

            <hr class="section-divider">

            {{-- CTA --}}
            <div class="cta-section">
                <a href="{{ route('publikasi.index') }}" class="cta-btn">
                    Lihat Semua Publikasi →
                </a>
                <p class="cta-sub">Temukan ribuan publikasi ilmiah di DABRAKA</p>
            </div>

        </div>

        {{-- FOOTER --}}
        <div class="footer">
            <div class="footer-logo">
                <img src="{{ config('app.url') }}/assets/images/logos/logo-dark.svg" alt="DABRAKA">
            </div>
            <p class="footer-tagline">Platform Publikasi Ilmiah Terpercaya</p>
            <div class="footer-links">
                <a href="{{ route('home') }}">Website</a>
                <a href="{{ route('publikasi.index') }}">Publikasi</a>
                <a href="{{ route('subscription.index') }}">Kelola Langganan</a>
            </div>
            <hr class="footer-divider">
            <p class="footer-legal">
                Anda menerima email ini karena berlangganan newsletter DABRAKA.<br>
                <a href="{{ route('subscription.index') }}">Kelola preferensi</a>
                atau
                <a href="{{ route('subscription.destroy') }}">berhenti berlangganan</a>.<br>
                &copy; {{ date('Y') }} DABRAKA. Seluruh hak dilindungi undang-undang.
            </p>
        </div>

    </div>
</body>

</html>