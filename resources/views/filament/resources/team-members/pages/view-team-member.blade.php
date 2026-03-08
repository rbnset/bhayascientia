{{-- resources/views/filament/resources/team-members/pages/view-team-member.blade.php --}}
<x-filament-panels::page>

    @php
    $record = $this->getRecord();

    // ── Resolve foto ──────────────────────────────────────────────
    $photoUrl = (!empty($record->photo) && !filter_var($record->photo, FILTER_VALIDATE_URL))
    ? asset('storage/' . ltrim($record->photo, '/'))
    : ($record->photo ?? null);
    $fallbackUrl = 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'NN')
    . '&size=200&background=FFF7F2&color=FF6B18&bold=true';
    $finalPhoto = $photoUrl ?? $fallbackUrl;

    // ── Level config ──────────────────────────────────────────────
    $levelConfig = match($record->level) {
    'leadership' => [
    'label' => 'Leadership',
    'bg' => 'bg-red-500/10',
    'text' => 'text-red-400',
    'border' => 'border-red-500/30',
    'dot' => 'bg-red-500',
    'card_bg' => 'bg-gradient-to-br from-[#FF6B18] to-[#E64627]',
    'card_text' => 'text-white',
    ],
    'management' => [
    'label' => 'Management',
    'bg' => 'bg-amber-500/10',
    'text' => 'text-amber-400',
    'border' => 'border-amber-500/30',
    'dot' => 'bg-amber-500',
    'card_bg' => 'bg-[#1C1F26]',
    'card_text' => 'text-white',
    ],
    'department' => [
    'label' => 'Department',
    'bg' => 'bg-emerald-500/10',
    'text' => 'text-emerald-400',
    'border' => 'border-emerald-500/30',
    'dot' => 'bg-emerald-500',
    'card_bg' => 'bg-[#1C1F26]',
    'card_text' => 'text-white',
    ],
    default => [
    'label' => ucfirst($record->level),
    'bg' => 'bg-gray-500/10',
    'text' => 'text-gray-400',
    'border' => 'border-gray-500/30',
    'dot' => 'bg-gray-500',
    'card_bg' => 'bg-[#1C1F26]',
    'card_text' => 'text-white',
    ],
    };

    // ── Icon dept ─────────────────────────────────────────────────
    $iconSvg = match($record->icon_type ?? '') {
    'code' => '
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />',
    'content' => '
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
    ',
    'marketing' => '
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
    ',
    'operations' => '
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
    ',
    'support' => '
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
    ',
    default => '
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />',
    };
    @endphp

    <style>
        /* ── Animasi masuk ── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .anim {
            animation: fadeUp 0.35s ease both;
        }

        .anim-1 {
            animation-delay: 0.05s;
        }

        .anim-2 {
            animation-delay: 0.10s;
        }

        .anim-3 {
            animation-delay: 0.15s;
        }

        .anim-4 {
            animation-delay: 0.20s;
        }

        .anim-5 {
            animation-delay: 0.25s;
        }

        /* ── Base card ── */
        .vm-card {
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.07);
            padding: 24px;
            background: #1C1F26;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .vm-card:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            transform: translateY(-2px);
        }

        /* ── Preview card — mirip tampilan publik ── */
        .vm-preview-card {
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid rgba(255, 107, 24, 0.2);
        }

        /* ── Label & value ── */
        .vm-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 3px;
        }

        .vm-value {
            font-size: 14px;
            font-weight: 500;
            color: #f3f4f6;
        }

        .vm-value-muted {
            font-size: 14px;
            color: #6b7280;
        }

        /* ── Divider ── */
        .vm-divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            margin: 14px 0;
        }

        /* ── Section title ── */
        .vm-section-title {
            font-size: 12px;
            font-weight: 700;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        .vm-section-title svg {
            width: 14px;
            height: 14px;
            color: #FF6B18;
            flex-shrink: 0;
        }

        /* ── Badge ── */
        .vm-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid;
        }

        /* ── Grid layout ── */
        .vm-grid-4 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        @media (min-width: 768px) {
            .vm-grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1280px) {
            .vm-grid-4 {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .vm-grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        @media (min-width: 768px) {
            .vm-grid-2 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .vm-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        /* ── Copy btn ── */
        .vm-copy-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: #6b7280;
            cursor: pointer;
            padding: 2px 7px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            transition: all 0.15s;
        }

        .vm-copy-btn:hover {
            background: rgba(255, 107, 24, 0.15);
            color: #FF6B18;
            border-color: rgba(255, 107, 24, 0.3);
        }

        /* ── Preview banner ── */
        .vm-preview-banner {
            background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .vm-preview-link {
            display: inline-flex;
            align-items: center;
            gap-6px;
            background: white;
            color: #FF6B18;
            font-size: 13px;
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.15s;
            white-space: nowrap;
            gap: 6px;
        }

        .vm-preview-link:hover {
            background: #fff7f2;
            transform: scale(1.02);
        }

        /* ── Stat box ── */
        .vm-stat {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }

        .vm-stat-num {
            font-size: 26px;
            font-weight: 800;
            color: #f9fafb;
            line-height: 1;
        }

        .vm-stat-lbl {
            font-size: 10px;
            color: #6b7280;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 600;
        }

        /* ── Metadata collapsible ── */
        details.vm-collapsible summary {
            cursor: pointer;
            list-style: none;
        }

        details.vm-collapsible summary::-webkit-details-marker {
            display: none;
        }

        details.vm-collapsible[open] .vm-arrow {
            transform: rotate(180deg);
        }

        .vm-arrow {
            transition: transform 0.2s ease;
        }
    </style>

    <div class="space-y-4">

        {{-- ═══════════════════════════════════════════════════════════
        PREVIEW BANNER — link ke halaman publik
        ═══════════════════════════════════════════════════════════ --}}
        <div class="vm-preview-banner anim anim-1">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center flex-shrink-0 rounded-lg w-9 h-9 bg-white/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-white">Preview Tampilan Publik</div>
                    <div class="text-white/70 text-xs mt-0.5">
                        Lihat bagaimana <span class="font-semibold text-white">{{ $record->name }}</span>
                        tampil di halaman Tentang Kami
                    </div>
                </div>
            </div>
            <a href="{{ url('/tentang#team') }}" target="_blank" rel="noopener" class="vm-preview-link">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Lihat di Website
            </a>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
        ROW 1 — 4 card sejajar
        [Preview Card] [Identitas] [Kontak] [Bio]
        ═══════════════════════════════════════════════════════════ --}}
        <div class="vm-grid-4">

            {{-- Card 1: Preview Card — mirip tampilan publik --}}
            <div class="anim anim-1">
                <div
                    class="vm-preview-card {{ $levelConfig['card_bg'] }} p-6 text-center h-full flex flex-col items-center justify-center gap-4">

                    {{-- Foto + badge icon --}}
                    <div class="relative inline-block">
                        <div class="w-24 h-24 rounded-2xl overflow-hidden border-4
                        {{ $record->level === 'leadership' ? 'border-white/30' : 'border-white/10' }}
                        mx-auto shadow-lg">
                            <img src="{{ $finalPhoto }}" alt="{{ $record->name }}" class="object-cover w-full h-full"
                                onerror="this.src='{{ $fallbackUrl }}'">
                        </div>
                        @if($record->level === 'leadership')
                        <div
                            class="absolute flex items-center justify-center bg-white shadow-lg -bottom-2 -right-2 w-9 h-9 rounded-xl">
                            <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        @elseif($record->icon_type)
                        <div
                            class="absolute -bottom-2 -right-2 w-8 h-8 bg-gradient-to-br from-[#FF6B18] to-[#E64627] rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $iconSvg !!}
                            </svg>
                        </div>
                        @endif
                    </div>

                    {{-- Nama & jabatan --}}
                    <div>
                        <div class="font-black text-lg
                        {{ $record->level === 'leadership' ? 'text-white' : 'text-gray-100' }}
                        leading-tight">
                            {{ $record->name }}
                        </div>
                        <div class="text-sm mt-0.5
                        {{ $record->level === 'leadership' ? 'text-white/80' : 'text-[#FF6B18]' }}
                        font-semibold">
                            {{ $record->title }}
                        </div>
                    </div>

                    {{-- Level badge --}}
                    <span
                        class="vm-badge {{ $levelConfig['bg'] }} {{ $levelConfig['text'] }} {{ $levelConfig['border'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $levelConfig['dot'] }}"></span>
                        {{ $levelConfig['label'] }}
                    </span>

                    {{-- Status --}}
                    @if($record->is_active)
                    <span class="flex items-center gap-1.5 text-xs font-semibold text-emerald-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Aktif & Tampil
                    </span>
                    @else
                    <span class="flex items-center gap-1.5 text-xs font-semibold text-red-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Disembunyikan
                    </span>
                    @endif

                    {{-- Kontak icon --}}
                    @if($record->email || $record->linkedin)
                    <div class="flex items-center gap-2">
                        @if($record->email)
                        <a href="mailto:{{ $record->email }}"
                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-all
                        {{ $record->level === 'leadership' ? 'bg-white/20 hover:bg-white/40' : 'bg-white/10 hover:bg-[#FF6B18]' }}" title="Email">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </a>
                        @endif
                        @if($record->linkedin)
                        <a href="{{ $record->linkedin }}" target="_blank"
                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-all
                        {{ $record->level === 'leadership' ? 'bg-white/20 hover:bg-white/40' : 'bg-white/10 hover:bg-[#FF6B18]' }}" title="LinkedIn">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                        </a>
                        @endif
                    </div>
                    @endif

                    <div class="text-[10px] text-white/30 font-medium uppercase tracking-widest">
                        Preview Tampilan Publik
                    </div>
                </div>
            </div>

            {{-- Card 2: Identitas --}}
            <div class="vm-card anim anim-2">
                <div class="vm-section-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Identitas
                </div>
                <div class="space-y-3">
                    <div>
                        <div class="vm-label">Nama Lengkap</div>
                        <div class="text-base font-bold vm-value">{{ $record->name }}</div>
                    </div>
                    <hr class="vm-divider">
                    <div>
                        <div class="vm-label">Jabatan</div>
                        <div class="vm-value">{{ $record->title ?? '—' }}</div>
                    </div>
                    <hr class="vm-divider">
                    <div>
                        <div class="vm-label">Departemen</div>
                        <div class="vm-value-muted">{{ $record->department ?? '—' }}</div>
                    </div>
                    <hr class="vm-divider">
                    <div>
                        <div class="vm-label">Level</div>
                        <span
                            class="vm-badge {{ $levelConfig['bg'] }} {{ $levelConfig['text'] }} {{ $levelConfig['border'] }} mt-1">
                            <span class="w-1.5 h-1.5 rounded-full {{ $levelConfig['dot'] }}"></span>
                            {{ $levelConfig['label'] }}
                        </span>
                    </div>
                    <hr class="vm-divider">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="vm-label">Urutan Tampil</div>
                            <div class="vm-value"># {{ $record->order }}</div>
                        </div>
                        <div class="vm-stat" style="padding: 10px 20px;">
                            <div class="vm-stat-num" style="font-size:20px">{{ $record->order }}</div>
                            <div class="vm-stat-lbl">Urutan</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Kontak --}}
            <div class="vm-card anim anim-3">
                <div class="vm-section-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Kontak
                </div>
                <div class="space-y-4">
                    <div>
                        <div class="vm-label">Email</div>
                        @if($record->email)
                        <div class="flex items-start gap-2 mt-1">
                            <span class="flex-1 text-sm break-all vm-value">{{ $record->email }}</span>
                            <button class="flex-shrink-0 vm-copy-btn" onclick="
                                navigator.clipboard.writeText('{{ $record->email }}');
                                this.textContent = '✓ Disalin';
                                this.style.color = '#10b981';
                                setTimeout(() => { this.textContent = 'Salin'; this.style.color = ''; }, 2000);
                            ">
                                Salin
                            </button>
                        </div>
                        @else
                        <div class="mt-1 vm-value-muted">—</div>
                        @endif
                    </div>
                    <hr class="vm-divider">
                    <div>
                        <div class="vm-label">LinkedIn</div>
                        @if($record->linkedin)
                        <a href="{{ $record->linkedin }}" target="_blank" rel="noopener"
                            class="flex items-center gap-2 mt-1 text-sm font-medium text-blue-400 transition-colors hover:text-blue-300">
                            <svg class="flex-shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                            Lihat Profil LinkedIn
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                        @else
                        <div class="mt-1 vm-value-muted">—</div>
                        @endif
                    </div>
                    <hr class="vm-divider">
                    <div>
                        <div class="vm-label">Kirim Email</div>
                        @if($record->email)
                        <a href="mailto:{{ $record->email }}"
                            class="flex items-center gap-2 mt-1 text-[#FF6B18] hover:text-[#e64627] transition-colors text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Buka Email Client
                        </a>
                        @else
                        <div class="mt-1 vm-value-muted">—</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card 4: Bio --}}
            <div class="vm-card anim anim-4">
                <div class="vm-section-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Bio & Deskripsi
                </div>
                @if($record->description)
                <p class="text-sm leading-relaxed text-gray-300">{{ $record->description }}</p>
                @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="flex items-center justify-center w-12 h-12 mb-3 bg-white/5 rounded-xl">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600">Belum ada deskripsi.</span>
                </div>
                @endif
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════
        ROW 2 — Stats Department (jika level = department)
        ═══════════════════════════════════════════════════════════ --}}
        @if($record->level === 'department')
        <div class="vm-card anim anim-5">
            <div class="vm-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Pengaturan Department Card
            </div>
            <div class="vm-grid-3">
                <div class="vm-stat">
                    <div class="vm-stat-num">{{ $record->member_count ?? 0 }}</div>
                    <div class="vm-stat-lbl">Jumlah Anggota</div>
                </div>
                <div class="vm-stat">
                    <div class="text-lg vm-stat-num">{{ $record->icon_type ? ucfirst($record->icon_type) : '—' }}</div>
                    <div class="vm-stat-lbl">Ikon Departemen</div>
                </div>
                <div class="vm-stat">
                    <div class="text-lg vm-stat-num"># {{ $record->order }}</div>
                    <div class="vm-stat-lbl">Urutan Tampil</div>
                </div>
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════
        ROW 3 — Metadata (collapsible)
        ═══════════════════════════════════════════════════════════ --}}
        <details class="vm-card vm-collapsible anim anim-5">
            <summary>
                <div class="flex items-center justify-between">
                    <div class="pb-0 mb-0 border-0 vm-section-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Metadata
                    </div>
                    <svg class="w-4 h-4 text-gray-500 vm-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </summary>
            <hr class="mt-4 vm-divider">
            <div class="mt-4 vm-grid-2">
                <div>
                    <div class="vm-label">Dibuat Pada</div>
                    <div class="text-sm vm-value">{{ $record->created_at?->translatedFormat('d F Y, H:i') ?? '—' }}
                    </div>
                    <div class="text-xs text-gray-600 mt-0.5">{{ $record->created_at?->diffForHumans() }}</div>
                </div>
                <div>
                    <div class="vm-label">Terakhir Diperbarui</div>
                    <div class="text-sm vm-value">{{ $record->updated_at?->translatedFormat('d F Y, H:i') ?? '—' }}
                    </div>
                    <div class="text-xs text-gray-600 mt-0.5">{{ $record->updated_at?->diffForHumans() }}</div>
                </div>
            </div>
        </details>

    </div>

</x-filament-panels::page>