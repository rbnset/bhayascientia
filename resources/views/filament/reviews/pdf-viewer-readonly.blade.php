{{--
resources/views/filament/reviews/pdf-viewer-readonly.blade.php

Dipakai di ViewReview.php via modalContent pada Action "Lihat Anotasi PDF".
Author HANYA bisa scroll/zoom — tidak ada toolbar edit sama sekali.

FIXES v2:
- Guard double-init (window._rpvrActive)
- waitLib retry agar pdfjsLib siap sebelum run
- Loading clamp 100%
- Arrow direction dari path_points
- textLayer span transform-origin fix
- 2 spinner fix (guard + single init)
--}}
@php
$reviewerName = $review->reviewer?->name ?? 'Reviewer';
$pubTitle = $review->publicationVersion?->publication?->title ?? 'Naskah';
$versionNo = $review->publicationVersion?->version_number ?? 1;
$decision = $review->decision;
$decisionLabel = match($decision) {
'accepted' => ['Diterima ✅', '#059669', '#D1FAE5'],
'revision_required' => ['Perlu Revisi ✏️', '#D97706', '#FEF3C7'],
'rejected' => ['Ditolak ❌', '#DC2626', '#FEE2E2'],
default => ['Dalam Review ⏳', '#7C3AED', '#EDE9FE'],
};
$annotCount = \App\Models\PdfAnnotation::where('review_id', $review->id)->count();
@endphp

<style>
    @keyframes rpvr-spin {
        to {
            transform: rotate(360deg);
        }
    }

    #rpvr-canvas-wrap {
        scroll-behavior: smooth;
    }

    #rpvr-text-layer span {
        position: absolute;
        white-space: pre;
        color: transparent;
        line-height: 1;
        transform-origin: 0% 0%;
        cursor: text;
    }

    .rpvr-search-hl {
        position: absolute;
        background: rgba(255, 215, 0, .45);
        border-radius: 2px;
        pointer-events: none;
        z-index: 7;
    }

    .rpvr-search-hl.active-match {
        background: rgba(255, 107, 24, .6);
        outline: 2px solid #FF6B18;
    }

    #rpvr-panel-list::-webkit-scrollbar {
        width: 4px;
    }

    #rpvr-panel-list::-webkit-scrollbar-thumb {
        background: #3d3d3d;
        border-radius: 99px;
    }

    .rpvr-panel-item {
        display: flex;
        align-items: flex-start;
        gap: .5rem;
        padding: .5rem;
        background: #1a1a1a;
        border-radius: 8px;
        cursor: pointer;
        border: 1px solid transparent;
        transition: border-color .15s;
    }

    .rpvr-panel-item:hover {
        border-color: #3d3d3d;
        background: #1f1f1f;
    }

    .rpvr-panel-item.active-item {
        border-color: #FF6B18;
        background: rgba(255, 107, 24, .08);
    }

    .rpvr-sticky-note {
        position: absolute;
        z-index: 9;
        min-width: 150px;
        max-width: 210px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, .4);
        pointer-events: auto;
        cursor: pointer;
    }

    .rpvr-sticky-note[data-color="yellow"] {
        background: #FEF9C3;
        border: 1.5px solid #FDE047;
    }

    .rpvr-sticky-note[data-color="green"] {
        background: #DCFCE7;
        border: 1.5px solid #86EFAC;
    }

    .rpvr-sticky-note[data-color="blue"] {
        background: #DBEAFE;
        border: 1.5px solid #93C5FD;
    }

    .rpvr-sticky-note[data-color="orange"] {
        background: #FFEDD5;
        border: 1.5px solid #FDBA74;
    }

    .rpvr-sticky-note[data-color="pink"] {
        background: #FCE7F3;
        border: 1.5px solid #F9A8D4;
    }

    .rpvr-sticky-note[data-color="purple"] {
        background: #EDE9FE;
        border: 1.5px solid #C4B5FD;
    }

    .rpvr-sticky-note[data-color="red"] {
        background: #FEE2E2;
        border: 1.5px solid #FCA5A5;
    }

    .rpvr-sticky-note[data-color="cyan"] {
        background: #CFFAFE;
        border: 1.5px solid #67E8F9;
    }

    .rpvr-sticky-note[data-color="black"] {
        background: #1F2937;
        border: 1.5px solid #374151;
    }

    .rpvr-sticky-note[data-color="white"] {
        background: #F9FAFB;
        border: 1.5px solid #D1D5DB;
    }

    .rpvr-sticky-note .rpvr-sn-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .25rem .4rem;
        background: rgba(0, 0, 0, .1);
        font-size: 11px;
        font-weight: 700;
        color: rgba(0, 0, 0, .7);
    }

    .rpvr-sticky-note[data-color="black"] .rpvr-sn-header {
        color: #9CA3AF;
    }

    .rpvr-sticky-note .rpvr-sn-body {
        padding: .4rem .5rem;
        font-size: 12px;
        color: rgba(0, 0, 0, .85);
        word-break: break-word;
        white-space: pre-wrap;
    }

    .rpvr-sticky-note[data-color="black"] .rpvr-sn-body {
        color: #D1D5DB;
    }
</style>

<div id="rpvr-wrap" style="background:#1A1A1A;border-radius:12px;overflow:hidden;display:flex;flex-direction:column;
            height:85vh;min-height:600px;font-family:ui-sans-serif,system-ui,sans-serif;">

    {{-- ══ META HEADER ══ --}}
    <div style="background:#111;border-bottom:1px solid #2d2d2d;padding:.75rem 1rem;
                display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;flex-shrink:0;">

        {{-- Reviewer --}}
        <div style="display:flex;align-items:center;gap:.5rem;background:#1f1f1f;
                    border:1px solid #3d3d3d;border-radius:8px;padding:.35rem .75rem;">
            <div style="width:28px;height:28px;background:linear-gradient(135deg,#FF6B18,#e55d10);
                        border-radius:50%;display:flex;align-items:center;justify-content:center;
                        font-size:12px;font-weight:700;color:#fff;flex-shrink:0;">
                {{ strtoupper(substr($reviewerName, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:10px;color:#6b7280;line-height:1;">Direview oleh</div>
                <div style="font-size:12px;font-weight:700;color:#fff;line-height:1.3;">{{ $reviewerName }}</div>
            </div>
        </div>

        {{-- Version --}}
        <div style="background:#1f1f1f;border:1px solid #3d3d3d;border-radius:8px;padding:.35rem .75rem;">
            <div style="font-size:10px;color:#6b7280;line-height:1;">Versi</div>
            <div style="font-size:12px;font-weight:700;color:#a78bfa;line-height:1.3;">v{{ $versionNo }}</div>
        </div>

        {{-- Decision --}}
        <div style="background:{{ $decisionLabel[2] }};border:1.5px solid {{ $decisionLabel[1] }};
                    border-radius:8px;padding:.35rem .75rem;">
            <div style="font-size:10px;color:{{ $decisionLabel[1] }};font-weight:700;line-height:1;">Keputusan</div>
            <div style="font-size:12px;font-weight:700;color:{{ $decisionLabel[1] }};line-height:1.3;">
                {{ $decisionLabel[0] }}
            </div>
        </div>

        {{-- Annotation count --}}
        <div style="background:#1f1f1f;border:1px solid #3d3d3d;border-radius:8px;padding:.35rem .75rem;">
            <div style="font-size:10px;color:#6b7280;line-height:1;">Total Anotasi</div>
            <div style="font-size:12px;font-weight:700;color:#FF6B18;line-height:1.3;">
                {{ $annotCount }} catatan
            </div>
        </div>

        {{-- Readonly badge --}}
        <div style="margin-left:auto;display:flex;align-items:center;gap:.4rem;
                    background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.3);
                    border-radius:8px;padding:.35rem .75rem;">
            <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="#60a5fa" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                         -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span style="font-size:11px;font-weight:700;color:#60a5fa;">Mode Lihat Saja</span>
        </div>
    </div>

    {{-- ══ TOOLBAR ══ --}}
    <div id="rpvr-toolbar" style="background:#262626;border-bottom:1px solid #3d3d3d;padding:.4rem .75rem;
                display:flex;align-items:center;gap:.5rem;flex-shrink:0;flex-wrap:wrap;">

        {{-- Page nav --}}
        <div style="display:flex;align-items:center;gap:.35rem;background:#333;border-radius:8px;padding:.3rem .5rem;">
            <button id="rpvr-prev" style="width:28px;height:28px;border-radius:6px;border:none;background:#4d4d4d;
                           color:#fff;cursor:pointer;font-size:14px;">‹</button>
            <input type="number" id="rpvr-page-input" style="width:38px;text-align:center;background:#1a1a1a;border:1.5px solid #4d4d4d;
                           color:#fff;border-radius:6px;font-size:12px;font-weight:700;
                           padding:.2rem .3rem;outline:none;" value="1" min="1">
            <span style="color:#555;font-size:11px;">/</span>
            <span id="rpvr-page-total" style="color:#9ca3af;font-size:12px;font-weight:600;">—</span>
            <button id="rpvr-next" style="width:28px;height:28px;border-radius:6px;border:none;background:#4d4d4d;
                           color:#fff;cursor:pointer;font-size:14px;">›</button>
        </div>

        {{-- Zoom --}}
        <button id="rpvr-zoom-out" style="padding:.3rem .6rem;border-radius:6px;border:none;background:#3d3d3d;
                       color:#d1d5db;cursor:pointer;font-size:14px;font-weight:700;">−</button>
        <span id="rpvr-zoom-val"
            style="color:#d1d5db;font-size:11px;font-weight:700;min-width:38px;text-align:center;">100%</span>
        <button id="rpvr-zoom-in" style="padding:.3rem .6rem;border-radius:6px;border:none;background:#3d3d3d;
                       color:#d1d5db;cursor:pointer;font-size:14px;font-weight:700;">+</button>

        {{-- Search --}}
        <button id="rpvr-search-btn" style="display:flex;align-items:center;gap:.3rem;padding:.3rem .65rem;
                       border-radius:6px;border:none;background:#3d3d3d;color:#d1d5db;
                       cursor:pointer;font-size:11px;font-weight:600;">
            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Cari
        </button>

        {{-- Panel toggle --}}
        <button id="rpvr-panel-btn" style="position:relative;display:flex;align-items:center;gap:.3rem;padding:.3rem .65rem;
                       border-radius:6px;border:none;background:#3d3d3d;color:#d1d5db;
                       cursor:pointer;font-size:11px;font-weight:600;">
            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <line x1="8" y1="6" x2="21" y2="6" stroke-width="2" />
                <line x1="8" y1="12" x2="21" y2="12" stroke-width="2" />
                <line x1="8" y1="18" x2="21" y2="18" stroke-width="2" />
                <circle cx="3" cy="6" r="1.5" fill="currentColor" />
                <circle cx="3" cy="12" r="1.5" fill="currentColor" />
                <circle cx="3" cy="18" r="1.5" fill="currentColor" />
            </svg>
            Daftar Anotasi
            <span id="rpvr-badge" style="display:none;background:#FF6B18;color:#fff;font-size:9px;font-weight:700;
                         min-width:16px;height:16px;border-radius:99px;
                         align-items:center;justify-content:center;padding:0 3px;">0</span>
        </button>

        <div style="flex:1;"></div>
        <span id="rpvr-progress-txt" style="font-size:10px;color:#4b5563;white-space:nowrap;"></span>
    </div>

    {{-- Progress bar --}}
    <div style="height:2px;background:#333;flex-shrink:0;">
        <div id="rpvr-progress-bar" style="height:100%;background:linear-gradient(90deg,#FF6B18,#e55d10);
                    width:0%;transition:width .3s;"></div>
    </div>

    {{-- ══ MAIN AREA ══ --}}
    <div style="flex:1;display:flex;overflow:hidden;position:relative;">

        {{-- Canvas area --}}
        <div id="rpvr-canvas-wrap" style="flex:1;overflow:auto;display:flex;justify-content:center;
                    align-items:flex-start;background:#404040;position:relative;">

            {{-- Loading --}}
            <div id="rpvr-loading" style="position:absolute;inset:0;display:flex;flex-direction:column;
                        align-items:center;justify-content:center;gap:.75rem;
                        background:#1a1a1a;z-index:20;">
                <div style="width:36px;height:36px;border:3px solid #333;border-top-color:#FF6B18;
                            border-radius:50%;animation:rpvr-spin .8s linear infinite;"></div>
                <p style="color:#fff;font-size:13px;font-weight:600;margin:0;">Memuat dokumen...</p>
                <p id="rpvr-load-sub" style="color:#6b7280;font-size:11px;margin:0;">Harap tunggu sebentar</p>
            </div>

            {{-- Stage --}}
            <div id="rpvr-stage"
                style="position:relative;display:none;margin:1rem;box-shadow:0 8px 32px rgba(0,0,0,.5);">
                <canvas id="rpvr-canvas"></canvas>
                <div id="rpvr-text-layer" style="position:absolute;inset:0;overflow:hidden;pointer-events:none;
                               user-select:text;-webkit-user-select:text;"></div>
                <div id="rpvr-annotation-layer"
                    style="position:absolute;inset:0;pointer-events:none;overflow:visible;z-index:5;"></div>
                <canvas id="rpvr-freehand-canvas"
                    style="position:absolute;inset:0;pointer-events:none;z-index:10;touch-action:none;"></canvas>
            </div>
        </div>

        {{-- ══ ANNOTATION PANEL ══ --}}
        <div id="rpvr-panel" style="width:0;overflow:hidden;background:#111;border-left:1px solid #2d2d2d;
                    display:flex;flex-direction:column;transition:width .25s ease;flex-shrink:0;">

            <div style="padding:.75rem;border-bottom:1px solid #2d2d2d;display:flex;
                        align-items:center;justify-content:space-between;flex-shrink:0;">
                <span style="font-size:13px;font-weight:700;color:#fff;">📝 Anotasi Reviewer</span>
                <button id="rpvr-panel-close" style="background:none;border:none;color:#6b7280;cursor:pointer;
                               font-size:16px;line-height:1;padding:2px 4px;">✕</button>
            </div>

            <div style="padding:.5rem .75rem;border-bottom:1px solid #1f1f1f;flex-shrink:0;">
                <label style="font-size:10px;color:#6b7280;display:block;margin-bottom:.25rem;">Filter Halaman</label>
                <select id="rpvr-panel-filter" style="width:100%;background:#1f1f1f;border:1.5px solid #3d3d3d;color:#d1d5db;
                               border-radius:6px;font-size:11px;padding:.25rem .4rem;outline:none;cursor:pointer;">
                    <option value="all">Semua halaman</option>
                    <option value="current">Halaman ini saja</option>
                </select>
            </div>

            <div id="rpvr-panel-list"
                style="flex:1;overflow-y:auto;padding:.4rem;display:flex;flex-direction:column;gap:3px;">
                <div style="text-align:center;color:#4b5563;font-size:12px;padding:1.5rem;">
                    Memuat anotasi...
                </div>
            </div>

            <div id="rpvr-panel-footer" style="padding:.6rem .75rem;border-top:1px solid #2d2d2d;
                        display:flex;gap:.5rem;flex-wrap:wrap;flex-shrink:0;">
                <div style="flex:1;background:#1f1f1f;border-radius:6px;padding:.4rem .5rem;text-align:center;">
                    <div id="rpvr-stat-total" style="font-size:16px;font-weight:700;color:#FF6B18;">0</div>
                    <div style="font-size:9px;color:#6b7280;">Total</div>
                </div>
                <div style="flex:1;background:#1f1f1f;border-radius:6px;padding:.4rem .5rem;text-align:center;">
                    <div id="rpvr-stat-page" style="font-size:16px;font-weight:700;color:#60a5fa;">0</div>
                    <div style="font-size:9px;color:#6b7280;">Di halaman ini</div>
                </div>
                <div style="flex:1;background:#1f1f1f;border-radius:6px;padding:.4rem .5rem;text-align:center;">
                    <div id="rpvr-stat-pages" style="font-size:16px;font-weight:700;color:#4ade80;">0</div>
                    <div style="font-size:9px;color:#6b7280;">Hal. berisi</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ SEARCH OVERLAY ══ --}}
    <div id="rpvr-search" style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);
                display:none;align-items:flex-start;justify-content:center;padding-top:80px;">
        <div style="background:#1a1a1a;border:1.5px solid #3d3d3d;border-radius:14px;
                    padding:.875rem;width:420px;max-width:calc(100vw - 2rem);
                    box-shadow:0 16px 48px rgba(0,0,0,.7);">
            <div style="display:flex;gap:.4rem;">
                <input type="text" id="rpvr-search-input" style="flex:1;background:#2d2d2d;border:1.5px solid #3d3d3d;color:#fff;
                              border-radius:8px;padding:.45rem .7rem;font-size:13px;outline:none;"
                    placeholder="Cari kata atau kalimat...">
                <button id="rpvr-sprev"
                    style="width:32px;height:32px;background:#2d2d2d;border:1px solid #3d3d3d;border-radius:7px;color:#9ca3af;cursor:pointer;font-size:13px;">↑</button>
                <button id="rpvr-snext"
                    style="width:32px;height:32px;background:#2d2d2d;border:1px solid #3d3d3d;border-radius:7px;color:#9ca3af;cursor:pointer;font-size:13px;">↓</button>
                <button id="rpvr-sclose"
                    style="width:32px;height:32px;background:#2d2d2d;border:1px solid #3d3d3d;border-radius:7px;color:#9ca3af;cursor:pointer;font-size:13px;">✕</button>
            </div>
            <div id="rpvr-search-status" style="font-size:11px;color:#6b7280;margin-top:.4rem;">Ketik untuk mencari...
            </div>
            <div id="rpvr-search-results"
                style="margin-top:.5rem;max-height:220px;overflow-y:auto;display:flex;flex-direction:column;gap:2px;">
            </div>
        </div>
    </div>

    {{-- ══ TOOLTIP ══ --}}
    <div id="rpvr-tooltip" style="position:fixed;z-index:9998;background:#1a1a1a;border:1.5px solid #3d3d3d;
                border-radius:10px;padding:.6rem .8rem;min-width:200px;max-width:320px;
                box-shadow:0 8px 24px rgba(0,0,0,.5);display:none;">
        <div id="rpvr-tip-reviewer" style="font-size:10px;color:#FF6B18;font-weight:700;margin-bottom:.25rem;"></div>
        <div id="rpvr-tip-text" style="font-size:12px;color:#d1d5db;word-break:break-word;margin-bottom:.4rem;"></div>
        <button id="rpvr-tip-close" style="padding:.25rem .6rem;background:#2d2d2d;border:1px solid #3d3d3d;
                       color:#9ca3af;border-radius:6px;font-size:11px;cursor:pointer;width:100%;">
            ✕ Tutup
        </button>
    </div>
</div>{{-- /#rpvr-wrap --}}

{{-- ══ CONFIG ══ --}}
<script>
    window.RPVR_CONFIG = {
    pdfUrl   : @json($pdfUrl),
    apiUrl   : @json($apiUrl),
    reviewId : @json($review->id),
    reviewer : @json($reviewerName),
};
</script>

{{-- pdf.js CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" crossorigin="anonymous"></script>

{{-- ══ READONLY VIEWER SCRIPT ══ --}}
<script>
    (function () {
    'use strict';

    /* ── FIX: Guard double-init ───────────────────────────────
       Filament re-render modal → script jalan 2x → 2 spinner
    ─────────────────────────────────────────────────────────── */
    var _gk = '_rpvrA_' + ((window.RPVR_CONFIG && window.RPVR_CONFIG.reviewId) || 'x');
    if (window[_gk]) {
        /* Cek apakah DOM masih ada — jika ya, skip */
        if (document.getElementById('rpvr-stage')) {
            console.log('[RPVR] already running');
            return;
        }
        /* DOM rebuild — reset dan lanjut */
        console.log('[RPVR] DOM rebuilt, re-initializing');
    }
    window[_gk] = true;

    /* ── FIX: Wait for pdfjsLib dengan retry ─────────────────
       Tanpa ini: pdfjsLib belum siap → error → blank forever
    ─────────────────────────────────────────────────────────── */
    var _wt = 0;
    function waitAndRun() {
        if (typeof pdfjsLib === 'undefined') {
            if (_wt++ > 200) { console.error('[RPVR] pdfjsLib timeout'); return; }
            return setTimeout(waitAndRun, 100);
        }
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        pdfjsLib.verbosity = 0;
        main();
    }
    waitAndRun();

    function main() {
        var CFG = window.RPVR_CONFIG;
        if (!CFG || !CFG.pdfUrl) { console.error('[RPVR] config missing'); return; }

        /* ── Colors ── */
        var COLORS = {
            yellow:'#FFD700', green:'#4ADE80', red:'#EF4444', blue:'#60A5FA',
            orange:'#FF6B18', black:'#111111', white:'#FFFFFF',
            pink:'#F472B6',   purple:'#A78BFA', cyan:'#22D3EE'
        };
        function hex(n) { return COLORS[n] || '#FFD700'; }

        /* ── State ── */
        var pdfDoc = null, pageNum = 1, pageRendering = false, pendingPage = null;
        var baseScale = 1.0, zoomFactor = 1.0;
        var ZOOM_MIN=0.5, ZOOM_MAX=4.0, ZOOM_STEP=0.25;
        var DPR = window.devicePixelRatio || 1;
        var annots = [], panelOpen = false, filterMode = 'all';
        var searchResults = [], searchIndex = -1, searchHLs = [], searchQuery = '';
        var searchDebounce = null, activeAnnotId = null;

        /* ── DOM ── */
        var wrap       = document.getElementById('rpvr-canvas-wrap');
        var stage      = document.getElementById('rpvr-stage');
        var canvas     = document.getElementById('rpvr-canvas');
        var ctx        = canvas.getContext('2d');
        var textLayer  = document.getElementById('rpvr-text-layer');
        var annotLayer = document.getElementById('rpvr-annotation-layer');
        var freeCanvas = document.getElementById('rpvr-freehand-canvas');
        var freeCtx    = freeCanvas ? freeCanvas.getContext('2d') : null;
        var loadingEl  = document.getElementById('rpvr-loading');
        var loadSub    = document.getElementById('rpvr-load-sub');
        var tooltip    = document.getElementById('rpvr-tooltip');
        var panel      = document.getElementById('rpvr-panel');

        /* ── Utils ── */
        function esc(s) {
            return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;')
                .replace(/>/g,'&gt;').replace(/\n/g,'<br>');
        }
        function syncFC() {
            if (!freeCanvas) return;
            var w=stage.offsetWidth, h=stage.offsetHeight;
            if (freeCanvas.width!==w||freeCanvas.height!==h){freeCanvas.width=w;freeCanvas.height=h;}
            freeCanvas.style.width=w+'px'; freeCanvas.style.height=h+'px';
        }
        function $id(id) { return document.getElementById(id); }

        /* ── Load annotations ── */
        async function loadAnnotations() {
            try {
                var r = await fetch(CFG.apiUrl, {
                    credentials:'same-origin',
                    headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
                });
                if (!r.ok) throw new Error(r.status);
                var j = await r.json();
                var rows = Array.isArray(j.data) ? j.data : [];
                /* Normalisasi rect dan arrow coords */
                annots = rows.map(function(a) {
                    if (!a.rect && a.rect_x != null) {
                        a.rect = { x:+a.rect_x, y:+a.rect_y, w:+a.rect_w, h:+a.rect_h };
                    }
                    /* Pulihkan arrow/line direction dari path_points */
                    if (a.type==='shape' && (a.shape_type==='arrow'||a.shape_type==='line')) {
                        if (a.arrow_x1==null && Array.isArray(a.path_points) && a.path_points.length>=2) {
                            a.arrow_x1=+a.path_points[0][0]; a.arrow_y1=+a.path_points[0][1];
                            a.arrow_x2=+a.path_points[1][0]; a.arrow_y2=+a.path_points[1][1];
                        }
                    }
                    return a;
                });
                console.log('[RPVR] loaded', annots.length, 'annotations');
                renderAnnotations();
                buildPanel();
                updateBadge();
                updateStats();
            } catch(e) {
                console.error('[RPVR] load annotations:', e);
            }
        }

        /* ── Render annotations ── */
        function renderAnnotations() {
            annotLayer.innerHTML = '';
            annotLayer.style.pointerEvents = 'none';
            syncFC();
            if (freeCtx) freeCtx.clearRect(0,0,freeCanvas.width,freeCanvas.height);
            stage.querySelectorAll('.rpvr-sticky-note').forEach(function(e){e.remove();});
            var scale = baseScale * zoomFactor;
            annots.filter(function(a){return a.page===pageNum;}).forEach(function(a){
                if (a.type==='highlight'||a.type==='comment') rHL(a,scale);
                else if (a.type==='underline')     rUL(a,scale);
                else if (a.type==='strikethrough') rST(a,scale);
                else if (a.type==='freehand')      rFH(a,scale);
                else if (a.type==='shape')         rSH(a,scale);
                else if (a.type==='sticky')        rSticky(a,scale);
            });
            updateStats();
            if (searchResults.length>0&&searchQuery) applySearchHL();
        }

        /* ── Render helpers ── */
        function rHL(a,s) {
            if (!a.rect) return;
            var el=document.createElement('div'), isAct=activeAnnotId==a.id;
            el.dataset.annotId=String(a.id);
            el.style.cssText='position:absolute;left:'+(a.rect.x*s)+'px;top:'+(a.rect.y*s)+'px;width:'+(a.rect.w*s)+'px;height:'+(a.rect.h*s)+'px;background:'+hex(a.color)+';opacity:'+(isAct?.75:.38)+';border-radius:2px;pointer-events:auto;cursor:pointer;z-index:5;outline:'+(isAct?'2px solid #FF6B18':'none')+';transition:opacity .15s;';
            if (a.type==='comment'&&a.comment) {
                var dot=document.createElement('span');
                dot.style.cssText='position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:#60A5FA;border-radius:50%;pointer-events:none;';
                el.appendChild(dot);
            }
            attachClick(el,a); annotLayer.appendChild(el);
        }
        function rUL(a,s) {
            if (!a.rect) return;
            var el=document.createElement('div'); el.dataset.annotId=String(a.id);
            var t=Math.max(1.5,2*s);
            el.style.cssText='position:absolute;left:'+(a.rect.x*s)+'px;top:'+((a.rect.y+a.rect.h)*s-t)+'px;width:'+(a.rect.w*s)+'px;height:'+t+'px;background:'+hex(a.color)+';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';
            attachClick(el,a); annotLayer.appendChild(el);
        }
        function rST(a,s) {
            if (!a.rect) return;
            var el=document.createElement('div'); el.dataset.annotId=String(a.id);
            var t=Math.max(1.5,2*s);
            /* Tengah visual teks Latin ~62% dari tinggi */
            var top=a.rect.y*s+a.rect.h*s*0.62-t/2;
            el.style.cssText='position:absolute;left:'+(a.rect.x*s)+'px;top:'+top+'px;width:'+(a.rect.w*s)+'px;height:'+t+'px;background:'+hex(a.color)+';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';
            attachClick(el,a); annotLayer.appendChild(el);
        }
        function rFH(a,s) {
            if (!a.path_points||!a.path_points.length||!freeCtx) return;
            var pts=a.path_points;
            freeCtx.save(); freeCtx.strokeStyle=hex(a.color); freeCtx.lineWidth=(a.stroke_width||2)*s;
            freeCtx.lineCap='round'; freeCtx.lineJoin='round'; freeCtx.globalAlpha=.92;
            freeCtx.beginPath(); freeCtx.moveTo(pts[0][0]*s,pts[0][1]*s);
            for(var i=1;i<pts.length;i++) freeCtx.lineTo(pts[i][0]*s,pts[i][1]*s);
            freeCtx.stroke(); freeCtx.restore();
            if (a.rect&&(a.rect.w>0||a.rect.h>0)) {
                var hit=document.createElement('div'); hit.dataset.annotId=String(a.id);
                hit.style.cssText='position:absolute;left:'+((a.rect.x-8)*s)+'px;top:'+((a.rect.y-8)*s)+'px;width:'+((a.rect.w+16)*s)+'px;height:'+((a.rect.h+16)*s)+'px;background:transparent;pointer-events:auto;cursor:pointer;z-index:6;';
                attachClick(hit,a); annotLayer.appendChild(hit);
            }
        }
        function rSH(a,s) {
            if (!a.rect) return;
            var col=hex(a.color), sw=Math.max(1,(a.stroke_width||2)*s);
            var st=a.shape_type||'rect';
            var el=document.createElement('div'); el.dataset.annotId=String(a.id);

            if (st==='arrow'||st==='line') {
                /* FIX: gunakan coords asli dari path_points */
                var ax1=a.arrow_x1!=null?a.arrow_x1*s:a.rect.x*s;
                var ay1=a.arrow_y1!=null?a.arrow_y1*s:(a.rect.y+a.rect.h/2)*s;
                var ax2=a.arrow_x2!=null?a.arrow_x2*s:(a.rect.x+a.rect.w)*s;
                var ay2=a.arrow_y2!=null?a.arrow_y2*s:(a.rect.y+a.rect.h/2)*s;
                var bx=Math.min(ax1,ax2)-sw*2, by=Math.min(ay1,ay2)-sw*2;
                var bw=Math.abs(ax2-ax1)+sw*4, bh=Math.abs(ay2-ay1)+sw*4;
                var lx1=ax1-bx, ly1=ay1-by, lx2=ax2-bx, ly2=ay2-by;
                el.style.cssText='position:absolute;left:'+bx+'px;top:'+by+'px;width:'+bw+'px;height:'+bh+'px;pointer-events:auto;cursor:pointer;z-index:5;';
                var svg='';
                if (st==='line') {
                    svg='<line x1="'+lx1+'" y1="'+ly1+'" x2="'+lx2+'" y2="'+ly2+'" stroke="'+col+'" stroke-width="'+sw+'" stroke-linecap="round"/>';
                } else {
                    var dx=lx2-lx1, dy=ly2-ly1, len=Math.sqrt(dx*dx+dy*dy);
                    if (len>1) {
                        var hl=Math.min(len*.35,Math.max(10,sw*5));
                        var ang=Math.atan2(dy,dx);
                        var hx1=lx2-hl*Math.cos(ang-Math.PI/6), hy1=ly2-hl*Math.sin(ang-Math.PI/6);
                        var hx2=lx2-hl*Math.cos(ang+Math.PI/6), hy2=ly2-hl*Math.sin(ang+Math.PI/6);
                        svg='<line x1="'+lx1+'" y1="'+ly1+'" x2="'+lx2+'" y2="'+ly2+'" stroke="'+col+'" stroke-width="'+sw+'" stroke-linecap="round"/>'
                           +'<polyline points="'+hx1+','+hy1+' '+lx2+','+ly2+' '+hx2+','+hy2+'" fill="none" stroke="'+col+'" stroke-width="'+sw+'" stroke-linecap="round" stroke-linejoin="round"/>';
                    }
                }
                el.innerHTML='<svg xmlns="http://www.w3.org/2000/svg" width="'+bw+'" height="'+bh+'" style="overflow:visible;display:block;pointer-events:none">'+svg+'</svg>';
            } else {
                var x=a.rect.x*s, y=a.rect.y*s, w=Math.max(4,a.rect.w*s), h=Math.max(4,a.rect.h*s);
                el.style.cssText='position:absolute;left:'+x+'px;top:'+y+'px;width:'+w+'px;height:'+h+'px;pointer-events:auto;cursor:pointer;z-index:5;';
                var svg='';
                if (st==='rect')
                    svg='<rect x="'+(sw/2)+'" y="'+(sw/2)+'" width="'+Math.max(1,w-sw)+'" height="'+Math.max(1,h-sw)+'" rx="2" fill="none" stroke="'+col+'" stroke-width="'+sw+'"/>';
                else if (st==='ellipse')
                    svg='<ellipse cx="'+(w/2)+'" cy="'+(h/2)+'" rx="'+Math.max(1,w/2-sw/2)+'" ry="'+Math.max(1,h/2-sw/2)+'" fill="none" stroke="'+col+'" stroke-width="'+sw+'"/>';
                el.innerHTML='<svg xmlns="http://www.w3.org/2000/svg" width="'+w+'" height="'+h+'" style="overflow:visible;display:block;pointer-events:none">'+svg+'</svg>';
            }
            attachClick(el,a); annotLayer.appendChild(el);
        }
        function rSticky(a,s) {
            if (!a.rect) return;
            var note=document.createElement('div');
            note.className='rpvr-sticky-note'; note.dataset.annotId=String(a.id);
            note.dataset.color=a.color||'yellow';
            note.style.left=(a.rect.x*s)+'px'; note.style.top=(a.rect.y*s)+'px';
            note.innerHTML='<div class="rpvr-sn-header"><span>📌 '+esc(CFG.reviewer).substring(0,14)+'</span></div>'
                +'<div class="rpvr-sn-body">'+esc(a.comment)+'</div>';
            note.addEventListener('click',function(ev){ev.stopPropagation();showTip(a,ev.clientX,ev.clientY);});
            stage.appendChild(note);
        }

        function attachClick(el,a) {
            el.addEventListener('click',function(ev){ev.stopPropagation();showTip(a,ev.clientX,ev.clientY);});
            el.addEventListener('touchend',function(ev){
                ev.stopPropagation();if(ev.cancelable)ev.preventDefault();
                var t=ev.changedTouches[0];showTip(a,t.clientX,t.clientY);
            },{passive:false});
        }

        /* ── Tooltip ── */
        function showTip(a,cx,cy) {
            var ic={highlight:'✏️',underline:'__',strikethrough:'~~',freehand:'🖊',shape:'⬛',comment:'💬',sticky:'📌'};
            activeAnnotId=a.id;
            var rev=$id('rpvr-tip-reviewer'), txt=$id('rpvr-tip-text');
            if (rev) rev.textContent=(ic[a.type]||'•')+' '+a.type+' · oleh '+CFG.reviewer;
            var msg=a.comment||(a.selected_text?'"'+a.selected_text.substring(0,120)+'"'):('Anotasi '+a.type+' di hal.'+a.page);
            if (txt) txt.textContent=msg;
            tooltip.style.display='block';
            var vw=window.innerWidth,vh=window.innerHeight;
            tooltip.style.left=Math.max(4,Math.min(cx-160,vw-324))+'px';
            tooltip.style.top=((cy+160>vh)?Math.max(4,cy-160):cy+8)+'px';
            document.querySelectorAll('.rpvr-panel-item').forEach(function(el){
                el.classList.toggle('active-item',el.dataset.annotId==a.id);
            });
            renderAnnotations();
        }

        $id('rpvr-tip-close')&&$id('rpvr-tip-close').addEventListener('click',function(){
            tooltip.style.display='none'; activeAnnotId=null; renderAnnotations();
        });
        document.addEventListener('click',function(e){
            if (tooltip.style.display==='block'&&!tooltip.contains(e.target)
                &&!e.target.closest('[data-annot-id],.rpvr-sticky-note')) {
                tooltip.style.display='none'; activeAnnotId=null;
            }
        });

        /* ── Panel ── */
        function togglePanel(open) { panelOpen=open; panel.style.width=open?'280px':'0'; }
        $id('rpvr-panel-btn')&&$id('rpvr-panel-btn').addEventListener('click',function(){togglePanel(!panelOpen);});
        $id('rpvr-panel-close')&&$id('rpvr-panel-close').addEventListener('click',function(){togglePanel(false);});
        $id('rpvr-panel-filter')&&$id('rpvr-panel-filter').addEventListener('change',function(){filterMode=this.value;buildPanel();});

        function buildPanel() {
            var list=$id('rpvr-panel-list'); if (!list)return;
            var filtered=filterMode==='current'?annots.filter(function(a){return a.page===pageNum;}):[].concat(annots);
            if (!filtered.length) {
                list.innerHTML='<div style="text-align:center;color:#4b5563;font-size:12px;padding:1.5rem;">'
                    +(filterMode==='current'?'Tidak ada anotasi di halaman ini.':'Belum ada anotasi.')+'</div>';
                return;
            }
            var ic={highlight:'✏️',underline:'__',strikethrough:'~~',freehand:'🖊',shape:'⬛',comment:'💬',sticky:'📌'};
            list.innerHTML='';
            filtered.slice().sort(function(a,b){return a.page-b.page||a.id-b.id;}).forEach(function(a){
                var el=document.createElement('div'); el.className='rpvr-panel-item'; el.dataset.annotId=String(a.id);
                if (activeAnnotId==a.id) el.classList.add('active-item');
                var txt=a.comment||a.selected_text||a.shape_type||'—';
                el.innerHTML='<div style="width:10px;height:10px;border-radius:50%;background:'+hex(a.color)+';flex-shrink:0;margin-top:3px;"></div>'
                    +'<div style="flex:1;min-width:0;">'
                    +'<div style="display:flex;align-items:center;gap:.3rem;margin-bottom:.15rem;">'
                    +'<span style="font-size:10px;font-weight:700;color:#9ca3af;">'+(ic[a.type]||'•')+' '+a.type+'</span>'
                    +'<span style="font-size:10px;color:#FF6B18;margin-left:auto;">Hal.'+a.page+'</span>'
                    +'</div>'
                    +'<div style="font-size:11px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+esc(txt).substring(0,80)+'</div>'
                    +'</div>';
                el.addEventListener('click',function(){
                    tooltip.style.display='none';
                    if (a.page!==pageNum) { renderPdfPage(a.page).then(function(){activeAnnotId=a.id;renderAnnotations();showTipById(a);}); }
                    else { activeAnnotId=a.id; renderAnnotations(); showTipById(a); }
                });
                list.appendChild(el);
            });
        }

        function showTipById(a) {
            var el=document.querySelector('[data-annot-id="'+a.id+'"]');
            if (el) { var r=el.getBoundingClientRect(); showTip(a,r.left+r.width/2,r.top); el.scrollIntoView({behavior:'smooth',block:'center'}); }
        }
        function updateBadge() {
            var b=$id('rpvr-badge'), n=annots.length;
            if (b){b.textContent=n>99?'99+':String(n);b.style.display=n>0?'flex':'none';}
        }
        function updateStats() {
            var on=annots.filter(function(a){return a.page===pageNum;}).length;
            var pgs=new Set(annots.map(function(a){return a.page;})).size;
            var st=$id('rpvr-stat-total'); if(st)st.textContent=annots.length;
            var sp=$id('rpvr-stat-page'); if(sp)sp.textContent=on;
            var spp=$id('rpvr-stat-pages'); if(spp)spp.textContent=pgs;
        }

        /* ── PDF Render ── */
        async function renderPdfPage(num) {
            if (pageRendering){pendingPage=num;return;}
            pageRendering=true; pageNum=num;
            var page=await pdfDoc.getPage(num);
            if (baseScale===1.0) {
                var cw=wrap.clientWidth||900, nw=page.getViewport({scale:1}).width;
                baseScale=Math.max(0.5,Math.min((cw-32)/nw,2.5));
            }
            var cs=baseScale*zoomFactor, vpCss=page.getViewport({scale:cs}), vpR=page.getViewport({scale:cs*DPR});
            canvas.width=Math.floor(vpR.width); canvas.height=Math.floor(vpR.height);
            canvas.style.width=Math.floor(vpCss.width)+'px'; canvas.style.height=Math.floor(vpCss.height)+'px';
            stage.style.width=Math.floor(vpCss.width)+'px'; stage.style.height=Math.floor(vpCss.height)+'px';
            await page.render({canvasContext:ctx,viewport:vpR}).promise.catch(function(e){console.warn(e);});
            pageRendering=false;
            if (pendingPage!==null){var p=pendingPage;pendingPage=null;await renderPdfPage(p);return;}

            /* Text layer */
            textLayer.innerHTML=''; textLayer.style.width=Math.floor(vpCss.width)+'px'; textLayer.style.height=Math.floor(vpCss.height)+'px';
            var content=await page.getTextContent();
            content.items.forEach(function(item){
                if(!item.str||!item.str.trim())return;
                var tx=pdfjsLib.Util.transform(vpCss.transform,item.transform);
                var fh=Math.sqrt(tx[2]*tx[2]+tx[3]*tx[3]), angle=Math.atan2(tx[1],tx[0]);
                var span=document.createElement('span'); span.textContent=item.str;
                span.style.fontSize=fh+'px'; span.style.left=tx[4]+'px'; span.style.top=(tx[5]-fh)+'px';
                span.style.transformOrigin='0% 0%'; /* FIX: transformOrigin */
                textLayer.appendChild(span);
                var tw=item.width*cs, mw=span.getBoundingClientRect().width;
                var t=angle!==0?'rotate('+(-angle)+'rad)':'';
                if(mw>1&&tw>0) t+=' scaleX('+(tw/mw)+')';
                if(t.trim()) span.style.transform=t.trim();
            });

            /* Update UI */
            stage.style.display='block'; loadingEl.style.display='none';
            var pi=$id('rpvr-page-input'); if(pi)pi.value=num;
            var pr=$id('rpvr-prev'); if(pr)pr.disabled=num<=1;
            var nx=$id('rpvr-next'); if(nx)nx.disabled=!pdfDoc||num>=pdfDoc.numPages;
            var pct=pdfDoc?(num/pdfDoc.numPages*100):0;
            var pb=$id('rpvr-progress-bar'); if(pb)pb.style.width=pct+'%';
            var zv=$id('rpvr-zoom-val'); if(zv)zv.textContent=Math.round(zoomFactor*100)+'%';
            var pt=$id('rpvr-progress-txt'); if(pt)pt.textContent='Hal. '+num+'/'+(pdfDoc?pdfDoc.numPages:'?')+' · '+Math.round(pct)+'%';
            if(wrap)wrap.scrollTo({top:0,behavior:'smooth'});
            syncFC(); renderAnnotations();
            if(panelOpen)buildPanel();
            if(searchQuery)applySearchHL();
        }

        /* ── Navigation ── */
        $id('rpvr-prev')&&$id('rpvr-prev').addEventListener('click',function(){if(pageNum>1){pageNum--;renderPdfPage(pageNum);}});
        $id('rpvr-next')&&$id('rpvr-next').addEventListener('click',function(){if(pdfDoc&&pageNum<pdfDoc.numPages){pageNum++;renderPdfPage(pageNum);}});
        $id('rpvr-page-input')&&$id('rpvr-page-input').addEventListener('change',function(){var n=parseInt(this.value);if(pdfDoc&&n>=1&&n<=pdfDoc.numPages)renderPdfPage(n);else this.value=pageNum;});

        /* ── Zoom ── */
        function doZoom(dir){
            zoomFactor=dir>0?Math.min(zoomFactor+ZOOM_STEP,ZOOM_MAX):Math.max(zoomFactor-ZOOM_STEP,ZOOM_MIN);
            baseScale=1.0;
            if(pdfDoc)pdfDoc.getPage(pageNum).then(function(p){
                var cw=wrap.clientWidth||900,nw=p.getViewport({scale:1}).width;
                baseScale=Math.max(0.5,Math.min((cw-32)/nw,2.5));
                renderPdfPage(pageNum);
            });
        }
        $id('rpvr-zoom-in')&&$id('rpvr-zoom-in').addEventListener('click',function(){doZoom(1);});
        $id('rpvr-zoom-out')&&$id('rpvr-zoom-out').addEventListener('click',function(){doZoom(-1);});

        /* ── Search ── */
        function clearSearchHL(){annotLayer.querySelectorAll('.rpvr-search-hl').forEach(function(e){e.remove();});searchHLs=[];}
        function applySearchHL(){
            clearSearchHL(); if(!searchQuery||!pdfDoc)return;
            var q=searchQuery.toLowerCase(), sr=stage.getBoundingClientRect();
            Array.from(textLayer.querySelectorAll('span')).forEach(function(span){
                if(!span.firstChild)return;
                var text=span.textContent,lower=text.toLowerCase(),idx=lower.indexOf(q);
                while(idx!==-1){
                    try{
                        var range=document.createRange();range.setStart(span.firstChild,idx);range.setEnd(span.firstChild,Math.min(idx+q.length,text.length));
                        Array.from(range.getClientRects()).forEach(function(rect){
                            if(rect.width<1||rect.height<1)return;
                            var el=document.createElement('div');el.className='rpvr-search-hl';
                            el.style.left=(rect.left-sr.left)+'px';el.style.top=(rect.top-sr.top)+'px';
                            el.style.width=rect.width+'px';el.style.height=rect.height+'px';
                            el.style.position='absolute';
                            annotLayer.appendChild(el);searchHLs.push(el);
                        });
                    }catch(_){}
                    idx=lower.indexOf(q,idx+1);
                }
            });
            searchHLs.forEach(function(el,i){el.classList.toggle('active-match',i===searchIndex);});
            if(searchHLs[searchIndex])searchHLs[searchIndex].scrollIntoView({behavior:'smooth',block:'center'});
        }
        async function doSearch(query){
            if(!pdfDoc||!query.trim()){clearSearchHL();searchQuery='';var ss=$id('rpvr-search-status');if(ss)ss.textContent='Ketik untuk mencari...';return;}
            var ss=$id('rpvr-search-status');if(ss)ss.textContent='Mencari...';
            searchResults=[];searchQuery=query;var q=query.toLowerCase();
            for(var p=1;p<=pdfDoc.numPages;p++){var pg=await pdfDoc.getPage(p);var c=await pg.getTextContent();var text=c.items.map(function(i){return i.str;}).join(' ');var lt=text.toLowerCase();var idx=lt.indexOf(q);while(idx!==-1){searchResults.push({page:p,excerpt:text.substring(Math.max(0,idx-35),idx+q.length+50).trim()});idx=lt.indexOf(q,idx+1);}}
            var list=$id('rpvr-search-results');if(list)list.innerHTML='';
            if(!searchResults.length){if(ss)ss.textContent='Tidak ditemukan: "'+query+'"';clearSearchHL();return;}
            if(ss)ss.textContent=searchResults.length+' hasil';
            searchIndex=0;
            var escaped=query.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');
            searchResults.slice(0,40).forEach(function(r,i){
                var el=document.createElement('div');
                el.style.cssText='padding:.35rem .5rem;background:#1f1f1f;border-radius:6px;cursor:pointer;font-size:11px;color:#9ca3af;display:flex;gap:.5rem;align-items:baseline;border:1px solid transparent;margin-bottom:2px;';
                el.innerHTML='<span style="color:#FF6B18;font-weight:700;flex-shrink:0;">Hal.'+r.page+'</span><span>'+esc(r.excerpt).replace(new RegExp(escaped,'gi'),function(m){return'<mark style="background:rgba(255,107,24,.35);color:#fff;border-radius:2px;padding:0 1px;">'+m+'</mark>';})+'</span>';
                el.addEventListener('click',function(){searchIndex=i;if(r.page!==pageNum)renderPdfPage(r.page).then(function(){applySearchHL();});else applySearchHL();});
                if(list)list.appendChild(el);
            });
            if(searchResults[0].page===pageNum)applySearchHL();else renderPdfPage(searchResults[0].page);
        }
        function openSearch(){var ov=$id('rpvr-search');if(ov)ov.style.display='flex';var inp=$id('rpvr-search-input');if(inp)setTimeout(function(){inp.focus();},50);}
        function closeSearch(){var ov=$id('rpvr-search');if(ov)ov.style.display='none';clearSearchHL();searchQuery='';searchResults=[];searchIndex=-1;var i=$id('rpvr-search-input');if(i)i.value='';var rl=$id('rpvr-search-results');if(rl)rl.innerHTML='';var ss=$id('rpvr-search-status');if(ss)ss.textContent='Ketik untuk mencari...';}

        $id('rpvr-search-input')&&$id('rpvr-search-input').addEventListener('input',function(){clearTimeout(searchDebounce);var v=this.value;searchDebounce=setTimeout(function(){doSearch(v);},450);});
        $id('rpvr-sclose')&&$id('rpvr-sclose').addEventListener('click',closeSearch);
        $id('rpvr-snext')&&$id('rpvr-snext').addEventListener('click',function(){if(!searchResults.length)return;searchIndex=(searchIndex+1)%searchResults.length;var r=searchResults[searchIndex];if(r.page!==pageNum)renderPdfPage(r.page).then(function(){applySearchHL();});else applySearchHL();});
        $id('rpvr-sprev')&&$id('rpvr-sprev').addEventListener('click',function(){if(!searchResults.length)return;searchIndex=(searchIndex-1+searchResults.length)%searchResults.length;var r=searchResults[searchIndex];if(r.page!==pageNum)renderPdfPage(r.page).then(function(){applySearchHL();});else applySearchHL();});
        $id('rpvr-search')&&$id('rpvr-search').addEventListener('click',function(e){if(e.target===$id('rpvr-search'))closeSearch();});
        $id('rpvr-search-btn')&&$id('rpvr-search-btn').addEventListener('click',openSearch);

        /* ── Keyboard ── */
        document.addEventListener('keydown',function(e){
            if(['INPUT','TEXTAREA'].includes(e.target.tagName))return;
            if((e.ctrlKey||e.metaKey)&&e.key==='f'){e.preventDefault();openSearch();return;}
            switch(e.key){
                case'ArrowLeft':if(pageNum>1){pageNum--;renderPdfPage(pageNum);}break;
                case'ArrowRight':if(pdfDoc&&pageNum<pdfDoc.numPages){pageNum++;renderPdfPage(pageNum);}break;
                case'+':case'=':doZoom(1);break;case'-':doZoom(-1);break;
                case'Escape':closeSearch();tooltip.style.display='none';break;
            }
        });

        /* ── Resize ── */
        var resT=null,lastW=wrap?wrap.clientWidth:0;
        window.addEventListener('resize',function(){
            var w=wrap?wrap.clientWidth:0;if(Math.abs(w-lastW)<20)return;lastW=w;
            clearTimeout(resT);resT=setTimeout(function(){if(!pdfDoc)return;baseScale=1.0;renderPdfPage(pageNum);},250);
        });
        if(canvas)new MutationObserver(function(){syncFC();}).observe(canvas,{attributes:true,attributeFilter:['width','height']});

        /* ── Load PDF ── */
        var task=pdfjsLib.getDocument({url:CFG.pdfUrl,withCredentials:false,verbosity:0,rangeChunkSize:65536});
        task.onProgress=function(d){
            /* FIX: clamp 100% */
            if(d.total>0&&loadSub)loadSub.textContent='Mengunduh... '+Math.min(100,Math.round(d.loaded/d.total*100))+'%';
        };
        task.promise.then(async function(doc){
            pdfDoc=doc;
            var pt=$id('rpvr-page-total'); if(pt)pt.textContent=doc.numPages;
            var pi=$id('rpvr-page-input'); if(pi)pi.max=doc.numPages;
            await renderPdfPage(1);
            await loadAnnotations();
            console.log('[RPVR] ready, reviewId=',CFG.reviewId);
        }).catch(function(err){
            console.error('[RPVR] load error:',err);
            if(loadingEl)loadingEl.innerHTML='<div style="font-size:2rem">⚠️</div><p style="color:#ef4444;font-weight:700;font-size:13px;">Gagal memuat PDF</p><p style="color:#6b7280;font-size:11px;">'+err.message+'</p><button type="button" onclick="window.location.reload()" style="margin-top:.75rem;padding:.4rem .875rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">🔄 Muat Ulang</button>';
        });
    } /* end main() */

})();
</script>