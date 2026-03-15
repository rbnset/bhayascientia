{{--
resources/views/filament/reviews/pdf-viewer-readonly.blade.php
Mobile-first responsive PDF viewer — read-only for author
--}}

@php
use App\Models\PublicationVersion;
use App\Models\PdfAnnotation;

$review = $this->record ?? null;
$reviewId = $review?->id ?? null;
$versionId = $review?->publication_version_id ?? null;

$pdfUrl = null;
$publicationTitle = null;

if ($versionId) {
$version = PublicationVersion::with('publication')->find($versionId);
if ($version) {
$publicationTitle = $version->publication?->title;
$pdfUrl = $version->pdf_file_path
? route('manuscripts.view', $version)
: null;
}
}

$annotations = $reviewId
? PdfAnnotation::where('review_id', $reviewId)
->get()
->map(function ($a) {
$arr = $a->toArray();
$arr['arrow_x1'] = null;
$arr['arrow_y1'] = null;
$arr['arrow_x2'] = null;
$arr['arrow_y2'] = null;
return $arr;
})
->toArray()
: [];
@endphp

@if (!$pdfUrl)
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                gap:1rem;text-align:center;padding:3rem 1rem;min-height:280px;">
    <div style="font-size:2.5rem;">📄</div>
    <p style="color:#6B7280;font-size:.875rem;margin:0;">PDF tidak tersedia.</p>
</div>
@else

<link rel="stylesheet"
    href="{{ asset('css/review-pdf-viewer.css') }}?v={{ filemtime(public_path('css/review-pdf-viewer.css')) }}">

<style>
    /* ════════════════════════════════════════════════════════
   RESET & BASE — mobile first
════════════════════════════════════════════════════════ */
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    #rpv-ro-wrap {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        background: #141414;
        font-size: 13px;
        -webkit-font-smoothing: antialiased;
    }

    /* ── Canvas wrap ── */
    #rpv-ro-wrap #rpv-canvas-wrap {
        overflow-x: hidden !important;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    #rpv-ro-wrap #rpv-stage {
        margin: 0 auto;
        max-width: 100%;
    }

    /* ════════════════════════════════════════════════════════
   TOOLBAR — mobile first (semua tombol tampil)
════════════════════════════════════════════════════════ */
    #rpv-ro-toolbar {
        display: flex;
        align-items: center;
        gap: 3px;
        padding: 6px 8px;
        background: #1a1a1a;
        border-bottom: 1px solid #2d2d2d;
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        min-height: 44px;
    }

    #rpv-ro-toolbar::-webkit-scrollbar {
        display: none;
    }

    /* Title — hanya desktop */
    #rpv-ro-title {
        display: none;
    }

    @media (min-width: 640px) {
        #rpv-ro-title {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #9CA3AF;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 160px;
            flex-shrink: 0;
        }
    }

    /* Badge view-only */
    #rpv-ro-badge-vo {
        display: none;
        flex-shrink: 0;
    }

    @media (min-width: 768px) {
        #rpv-ro-badge-vo {
            display: inline-flex;
            align-items: center;
            background: #374151;
            color: #9CA3AF;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
            border: 1px solid #4B5563;
            letter-spacing: .5px;
            text-transform: uppercase;
            white-space: nowrap;
        }
    }

    /* ── Tombol umum ── */
    .rpv-ro-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        padding: 0 8px;
        height: 30px;
        min-width: 30px;
        background: #2d2d2d;
        border: 1px solid #3d3d3d;
        border-radius: 6px;
        color: #d1d5db;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
        transition: background .15s, border-color .15s, color .15s;
        -webkit-tap-highlight-color: transparent;
        user-select: none;
    }

    .rpv-ro-btn:hover {
        background: #3d3d3d;
        border-color: #4d4d4d;
        color: #fff;
    }

    .rpv-ro-btn:active {
        background: #4d4d4d;
        transform: scale(.96);
    }

    .rpv-ro-btn.primary {
        background: #FF6B18;
        border-color: #FF6B18;
        color: #fff;
    }

    .rpv-ro-btn.primary:hover {
        background: #e55c10;
    }

    .rpv-ro-btn.primary:active {
        background: #cc530e;
    }

    .rpv-ro-btn:disabled {
        opacity: .35;
        cursor: not-allowed;
    }

    .rpv-ro-btn svg {
        flex-shrink: 0;
    }

    /* Teks label tombol — sembunyikan di mobile kecil */
    .rpv-ro-btn-label {
        display: none;
    }

    @media (min-width: 480px) {
        .rpv-ro-btn-label {
            display: inline;
        }
    }

    /* ── Separator ── */
    .rpv-ro-sep {
        width: 1px;
        height: 20px;
        background: #3d3d3d;
        flex-shrink: 0;
        margin: 0 2px;
    }

    /* ── Page group ── */
    .rpv-ro-page-group {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        background: #222;
        border: 1px solid #3d3d3d;
        border-radius: 6px;
        padding: 0 2px;
        height: 30px;
        flex-shrink: 0;
    }

    .rpv-ro-page-input {
        width: 32px;
        height: 22px;
        background: transparent;
        border: none;
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        text-align: center;
        outline: none;
        -moz-appearance: textfield;
    }

    .rpv-ro-page-input::-webkit-inner-spin-button,
    .rpv-ro-page-input::-webkit-outer-spin-button {
        -webkit-appearance: none;
    }

    .rpv-ro-page-sep {
        color: #6B7280;
        font-size: 11px;
        padding: 0 1px;
    }

    .rpv-ro-page-total {
        color: #9CA3AF;
        font-size: 11px;
        padding: 0 3px;
    }

    /* ── Mode buttons ── */
    .rpv-ro-mode-btn.active {
        background: #FF6B18 !important;
        color: #fff !important;
        border-color: #FF6B18 !important;
    }

    /* ── Zoom value ── */
    #rpv-ro-zoom-val {
        font-size: 11px;
        font-weight: 600;
        color: #9CA3AF;
        min-width: 36px;
        text-align: center;
        flex-shrink: 0;
    }

    /* ════════════════════════════════════════════════════════
   PROGRESS BAR (halaman)
════════════════════════════════════════════════════════ */
    .rpv-ro-progress-track {
        height: 2px;
        background: #2d2d2d;
    }

    .rpv-ro-progress-fill {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #FF6B18, #ff9a5c);
        transition: width .3s;
    }

    /* ════════════════════════════════════════════════════════
   LOADING
════════════════════════════════════════════════════════ */
    #rpv-ro-loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 3rem 1rem;
        min-height: 300px;
    }

    .rpv-ro-spinner {
        width: 36px;
        height: 36px;
        border: 3px solid #2d2d2d;
        border-top-color: #FF6B18;
        border-radius: 50%;
        animation: rpv-ro-spin .7s linear infinite;
    }

    @keyframes rpv-ro-spin {
        to {
            transform: rotate(360deg);
        }
    }

    #rpv-ro-load-bar-wrap {
        width: min(200px, 70vw);
        height: 5px;
        background: #2d2d2d;
        border-radius: 99px;
        overflow: hidden;
    }

    #rpv-ro-load-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #FF6B18, #ff9a5c);
        border-radius: 99px;
        transition: width .3s;
    }

    /* ════════════════════════════════════════════════════════
   FULLSCREEN
════════════════════════════════════════════════════════ */
    #rpv-ro-wrap.is-fullscreen {
        position: fixed !important;
        inset: 0 !important;
        z-index: 99990 !important;
        border-radius: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        display: flex;
        flex-direction: column;
    }

    #rpv-ro-wrap.is-fullscreen #rpv-canvas-wrap {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* ════════════════════════════════════════════════════════
   READING MODES
════════════════════════════════════════════════════════ */
    #rpv-ro-wrap.mode-sepia #rpv-canvas {
        filter: sepia(.65) brightness(.95);
    }

    #rpv-ro-wrap.mode-night #rpv-canvas {
        filter: invert(1) hue-rotate(180deg) brightness(.85);
    }

    #rpv-ro-wrap.mode-night #rpv-freehand-canvas {
        filter: invert(1) hue-rotate(180deg);
    }

    /* ════════════════════════════════════════════════════════
   ANNOTATION PANEL
════════════════════════════════════════════════════════ */
    #rpv-ro-panel {
        position: absolute;
        top: 0;
        right: -320px;
        width: min(300px, 90vw);
        height: 100%;
        background: #1a1a1a;
        border-left: 1px solid #2d2d2d;
        z-index: 200;
        display: flex;
        flex-direction: column;
        transition: right .25s cubic-bezier(.4, 0, .2, 1);
        border-radius: 0 0 10px 0;
    }

    #rpv-ro-panel.open {
        right: 0;
    }

    .rpv-ro-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        border-bottom: 1px solid #2d2d2d;
        flex-shrink: 0;
    }

    .rpv-ro-panel-title {
        font-size: 12px;
        font-weight: 700;
        color: #fff;
    }

    .rpv-ro-panel-close {
        background: none;
        border: none;
        color: #9CA3AF;
        cursor: pointer;
        font-size: 14px;
        padding: 2px 6px;
        border-radius: 4px;
        transition: color .15s;
    }

    .rpv-ro-panel-close:hover {
        color: #fff;
    }

    .rpv-ro-panel-list {
        flex: 1;
        overflow-y: auto;
        padding: 8px;
        scrollbar-width: thin;
        scrollbar-color: #3d3d3d transparent;
    }

    .rpv-ro-panel-empty {
        color: #6B7280;
        font-size: 12px;
        text-align: center;
        padding: 24px 12px;
        font-style: italic;
    }

    .rpv-ro-panel-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 8px;
        border-radius: 6px;
        cursor: pointer;
        transition: background .15s;
        margin-bottom: 4px;
        border: 1px solid transparent;
    }

    .rpv-ro-panel-item:hover {
        background: #2d2d2d;
        border-color: #3d3d3d;
    }

    .rpv-ro-panel-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 3px;
    }

    .rpv-ro-panel-body {
        flex: 1;
        min-width: 0;
    }

    .rpv-ro-panel-type {
        font-size: 10px;
        font-weight: 700;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .rpv-ro-panel-pg {
        font-size: 10px;
        color: #6B7280;
        margin-left: 6px;
    }

    .rpv-ro-panel-text {
        font-size: 11px;
        color: #d1d5db;
        margin-top: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ════════════════════════════════════════════════════════
   TOOLTIP
════════════════════════════════════════════════════════ */
    #rpv-ro-tooltip {
        position: fixed;
        z-index: 20000;
        background: #1a1a1a;
        border: 1.5px solid #3d3d3d;
        border-radius: 10px;
        padding: 10px 12px;
        width: min(260px, 90vw);
        box-shadow: 0 8px 32px rgba(0, 0, 0, .5);
        display: none;
        pointer-events: auto;
    }

    #rpv-ro-tooltip.show {
        display: block;
    }

    #rpv-ro-tip-text {
        font-size: 12px;
        color: #d1d5db;
        line-height: 1.5;
        margin-bottom: 8px;
        word-break: break-word;
    }

    #rpv-ro-tip-close {
        display: block;
        width: 100%;
        padding: 5px;
        background: #2d2d2d;
        border: 1px solid #3d3d3d;
        color: #9CA3AF;
        border-radius: 6px;
        font-size: 11px;
        cursor: pointer;
        text-align: center;
        transition: background .15s;
    }

    #rpv-ro-tip-close:hover {
        background: #3d3d3d;
        color: #fff;
    }

    /* ════════════════════════════════════════════════════════
   BOOKMARK TOAST
════════════════════════════════════════════════════════ */
    #rpv-ro-bookmark-toast {
        position: absolute;
        top: 50px;
        left: 50%;
        transform: translateX(-50%) translateY(-8px);
        background: #1a1a1a;
        border: 1.5px solid #FF6B18;
        color: #fff;
        padding: 6px 14px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 600;
        z-index: 500;
        opacity: 0;
        transition: opacity .3s, transform .3s;
        pointer-events: none;
        white-space: nowrap;
    }

    #rpv-ro-bookmark-toast.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    /* ════════════════════════════════════════════════════════
   EXPORT OVERLAY
════════════════════════════════════════════════════════ */
    #rpv-ro-export-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .8);
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        z-index: 300;
        border-radius: inherit;
    }

    #rpv-ro-export-overlay.show {
        display: flex;
    }

    /* ════════════════════════════════════════════════════════
   STICKY NOTE — responsive size
════════════════════════════════════════════════════════ */
    .rpv-ro-sticky {
        position: absolute;
        z-index: 9;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .3);
        cursor: pointer;
        /* Ukuran dasar — mobile */
        width: 130px;
        min-height: 70px;
        font-size: 10px;
        transition: box-shadow .15s, transform .15s;
        -webkit-tap-highlight-color: transparent;
    }

    .rpv-ro-sticky:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, .4);
        transform: scale(1.02);
        z-index: 10;
    }

    @media (min-width: 640px) {
        .rpv-ro-sticky {
            width: 160px;
            min-height: 85px;
            font-size: 11px;
        }
    }

    @media (min-width: 1024px) {
        .rpv-ro-sticky {
            width: 180px;
            min-height: 95px;
            font-size: 12px;
        }
    }

    .rpv-ro-sticky-header {
        display: flex;
        align-items: center;
        padding: 4px 6px;
        border-radius: 6px 6px 0 0;
        font-size: 11px;
        font-weight: 700;
    }

    .rpv-ro-sticky-body {
        padding: 5px 7px 6px;
        line-height: 1.45;
        word-break: break-word;
        overflow: hidden;
        color: #1F2937;
    }

    /* Warna sticky */
    .rpv-ro-sticky[data-color="yellow"] {
        background: #FEF9C3;
        border: 1.5px solid #EAB308;
    }

    .rpv-ro-sticky[data-color="yellow"] .rpv-ro-sticky-header {
        background: rgba(234, 179, 8, .25);
    }

    .rpv-ro-sticky[data-color="green"] {
        background: #DCFCE7;
        border: 1.5px solid #22C55E;
    }

    .rpv-ro-sticky[data-color="green"] .rpv-ro-sticky-header {
        background: rgba(34, 197, 94, .25);
    }

    .rpv-ro-sticky[data-color="red"] {
        background: #FEE2E2;
        border: 1.5px solid #EF4444;
    }

    .rpv-ro-sticky[data-color="red"] .rpv-ro-sticky-header {
        background: rgba(239, 68, 68, .25);
    }

    .rpv-ro-sticky[data-color="blue"] {
        background: #DBEAFE;
        border: 1.5px solid #3B82F6;
    }

    .rpv-ro-sticky[data-color="blue"] .rpv-ro-sticky-header {
        background: rgba(59, 130, 246, .25);
    }

    .rpv-ro-sticky[data-color="orange"] {
        background: #FFEDD5;
        border: 1.5px solid #F97316;
    }

    .rpv-ro-sticky[data-color="orange"] .rpv-ro-sticky-header {
        background: rgba(249, 115, 22, .25);
    }

    .rpv-ro-sticky[data-color="pink"] {
        background: #FCE7F3;
        border: 1.5px solid #EC4899;
    }

    .rpv-ro-sticky[data-color="pink"] .rpv-ro-sticky-header {
        background: rgba(236, 72, 153, .25);
    }

    .rpv-ro-sticky[data-color="purple"] {
        background: #EDE9FE;
        border: 1.5px solid #8B5CF6;
    }

    .rpv-ro-sticky[data-color="purple"] .rpv-ro-sticky-header {
        background: rgba(139, 92, 246, .25);
    }

    .rpv-ro-sticky[data-color="cyan"] {
        background: #CFFAFE;
        border: 1.5px solid #06B6D4;
    }

    .rpv-ro-sticky[data-color="cyan"] .rpv-ro-sticky-header {
        background: rgba(6, 182, 212, .25);
    }

    .rpv-ro-sticky[data-color="black"] {
        background: #1F2937;
        border: 1.5px solid #374151;
    }

    .rpv-ro-sticky[data-color="black"] .rpv-ro-sticky-header {
        background: rgba(255, 255, 255, .08);
    }

    .rpv-ro-sticky[data-color="black"] .rpv-ro-sticky-body {
        color: #E5E7EB;
    }

    .rpv-ro-sticky[data-color="white"] {
        background: #F9FAFB;
        border: 1.5px solid #D1D5DB;
    }

    .rpv-ro-sticky[data-color="white"] .rpv-ro-sticky-header {
        background: rgba(0, 0, 0, .06);
    }

    /* ════════════════════════════════════════════════════════
   BADGE count
════════════════════════════════════════════════════════ */
    .rpv-ro-count-badge {
        display: none;
        position: absolute;
        top: -5px;
        right: -5px;
        background: #FF6B18;
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        min-width: 16px;
        height: 16px;
        border-radius: 99px;
        padding: 0 3px;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .rpv-ro-count-badge.show {
        display: flex;
    }

    /* ════════════════════════════════════════════════════════
   PANEL BACKDROP (mobile)
════════════════════════════════════════════════════════ */
    #rpv-ro-panel-backdrop {
        display: none;
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .5);
        z-index: 199;
    }

    #rpv-ro-panel-backdrop.show {
        display: block;
    }
</style>

<div id="rpv-ro-wrap">

    {{-- ══ TOOLBAR ══ --}}
    <div id="rpv-ro-toolbar" role="toolbar" aria-label="PDF viewer controls">

        <span id="rpv-ro-title" title="{{ $publicationTitle }}">
            📄 {{ Str::limit($publicationTitle ?? 'Naskah', 28) }}
        </span>

        <span id="rpv-ro-badge-vo">👁 View Only</span>

        <div class="rpv-ro-sep" style="display:none;" id="rpv-ro-sep1"></div>

        {{-- Navigasi halaman --}}
        <div class="rpv-ro-page-group">
            <button type="button" class="rpv-ro-btn" id="rpv-ro-prev"
                style="border:none;background:transparent;min-width:24px;padding:0 4px;" title="Halaman sebelumnya (←)"
                aria-label="Halaman sebelumnya">‹</button>
            <input type="number" id="rpv-ro-page-input" class="rpv-ro-page-input" value="1" min="1"
                aria-label="Nomor halaman">
            <span class="rpv-ro-page-sep">/</span>
            <span class="rpv-ro-page-total" id="rpv-ro-page-total">—</span>
            <button type="button" class="rpv-ro-btn" id="rpv-ro-next"
                style="border:none;background:transparent;min-width:24px;padding:0 4px;" title="Halaman berikutnya (→)"
                aria-label="Halaman berikutnya">›</button>
        </div>

        <div class="rpv-ro-sep"></div>

        {{-- Zoom --}}
        <button type="button" class="rpv-ro-btn" id="rpv-ro-zoom-out" title="Perkecil (-)"
            aria-label="Perkecil">−</button>
        <span id="rpv-ro-zoom-val">100%</span>
        <button type="button" class="rpv-ro-btn" id="rpv-ro-zoom-in" title="Perbesar (+)"
            aria-label="Perbesar">+</button>

        <div class="rpv-ro-sep"></div>

        {{-- Mode baca --}}
        <button type="button" class="rpv-ro-btn rpv-ro-mode-btn active" data-ro-mode="normal" title="Mode Normal"
            aria-label="Mode normal">☀️</button>
        <button type="button" class="rpv-ro-btn rpv-ro-mode-btn" data-ro-mode="sepia" title="Mode Sepia"
            aria-label="Mode sepia">📜</button>
        <button type="button" class="rpv-ro-btn rpv-ro-mode-btn" data-ro-mode="night" title="Mode Malam"
            aria-label="Mode malam">🌙</button>

        <div class="rpv-ro-sep"></div>

        {{-- Bookmark --}}
        <button type="button" class="rpv-ro-btn" id="rpv-ro-bookmark-btn" title="Tandai halaman ini"
            aria-label="Tandai halaman">
            🔖<span class="rpv-ro-btn-label"> Tandai</span>
        </button>

        {{-- Panel anotasi --}}
        <button type="button" class="rpv-ro-btn" id="rpv-ro-panel-btn" style="position:relative;"
            title="Lihat anotasi reviewer" aria-label="Lihat anotasi">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"
                aria-hidden="true">
                <line x1="8" y1="6" x2="21" y2="6" />
                <line x1="8" y1="12" x2="21" y2="12" />
                <line x1="8" y1="18" x2="21" y2="18" />
                <circle cx="3" cy="6" r="1" fill="currentColor" />
                <circle cx="3" cy="12" r="1" fill="currentColor" />
                <circle cx="3" cy="18" r="1" fill="currentColor" />
            </svg>
            <span class="rpv-ro-btn-label"> Anotasi</span>
            <span class="rpv-ro-count-badge" id="rpv-ro-badge">0</span>
        </button>

        {{-- Download --}}
        <button type="button" class="rpv-ro-btn primary" id="rpv-ro-download-btn"
            title="Download PDF beserta anotasi reviewer" aria-label="Download PDF dengan anotasi">
            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span class="rpv-ro-btn-label"> Download+Anotasi</span>
        </button>

        {{-- Fullscreen --}}
        <button type="button" class="rpv-ro-btn" id="rpv-ro-fs-btn" title="Layar penuh (F)" aria-label="Layar penuh">
            <svg id="rpv-ro-fs-icon" style="width:13px;height:13px;" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5
                         M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
            <span class="rpv-ro-btn-label" id="rpv-ro-fs-label"> Fullscreen</span>
        </button>

    </div>{{-- /#rpv-ro-toolbar --}}

    {{-- Progress bar halaman --}}
    <div class="rpv-ro-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100">
        <div class="rpv-ro-progress-fill" id="rpv-ro-progress"></div>
    </div>

    {{-- ══ CANVAS AREA ══ --}}
    <div id="rpv-canvas-wrap">

        {{-- Loading dengan progress persen --}}
        <div id="rpv-ro-loading" role="status" aria-live="polite">
            <div class="rpv-ro-spinner" aria-hidden="true"></div>
            <p style="color:#fff;font-size:13px;font-weight:600;margin:0;">Memuat dokumen...</p>
            <p style="color:#9CA3AF;font-size:12px;margin:0;" id="rpv-ro-load-pct">0%</p>
            <div id="rpv-ro-load-bar-wrap" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                <div id="rpv-ro-load-bar"></div>
            </div>
        </div>

        <div id="rpv-stage">
            <canvas id="rpv-canvas"></canvas>
            <div id="rpv-text-layer" style="pointer-events:none;user-select:none;"></div>
            <div id="rpv-annotation-layer"></div>
            <canvas id="rpv-freehand-canvas" style="pointer-events:none;position:absolute;inset:0;z-index:10;"
                aria-hidden="true"></canvas>
        </div>

        {{-- Backdrop panel (mobile) --}}
        <div id="rpv-ro-panel-backdrop" aria-hidden="true"></div>

        {{-- Panel anotasi --}}
        <div id="rpv-ro-panel" role="complementary" aria-label="Daftar anotasi reviewer">
            <div class="rpv-ro-panel-header">
                <span class="rpv-ro-panel-title">👁 Anotasi Reviewer</span>
                <button type="button" class="rpv-ro-panel-close" id="rpv-ro-panel-close"
                    aria-label="Tutup panel">✕</button>
            </div>
            <div class="rpv-ro-panel-list" id="rpv-ro-panel-list">
                <div class="rpv-ro-panel-empty">Belum ada anotasi.</div>
            </div>
        </div>

        {{-- Export overlay --}}
        <div id="rpv-ro-export-overlay" role="status" aria-live="polite" aria-label="Sedang mengekspor">
            <div class="rpv-ro-spinner" aria-hidden="true"></div>
            <p style="color:#fff;font-size:13px;font-weight:600;margin:0;">Mengekspor PDF + Anotasi...</p>
            <p style="color:#9CA3AF;font-size:12px;margin:0;" id="rpv-ro-export-status">Memproses halaman...</p>
        </div>

    </div>{{-- /#rpv-canvas-wrap --}}

    {{-- Tooltip --}}
    <div id="rpv-ro-tooltip" role="tooltip">
        <div id="rpv-ro-tip-text"></div>
        <button type="button" id="rpv-ro-tip-close" aria-label="Tutup tooltip">✕ Tutup</button>
    </div>

    {{-- Bookmark toast --}}
    <div id="rpv-ro-bookmark-toast" role="status" aria-live="polite">
        🔖 <span id="rpv-ro-bookmark-msg">Halaman ditandai</span>
    </div>

</div>{{-- /#rpv-ro-wrap --}}

<script>
    window.RPV_RO_CONFIG = {
        pdfUrl     : @json($pdfUrl),
        reviewId   : @json($reviewId),
        annotations: @json($annotations),
        title      : @json($publicationTitle),
    };
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" crossorigin="anonymous"></script>

<script>
    (function () {
    'use strict';

    var WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    var CFG    = window.RPV_RO_CONFIG;
    if (!CFG || !CFG.pdfUrl) return;

    pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER;
    pdfjsLib.verbosity = 0;

    /* ── Colors ── */
    var COLORS = {
        yellow:'#FFD700', green:'#4ADE80', red:'#EF4444', blue:'#60A5FA',
        orange:'#FF6B18', black:'#111111', white:'#FFFFFF',
        pink:'#F472B6',   purple:'#A78BFA', cyan:'#22D3EE'
    };
    function hex(n) { return COLORS[n] || '#FFD700'; }
    function esc(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;')
                            .replace(/>/g,'&gt;').replace(/\n/g,'<br>');
    }

    /* ── Responsive sticky size (matches CSS breakpoints) ── */
    function getStickySize() {
        var vw = window.innerWidth;
        if (vw < 640)  return { w: 130, h: 70,  fs: 10, pad: 5,  headerH: 18 };
        if (vw < 1024) return { w: 160, h: 85,  fs: 11, pad: 6,  headerH: 20 };
        return            { w: 180, h: 95,  fs: 12, pad: 7,  headerH: 22 };
    }

    /* ── DOM ── */
    var outerWrap  = document.getElementById('rpv-ro-wrap');
    var wrap       = document.getElementById('rpv-canvas-wrap');
    var stage      = document.getElementById('rpv-stage');
    var mainCanvas = document.getElementById('rpv-canvas');
    var ctx        = mainCanvas.getContext('2d');
    var annotLayer = document.getElementById('rpv-annotation-layer');
    var freeCanvas = document.getElementById('rpv-freehand-canvas');
    var freeCtx    = freeCanvas ? freeCanvas.getContext('2d') : null;
    var loadingEl  = document.getElementById('rpv-ro-loading');
    var tooltip    = document.getElementById('rpv-ro-tooltip');
    var exportOL   = document.getElementById('rpv-ro-export-overlay');
    var loadPct    = document.getElementById('rpv-ro-load-pct');
    var loadBar    = document.getElementById('rpv-ro-load-bar');

    /* ── State ── */
    var annots        = [];
    var pdfDoc        = null;
    var pageNum       = 1, pageRendering = false, pendingPage = null;
    var baseScale     = 1, zoomFactor = 1, needsRecompute = true;
    var DPR           = Math.min(window.devicePixelRatio || 1, 2);
    var ZOOM_MIN      = 0.4, ZOOM_MAX = 4, ZOOM_STEP = 0.2;
    var isFullscreen  = false;
    var exportBusy    = false;
    var SK_BOOKMARK   = 'rpv_ro_bm_'   + (CFG.reviewId || 'x');
    var SK_PAGE       = 'rpv_ro_pg_'   + (CFG.reviewId || 'x');
    var SK_MODE       = 'rpv_ro_mode_' + (CFG.reviewId || 'x');

    /* ── Normalize anotasi ── */
    function normalize(rows) {
        return rows.map(function (a) {
            if (!a.rect && a.rect_x != null) {
                a.rect = { x: +a.rect_x, y: +a.rect_y, w: +a.rect_w, h: +a.rect_h };
            }
            if (a.type === 'shape' && (a.shape_type === 'arrow' || a.shape_type === 'line')) {
                if (a.arrow_x1 == null && Array.isArray(a.path_points) && a.path_points.length >= 2) {
                    a.arrow_x1 = +a.path_points[0][0]; a.arrow_y1 = +a.path_points[0][1];
                    a.arrow_x2 = +a.path_points[1][0]; a.arrow_y2 = +a.path_points[1][1];
                }
            }
            return a;
        });
    }

    /* ── Sync freehand canvas ── */
    function syncFC() {
        if (!freeCanvas) return;
        var w = stage.offsetWidth, h = stage.offsetHeight;
        if (freeCanvas.width !== w || freeCanvas.height !== h) {
            freeCanvas.width = w; freeCanvas.height = h;
        }
        freeCanvas.style.width = w + 'px'; freeCanvas.style.height = h + 'px';
    }

    /* ── Snack ── */
    function snack(msg, color) {
        color = color || '#FF6B18';
        var el = document.createElement('div');
        el.textContent = msg;
        el.setAttribute('role', 'alert');
        el.style.cssText = 'position:fixed;top:1rem;left:50%;transform:translateX(-50%);'
            + 'background:#1A1A1A;border:1px solid ' + color + ';color:#fff;'
            + 'padding:.45rem 1rem;border-radius:99px;font-size:13px;font-weight:600;'
            + 'z-index:99999;transition:opacity .4s;pointer-events:none;white-space:nowrap;'
            + 'max-width:90vw;text-overflow:ellipsis;overflow:hidden;';
        document.body.appendChild(el);
        setTimeout(function () {
            el.style.opacity = 0;
            setTimeout(function () { el.remove(); }, 400);
        }, 2500);
    }

    /* ══════════════════════════════════════════════════════
       RENDER ANOTASI (tampilan di layar)
    ══════════════════════════════════════════════════════ */
    function doRender() {
        var s = baseScale * zoomFactor;
        annotLayer.innerHTML = '';
        annotLayer.style.pointerEvents = 'auto';
        syncFC();
        if (freeCtx) freeCtx.clearRect(0, 0, freeCanvas.width, freeCanvas.height);
        stage.querySelectorAll('.rpv-ro-sticky').forEach(function (e) { e.remove(); });

        annots.filter(function (a) { return a.page === pageNum; }).forEach(function (a) {
            if      (a.type === 'highlight' || a.type === 'comment') rHL(a, s);
            else if (a.type === 'underline')     rUL(a, s);
            else if (a.type === 'strikethrough') rST(a, s);
            else if (a.type === 'freehand')      rFH(a, s);
            else if (a.type === 'shape')         rSH(a, s);
            else if (a.type === 'sticky')        rSticky(a, s);
        });
        updateBadge();
    }

    function attachClick(el, a) {
        el.addEventListener('click', function (ev) {
            ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY);
        });
        el.addEventListener('touchend', function (ev) {
            ev.stopPropagation();
            var t = ev.changedTouches[0];
            showTip(a, t.clientX, t.clientY);
        }, { passive: true });
    }

    function rHL(a, s) {
        if (!a.rect) return;
        var el = document.createElement('div'); el.dataset.annotId = String(a.id);
        el.style.cssText = 'position:absolute;left:'+(a.rect.x*s)+'px;top:'+(a.rect.y*s)
            +'px;width:'+(a.rect.w*s)+'px;height:'+(a.rect.h*s)
            +'px;background:'+hex(a.color)+';opacity:.38;border-radius:2px;'
            +'pointer-events:auto;cursor:pointer;z-index:5;';
        if (a.type === 'comment' && a.comment) {
            var dot = document.createElement('span');
            dot.style.cssText = 'position:absolute;top:-4px;right:-4px;width:8px;height:8px;'
                + 'background:#60A5FA;border-radius:50%;pointer-events:none;box-shadow:0 0 4px #60A5FA;';
            el.appendChild(dot);
        }
        attachClick(el, a); annotLayer.appendChild(el);
    }

    function rUL(a, s) {
        if (!a.rect) return;
        var el = document.createElement('div'); el.dataset.annotId = String(a.id);
        el.style.cssText = 'position:absolute;left:'+(a.rect.x*s)+'px;top:'+((a.rect.y+a.rect.h)*s-1)
            +'px;width:'+(a.rect.w*s)+'px;height:'+Math.max(1.5,2*s)+'px;background:'+hex(a.color)
            +';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';
        attachClick(el, a); annotLayer.appendChild(el);
    }

    function rST(a, s) {
        if (!a.rect) return;
        var el = document.createElement('div'); el.dataset.annotId = String(a.id);
        var t = Math.max(1.5, 2*s);
        el.style.cssText = 'position:absolute;left:'+(a.rect.x*s)+'px;top:'
            +(a.rect.y*s + a.rect.h*s*0.62 - t/2)
            +'px;width:'+(a.rect.w*s)+'px;height:'+t+'px;background:'+hex(a.color)
            +';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';
        attachClick(el, a); annotLayer.appendChild(el);
    }

    function rFH(a, s) {
        if (!a.path_points || !a.path_points.length || !freeCtx) return;
        var pts = a.path_points;
        freeCtx.save();
        freeCtx.strokeStyle = hex(a.color);
        freeCtx.lineWidth   = (a.stroke_width||2) * s;
        freeCtx.lineCap     = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = .92;
        freeCtx.beginPath(); freeCtx.moveTo(pts[0][0]*s, pts[0][1]*s);
        for (var i = 1; i < pts.length; i++) freeCtx.lineTo(pts[i][0]*s, pts[i][1]*s);
        freeCtx.stroke(); freeCtx.restore();
        if (a.rect && (a.rect.w > 0 || a.rect.h > 0)) {
            var hit = document.createElement('div'); hit.dataset.annotId = String(a.id);
            hit.style.cssText = 'position:absolute;left:'+((a.rect.x-8)*s)+'px;top:'+((a.rect.y-8)*s)
                +'px;width:'+((a.rect.w+16)*s)+'px;height:'+((a.rect.h+16)*s)
                +'px;background:transparent;pointer-events:auto;cursor:pointer;z-index:6;';
            attachClick(hit, a); annotLayer.appendChild(hit);
        }
    }

    function rSH(a, s) {
        if (!a.rect) return;
        var col = hex(a.color), sw = Math.max(1,(a.stroke_width||2)*s), st = a.shape_type||'rect';
        var el = document.createElement('div'); el.dataset.annotId = String(a.id);
        if (st === 'arrow' || st === 'line') {
            var ax1=a.arrow_x1!=null?a.arrow_x1*s:a.rect.x*s;
            var ay1=a.arrow_y1!=null?a.arrow_y1*s:(a.rect.y+a.rect.h/2)*s;
            var ax2=a.arrow_x2!=null?a.arrow_x2*s:(a.rect.x+a.rect.w)*s;
            var ay2=a.arrow_y2!=null?a.arrow_y2*s:(a.rect.y+a.rect.h/2)*s;
            var bx=Math.min(ax1,ax2)-sw*2, by=Math.min(ay1,ay2)-sw*2;
            var bw=Math.abs(ax2-ax1)+sw*4, bh=Math.abs(ay2-ay1)+sw*4;
            var lx1=ax1-bx, ly1=ay1-by, lx2=ax2-bx, ly2=ay2-by;
            el.style.cssText = 'position:absolute;left:'+bx+'px;top:'+by+'px;width:'+bw+'px;height:'+bh+'px;pointer-events:auto;cursor:pointer;z-index:5;';
            var inner = '';
            if (st === 'line') {
                inner = '<line x1="'+lx1+'" y1="'+ly1+'" x2="'+lx2+'" y2="'+ly2+'" stroke="'+col+'" stroke-width="'+sw+'" stroke-linecap="round"/>';
            } else {
                var dx=lx2-lx1, dy=ly2-ly1, len=Math.sqrt(dx*dx+dy*dy);
                if (len > 1) {
                    var hl=Math.min(len*.35,Math.max(10,sw*5)), ag=Math.atan2(dy,dx);
                    var hx1=lx2-hl*Math.cos(ag-Math.PI/6), hy1=ly2-hl*Math.sin(ag-Math.PI/6);
                    var hx2=lx2-hl*Math.cos(ag+Math.PI/6), hy2=ly2-hl*Math.sin(ag+Math.PI/6);
                    inner = '<line x1="'+lx1+'" y1="'+ly1+'" x2="'+lx2+'" y2="'+ly2+'" stroke="'+col+'" stroke-width="'+sw+'" stroke-linecap="round"/>'
                          + '<polyline points="'+hx1+','+hy1+' '+lx2+','+ly2+' '+hx2+','+hy2+'" fill="none" stroke="'+col+'" stroke-width="'+sw+'" stroke-linecap="round" stroke-linejoin="round"/>';
                }
            }
            el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="'+bw+'" height="'+bh+'" style="overflow:visible;display:block;pointer-events:none">'+inner+'</svg>';
        } else {
            var x=a.rect.x*s, y=a.rect.y*s, w=Math.max(4,a.rect.w*s), h=Math.max(4,a.rect.h*s);
            el.style.cssText = 'position:absolute;left:'+x+'px;top:'+y+'px;width:'+w+'px;height:'+h+'px;pointer-events:auto;cursor:pointer;z-index:5;';
            var inner = '';
            if (st === 'rect')
                inner = '<rect x="'+(sw/2)+'" y="'+(sw/2)+'" width="'+Math.max(1,w-sw)+'" height="'+Math.max(1,h-sw)+'" rx="2" fill="none" stroke="'+col+'" stroke-width="'+sw+'"/>';
            else if (st === 'ellipse')
                inner = '<ellipse cx="'+(w/2)+'" cy="'+(h/2)+'" rx="'+Math.max(1,w/2-sw/2)+'" ry="'+Math.max(1,h/2-sw/2)+'" fill="none" stroke="'+col+'" stroke-width="'+sw+'"/>';
            el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="'+w+'" height="'+h+'" style="overflow:visible;display:block;pointer-events:none">'+inner+'</svg>';
        }
        attachClick(el, a); annotLayer.appendChild(el);
    }

    function rSticky(a, s) {
        if (!a.rect) return;
        var sz   = getStickySize();
        var note = document.createElement('div');
        note.className  = 'rpv-ro-sticky';
        note.dataset.color = a.color || 'yellow';
        /* Posisi dari koordinat PDF (sama dengan reviewer) */
        note.style.left = (a.rect.x * s) + 'px';
        note.style.top  = (a.rect.y * s) + 'px';
        /* Override ukuran sesuai breakpoint */
        note.style.width   = sz.w + 'px';
        note.style.fontSize = sz.fs + 'px';

        note.innerHTML = '<div class="rpv-ro-sticky-header"><span style="font-size:12px;">📌</span></div>'
            + '<div class="rpv-ro-sticky-body">'
            + esc(a.comment || '')
            + '</div>';

        note.addEventListener('click', function (ev) { ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY); });
        note.addEventListener('touchend', function (ev) {
            ev.stopPropagation();
            var t = ev.changedTouches[0];
            showTip(a, t.clientX, t.clientY);
        }, { passive: true });

        stage.appendChild(note);
    }

    /* ══════════════════════════════════════════════════════
       DRAW ANOTASI KE CANVAS (export PDF)
       Sticky: ukuran menggunakan scale yang sama dengan koordinat PDF
       sehingga posisi dan ukuran konsisten antara preview & export
    ══════════════════════════════════════════════════════ */
    function drawAnnotOnCanvas(c, a, s) {
        if (!a.rect && a.type !== 'freehand') return;
        c.save();
        var col = hex(a.color);

        if (a.type === 'highlight' || a.type === 'comment') {
            if (!a.rect) { c.restore(); return; }
            c.globalAlpha = .38; c.fillStyle = col;
            c.fillRect(a.rect.x*s, a.rect.y*s, a.rect.w*s, a.rect.h*s);
            if (a.type === 'comment' && a.comment) {
                c.globalAlpha = 1; c.fillStyle = '#60A5FA';
                c.beginPath();
                c.arc((a.rect.x+a.rect.w)*s - 4, a.rect.y*s + 4, 4, 0, Math.PI*2);
                c.fill();
            }
        } else if (a.type === 'underline') {
            if (!a.rect) { c.restore(); return; }
            c.globalAlpha = .9; c.fillStyle = col;
            c.fillRect(a.rect.x*s, (a.rect.y+a.rect.h)*s - 1, a.rect.w*s, Math.max(1.5, 2*s));
        } else if (a.type === 'strikethrough') {
            if (!a.rect) { c.restore(); return; }
            c.globalAlpha = .9; c.fillStyle = col;
            var st2 = Math.max(1.5, 2*s);
            c.fillRect(a.rect.x*s, a.rect.y*s + a.rect.h*s*0.62 - st2/2, a.rect.w*s, st2);
        } else if (a.type === 'freehand') {
            if (!a.path_points || !a.path_points.length) { c.restore(); return; }
            c.globalAlpha = .92; c.strokeStyle = col;
            c.lineWidth = (a.stroke_width||2)*s; c.lineCap = 'round'; c.lineJoin = 'round';
            c.beginPath(); c.moveTo(a.path_points[0][0]*s, a.path_points[0][1]*s);
            for (var i = 1; i < a.path_points.length; i++)
                c.lineTo(a.path_points[i][0]*s, a.path_points[i][1]*s);
            c.stroke();
        } else if (a.type === 'shape') {
            if (!a.rect) { c.restore(); return; }
            var sw = (a.stroke_width||2)*s, stype = a.shape_type||'rect';
            c.globalAlpha = .85; c.strokeStyle = col; c.lineWidth = sw;
            c.lineCap = 'round'; c.lineJoin = 'round';
            if (stype === 'rect') {
                c.strokeRect(a.rect.x*s+sw/2, a.rect.y*s+sw/2,
                    Math.max(1,a.rect.w*s-sw), Math.max(1,a.rect.h*s-sw));
            } else if (stype === 'ellipse') {
                c.beginPath();
                c.ellipse((a.rect.x+a.rect.w/2)*s, (a.rect.y+a.rect.h/2)*s,
                    Math.max(1,a.rect.w*s/2-sw/2), Math.max(1,a.rect.h*s/2-sw/2),
                    0, 0, Math.PI*2); c.stroke();
            } else if (stype === 'line') {
                var lx1=a.arrow_x1!=null?a.arrow_x1*s:a.rect.x*s;
                var ly1=a.arrow_y1!=null?a.arrow_y1*s:(a.rect.y+a.rect.h/2)*s;
                var lx2=a.arrow_x2!=null?a.arrow_x2*s:(a.rect.x+a.rect.w)*s;
                var ly2=a.arrow_y2!=null?a.arrow_y2*s:(a.rect.y+a.rect.h/2)*s;
                c.beginPath(); c.moveTo(lx1,ly1); c.lineTo(lx2,ly2); c.stroke();
            } else if (stype === 'arrow') {
                var ax1=a.arrow_x1!=null?a.arrow_x1*s:a.rect.x*s;
                var ay1=a.arrow_y1!=null?a.arrow_y1*s:(a.rect.y+a.rect.h/2)*s;
                var ax2=a.arrow_x2!=null?a.arrow_x2*s:(a.rect.x+a.rect.w)*s;
                var ay2=a.arrow_y2!=null?a.arrow_y2*s:(a.rect.y+a.rect.h/2)*s;
                var adx=ax2-ax1, ady=ay2-ay1, alen=Math.sqrt(adx*adx+ady*ady);
                if (alen < 2) { c.restore(); return; }
                var headLen=Math.min(alen*.35,Math.max(10,sw*5)), aang=Math.atan2(ady,adx);
                c.beginPath(); c.moveTo(ax1,ay1); c.lineTo(ax2,ay2); c.stroke();
                c.beginPath();
                c.moveTo(ax2-headLen*Math.cos(aang-Math.PI/6), ay2-headLen*Math.sin(aang-Math.PI/6));
                c.lineTo(ax2,ay2);
                c.lineTo(ax2-headLen*Math.cos(aang+Math.PI/6), ay2-headLen*Math.sin(aang+Math.PI/6));
                c.stroke();
            }
        } else if (a.type === 'sticky') {
            if (!a.rect || !a.comment) { c.restore(); return; }

            /*
             * KUNCI KONSISTENSI:
             * Di layar, sticky note memakai ukuran CSS tetap (getStickySize().w px)
             * yang TIDAK di-scale bersama PDF.
             * Di export (canvas), kita harus meniru ukuran yang sama secara
             * proporsional terhadap scale export.
             *
             * Screen scale = baseScale * zoomFactor
             * Export scale = s (parameter fungsi ini, biasanya 2.0)
             * Rasio = s / (baseScale * zoomFactor)
             *
             * Ukuran sticky di export = stickyPxScreen * rasio
             */
            var screenS  = baseScale * zoomFactor;
            var ratio    = s / screenS;
            var sz       = getStickySize();

            /* Ukuran sticky di layar (px) — dari CSS */
            var sW_screen = sz.w;
            var sH_screen = Math.max(sz.h, 70);

            /* Ukuran di canvas export */
            var sW = sW_screen * ratio;
            var sH = sH_screen * ratio;
            var sx = a.rect.x * s;
            var sy = a.rect.y * s;

            /* Font size di export = font size layar * ratio */
            var fs       = sz.fs * ratio;
            var headerH  = sz.headerH * ratio;
            var padX     = sz.pad * ratio;

            var bgMap = {
                yellow:'#FEF9C3', green:'#DCFCE7', red:'#FEE2E2', blue:'#DBEAFE',
                orange:'#FFEDD5', pink:'#FCE7F3', purple:'#EDE9FE', cyan:'#CFFAFE',
                black:'#1F2937',  white:'#F9FAFB'
            };
            var colBorder = {
                yellow:'#EAB308', green:'#22C55E', red:'#EF4444', blue:'#3B82F6',
                orange:'#F97316', pink:'#EC4899', purple:'#8B5CF6', cyan:'#06B6D4',
                black:'#374151',  white:'#D1D5DB'
            };

            /* Background */
            c.globalAlpha = .92;
            c.fillStyle   = bgMap[a.color] || '#FEF9C3';
            c.beginPath();
            if (c.roundRect) c.roundRect(sx, sy, sW, sH, 6 * ratio);
            else c.rect(sx, sy, sW, sH);
            c.fill();

            /* Border */
            c.globalAlpha = 1;
            c.strokeStyle = colBorder[a.color] || '#EAB308';
            c.lineWidth   = Math.max(1, 1.5 * ratio);
            c.beginPath();
            if (c.roundRect) c.roundRect(sx, sy, sW, sH, 6 * ratio);
            else c.rect(sx, sy, sW, sH);
            c.stroke();

            /* Header */
            c.globalAlpha = .25;
            c.fillStyle   = colBorder[a.color] || '#EAB308';
            c.beginPath();
            if (c.roundRect) c.roundRect(sx, sy, sW, headerH, [6*ratio, 6*ratio, 0, 0]);
            else c.rect(sx, sy, sW, headerH);
            c.fill();

            /* Icon */
            c.globalAlpha = 1;
            c.font        = Math.round(fs * 1.1) + 'px serif';
            c.fillText('📌', sx + padX * 0.5, sy + headerH - ratio * 3);

            /* Teks komentar — ukuran SAMA dengan di layar */
            c.globalAlpha = 1;
            c.fillStyle   = a.color === 'black' ? '#E5E7EB' : '#1F2937';
            c.font        = '500 ' + Math.round(fs) + 'px ui-sans-serif,system-ui,sans-serif';

            var lY    = sy + headerH + fs + ratio * 2;
            var lX    = sx + padX;
            var maxW  = sW - padX * 2;
            var lineH = fs * 1.45;
            var words = a.comment.split(' ');
            var line  = '';

            for (var wi = 0; wi < words.length; wi++) {
                var test = line + words[wi] + ' ';
                if (c.measureText(test).width > maxW && line !== '') {
                    c.fillText(line.trimEnd(), lX, lY);
                    line = words[wi] + ' ';
                    lY  += lineH;
                    if (lY > sy + sH - ratio * 3) {
                        c.fillText('...', lX, lY - lineH + fs);
                        break;
                    }
                } else {
                    line = test;
                }
            }
            if (line.trim() && lY <= sy + sH - ratio * 3) {
                c.fillText(line.trimEnd(), lX, lY);
            }
        }
        c.restore();
    }

    /* ══════════════════════════════════════════════════════
       TOOLTIP
    ══════════════════════════════════════════════════════ */
    function showTip(a, cx, cy) {
        var ic  = { highlight:'✏️', underline:'__', strikethrough:'~~',
                    freehand:'🖊', shape:'⬛', comment:'💬', sticky:'📌' };
        var txt = a.comment
            ? ic[a.type] + ' ' + a.comment.substring(0, 140)
            : a.selected_text
                ? ic[a.type] + ' "' + a.selected_text.substring(0, 80) + '"'
                : ic[a.type] + ' ' + a.type;
        var tipTxt = document.getElementById('rpv-ro-tip-text');
        if (tipTxt) tipTxt.textContent = txt;
        tooltip.classList.add('show');
        var vw = window.innerWidth, vh = window.innerHeight;
        var tw = Math.min(260, vw * 0.9);
        var tl = Math.max(8, Math.min(cx - tw/2, vw - tw - 8));
        var tt = (cy + 150 > vh) ? Math.max(8, cy - 150) : cy + 10;
        tooltip.style.left  = tl + 'px';
        tooltip.style.top   = tt + 'px';
        tooltip.style.width = tw + 'px';
    }

    document.getElementById('rpv-ro-tip-close').addEventListener('click', function () {
        tooltip.classList.remove('show');
    });
    document.addEventListener('click', function (e) {
        if (tooltip && tooltip.classList.contains('show')) {
            if (tooltip.contains(e.target)) return;
            if (e.target.closest && e.target.closest('[data-annot-id],.rpv-ro-sticky')) return;
            tooltip.classList.remove('show');
        }
    });

    /* ══════════════════════════════════════════════════════
       BADGE & PANEL
    ══════════════════════════════════════════════════════ */
    function updateBadge() {
        var n     = annots.length;
        var badge = document.getElementById('rpv-ro-badge');
        if (badge) { badge.textContent = n > 99 ? '99+' : String(n); badge.classList.toggle('show', n > 0); }
    }

    function buildPanel() {
        var list = document.getElementById('rpv-ro-panel-list'); if (!list) return;
        if (!annots.length) {
            list.innerHTML = '<div class="rpv-ro-panel-empty">Belum ada anotasi dari reviewer.</div>';
            return;
        }
        list.innerHTML = '';
        var ic = { highlight:'✏️', underline:'__', strikethrough:'~~',
                   freehand:'🖊', shape:'⬛', comment:'💬', sticky:'📌' };
        annots.slice().sort(function (a, b) { return a.page - b.page || a.id - b.id; })
            .forEach(function (a) {
                var el = document.createElement('div');
                el.className = 'rpv-ro-panel-item';
                el.setAttribute('role', 'button');
                el.setAttribute('tabindex', '0');
                el.setAttribute('aria-label', 'Anotasi halaman ' + a.page);
                el.innerHTML = '<div class="rpv-ro-panel-dot" style="background:'+hex(a.color)+'"></div>'
                    + '<div class="rpv-ro-panel-body">'
                    + '<span class="rpv-ro-panel-type">'+(ic[a.type]||'•')+' '+a.type+'</span>'
                    + '<span class="rpv-ro-panel-pg">Hal.'+a.page+'</span>'
                    + '<div class="rpv-ro-panel-text">'+esc(a.comment||a.selected_text||a.shape_type||'—')+'</div>'
                    + '</div>';
                function goToAnnot() {
                    if (a.page !== pageNum) renderPage(a.page);
                    closePanel();
                }
                el.addEventListener('click', goToAnnot);
                el.addEventListener('keydown', function (e) { if (e.key === 'Enter') goToAnnot(); });
                list.appendChild(el);
            });
    }

    function openPanel() {
        var panel    = document.getElementById('rpv-ro-panel');
        var backdrop = document.getElementById('rpv-ro-panel-backdrop');
        if (panel)    panel.classList.add('open');
        if (backdrop) backdrop.classList.add('show');
        buildPanel();
    }
    function closePanel() {
        var panel    = document.getElementById('rpv-ro-panel');
        var backdrop = document.getElementById('rpv-ro-panel-backdrop');
        if (panel)    panel.classList.remove('open');
        if (backdrop) backdrop.classList.remove('show');
    }

    document.getElementById('rpv-ro-panel-btn').addEventListener('click', function (e) {
        e.stopPropagation();
        var panel = document.getElementById('rpv-ro-panel');
        panel && panel.classList.contains('open') ? closePanel() : openPanel();
    });
    document.getElementById('rpv-ro-panel-close').addEventListener('click', closePanel);
    document.getElementById('rpv-ro-panel-backdrop').addEventListener('click', closePanel);

    /* ══════════════════════════════════════════════════════
       READING MODE
    ══════════════════════════════════════════════════════ */
    function applyMode(mode) {
        if (outerWrap) {
            outerWrap.classList.remove('mode-sepia', 'mode-night');
            if (mode !== 'normal') outerWrap.classList.add('mode-' + mode);
        }
        document.querySelectorAll('.rpv-ro-mode-btn').forEach(function (b) {
            b.classList.toggle('active', b.dataset.roMode === mode);
        });
        try { localStorage.setItem(SK_MODE, mode); } catch(e) {}
    }

    document.querySelectorAll('.rpv-ro-mode-btn').forEach(function (btn) {
        btn.addEventListener('click', function () { applyMode(btn.dataset.roMode); });
    });
    try { var savedMode = localStorage.getItem(SK_MODE); if (savedMode) applyMode(savedMode); } catch(e) {}

    /* ══════════════════════════════════════════════════════
       BOOKMARK
    ══════════════════════════════════════════════════════ */
    function showBookmarkToast(msg) {
        var toast = document.getElementById('rpv-ro-bookmark-toast');
        var msgEl = document.getElementById('rpv-ro-bookmark-msg');
        if (!toast) return;
        if (msgEl) msgEl.textContent = msg;
        toast.classList.add('show');
        setTimeout(function () { toast.classList.remove('show'); }, 2500);
    }
    function getBookmark() {
        try { var v = localStorage.getItem(SK_BOOKMARK); return v ? parseInt(v) : null; } catch(e) { return null; }
    }
    function updateBookmarkBtn() {
        var btn = document.getElementById('rpv-ro-bookmark-btn'); if (!btn) return;
        var bm  = getBookmark();
        btn.style.color = (bm && bm === pageNum) ? '#FF6B18' : '';
        btn.title = (bm && bm === pageNum) ? 'Halaman ini sudah ditandai — klik hapus'
            : (bm ? 'Tandai halaman ini (sekarang: hal.'+bm+')' : 'Tandai halaman ini');
    }

    document.getElementById('rpv-ro-bookmark-btn').addEventListener('click', function () {
        var bm = getBookmark();
        if (bm && bm === pageNum) {
            try { localStorage.removeItem(SK_BOOKMARK); } catch(e) {}
            showBookmarkToast('Tanda baca dihapus');
        } else {
            try { localStorage.setItem(SK_BOOKMARK, pageNum); } catch(e) {}
            showBookmarkToast('Halaman ' + pageNum + ' ditandai ✓');
        }
        updateBookmarkBtn();
    });

    /* ══════════════════════════════════════════════════════
       FULLSCREEN
    ══════════════════════════════════════════════════════ */
    function updateFsBtn() {
        var icon  = document.getElementById('rpv-ro-fs-icon');
        var label = document.getElementById('rpv-ro-fs-label');
        var btn   = document.getElementById('rpv-ro-fs-btn');
        if (!btn) return;
        if (isFullscreen) {
            if (label) label.textContent = ' Keluar';
            if (icon) icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
            btn.title = 'Keluar layar penuh (Esc)';
        } else {
            if (label) label.textContent = ' Fullscreen';
            if (icon) icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>';
            btn.title = 'Layar penuh (F)';
        }
    }

    function enterFS() {
        isFullscreen = true;
        if (outerWrap) outerWrap.classList.add('is-fullscreen');
        document.body.style.overflow = 'hidden';
        updateFsBtn(); needsRecompute = true; renderPage(pageNum);
    }
    function exitFS() {
        isFullscreen = false;
        if (outerWrap) outerWrap.classList.remove('is-fullscreen');
        document.body.style.overflow = '';
        updateFsBtn(); needsRecompute = true; renderPage(pageNum);
    }

    document.getElementById('rpv-ro-fs-btn').addEventListener('click', function () {
        isFullscreen ? exitFS() : enterFS();
    });

    /* ══════════════════════════════════════════════════════
       DOWNLOAD PDF + ANOTASI
    ══════════════════════════════════════════════════════ */
    document.getElementById('rpv-ro-download-btn').addEventListener('click', async function () {
        if (exportBusy) { snack('⏳ Sedang proses...'); return; }
        if (!pdfDoc)    { snack('PDF belum dimuat!'); return; }
        var jsPDFLib = (window.jspdf && window.jspdf.jsPDF) || window.jsPDF;
        if (!jsPDFLib) { snack('⚠️ Library belum siap', '#F59E0B'); return; }

        exportBusy = true;
        if (exportOL) exportOL.classList.add('show');

        try {
            var EXPORT_S = 2.0;
            var offC     = document.createElement('canvas');
            var offCtx   = offC.getContext('2d');
            var pdf      = null;
            var status   = document.getElementById('rpv-ro-export-status');

            for (var p = 1; p <= pdfDoc.numPages; p++) {
                if (status) status.textContent = 'Halaman ' + p + ' / ' + pdfDoc.numPages;

                var pg  = await pdfDoc.getPage(p);
                var vpE = pg.getViewport({ scale: EXPORT_S });
                offC.width  = Math.floor(vpE.width);
                offC.height = Math.floor(vpE.height);
                offCtx.clearRect(0, 0, offC.width, offC.height);
                await pg.render({ canvasContext: offCtx, viewport: vpE }).promise;

                /* Gambar anotasi dengan scale yang sama */
                annots.filter(function (a) { return a.page === p; })
                      .forEach(function (a) { drawAnnotOnCanvas(offCtx, a, EXPORT_S); });

                var wMm = vpE.width  * .264583;
                var hMm = vpE.height * .264583;
                if (!pdf) {
                    pdf = new jsPDFLib({
                        orientation: vpE.width > vpE.height ? 'landscape' : 'portrait',
                        unit: 'mm', format: [wMm, hMm]
                    });
                } else {
                    pdf.addPage([wMm, hMm], vpE.width > vpE.height ? 'landscape' : 'portrait');
                }
                pdf.addImage(offC.toDataURL('image/jpeg', .92), 'JPEG', 0, 0, wMm, hMm, '', 'FAST');
            }

            var fname = (CFG.title || 'naskah').replace(/[^a-z0-9]/gi, '-').toLowerCase();
            pdf.save(fname + '-annotated-' + Date.now() + '.pdf');
            snack('✅ PDF + anotasi berhasil didownload!', '#22c55e');

        } catch (err) {
            console.error('[RPV-RO] download error:', err);
            snack('❌ Gagal: ' + err.message, '#ef4444');
        } finally {
            exportBusy = false;
            if (exportOL) exportOL.classList.remove('show');
        }
    });

    /* ══════════════════════════════════════════════════════
       NAVIGASI & ZOOM
    ══════════════════════════════════════════════════════ */
    function computeBase(page) {
        var cw = wrap ? wrap.clientWidth : 800;
        var nw = page.getViewport({ scale: 1 }).width;
        baseScale      = Math.max(.4, Math.min((cw - 8) / nw, 2.5));
        needsRecompute = false;
    }

    function prevPage() { if (pageNum > 1) renderPage(pageNum - 1); }
    function nextPage() { if (pdfDoc && pageNum < pdfDoc.numPages) renderPage(pageNum + 1); }

    function doZoom(dir) {
        zoomFactor     = dir > 0 ? Math.min(zoomFactor + ZOOM_STEP, ZOOM_MAX) : Math.max(zoomFactor - ZOOM_STEP, ZOOM_MIN);
        needsRecompute = true;
        var zv = document.getElementById('rpv-ro-zoom-val');
        if (zv) zv.textContent = Math.round(zoomFactor * 100) + '%';
        if (pdfDoc) renderPage(pageNum);
    }

    document.getElementById('rpv-ro-prev').addEventListener('click', prevPage);
    document.getElementById('rpv-ro-next').addEventListener('click', nextPage);
    document.getElementById('rpv-ro-zoom-in').addEventListener('click',  function () { doZoom(1);  });
    document.getElementById('rpv-ro-zoom-out').addEventListener('click', function () { doZoom(-1); });
    document.getElementById('rpv-ro-page-input').addEventListener('change', function () {
        var n = parseInt(this.value);
        if (pdfDoc && n >= 1 && n <= pdfDoc.numPages) renderPage(n);
        else this.value = pageNum;
    });

    /* Keyboard */
    document.addEventListener('keydown', function (e) {
        if (['INPUT','TEXTAREA'].includes(e.target.tagName)) return;
        if (e.key === 'ArrowLeft')              prevPage();
        if (e.key === 'ArrowRight')             nextPage();
        if (e.key === '+' || e.key === '=')     doZoom(1);
        if (e.key === '-')                      doZoom(-1);
        if (e.key === 'f' || e.key === 'F')     isFullscreen ? exitFS() : enterFS();
        if (e.key === 'Escape') {
            if (isFullscreen) exitFS();
            else { tooltip.classList.remove('show'); closePanel(); }
        }
    });

    /* Pinch zoom */
    var lpd = 0;
    if (wrap) {
        wrap.addEventListener('touchstart', function (e) {
            if (e.touches.length === 2)
                lpd = Math.hypot(e.touches[0].clientX-e.touches[1].clientX,
                                 e.touches[0].clientY-e.touches[1].clientY);
        }, { passive: true });
        wrap.addEventListener('touchmove', function (e) {
            if (e.touches.length !== 2) return;
            var d = Math.hypot(e.touches[0].clientX-e.touches[1].clientX,
                               e.touches[0].clientY-e.touches[1].clientY);
            if (Math.abs(d-lpd) > 14) { d > lpd ? doZoom(1) : doZoom(-1); lpd = d; }
        }, { passive: true });
    }

    /* Swipe halaman */
    var swX = 0, swY = 0;
    if (wrap) {
        wrap.addEventListener('touchstart', function (e) {
            if (e.touches.length === 1) { swX = e.touches[0].clientX; swY = e.touches[0].clientY; }
        }, { passive: true });
        wrap.addEventListener('touchend', function (e) {
            if (e.changedTouches.length !== 1) return;
            var dx = swX - e.changedTouches[0].clientX;
            var dy = swY - e.changedTouches[0].clientY;
            /* Hanya swipe horizontal yang signifikan */
            if (Math.abs(dx) > Math.abs(dy) * 1.5 && Math.abs(dx) > 50) {
                dx > 0 ? nextPage() : prevPage();
            }
        }, { passive: true });
    }

    /* Resize */
    var resT = null, lastW = wrap ? wrap.clientWidth : 0;
    window.addEventListener('resize', function () {
        var w = wrap ? wrap.clientWidth : 0;
        if (Math.abs(w-lastW) < 15) return; lastW = w;
        clearTimeout(resT);
        resT = setTimeout(function () {
            if (!pdfDoc) return; needsRecompute = true; renderPage(pageNum);
        }, 200);
    });

    /* ══════════════════════════════════════════════════════
       RENDER HALAMAN
    ══════════════════════════════════════════════════════ */
    function renderPage(num) {
        if (num < 1 || (pdfDoc && num > pdfDoc.numPages)) return;
        if (pageRendering) { pendingPage = num; return; }
        pageRendering = true; pageNum = num;
        try { localStorage.setItem(SK_PAGE, num); } catch(e) {}

        pdfDoc.getPage(num).then(async function (page) {
            if (needsRecompute) computeBase(page);
            var cs  = baseScale * zoomFactor;
            var vpC = page.getViewport({ scale: cs });
            var vpR = page.getViewport({ scale: cs * DPR });

            mainCanvas.width  = Math.floor(vpR.width);
            mainCanvas.height = Math.floor(vpR.height);
            mainCanvas.style.width  = Math.floor(vpC.width)  + 'px';
            mainCanvas.style.height = Math.floor(vpC.height) + 'px';
            stage.style.width  = Math.floor(vpC.width)  + 'px';
            stage.style.height = Math.floor(vpC.height) + 'px';

            await page.render({ canvasContext: ctx, viewport: vpR }).promise.catch(function(){});

            pageRendering = false;
            if (pendingPage !== null) { var pp = pendingPage; pendingPage = null; renderPage(pp); return; }

            stage.style.display = 'block';
            if (loadingEl) loadingEl.style.display = 'none';

            var piEl = document.getElementById('rpv-ro-page-input'); if (piEl) piEl.value = num;
            var prev = document.getElementById('rpv-ro-prev'); if (prev) prev.disabled = num <= 1;
            var next = document.getElementById('rpv-ro-next'); if (next) next.disabled = !pdfDoc || num >= pdfDoc.numPages;
            var prog = document.getElementById('rpv-ro-progress');
            if (prog) prog.style.width = (pdfDoc ? num/pdfDoc.numPages*100 : 0) + '%';
            var zv = document.getElementById('rpv-ro-zoom-val');
            if (zv) zv.textContent = Math.round(zoomFactor * 100) + '%';
            if (wrap) wrap.scrollTo({ top: 0, behavior: 'smooth' });

            updateBookmarkBtn();
            doRender();

        }).catch(function (e) {
            console.error('[RPV-RO] render error:', e);
            pageRendering = false;
            if (loadingEl) loadingEl.style.display = 'none';
            stage.style.display = 'block';
        });
    }

    /* ══════════════════════════════════════════════════════
       LOAD PDF
    ══════════════════════════════════════════════════════ */
    stage.style.display = 'none';
    if (loadingEl) { loadingEl.style.display = 'flex'; }

    annots = normalize(CFG.annotations || []);
    updateBadge();

    var task = pdfjsLib.getDocument({ url: CFG.pdfUrl, withCredentials: false, verbosity: 0 });

    task.onProgress = function (data) {
        if (data.total > 0) {
            var pct = Math.min(100, Math.round(data.loaded / data.total * 100));
            if (loadPct) loadPct.textContent = pct + '%';
            if (loadBar) loadBar.style.width  = pct + '%';
            var loadBarWrap = document.getElementById('rpv-ro-load-bar-wrap');
            if (loadBarWrap) loadBarWrap.setAttribute('aria-valuenow', pct);
        }
    };

    task.promise.then(function (doc) {
        pdfDoc = doc;
        if (loadPct) loadPct.textContent = '100%';
        if (loadBar) loadBar.style.width  = '100%';

        var ptEl = document.getElementById('rpv-ro-page-total'); if (ptEl) ptEl.textContent = doc.numPages;
        var piEl = document.getElementById('rpv-ro-page-input'); if (piEl) piEl.max = doc.numPages;

        var startPage = 1;
        try {
            var lastPg = parseInt(localStorage.getItem(SK_PAGE) || '1');
            if (lastPg > 1 && lastPg <= doc.numPages) startPage = lastPg;
        } catch(e) {}

        renderPage(startPage);

        var bm = getBookmark();
        if (bm && bm !== startPage && bm <= doc.numPages) {
            setTimeout(function () {
                snack('🔖 Tanda baca di hal.' + bm, '#60A5FA');
            }, 1500);
        }

    }).catch(function (err) {
        console.error('[RPV-RO] load error:', err);
        if (loadingEl) loadingEl.innerHTML = '<div style="font-size:2rem">⚠️</div>'
            + '<p style="color:#ef4444;font-weight:700;font-size:13px;margin:0;">Gagal memuat PDF</p>'
            + '<p style="color:#6b7280;font-size:11px;margin:.25rem 0;">' + err.message + '</p>'
            + '<button type="button" onclick="window.location.reload()" style="margin-top:.75rem;'
            + 'padding:.4rem .875rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;'
            + 'font-size:12px;font-weight:700;cursor:pointer;">🔄 Muat Ulang</button>';
    });

})();
</script>

@endif