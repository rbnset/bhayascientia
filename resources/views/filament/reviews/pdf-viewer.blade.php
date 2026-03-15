{{--
resources/views/filament/reviews/pdf-viewer.blade.php

Diembed di ReviewForm Step 2 via:
View::make('filament.reviews.pdf-viewer')->columnSpanFull()

Dependencies:
- public/css/review-pdf-viewer.css
- public/js/review-pdf-viewer.js
- Route: manuscripts.view → /manuscripts/{version}
- Route: api/review-annotations/{reviewId}/...
--}}

@php
use App\Models\PublicationVersion;

$review = $this->record ?? null;
$formState = $this->data ?? [];
$versionId = $formState['publication_version_id'] ?? null;
$reviewId = $review?->id ?? null;

$version = null;
$pdfUrl = null;
$publicationTitle = null;
$reviewerName = auth()->user()?->name;

if ($versionId) {
$version = PublicationVersion::with('publication.publicationType')->find($versionId);
if ($version) {
$publicationTitle = $version->publication?->title;
$pdfUrl = $version->pdf_file_path
? route('manuscripts.view', $version)
: null;
}
}

$annotApiBase = $reviewId
? url("/api/review-annotations/{$reviewId}")
: null;
@endphp

{{-- ═══════════════════════════════════════════════════
CSS
════════════════════════════════════════════════════ --}}
<link rel="stylesheet"
    href="{{ asset('css/review-pdf-viewer.css') }}?v={{ filemtime(public_path('css/review-pdf-viewer.css')) }}">

{{-- ═══════════════════════════════════════════════════
OUTER WRAP
════════════════════════════════════════════════════ --}}
<div id="rpv-outer-wrap">

    {{-- ──────────────────────────────────────────────
    STATE 1: Belum pilih naskah
    ─────────────────────────────────────────────── --}}
    @if (!$versionId || !$pdfUrl)
    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                    gap:1rem;text-align:center;padding:3rem 2rem;min-height:400px;">
        <div style="font-size:3rem;">📄</div>
        <p style="color:#fff;font-weight:700;font-size:1rem;margin:0;">Pilih Naskah di Step 1</p>
        <p style="color:#6B7280;font-size:.875rem;max-width:340px;line-height:1.5;margin:0;">
            Kembali ke step sebelumnya dan pilih versi publikasi yang akan direview.
            PDF dan alat anotasi akan muncul di sini.
        </p>
        <div style="display:flex;gap:.4rem;flex-wrap:wrap;justify-content:center;margin-top:.5rem;">
            @foreach(['✏️ Highlight','💬 Komentar','📌 Sticky Note','🖊 Pen','🖌️ Brush','⬛ Shape'] as $tool)
            <span style="background:#2d2d2d;color:#9CA3AF;font-size:11px;
                                 padding:.3rem .65rem;border-radius:99px;border:1px solid #3d3d3d;">
                {{ $tool }}
            </span>
            @endforeach
        </div>
    </div>

    {{-- ──────────────────────────────────────────────
    STATE 2: Review belum disimpan (belum ada ID)
    ─────────────────────────────────────────────── --}}
    @elseif (!$reviewId)
    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                    gap:1rem;text-align:center;padding:3rem 2rem;min-height:400px;">
        <div style="font-size:3rem;">⚠️</div>
        <p style="color:#F59E0B;font-weight:700;font-size:1rem;margin:0;">Simpan Draft Dulu</p>
        <p style="color:#6B7280;font-size:.875rem;max-width:380px;line-height:1.5;margin:0;">
            Anotasi membutuhkan ID review. Simpan form ini sebagai draft terlebih dahulu,
            lalu buka kembali untuk mulai memberi anotasi.
        </p>
        <p style="color:#4B5563;font-size:11px;margin:0;">
            PDF: <strong style="color:#9CA3AF;">{{ $publicationTitle }}</strong>
        </p>
    </div>

    {{-- ──────────────────────────────────────────────
    STATE 3: Siap — tampilkan viewer
    ─────────────────────────────────────────────── --}}
    @else

    {{-- ══ TOOLBAR ══ --}}
    <div id="rpv-toolbar">

        <span class="rpv-title" title="{{ $publicationTitle }}">
            📄 {{ Str::limit($publicationTitle ?? 'Naskah', 38) }}
        </span>

        {{-- Navigasi halaman --}}
        <div class="rpv-page-group">
            <button type="button" class="rpv-btn" id="rpv-prev" title="Halaman sebelumnya (←)">‹</button>
            <input type="number" id="rpv-page-input" class="rpv-page-input" value="1" min="1">
            <span class="rpv-page-sep">/</span>
            <span class="rpv-page-total" id="rpv-page-total">—</span>
            <button type="button" class="rpv-btn" id="rpv-next" title="Halaman berikutnya (→)">›</button>
        </div>

        {{-- Zoom (desktop only) --}}
        <button type="button" class="rpv-btn rpv-desktop-only" id="rpv-zoom-out" title="Perkecil (-)">−</button>
        <span class="rpv-zoom-val rpv-desktop-only" id="rpv-zoom-val">100%</span>
        <button type="button" class="rpv-btn rpv-desktop-only" id="rpv-zoom-in" title="Perbesar (+)">+</button>

        {{-- Mode baca (desktop only) --}}
        <div class="rpv-desktop-only" style="display:flex;gap:2px;">
            <button type="button" class="rpv-btn active" data-rpv-mode="normal" title="Normal">☀️</button>
            <button type="button" class="rpv-btn" data-rpv-mode="sepia" title="Sepia">📜</button>
            <button type="button" class="rpv-btn" data-rpv-mode="night" title="Night">🌙</button>
        </div>

        {{-- Cari --}}
        <button type="button" class="rpv-btn" id="rpv-search-btn" title="Cari teks (Ctrl+F)">
            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span class="rpv-desktop-only">Cari</span>
        </button>

        {{-- Fullscreen (desktop only) --}}
        <button type="button" class="rpv-btn rpv-desktop-only" id="rpv-fs-btn" title="Layar penuh (F)">
            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5
                             M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
            <span>Layar Penuh</span>
        </button>

        {{-- Download + Anotasi --}}
        <button type="button" class="rpv-btn primary" id="rpv-download-btn" title="Download PDF dengan anotasi">
            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1
                             m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span>Download + Anotasi</span>
        </button>

    </div>{{-- /#rpv-toolbar --}}

    {{-- Progress bar --}}
    <div class="rpv-progress-track">
        <div class="rpv-progress-fill" id="rpv-progress"></div>
    </div>

    {{-- ══ CANVAS AREA ══ --}}
    <div id="rpv-canvas-wrap">

        {{-- Loading state --}}
        <div id="rpv-loading">
            <div class="rpv-spinner"></div>
            <p style="color:#fff;font-size:13px;font-weight:600;margin:0;">
                Memuat dokumen...
            </p>
            <p style="color:#6b7280;font-size:11px;margin:0;" id="rpv-load-sub">
                Harap tunggu sebentar
            </p>
        </div>

        {{-- Stage: canvas + layers --}}
        <div id="rpv-stage">
            <canvas id="rpv-canvas"></canvas>
            <div id="rpv-text-layer"></div>
            <div id="rpv-annotation-layer"></div>
            <canvas id="rpv-freehand-canvas"></canvas>
        </div>

        {{-- Annotation panel (slide in dari kanan) --}}
        <div id="rpv-panel">
            <div class="rpv-panel-header">
                <span class="rpv-panel-title">📝 Anotasi Saya</span>
                <button type="button" class="rpv-panel-close" id="rpv-panel-close">✕</button>
            </div>
            <div class="rpv-panel-list" id="rpv-panel-list">
                <div class="rpv-panel-empty">Belum ada anotasi.</div>
            </div>
            <div class="rpv-panel-footer">
                <button type="button" class="rpv-panel-clear" id="rpv-panel-clear">🗑 Hapus semua di halaman
                    ini</button>
            </div>
        </div>

        {{-- Export overlay --}}
        <div id="rpv-export-overlay">
            <div class="rpv-spinner"></div>
            <p style="color:#fff;font-size:13px;font-weight:600;margin:0;">
                Mengekspor PDF...
            </p>
            <p style="color:#6b7280;font-size:11px;margin:0;" id="rpv-export-status">Memproses halaman...</p>
        </div>

        {{-- Mobile FAB --}}
        <div id="rpv-mobile-fab">
            <button type="button" id="rpv-mobile-fab-btn" aria-label="Menu">
                <svg style="width:22px;height:22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
            </button>
        </div>

    </div>{{-- /#rpv-canvas-wrap --}}

    {{-- ══ ANNOTATION BAR ══ --}}
    <div id="rpv-annot-bar">
        <div class="rpv-annot-label" id="rpv-active-label">✏️ Highlight</div>
        <div class="rpv-ab-tools">

            {{-- Navigasi --}}
            <button type="button" class="rpv-tool" data-tool="pan" title="Geser (Hand)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M18 11V6.5a1.5 1.5 0 00-3 0V11m0 0V8.5a1.5 1.5 0 00-3 0V11
                                 m0 0V10a1.5 1.5 0 00-3 0v6c0 2.21 1.79 4 4 4h2a4 4 0 004-4v-5
                                 a1.5 1.5 0 00-3 0" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
            <button type="button" class="rpv-tool" data-tool="select" title="Pilih anotasi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M5 3l14 9-7 1-3 7L5 3z" stroke-linejoin="round" />
                </svg>
            </button>

            <div class="rpv-ab-sep"></div>

            {{-- Markup teks --}}
            <button type="button" class="rpv-tool active" data-tool="highlight" title="Highlight teks">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M9 19l-2 2H5l1-2L15 9l2 2L9 19z" stroke-linejoin="round" />
                    <path d="M15 9l2 2-1.5 1.5L13.5 10.5 15 9z" fill="currentColor" stroke="none" />
                    <line x1="5" y1="21" x2="19" y2="21" />
                </svg>
            </button>
            <button type="button" class="rpv-tool" data-tool="underline" title="Underline teks">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3" stroke-linecap="round" />
                    <line x1="4" y1="21" x2="20" y2="21" />
                </svg>
            </button>
            <button type="button" class="rpv-tool" data-tool="strikethrough" title="Strikethrough teks">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M17.3 12H6.7M10 7.2C10 7.2 9 6 11.5 6c2.1 0 3 1 3 2.2 0 2-2 2.8-3.5 3"
                        stroke-linecap="round" />
                    <path d="M14 17c0 0 1 1-1.5 1-2.1 0-3.5-1-3.5-2.5" stroke-linecap="round" />
                </svg>
            </button>
            <button type="button" class="rpv-tool" data-tool="comment" title="Komentar di teks">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                </svg>
            </button>

            <div class="rpv-ab-sep"></div>

            {{-- Drawing --}}
            <button type="button" class="rpv-tool" data-tool="freehand" title="Pen bebas">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M12 20h9" />
                    <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
                </svg>
            </button>
            <button type="button" class="rpv-tool" data-tool="brush" title="Brush tebal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M9.06 11.9l8.07-8.06a2.85 2.85 0 114.03 4.03l-8.06 8.08" />
                    <path d="M7.07 14.94c-1.66 0-3 1.35-3 3.02 0 1.33-2.5 1.52-2 2.02
                                  1 1 2.48 1 3.5 1 1.66 0 3-1.34 3-3s-1.34-3.04-1.5-3.04z" fill="currentColor"
                        stroke="none" />
                </svg>
            </button>
            <button type="button" class="rpv-tool" data-tool="shape" title="Shape">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <circle cx="17.5" cy="6.5" r="3.5" />
                    <path d="M3 20h4M5 18v4M14 15l5 5m0-5l-5 5" />
                </svg>
            </button>
            <button type="button" class="rpv-tool" data-tool="sticky" title="Sticky note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="9" y1="13" x2="15" y2="13" />
                    <line x1="9" y1="17" x2="13" y2="17" />
                </svg>
            </button>

            <div class="rpv-ab-sep"></div>

            {{-- Eraser --}}
            <button type="button" class="rpv-tool" data-tool="eraser" title="Hapus anotasi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M20 20H7L3 16l10-10 7 7-3 3" />
                    <path d="M6.5 17.5l5-5" />
                </svg>
            </button>

            <div class="rpv-ab-sep"></div>

            {{-- Shape picker (muncul saat tool=shape) --}}
            <div class="rpv-shapes" id="rpv-shapes">
                <button type="button" class="rpv-shape active" data-shape="rect" title="Kotak">⬛</button>
                <button type="button" class="rpv-shape" data-shape="ellipse" title="Lingkaran">⭕</button>
                <button type="button" class="rpv-shape" data-shape="arrow" title="Panah">➡</button>
                <button type="button" class="rpv-shape" data-shape="line" title="Garis">—</button>
                <div class="rpv-ab-sep"></div>
            </div>

            {{-- Size picker --}}
            <div class="rpv-sizes" id="rpv-sizes">
                <div class="rpv-size selected" data-size="2" title="Tipis"></div>
                <div class="rpv-size" data-size="4" title="Normal"></div>
                <div class="rpv-size" data-size="8" title="Tebal"></div>
                <div class="rpv-size" data-size="14" title="Sangat tebal"></div>
                <div class="rpv-ab-sep"></div>
            </div>

            {{-- Colors --}}
            <div class="rpv-colors">
                <div class="rpv-color selected" data-color="yellow" title="Kuning"></div>
                <div class="rpv-color" data-color="green" title="Hijau"></div>
                <div class="rpv-color" data-color="red" title="Merah"></div>
                <div class="rpv-color" data-color="blue" title="Biru"></div>
                <div class="rpv-color" data-color="orange" title="Oranye"></div>
                <div class="rpv-color" data-color="pink" title="Pink"></div>
                <div class="rpv-color" data-color="purple" title="Ungu"></div>
                <div class="rpv-color" data-color="cyan" title="Cyan"></div>
                <div class="rpv-color" data-color="black" title="Hitam"></div>
                <div class="rpv-color" data-color="white" title="Putih"></div>
            </div>

            <div class="rpv-ab-sep"></div>

            {{-- Undo / Redo --}}
            <button type="button" class="rpv-action" id="rpv-undo" title="Undo (Ctrl+Z)" disabled>↩</button>
            <button type="button" class="rpv-action" id="rpv-redo" title="Redo (Ctrl+Y)" disabled>↪</button>

            <div class="rpv-ab-sep"></div>

            {{-- Panel toggle --}}
            <button type="button" class="rpv-tool" id="rpv-panel-btn" title="Daftar anotasi" style="position:relative;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <line x1="8" y1="6" x2="21" y2="6" />
                    <line x1="8" y1="12" x2="21" y2="12" />
                    <line x1="8" y1="18" x2="21" y2="18" />
                    <circle cx="3" cy="6" r="1" fill="currentColor" />
                    <circle cx="3" cy="12" r="1" fill="currentColor" />
                    <circle cx="3" cy="18" r="1" fill="currentColor" />
                </svg>
                <span class="rpv-badge" id="rpv-badge">0</span>
            </button>

        </div>{{-- /.rpv-ab-tools --}}
    </div>{{-- /#rpv-annot-bar --}}

    {{-- ══ OVERLAYS (di luar canvas-wrap agar z-index benar) ══ --}}

    {{-- Tooltip --}}
    <div id="rpv-tooltip">
        <div class="rpv-tip-text" id="rpv-tip-text"></div>
        <div class="rpv-tip-actions">
            <button type="button" id="rpv-tip-edit" style="flex:1;padding:.3rem;background:rgba(96,165,250,.12);
                           border:1px solid #60a5fa;color:#60a5fa;border-radius:6px;
                           font-size:11px;cursor:pointer;display:none;">✏️ Edit</button>
            <button type="button" class="rpv-tip-del" id="rpv-tip-del">🗑 Hapus</button>
            <button type="button" class="rpv-tip-close" id="rpv-tip-close">✕ Tutup</button>
        </div>
    </div>

    {{-- Comment popup --}}
    <div class="rpv-popup" id="rpv-comment-pop">
        <p class="rpv-popup-title">💬 Tambah Komentar</p>
        <textarea id="rpv-comment-txt" placeholder="Catatan reviewer untuk teks ini..."></textarea>
        <div class="rpv-popup-actions">
            <button type="button" class="rpv-popup-save" id="rpv-comment-save">Simpan</button>
            <button type="button" class="rpv-popup-cancel" id="rpv-comment-cancel">Batal</button>
        </div>
    </div>

    {{-- Sticky popup --}}
    <div class="rpv-popup" id="rpv-sticky-pop">
        <p class="rpv-popup-title">📌 Tambah Sticky Note</p>
        <textarea id="rpv-sticky-txt" placeholder="Catatan untuk bagian ini..."></textarea>
        <div class="rpv-popup-actions">
            <button type="button" class="rpv-popup-save" id="rpv-sticky-save">Tempel</button>
            <button type="button" class="rpv-popup-cancel" id="rpv-sticky-cancel">Batal</button>
        </div>
    </div>

    {{-- Search overlay --}}
    <div id="rpv-search">
        <div id="rpv-search-box">
            <div class="rpv-search-row">
                <input type="text" id="rpv-search-input" placeholder="Cari kata atau kalimat...">
                <button type="button" class="rpv-snav" id="rpv-sprev">↑</button>
                <button type="button" class="rpv-snav" id="rpv-snext">↓</button>
                <button type="button" class="rpv-snav" id="rpv-sclose">✕</button>
            </div>
            <div id="rpv-search-status">Ketik untuk mencari...</div>
            <div id="rpv-search-results"></div>
        </div>
    </div>

    {{-- Mobile bottom sheet backdrop --}}
    <div id="rpv-sheet-backdrop"></div>

    {{-- Mobile bottom sheet --}}
    <div id="rpv-bottom-sheet">
        <div class="rpv-sheet-handle"></div>

        <p style="font-size:12px;font-weight:700;color:#fff;margin:0 0 .75rem;
                      white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
            {{ Str::limit($publicationTitle ?? 'Naskah', 38) }}
        </p>

        {{-- Navigasi halaman --}}
        <div class="rpv-sheet-sec">
            <span class="rpv-sheet-lbl">Navigasi</span>
            <div class="rpv-sheet-page-row">
                <button type="button" class="rpv-sheet-page-btn" id="rpv-sheet-prev">‹</button>
                <div class="rpv-sheet-page-display">
                    <strong id="rpv-sheet-page">1</strong>
                    <small>halaman</small>
                </div>
                <button type="button" class="rpv-sheet-page-btn" id="rpv-sheet-next">›</button>
            </div>
        </div>

        {{-- Zoom --}}
        <div class="rpv-sheet-sec">
            <span class="rpv-sheet-lbl">Zoom</span>
            <div class="rpv-sheet-zoom-row">
                <button type="button" class="rpv-sheet-zoom-btn" id="rpv-sheet-zoom-out">−</button>
                <span class="rpv-sheet-zoom-val" id="rpv-sheet-zoom-val">100%</span>
                <button type="button" class="rpv-sheet-zoom-btn" id="rpv-sheet-zoom-in">+</button>
            </div>
        </div>

        {{-- Mode baca --}}
        <div class="rpv-sheet-sec">
            <span class="rpv-sheet-lbl">Mode Baca</span>
            <div class="rpv-sheet-mode-row">
                <div class="rpv-sheet-mode-card active" data-rpv-sheet-mode="normal">☀️<br>Normal</div>
                <div class="rpv-sheet-mode-card" data-rpv-sheet-mode="sepia">📜<br>Sepia</div>
                <div class="rpv-sheet-mode-card" data-rpv-sheet-mode="night">🌙<br>Night</div>
            </div>
        </div>

        {{-- Fullscreen & Search --}}
        <div class="rpv-sheet-sec" style="display:flex;gap:.5rem;">
            <button type="button" id="rpv-sheet-fs" style="flex:1;padding:.55rem;background:#2d2d2d;border:1px solid #3d3d3d;
                           color:#d1d5db;border-radius:8px;font-size:11px;font-weight:600;
                           cursor:pointer;">🔲 Fullscreen</button>
            <button type="button" id="rpv-sheet-search" style="flex:1;padding:.55rem;background:#2d2d2d;border:1px solid #3d3d3d;
                           color:#d1d5db;border-radius:8px;font-size:11px;font-weight:600;
                           cursor:pointer;">🔍 Cari</button>
        </div>

        <button type="button" class="rpv-sheet-close" id="rpv-sheet-close">Tutup</button>
    </div>{{-- /#rpv-bottom-sheet --}}

    {{-- Sync indicator --}}
    <div id="rpv-sync">
        <div class="rpv-sync-dot"></div>
        <span id="rpv-sync-txt">Menyimpan...</span>
    </div>

    {{-- Eraser cursor --}}
    <div id="rpv-eraser-cursor"></div>

    {{-- ══ SCRIPTS ══ --}}

    {{-- Config untuk JS --}}
    <script>
        window.RPV_CONFIG = {
                pdfUrl      : @json($pdfUrl),
                reviewId    : @json($reviewId),
                apiBase     : @json($annotApiBase),
                reviewerName: @json($reviewerName),
            };
    </script>

    {{-- pdf.js 3.11 --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" crossorigin="anonymous"></script>

    {{-- jsPDF UMD --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" crossorigin="anonymous"></script>

    {{-- Review PDF Viewer (cache-bust dengan filemtime) --}}
    <script src="{{ asset('js/review-pdf-viewer.js') }}?v={{ filemtime(public_path('js/review-pdf-viewer.js')) }}">
    </script>

    @endif

</div>{{-- /#rpv-outer-wrap --}}