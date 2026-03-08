{{-- resources/views/filament/resources/team-members/pages/view-team-member.blade.php --}}
<x-filament-panels::page>

    @php
    $record = $this->getRecord();

    $photoUrl = (!empty($record->photo) && !filter_var($record->photo, FILTER_VALIDATE_URL))
    ? asset('storage/' . ltrim($record->photo, '/'))
    : ($record->photo ?? null);
    $fallbackUrl = 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'NN')
    . '&size=200&background=FFF7F2&color=FF6B18&bold=true';
    $finalPhoto = $photoUrl ?? $fallbackUrl;

    $levelConfig = match($record->level) {
    'leadership' => ['label' => 'Leadership', 'dot' => '#ef4444'],
    'management' => ['label' => 'Management', 'dot' => '#f59e0b'],
    'department' => ['label' => 'Department', 'dot' => '#10b981'],
    default => ['label' => ucfirst($record->level ?? ''), 'dot' => '#9ca3af'],
    };

    $iconPath = match($record->icon_type ?? '') {
    'code' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
    'content' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828
    15H9v-2.828l8.586-8.586z',
    'marketing' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0
    017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
    'operations' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9
    5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    'support' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21
    12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z',
    default => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    };

    $publicUrl = route('tentang') . '#team';
    @endphp

    <style>
        /* ════════════════════════════════════════════════════
   TOKEN & RESET
════════════════════════════════════════════════════ */
        :root {
            --o: #FF6B18;
            --od: #E64627;
            --ob: #cc5514;
            /* orange lebih gelap untuk border di dark */
        }

        /* ── Animasi ──────────────────────────────────────── */
        @keyframes fu {
            from {
                opacity: 0;
                transform: translateY(10px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .a {
            animation: fu .3s ease both
        }

        .d1 {
            animation-delay: .04s
        }

        .d2 {
            animation-delay: .08s
        }

        .d3 {
            animation-delay: .12s
        }

        .d4 {
            animation-delay: .16s
        }

        .d5 {
            animation-delay: .20s
        }

        .d6 {
            animation-delay: .24s
        }

        /* ════════════════════════════════════════════════════
   CARD
   Light : putih + border abu
   Dark  : orange gradient + border orange gelap
════════════════════════════════════════════════════ */
        .vc {
            border-radius: 14px;
            padding: 20px;
            height: 100%;
            transition: box-shadow .2s, transform .2s;
            /* LIGHT */
            background: #ffffff;
            border: 1px solid #e5e7eb;
            color: #111827;
        }

        .dark .vc {
            /* DARK — orange */
            background: linear-gradient(145deg, var(--o) 0%, var(--od) 100%);
            border: 1px solid var(--ob);
            color: #ffffff;
        }

        .vc:hover {
            box-shadow: 0 6px 24px rgba(0, 0, 0, .08);
            transform: translateY(-2px)
        }

        .dark .vc:hover {
            box-shadow: 0 8px 28px rgba(230, 70, 39, .35);
            transform: translateY(-2px)
        }

        /* ── Section title ──────────────────────────────── */
        .vst {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 16px;
            padding-bottom: 12px;
            /* Light */
            color: #6b7280;
            border-bottom: 1px solid #f3f4f6;
        }

        .dark .vst {
            color: rgba(255, 255, 255, .7);
            border-bottom-color: rgba(255, 255, 255, .15)
        }

        .vst svg {
            width: 13px;
            height: 13px;
            flex-shrink: 0
        }

        /* icon orange di light, putih di dark */
        .vst .ico {
            color: var(--o)
        }

        .dark .vst .ico {
            color: #fff
        }

        /* ── Label / Value / Muted ─────────────────────── */
        .vl {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .09em;
            margin-bottom: 2px;
            color: #9ca3af
        }

        .dark .vl {
            color: rgba(255, 255, 255, .55)
        }

        .vv {
            font-size: 14px;
            font-weight: 500;
            color: #111827
        }

        .dark .vv {
            color: #ffffff
        }

        .vm {
            font-size: 14px;
            color: #9ca3af
        }

        .dark .vm {
            color: rgba(255, 255, 255, .5)
        }

        /* ── Divider ────────────────────────────────────── */
        .vhr {
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 12px 0
        }

        .dark .vhr {
            border-top-color: rgba(255, 255, 255, .12)
        }

        /* ── Badge (level) ──────────────────────────────── */
        .vbadge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid;
        }

        /* light: warna asli via inline style  */
        /* dark: putih transparan di atas orange */
        .dark .vbadge {
            background: rgba(255, 255, 255, .2) !important;
            color: #fff !important;
            border-color: rgba(255, 255, 255, .35) !important;
        }

        /* ── Stat box ────────────────────────────────────── */
        .vstat {
            border-radius: 10px;
            padding: 14px;
            text-align: center;
            /* light */
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        .dark .vstat {
            background: rgba(0, 0, 0, .2);
            border-color: rgba(255, 255, 255, .1)
        }

        .vsn {
            font-size: 24px;
            font-weight: 800;
            line-height: 1;
            color: #111827
        }

        .dark .vsn {
            color: #fff
        }

        .vsl {
            font-size: 10px;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 600;
            color: #9ca3af
        }

        .dark .vsl {
            color: rgba(255, 255, 255, .55)
        }

        /* ── Copy button ─────────────────────────────────── */
        .vcopy {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            cursor: pointer;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
            border: 1px solid;
            transition: all .15s;
            /* light */
            color: #6b7280;
            background: #f9fafb;
            border-color: #e5e7eb;
        }

        .dark .vcopy {
            color: rgba(255, 255, 255, .7);
            background: rgba(0, 0, 0, .2);
            border-color: rgba(255, 255, 255, .2)
        }

        .vcopy:hover {
            color: var(--o);
            background: #fff7f2;
            border-color: rgba(255, 107, 24, .3)
        }

        .dark .vcopy:hover {
            color: #fff;
            background: rgba(0, 0, 0, .35);
            border-color: rgba(255, 255, 255, .4)
        }

        /* ── Link ────────────────────────────────────────── */
        .vlink {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: color .15s
        }

        /* ════════════════════════════════════════════════════
   BANNER
   Mobile : icon + judul di atas → button di bawah
   Desktop: row sejajar
════════════════════════════════════════════════════ */
        .vbanner {
            background: linear-gradient(135deg, var(--o) 0%, var(--od) 100%);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            /* mobile: kolom */
        }

        @media(min-width:640px) {
            .vbanner {
                flex-direction: row;
                align-items: center;
                justify-content: space-between
            }
        }

        .vbanner-top {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0
        }

        .vbanner-icon {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            background: rgba(255, 255, 255, .2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .vbanner-title {
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            line-height: 1.3
        }

        .vbanner-sub {
            color: rgba(255, 255, 255, .8);
            font-size: 11px;
            margin-top: 2px
        }

        .vbanner-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #fff;
            color: var(--o);
            font-size: 13px;
            font-weight: 700;
            padding: 9px 18px;
            border-radius: 9px;
            text-decoration: none;
            white-space: nowrap;
            transition: all .15s;
            /* mobile: full width */
            width: 100%;
        }

        @media(min-width:640px) {
            .vbanner-btn {
                width: auto;
                flex-shrink: 0
            }
        }

        .vbanner-btn:hover {
            background: #fff7f2;
            transform: scale(1.02)
        }

        /* ════════════════════════════════════════════════════
   HERO CARD (preview publik)
   Light: orange gradient
   Dark : orange gradient (sama, karena semua orange)
════════════════════════════════════════════════════ */
        .vhero {
            border-radius: 14px;
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 12px;
            height: 100%;
            background: linear-gradient(145deg, var(--o) 0%, var(--od) 100%);
            border: 2px solid rgba(255, 107, 24, .3);
        }

        .dark .vhero {
            border-color: var(--ob)
        }

        .vhero-name {
            font-weight: 900;
            font-size: 16px;
            color: #fff;
            line-height: 1.2
        }

        .vhero-title {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, .85);
            margin-top: 2px
        }

        .vhero-status-on {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #fff
        }

        .vhero-status-off {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            color: rgba(255, 255, 255, .7)
        }

        .vhero-ibtn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .2);
            transition: background .15s;
            text-decoration: none;
        }

        .vhero-ibtn:hover {
            background: rgba(255, 255, 255, .35)
        }

        .vhero-photo {
            width: 96px;
            height: 96px;
            border-radius: 16px;
            overflow: hidden;
            border: 3px solid rgba(255, 255, 255, .3);
            box-shadow: 0 4px 20px rgba(0, 0, 0, .2);
        }

        .vhero-footer {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: rgba(255, 255, 255, .3)
        }

        /* ── Collapsible ─────────────────────────────────── */
        details.vdet summary {
            cursor: pointer;
            list-style: none
        }

        details.vdet summary::-webkit-details-marker {
            display: none
        }

        .varr {
            transition: transform .2s
        }

        details.vdet[open] .varr {
            transform: rotate(180deg)
        }

        /* ── Grids ───────────────────────────────────────── */
        .vg4 {
            display: grid;
            gap: 14px;
            grid-template-columns: 1fr;
            align-items: stretch
        }

        @media(min-width:640px) {
            .vg4 {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        @media(min-width:1280px) {
            .vg4 {
                grid-template-columns: repeat(4, 1fr)
            }
        }

        .vg2 {
            display: grid;
            gap: 14px;
            grid-template-columns: 1fr
        }

        @media(min-width:640px) {
            .vg2 {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        .vg3 {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, 1fr)
        }

        @media(max-width:480px) {
            .vg3 {
                grid-template-columns: 1fr
            }
        }

        /* ── Status / active icons ───────────────────────── */
        .icon-ok {
            color: #fff
        }

        .icon-off {
            color: rgba(255, 255, 255, .7)
        }
    </style>

    <div class="space-y-4">

        {{-- ══════════════════════════════════════════════
        BANNER — mobile: judul di atas, CTA di bawah
        ══════════════════════════════════════════════ --}}
        <div class="vbanner a d1">
            {{-- Baris atas: icon + teks --}}
            <div class="vbanner-top">
                <div class="vbanner-icon">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
                <div>
                    <div class="vbanner-title">Preview Tampilan Publik</div>
                    <div class="vbanner-sub">{{ $record->name }} — Halaman Tentang Kami</div>
                </div>
            </div>
            {{-- Baris bawah (mobile) / kanan (desktop): tombol --}}
            <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="vbanner-btn">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Lihat di Website
            </a>
        </div>

        {{-- ══════════════════════════════════════════════
        ROW 1 — 4 card
        ══════════════════════════════════════════════ --}}
        <div class="vg4">

            {{-- Card 1: Hero preview --}}
            <div class="a d2">
                <div class="vhero">
                    {{-- Foto --}}
                    <div class="relative">
                        <div class="vhero-photo">
                            <img src="{{ $finalPhoto }}" alt="{{ $record->name }}" class="object-cover w-full h-full"
                                onerror="this.src='{{ $fallbackUrl }}'">
                        </div>
                        @if($record->level === 'leadership')
                        <div
                            class="absolute flex items-center justify-center w-8 h-8 bg-white shadow-lg -bottom-2 -right-2 rounded-xl">
                            <svg class="w-4 h-4 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        @elseif($record->icon_type)
                        <div
                            class="absolute flex items-center justify-center bg-white rounded-lg shadow-lg -bottom-2 -right-2 w-7 h-7">
                            <svg class="w-3.5 h-3.5 text-[#FF6B18]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $iconPath }}" />
                            </svg>
                        </div>
                        @endif
                    </div>

                    <div>
                        <div class="vhero-name">{{ $record->name }}</div>
                        <div class="vhero-title">{{ $record->title }}</div>
                    </div>

                    {{-- Badge level — selalu putih transparan di atas orange --}}
                    <span class="vbadge"
                        style="background:rgba(255,255,255,.2);color:#fff;border-color:rgba(255,255,255,.35)">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        {{ $levelConfig['label'] }}
                    </span>

                    {{-- Status --}}
                    @if($record->is_active)
                    <div class="vhero-status-on">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Aktif & Tampil
                    </div>
                    @else
                    <div class="vhero-status-off">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Disembunyikan
                    </div>
                    @endif

                    {{-- Kontak cepat --}}
                    @if($record->email || $record->linkedin)
                    <div class="flex items-center gap-2">
                        @if($record->email)
                        <a href="mailto:{{ $record->email }}" class="vhero-ibtn" title="{{ $record->email }}">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </a>
                        @endif
                        @if($record->linkedin)
                        <a href="{{ $record->linkedin }}" target="_blank" class="vhero-ibtn" title="LinkedIn">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                        </a>
                        @endif
                    </div>
                    @endif

                    <div class="vhero-footer">Preview Publik</div>
                </div>
            </div>

            {{-- Card 2: Identitas --}}
            <div class="vc a d3">
                <div class="vst">
                    <svg class="ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Identitas
                </div>
                <div class="space-y-3">
                    <div>
                        <div class="vl">Nama Lengkap</div>
                        <div class="text-base font-bold vv">{{ $record->name }}</div>
                    </div>
                    <hr class="vhr">
                    <div>
                        <div class="vl">Jabatan</div>
                        <div class="vv">{{ $record->title ?? '—' }}</div>
                    </div>
                    <hr class="vhr">
                    <div>
                        <div class="vl">Departemen</div>
                        <div class="vm">{{ $record->department ?? '—' }}</div>
                    </div>
                    <hr class="vhr">
                    <div>
                        <div class="vl">Level</div>
                        {{-- Light: warna asli | Dark: override ke putih transparan via .dark .vbadge --}}
                        <span class="mt-1 vbadge"
                            style="background:{{ $levelConfig['dot'] }}1a;color:{{ $levelConfig['dot'] }};border-color:{{ $levelConfig['dot'] }}4d">
                            <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $levelConfig['dot'] }}"></span>
                            {{ $levelConfig['label'] }}
                        </span>
                    </div>
                    <hr class="vhr">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="vl">Urutan Tampil</div>
                            <div class="vv"># {{ $record->order }}</div>
                        </div>
                        <div class="flex-shrink-0 vstat" style="padding:10px 18px">
                            <div class="vsn" style="font-size:18px">{{ $record->order }}</div>
                            <div class="vsl">Urutan</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Kontak --}}
            <div class="vc a d4">
                <div class="vst">
                    <svg class="ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Kontak
                </div>
                <div class="space-y-4">
                    <div>
                        <div class="vl">Email</div>
                        @if($record->email)
                        <div class="flex flex-wrap items-start gap-2 mt-1">
                            <span class="flex-1 text-sm break-all vv">{{ $record->email }}</span>
                            <button class="flex-shrink-0 vcopy" onclick="
                                    navigator.clipboard.writeText('{{ $record->email }}');
                                    this.textContent='✓ Disalin';
                                    setTimeout(()=>{this.textContent='Salin';},2000);
                                ">Salin</button>
                        </div>
                        @else
                        <div class="mt-1 vm">—</div>
                        @endif
                    </div>
                    <hr class="vhr">
                    <div>
                        <div class="vl">LinkedIn</div>
                        @if($record->linkedin)
                        <a href="{{ $record->linkedin }}" target="_blank" rel="noopener" class="mt-1 vlink"
                            style="color:#3b82f6">
                            <svg class="flex-shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                            Lihat Profil
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                        @else
                        <div class="mt-1 vm">—</div>
                        @endif
                    </div>
                    <hr class="vhr">
                    <div>
                        <div class="vl">Kirim Email</div>
                        @if($record->email)
                        <a href="mailto:{{ $record->email }}" class="mt-1 vlink" style="color:var(--o)">
                            <svg class="flex-shrink-0 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Buka Email Client
                        </a>
                        @else
                        <div class="mt-1 vm">—</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card 4: Bio --}}
            <div class="vc a d5">
                <div class="vst">
                    <svg class="ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Bio & Deskripsi
                </div>
                @if($record->description)
                <p class="text-sm leading-relaxed text-gray-600 dark:text-white">
                    {{ $record->description }}
                </p>
                @else
                <div class="flex flex-col items-center justify-center gap-3 py-8 text-center">
                    <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-white/10">
                        <svg class="w-6 h-6 text-gray-300 dark:text-white/40" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-400 dark:text-white/50">Belum ada deskripsi.</span>
                </div>
                @endif
            </div>

        </div>

        {{-- ══════════════════════════════════════════════
        ROW 2 — Stats (hanya department)
        ══════════════════════════════════════════════ --}}
        @if($record->level === 'department')
        <div class="vc a d5">
            <div class="vst">
                <svg class="ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Pengaturan Department Card
            </div>
            <div class="vg3">
                <div class="vstat">
                    <div class="vsn">{{ $record->member_count ?? 0 }}</div>
                    <div class="vsl">Anggota</div>
                </div>
                <div class="vstat">
                    <div class="vsn" style="font-size:16px">{{ $record->icon_type ? ucfirst($record->icon_type) : '—' }}
                    </div>
                    <div class="vsl">Ikon</div>
                </div>
                <div class="vstat">
                    <div class="vsn" style="font-size:16px">#{{ $record->order }}</div>
                    <div class="vsl">Urutan</div>
                </div>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════
        ROW 3 — Metadata
        ══════════════════════════════════════════════ --}}
        <details class="vc vdet a d6">
            <summary>
                <div class="flex items-center justify-between">
                    <div class="pb-0 mb-0 border-0 vst">
                        <svg class="ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Metadata
                    </div>
                    <svg class="w-4 h-4 text-gray-400 varr dark:text-white/50" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </summary>
            <hr class="mt-4 vhr">
            <div class="mt-4 vg2">
                <div>
                    <div class="vl">Dibuat Pada</div>
                    <div class="text-sm vv">{{ $record->created_at?->translatedFormat('d F Y, H:i') ?? '—' }}</div>
                    <div class="text-xs mt-0.5 text-gray-400 dark:text-white/40">
                        {{ $record->created_at?->diffForHumans() }}
                    </div>
                </div>
                <div>
                    <div class="vl">Terakhir Diperbarui</div>
                    <div class="text-sm vv">{{ $record->updated_at?->translatedFormat('d F Y, H:i') ?? '—' }}</div>
                    <div class="text-xs mt-0.5 text-gray-400 dark:text-white/40">
                        {{ $record->updated_at?->diffForHumans() }}
                    </div>
                </div>
            </div>
        </details>

    </div>
</x-filament-panels::page>