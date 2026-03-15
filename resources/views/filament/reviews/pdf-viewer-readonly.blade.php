{{--
resources/views/filament/reviews/pdf-viewer-readonly.blade.php

Dipakai di ViewReview.php via modalContent pada Action "Lihat Anotasi PDF".
Author HANYA bisa scroll/zoom — tidak ada toolbar edit sama sekali.

Variabel yang diinjeksi dari ViewReview::getHeaderActions():
$review : Review model (with publicationVersion, reviewer, notes)
$pdfUrl : route('manuscripts.view', $review->publicationVersion)
$apiUrl : url("/api/review-annotations/{$review->id}/readonly")
--}}
@php
$reviewerName = $review->reviewer?->name ?? 'Reviewer';
$pubTitle = $review->publicationVersion?->publication?->title ?? 'Naskah';
$versionNo = $review->publicationVersion?->version_number ?? 1;
$decision = $review->decision;
$decisionLabel = match($decision) {
'accepted' => ['Diterima ✅', '#059669', '#D1FAE5'],
'revision_required' => ['Perlu Revisi ✏️','#D97706', '#FEF3C7'],
'rejected' => ['Ditolak ❌', '#DC2626', '#FEE2E2'],
default => ['Dalam Review ⏳', '#7C3AED', '#EDE9FE'],
};
$annotCount = \App\Models\PdfAnnotation::where('review_id', $review->id)->count();
@endphp

<div id="rpvr-wrap"
    style="background:#1A1A1A; border-radius:12px; overflow:hidden; display:flex; flex-direction:column; height:85vh; min-height:600px; font-family: ui-sans-serif, system-ui, sans-serif;">

    {{-- ══ META HEADER ══ --}}
    <div
        style="background:#111; border-bottom:1px solid #2d2d2d; padding:.75rem 1rem; display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; flex-shrink:0;">

        {{-- Reviewer badge --}}
        <div
            style="display:flex; align-items:center; gap:.5rem; background:#1f1f1f; border:1px solid #3d3d3d; border-radius:8px; padding:.35rem .75rem;">
            <div
                style="width:28px; height:28px; background:linear-gradient(135deg,#FF6B18,#e55d10); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:#fff; flex-shrink:0;">
                {{ strtoupper(substr($reviewerName, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:10px; color:#6b7280; line-height:1;">Direview oleh</div>
                <div style="font-size:12px; font-weight:700; color:#fff; line-height:1.3;">{{ $reviewerName }}</div>
            </div>
        </div>

        {{-- Version badge --}}
        <div style="background:#1f1f1f; border:1px solid #3d3d3d; border-radius:8px; padding:.35rem .75rem;">
            <div style="font-size:10px; color:#6b7280; line-height:1;">Versi</div>
            <div style="font-size:12px; font-weight:700; color:#a78bfa; line-height:1.3;">v{{ $versionNo }}</div>
        </div>

        {{-- Decision badge --}}
        <div
            style="background:{{ $decisionLabel[2] }}; border:1.5px solid {{ $decisionLabel[1] }}; border-radius:8px; padding:.35rem .75rem;">
            <div style="font-size:10px; color:{{ $decisionLabel[1] }}; font-weight:700; line-height:1;">Keputusan</div>
            <div style="font-size:12px; font-weight:700; color:{{ $decisionLabel[1] }}; line-height:1.3;">{{
                $decisionLabel[0] }}</div>
        </div>

        {{-- Annotation count --}}
        <div style="background:#1f1f1f; border:1px solid #3d3d3d; border-radius:8px; padding:.35rem .75rem;">
            <div style="font-size:10px; color:#6b7280; line-height:1;">Total Anotasi</div>
            <div style="font-size:12px; font-weight:700; color:#FF6B18; line-height:1.3;">{{ $annotCount }} catatan
            </div>
        </div>

        {{-- Readonly notice --}}
        <div
            style="margin-left:auto; display:flex; align-items:center; gap:.4rem; background:rgba(96,165,250,.1); border:1px solid rgba(96,165,250,.3); border-radius:8px; padding:.35rem .75rem;">
            <svg style="width:14px;height:14px;color:#60a5fa;flex-shrink:0;" fill="none" stroke="#60a5fa"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span style="font-size:11px; font-weight:700; color:#60a5fa;">Mode Lihat Saja</span>
        </div>
    </div>

    {{-- ══ TOOLBAR PDF ══ --}}
    <div id="rpvr-toolbar"
        style="background:#262626; border-bottom:1px solid #3d3d3d; padding:.4rem .75rem; display:flex; align-items:center; gap:.5rem; flex-shrink:0; flex-wrap:wrap;">

        {{-- Page nav --}}
        <div
            style="display:flex; align-items:center; gap:.35rem; background:#333; border-radius:8px; padding:.3rem .5rem;">
            <button id="rpvr-prev"
                style="width:28px;height:28px;border-radius:6px;border:none;background:#4d4d4d;color:#fff;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;"
                title="Halaman sebelumnya (←)">
                ‹
            </button>
            <input type="number" id="rpvr-page-input"
                style="width:38px;text-align:center;background:#1a1a1a;border:1.5px solid #4d4d4d;color:#fff;border-radius:6px;font-size:12px;font-weight:700;padding:.2rem .3rem;outline:none;"
                value="1" min="1">
            <span style="color:#555;font-size:11px;">/</span>
            <span id="rpvr-page-total" style="color:#9ca3af;font-size:12px;font-weight:600;">—</span>
            <button id="rpvr-next"
                style="width:28px;height:28px;border-radius:6px;border:none;background:#4d4d4d;color:#fff;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;"
                title="Halaman berikutnya (→)">
                ›
            </button>
        </div>

        {{-- Zoom --}}
        <button id="rpvr-zoom-out"
            style="padding:.3rem .6rem;border-radius:6px;border:none;background:#3d3d3d;color:#d1d5db;cursor:pointer;font-size:14px;font-weight:700;">
            −
        </button>
        <span id="rpvr-zoom-val"
            style="color:#d1d5db;font-size:11px;font-weight:700;min-width:38px;text-align:center;">100%</span>
        <button id="rpvr-zoom-in"
            style="padding:.3rem .6rem;border-radius:6px;border:none;background:#3d3d3d;color:#d1d5db;cursor:pointer;font-size:14px;font-weight:700;">
            +
        </button>

        {{-- Search --}}
        <button id="rpvr-search-btn"
            style="display:flex;align-items:center;gap:.3rem;padding:.3rem .65rem;border-radius:6px;border:none;background:#3d3d3d;color:#d1d5db;cursor:pointer;font-size:11px;font-weight:600;">
            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Cari
        </button>

        {{-- Panel toggle --}}
        <button id="rpvr-panel-btn"
            style="position:relative;display:flex;align-items:center;gap:.3rem;padding:.3rem .65rem;border-radius:6px;border:none;background:#3d3d3d;color:#d1d5db;cursor:pointer;font-size:11px;font-weight:600;">
            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <line x1="8" y1="6" x2="21" y2="6" stroke-width="2" />
                <line x1="8" y1="12" x2="21" y2="12" stroke-width="2" />
                <line x1="8" y1="18" x2="21" y2="18" stroke-width="2" />
                <circle cx="3" cy="6" r="1.5" fill="currentColor" />
                <circle cx="3" cy="12" r="1.5" fill="currentColor" />
                <circle cx="3" cy="18" r="1.5" fill="currentColor" />
            </svg>
            Daftar Anotasi
            <span id="rpvr-badge"
                style="display:none;background:#FF6B18;color:#fff;font-size:9px;font-weight:700;min-width:16px;height:16px;border-radius:99px;align-items:center;justify-content:center;padding:0 3px;">
                0
            </span>
        </button>

        {{-- Progress --}}
        <div style="flex:1;"></div>
        <span id="rpvr-progress-txt" style="font-size:10px;color:#4b5563;white-space:nowrap;"></span>
    </div>

    {{-- Progress bar --}}
    <div style="height:2px;background:#333;flex-shrink:0;">
        <div id="rpvr-progress-bar"
            style="height:100%;background:linear-gradient(90deg,#FF6B18,#e55d10);width:0%;transition:width .3s;"></div>
    </div>

    {{-- ══ MAIN AREA (PDF + Panel) ══ --}}
    <div style="flex:1;display:flex;overflow:hidden;position:relative;">

        {{-- PDF Canvas area --}}
        <div id="rpvr-canvas-wrap"
            style="flex:1;overflow:auto;display:flex;justify-content:center;align-items:flex-start;background:#404040;position:relative;">

            {{-- Loading --}}
            <div id="rpvr-loading"
                style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.75rem;background:#1a1a1a;z-index:20;">
                <div
                    style="width:36px;height:36px;border:3px solid #333;border-top-color:#FF6B18;border-radius:50%;animation:rpvr-spin .8s linear infinite;">
                </div>
                <p style="color:#fff;font-size:13px;font-weight:600;margin:0;">Memuat dokumen...</p>
                <p id="rpvr-load-sub" style="color:#6b7280;font-size:11px;margin:0;">Harap tunggu sebentar</p>
            </div>

            {{-- Stage --}}
            <div id="rpvr-stage"
                style="position:relative;display:none;margin:1rem;box-shadow:0 8px 32px rgba(0,0,0,.5);">
                <canvas id="rpvr-canvas"></canvas>
                <div id="rpvr-text-layer"
                    style="position:absolute;inset:0;overflow:hidden;pointer-events:none;user-select:text;-webkit-user-select:text;">
                </div>
                <div id="rpvr-annotation-layer"
                    style="position:absolute;inset:0;pointer-events:none;overflow:visible;z-index:5;">
                </div>
                <canvas id="rpvr-freehand-canvas" style="position:absolute;inset:0;pointer-events:none;z-index:10;">
                </canvas>
            </div>
        </div>

        {{-- ══ ANNOTATION PANEL (slide-in dari kanan) ══ --}}
        <div id="rpvr-panel"
            style="width:0;overflow:hidden;background:#111;border-left:1px solid #2d2d2d;display:flex;flex-direction:column;transition:width .25s ease;flex-shrink:0;">

            <div
                style="padding:.75rem;border-bottom:1px solid #2d2d2d;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <span style="font-size:13px;font-weight:700;color:#fff;">📝 Anotasi Reviewer</span>
                <button id="rpvr-panel-close"
                    style="background:none;border:none;color:#6b7280;cursor:pointer;font-size:16px;line-height:1;padding:2px 4px;">✕</button>
            </div>

            {{-- Filter halaman --}}
            <div style="padding:.5rem .75rem;border-bottom:1px solid #1f1f1f;flex-shrink:0;">
                <label style="font-size:10px;color:#6b7280;display:block;margin-bottom:.25rem;">Filter Halaman</label>
                <select id="rpvr-panel-filter"
                    style="width:100%;background:#1f1f1f;border:1.5px solid #3d3d3d;color:#d1d5db;border-radius:6px;font-size:11px;padding:.25rem .4rem;outline:none;cursor:pointer;">
                    <option value="all">Semua halaman</option>
                    <option value="current">Halaman ini saja</option>
                </select>
            </div>

            <div id="rpvr-panel-list"
                style="flex:1;overflow-y:auto;padding:.4rem;display:flex;flex-direction:column;gap:3px;">
                <div id="rpvr-panel-empty" style="text-align:center;color:#4b5563;font-size:12px;padding:1.5rem;">
                    Memuat anotasi...
                </div>
            </div>

            {{-- Stats footer --}}
            <div id="rpvr-panel-footer"
                style="padding:.6rem .75rem;border-top:1px solid #2d2d2d;display:flex;gap:.5rem;flex-wrap:wrap;flex-shrink:0;">
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
                    <div style="font-size:9px;color:#6b7280;">Halaman berisi</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ SEARCH OVERLAY ══ --}}
    <div id="rpvr-search"
        style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);display:none;align-items:flex-start;justify-content:center;padding-top:80px;">
        <div
            style="background:#1a1a1a;border:1.5px solid #3d3d3d;border-radius:14px;padding:.875rem;width:420px;max-width:calc(100vw - 2rem);box-shadow:0 16px 48px rgba(0,0,0,.7);">
            <div style="display:flex;gap:.4rem;">
                <input type="text" id="rpvr-search-input"
                    style="flex:1;background:#2d2d2d;border:1.5px solid #3d3d3d;color:#fff;border-radius:8px;padding:.45rem .7rem;font-size:13px;outline:none;"
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
    <div id="rpvr-tooltip"
        style="position:fixed;z-index:9998;background:#1a1a1a;border:1.5px solid #3d3d3d;border-radius:10px;padding:.6rem .8rem;min-width:200px;max-width:320px;box-shadow:0 8px 24px rgba(0,0,0,.5);display:none;">
        <div id="rpvr-tip-reviewer" style="font-size:10px;color:#FF6B18;font-weight:700;margin-bottom:.25rem;"></div>
        <div id="rpvr-tip-text" style="font-size:12px;color:#d1d5db;word-break:break-word;margin-bottom:.4rem;"></div>
        <button id="rpvr-tip-close"
            style="padding:.25rem .6rem;background:#2d2d2d;border:1px solid #3d3d3d;color:#9ca3af;border-radius:6px;font-size:11px;cursor:pointer;width:100%;">
            ✕ Tutup
        </button>
    </div>

    {{-- Spinner keyframe --}}
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

        #rpvr-panel-list::-webkit-scrollbar-track {
            background: transparent;
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
            background: #FFD700;
        }

        .rpvr-sticky-note[data-color="green"] {
            background: #4ADE80;
        }

        .rpvr-sticky-note[data-color="orange"] {
            background: #FF6B18;
        }

        .rpvr-sticky-note[data-color="blue"] {
            background: #60A5FA;
        }

        .rpvr-sticky-note[data-color="pink"] {
            background: #F472B6;
        }

        .rpvr-sticky-note[data-color="purple"] {
            background: #A78BFA;
        }

        .rpvr-sticky-note[data-color="red"] {
            background: #FCA5A5;
        }

        .rpvr-sticky-note[data-color="cyan"] {
            background: #22D3EE;
        }

        .rpvr-sticky-note .rpvr-sn-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .25rem .4rem;
            background: rgba(0, 0, 0, .12);
            font-size: 11px;
            font-weight: 700;
            color: rgba(0, 0, 0, .7);
        }

        .rpvr-sticky-note .rpvr-sn-body {
            padding: .4rem .5rem;
            font-size: 12px;
            color: rgba(0, 0, 0, .85);
            word-break: break-word;
            white-space: pre-wrap;
        }

        .rpvr-freetext {
            position: absolute;
            z-index: 8;
            font-family: sans-serif;
            font-weight: 600;
            cursor: pointer;
            white-space: pre-wrap;
            word-break: break-word;
            max-width: 300px;
            line-height: 1.4;
            text-shadow: 0 1px 3px rgba(0, 0, 0, .35);
            user-select: none;
        }
    </style>

    {{-- ══ CONFIG ══ --}}
    <script>
        window.RPVR_CONFIG = {
            pdfUrl   : @json($pdfUrl),
            apiUrl   : @json($apiUrl),
            reviewId : @json($review->id),
            reviewer : @json($reviewerName),
        };
    </script>

    {{-- pdf.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    {{-- ══ READONLY VIEWER SCRIPT ══ --}}
    <script>
        (function () {
        'use strict';

        if (typeof pdfjsLib === 'undefined') { console.error('[RPVR] pdfjsLib not found'); return; }
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        pdfjsLib.verbosity = 0;

        const CFG = window.RPVR_CONFIG;
        if (!CFG?.pdfUrl) { console.error('[RPVR] config missing'); return; }

        /* ── COLORS ──────────────────────────────────────────── */
        const COLORS = {
            yellow:'#FFD700', green:'#4ADE80', red:'#EF4444', blue:'#60A5FA',
            orange:'#FF6B18', black:'#111111', white:'#FFFFFF',
            pink:'#F472B6', purple:'#A78BFA', cyan:'#22D3EE',
        };
        const hex = n => COLORS[n] || '#FFD700';

        /* ── STATE ───────────────────────────────────────────── */
        let pdfDoc = null, pageNum = 1, pageRendering = false, pendingPage = null;
        let baseScale = 1.0, zoomFactor = 1.0;
        const ZOOM_MIN = 0.5, ZOOM_MAX = 4.0, ZOOM_STEP = 0.25;
        const DPR = window.devicePixelRatio || 1;
        let annots = [], panelOpen = false, filterMode = 'all';
        let searchResults = [], searchIndex = -1, searchHighlights = [], currentQuery = '';
        let searchDebounce = null;
        let activeAnnotId = null;

        /* ── DOM ─────────────────────────────────────────────── */
        const wrap      = document.getElementById('rpvr-canvas-wrap');
        const stage     = document.getElementById('rpvr-stage');
        const canvas    = document.getElementById('rpvr-canvas');
        const ctx       = canvas.getContext('2d');
        const textLayer = document.getElementById('rpvr-text-layer');
        const annotLayer= document.getElementById('rpvr-annotation-layer');
        const freeCanvas= document.getElementById('rpvr-freehand-canvas');
        const freeCtx   = freeCanvas?.getContext('2d');
        const loadingEl = document.getElementById('rpvr-loading');
        const loadSub   = document.getElementById('rpvr-load-sub');
        const tooltip   = document.getElementById('rpvr-tooltip');
        const panel     = document.getElementById('rpvr-panel');

        /* ── UTILS ───────────────────────────────────────────── */
        function esc(s) {
            return String(s||'')
                .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                .replace(/\n/g,'<br>');
        }
        function syncFC() {
            if (!freeCanvas) return;
            const w = stage.offsetWidth, h = stage.offsetHeight;
            if (freeCanvas.width !== w || freeCanvas.height !== h) {
                freeCanvas.width = w; freeCanvas.height = h;
            }
            freeCanvas.style.width = w+'px'; freeCanvas.style.height = h+'px';
        }

        /* ── LOAD ANNOTATIONS ────────────────────────────────── */
        async function loadAnnotations() {
            try {
                const r = await fetch(CFG.apiUrl, {
                    credentials: 'same-origin',
                    headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }
                });
                if (!r.ok) throw new Error(r.status);
                const j = await r.json();
                annots = Array.isArray(j.data) ? j.data : [];
                console.log('[RPVR] loaded', annots.length, 'annotations');
                renderAnnotations();
                buildPanel();
                updateBadge();
                updateStats();
            } catch(e) {
                console.error('[RPVR] load annotations:', e);
            }
        }

        /* ── RENDER ANNOTATIONS ──────────────────────────────── */
        function renderAnnotations() {
            annotLayer.innerHTML = '';
            annotLayer.style.pointerEvents = 'none';
            syncFC();
            if (freeCtx) freeCtx.clearRect(0, 0, freeCanvas.width, freeCanvas.height);
            stage.querySelectorAll('.rpvr-sticky-note,.rpvr-freetext').forEach(e=>e.remove());

            const scale = baseScale * zoomFactor;
            annots.filter(a => a.page === pageNum).forEach(a => {
                switch (a.type) {
                    case 'highlight': case 'comment': rHL(a, scale); break;
                    case 'underline':      rUL(a, scale); break;
                    case 'strikethrough':  rST(a, scale); break;
                    case 'freehand':       rFH(a, scale); break;
                    case 'shape':          rSH(a, scale); break;
                    case 'sticky':         rSticky(a, scale); break;
                    case 'text':           rText(a, scale); break;
                }
            });

            updateStats();
            if (searchResults.length > 0 && currentQuery) applySearchHighlights();
        }

        /* ── RENDER HELPERS ──────────────────────────────────── */
        function rHL(a, s) {
            if (!a.rect) return;
            const el = document.createElement('div');
            el.dataset.annotId = String(a.id);
            const isActive = activeAnnotId == a.id;
            el.style.cssText = `position:absolute;left:${a.rect.x*s}px;top:${a.rect.y*s}px;width:${a.rect.w*s}px;height:${a.rect.h*s}px;background:${hex(a.color)};opacity:${isActive?.75:.38};border-radius:2px;pointer-events:auto;cursor:pointer;z-index:5;outline:${isActive?'2px solid #FF6B18':'none'};transition:opacity .15s;`;
            el.title = a.comment ? `💬 ${a.comment}` : (a.selected_text ? `"${a.selected_text.substring(0,60)}"` : a.type);
            if (a.type==='comment'&&a.comment) {
                const dot=document.createElement('span');
                dot.style.cssText='position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:#60A5FA;border-radius:50%;pointer-events:none;';
                el.appendChild(dot);
            }
            attachClick(el, a); annotLayer.appendChild(el);
        }
        function rUL(a, s) {
            if (!a.rect) return;
            const el=document.createElement('div'); el.dataset.annotId=String(a.id);
            const t=Math.max(1.5,2*s);
            el.style.cssText=`position:absolute;left:${a.rect.x*s}px;top:${(a.rect.y+a.rect.h)*s-t}px;width:${a.rect.w*s}px;height:${t}px;background:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;`;
            attachClick(el, a); annotLayer.appendChild(el);
        }
        function rST(a, s) {
            if (!a.rect) return;
            const el=document.createElement('div'); el.dataset.annotId=String(a.id);
            const t=Math.max(1.5,2*s);
            el.style.cssText=`position:absolute;left:${a.rect.x*s}px;top:${(a.rect.y+a.rect.h/2)*s-t/2}px;width:${a.rect.w*s}px;height:${t}px;background:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;`;
            attachClick(el, a); annotLayer.appendChild(el);
        }
        function rFH(a, s) {
            if (!a.path_points?.length||!freeCtx) return;
            const pts=a.path_points;
            freeCtx.save(); freeCtx.strokeStyle=hex(a.color); freeCtx.lineWidth=(a.stroke_width||2)*s;
            freeCtx.lineCap='round'; freeCtx.lineJoin='round'; freeCtx.globalAlpha=.92;
            freeCtx.beginPath(); freeCtx.moveTo(pts[0][0]*s,pts[0][1]*s);
            for(let i=1;i<pts.length;i++) freeCtx.lineTo(pts[i][0]*s,pts[i][1]*s);
            freeCtx.stroke(); freeCtx.restore();
            if (a.rect&&(a.rect.w>0||a.rect.h>0)) {
                const hit=document.createElement('div'); hit.dataset.annotId=String(a.id);
                hit.style.cssText=`position:absolute;left:${(a.rect.x-8)*s}px;top:${(a.rect.y-8)*s}px;width:${(a.rect.w+16)*s}px;height:${(a.rect.h+16)*s}px;background:transparent;pointer-events:auto;cursor:pointer;z-index:6;`;
                attachClick(hit, a); annotLayer.appendChild(hit);
            }
        }
        function rSH(a, s) {
            if (!a.rect) return;
            const x=a.rect.x*s,y=a.rect.y*s,w=Math.max(4,a.rect.w*s),h=Math.max(4,a.rect.h*s);
            const sw=Math.max(1,(a.stroke_width||2)*s),col=hex(a.color);
            const wrap2=document.createElement('div'); wrap2.dataset.annotId=String(a.id);
            wrap2.style.cssText=`position:absolute;left:${x}px;top:${y}px;width:${w}px;height:${h}px;pointer-events:auto;cursor:pointer;z-index:5;`;
            const st=a.shape_type||'rect'; let svg='';
            if(st==='rect') svg=`<rect x="${sw/2}" y="${sw/2}" width="${Math.max(1,w-sw)}" height="${Math.max(1,h-sw)}" rx="2" fill="none" stroke="${col}" stroke-width="${sw}"/>`;
            else if(st==='ellipse') svg=`<ellipse cx="${w/2}" cy="${h/2}" rx="${Math.max(1,w/2-sw/2)}" ry="${Math.max(1,h/2-sw/2)}" fill="none" stroke="${col}" stroke-width="${sw}"/>`;
            else if(st==='arrow'){const hh=Math.max(4,h*.35),hx=Math.max(sw*3,w*.25);svg=`<line x1="${sw}" y1="${h/2}" x2="${w-hx+sw}" y2="${h/2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/><polygon points="${w-sw/2},${h/2} ${w-hx},${h/2-hh} ${w-hx},${h/2+hh}" fill="${col}"/>`;}
            else if(st==='line') svg=`<line x1="${sw}" y1="${h/2}" x2="${w-sw}" y2="${h/2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/>`;
            wrap2.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" style="overflow:visible;display:block;pointer-events:none">${svg}</svg>`;
            attachClick(wrap2, a); annotLayer.appendChild(wrap2);
        }
        function rSticky(a, s) {
            if (!a.rect) return;
            const note=document.createElement('div');
            note.className='rpvr-sticky-note'; note.dataset.annotId=String(a.id); note.dataset.color=a.color||'yellow';
            note.style.left=(a.rect.x*s)+'px'; note.style.top=(a.rect.y*s)+'px';
            note.innerHTML=`<div class="rpvr-sn-header"><span>📌 ${esc(CFG.reviewer).substring(0,12)}</span></div><div class="rpvr-sn-body">${esc(a.comment)}</div>`;
            note.addEventListener('click', ev=>{ ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY); });
            stage.appendChild(note);
        }
        function rText(a, s) {
            if (!a.rect) return;
            const fontSize=Math.max(10,(a.stroke_width||14))*s;
            const el=document.createElement('div');
            el.className='rpvr-freetext'; el.dataset.annotId=String(a.id);
            el.style.cssText=`position:absolute;left:${a.rect.x*s}px;top:${a.rect.y*s}px;font-size:${fontSize}px;line-height:1.4;color:${hex(a.color)};pointer-events:auto;z-index:8;white-space:pre-wrap;word-break:break-word;max-width:${300*s}px;font-family:sans-serif;font-weight:600;text-shadow:0 1px 3px rgba(0,0,0,.35);`;
            el.textContent=a.comment||'';
            el.addEventListener('click', ev=>{ ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY); });
            stage.appendChild(el);
        }

        function attachClick(el, a) {
            el.addEventListener('click', ev=>{ ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY); });
            el.addEventListener('touchend', ev=>{
                ev.stopPropagation(); if(ev.cancelable)ev.preventDefault();
                const t=ev.changedTouches[0]; showTip(a, t.clientX, t.clientY);
            }, {passive:false});
        }

        /* ── TOOLTIP (readonly) ──────────────────────────────── */
        function showTip(a, cx, cy) {
            const ic={highlight:'✏️',underline:'__',strikethrough:'~~',freehand:'🖊',shape:'⬛',comment:'💬',sticky:'📌',text:'🔤'};
            activeAnnotId = a.id;

            const reviewerEl=document.getElementById('rpvr-tip-reviewer');
            const txtEl=document.getElementById('rpvr-tip-text');
            if (reviewerEl) reviewerEl.textContent=`${ic[a.type]||'•'} ${a.type} · oleh ${CFG.reviewer}`;

            let txt='';
            if (a.comment) txt=a.comment;
            else if (a.selected_text) txt=`"${a.selected_text.substring(0,120)}"`;
            else txt=`Anotasi ${a.type} di halaman ${a.page}`;
            if (txtEl) txtEl.textContent=txt;

            tooltip.style.display='block';
            const vw=window.innerWidth,vh=window.innerHeight;
            tooltip.style.left=Math.max(4,Math.min(cx-160,vw-324))+'px';
            tooltip.style.top=((cy+160>vh)?Math.max(4,cy-160):cy+8)+'px';

            // Highlight active item in panel
            document.querySelectorAll('.rpvr-panel-item').forEach(el=>{
                el.classList.toggle('active-item', el.dataset.annotId==a.id);
            });
            renderAnnotations(); // re-render to show highlight outline
        }

        document.getElementById('rpvr-tip-close')?.addEventListener('click', ()=>{
            tooltip.style.display='none'; activeAnnotId=null; renderAnnotations();
        });
        document.addEventListener('click', e=>{
            if (!tooltip.contains(e.target)&&!e.target.closest('[data-annot-id],.rpvr-sticky-note,.rpvr-freetext')) {
                tooltip.style.display='none'; activeAnnotId=null;
            }
        });

        /* ── PANEL ───────────────────────────────────────────── */
        function togglePanel(open) {
            panelOpen = open;
            panel.style.width = open ? '280px' : '0';
        }

        document.getElementById('rpvr-panel-btn')?.addEventListener('click', ()=>togglePanel(!panelOpen));
        document.getElementById('rpvr-panel-close')?.addEventListener('click', ()=>togglePanel(false));

        document.getElementById('rpvr-panel-filter')?.addEventListener('change', function(){
            filterMode = this.value; buildPanel();
        });

        function buildPanel() {
            const list = document.getElementById('rpvr-panel-list'); if (!list) return;
            const filtered = filterMode==='current' ? annots.filter(a=>a.page===pageNum) : [...annots];

            if (!filtered.length) {
                list.innerHTML=`<div id="rpvr-panel-empty" style="text-align:center;color:#4b5563;font-size:12px;padding:1.5rem;">${filterMode==='current'?'Tidak ada anotasi di halaman ini.':'Belum ada anotasi.'}</div>`;
                return;
            }

            const ic={highlight:'✏️',underline:'<u style="text-decoration:underline;">U</u>',strikethrough:'~~',freehand:'🖊',shape:'⬛',comment:'💬',sticky:'📌',text:'🔤'};
            list.innerHTML='';
            [...filtered].sort((a,b)=>a.page-b.page||a.id-b.id).forEach(a=>{
                const el=document.createElement('div');
                el.className='rpvr-panel-item'; el.dataset.annotId=String(a.id);
                if (activeAnnotId==a.id) el.classList.add('active-item');

                const txt = a.comment||a.selected_text||a.shape_type||'—';
                el.innerHTML=`
                    <div style="width:10px;height:10px;border-radius:50%;background:${hex(a.color)};flex-shrink:0;margin-top:3px;"></div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:.3rem;margin-bottom:.15rem;">
                            <span style="font-size:10px;font-weight:700;color:#9ca3af;">${ic[a.type]||'•'} ${a.type}</span>
                            <span style="font-size:10px;color:#FF6B18;margin-left:auto;">Hal.${a.page}</span>
                        </div>
                        <div style="font-size:11px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(txt).substring(0,80)}</div>
                    </div>
                `;
                el.addEventListener('click', ()=>{
                    tooltip.style.display='none';
                    if (a.page!==pageNum) { pageNum=a.page; renderPdfPage(pageNum).then(()=>{ activeAnnotId=a.id; renderAnnotations(); showTipFromId(a); }); }
                    else { activeAnnotId=a.id; renderAnnotations(); showTipFromId(a); }
                });
                list.appendChild(el);
            });
        }

        function showTipFromId(a) {
            const el=document.querySelector(`[data-annot-id="${a.id}"]`);
            if (el) {
                const r=el.getBoundingClientRect();
                showTip(a, r.left+r.width/2, r.top);
                el.scrollIntoView({behavior:'smooth',block:'center'});
            }
        }

        function updateBadge() {
            const badge=document.getElementById('rpvr-badge');
            const n=annots.length;
            if (badge) { badge.textContent=n>99?'99+':String(n); badge.style.display=n>0?'flex':'none'; }
        }

        function updateStats() {
            const onPage=annots.filter(a=>a.page===pageNum).length;
            const pagesWithAnnots=new Set(annots.map(a=>a.page)).size;
            const st=document.getElementById('rpvr-stat-total'); if(st) st.textContent=annots.length;
            const sp=document.getElementById('rpvr-stat-page'); if(sp) sp.textContent=onPage;
            const spp=document.getElementById('rpvr-stat-pages'); if(spp) spp.textContent=pagesWithAnnots;
        }

        /* ── PDF RENDER ──────────────────────────────────────── */
        async function renderPdfPage(num) {
            if (pageRendering) { pendingPage=num; return; }
            pageRendering=true; pageNum=num;

            const page = await pdfDoc.getPage(num);
            if (baseScale===1.0) {
                const cw=wrap.clientWidth||900, nw=page.getViewport({scale:1}).width;
                baseScale=Math.max(0.5,Math.min((cw-32)/nw,2.5));
            }
            const cssScale=baseScale*zoomFactor, renderScale=cssScale*DPR;
            const vpCss=page.getViewport({scale:cssScale}), vpRender=page.getViewport({scale:renderScale});

            canvas.width=Math.floor(vpRender.width); canvas.height=Math.floor(vpRender.height);
            canvas.style.width=Math.floor(vpCss.width)+'px'; canvas.style.height=Math.floor(vpCss.height)+'px';
            stage.style.width=Math.floor(vpCss.width)+'px'; stage.style.height=Math.floor(vpCss.height)+'px';

            await page.render({canvasContext:ctx,viewport:vpRender}).promise.catch(e=>console.warn(e));
            pageRendering=false;
            if (pendingPage!==null) { const p=pendingPage; pendingPage=null; await renderPdfPage(p); return; }

            // Text layer
            textLayer.innerHTML=''; textLayer.style.width=vpCss.width+'px'; textLayer.style.height=vpCss.height+'px';
            const content=await page.getTextContent();
            content.items.forEach(item=>{
                if (!item.str||!item.str.trim()) return;
                const tx=pdfjsLib.Util.transform(vpCss.transform,item.transform);
                const fh=Math.sqrt(tx[2]*tx[2]+tx[3]*tx[3]),angle=Math.atan2(tx[1],tx[0]);
                const span=document.createElement('span'); span.textContent=item.str;
                span.style.fontSize=fh+'px'; span.style.left=tx[4]+'px'; span.style.top=(tx[5]-fh)+'px';
                textLayer.appendChild(span);
                const tw=item.width*vpCss.scale,mw=span.getBoundingClientRect().width;
                let t=angle!==0?`rotate(${-angle}rad)`:'';
                if(mw>1&&tw>0) t+=` scaleX(${tw/mw})`;
                if(t.trim()) span.style.transform=t.trim();
            });

            // Update UI
            stage.style.display='block'; loadingEl.style.display='none';
            const inp=document.getElementById('rpvr-page-input'); if(inp) inp.value=num;
            document.getElementById('rpvr-prev').disabled=num<=1;
            document.getElementById('rpvr-next').disabled=!pdfDoc||num>=pdfDoc.numPages;
            const pct=pdfDoc?(num/pdfDoc.numPages*100):0;
            document.getElementById('rpvr-progress-bar').style.width=pct+'%';
            document.getElementById('rpvr-zoom-val').textContent=Math.round(zoomFactor*100)+'%';
            document.getElementById('rpvr-progress-txt').textContent=`Hal. ${num}/${pdfDoc?.numPages||'?'} · ${Math.round(pct)}%`;
            wrap.scrollTo({top:0,behavior:'smooth'});

            syncFC();
            renderAnnotations();
            if (panelOpen) buildPanel();
            if (currentQuery) applySearchHighlights();
        }

        /* ── NAVIGATION ──────────────────────────────────────── */
        document.getElementById('rpvr-prev')?.addEventListener('click',()=>{if(pageNum>1){pageNum--;renderPdfPage(pageNum);}});
        document.getElementById('rpvr-next')?.addEventListener('click',()=>{if(pdfDoc&&pageNum<pdfDoc.numPages){pageNum++;renderPdfPage(pageNum);}});
        document.getElementById('rpvr-page-input')?.addEventListener('change',function(){
            const n=parseInt(this.value);if(pdfDoc&&n>=1&&n<=pdfDoc.numPages)renderPdfPage(n);else this.value=pageNum;
        });

        /* ── ZOOM ────────────────────────────────────────────── */
        function doZoom(dir){
            zoomFactor=dir>0?Math.min(zoomFactor+ZOOM_STEP,ZOOM_MAX):Math.max(zoomFactor-ZOOM_STEP,ZOOM_MIN);
            baseScale=1.0; if(pdfDoc) pdfDoc.getPage(pageNum).then(p=>{
                const cw=wrap.clientWidth||900,nw=p.getViewport({scale:1}).width;
                baseScale=Math.max(0.5,Math.min((cw-32)/nw,2.5));
                renderPdfPage(pageNum);
            });
        }
        document.getElementById('rpvr-zoom-in')?.addEventListener('click',()=>doZoom(1));
        document.getElementById('rpvr-zoom-out')?.addEventListener('click',()=>doZoom(-1));

        /* ── SEARCH ──────────────────────────────────────────── */
        function openSearch(){document.getElementById('rpvr-search').style.display='flex';document.getElementById('rpvr-search-input')?.focus();}
        function closeSearch(){document.getElementById('rpvr-search').style.display='none';clearSearchHighlights();currentQuery='';searchResults=[];searchIndex=-1;const i=document.getElementById('rpvr-search-input');if(i)i.value='';document.getElementById('rpvr-search-results').innerHTML='';document.getElementById('rpvr-search-status').textContent='Ketik untuk mencari...';}
        function clearSearchHighlights(){annotLayer.querySelectorAll('.rpvr-search-hl').forEach(e=>e.remove());searchHighlights=[];}
        function applySearchHighlights(){
            clearSearchHighlights(); if(!currentQuery||!pdfDoc)return;
            const q=currentQuery.toLowerCase(),sr=stage.getBoundingClientRect();
            Array.from(textLayer.querySelectorAll('span')).forEach(span=>{
                if(!span.firstChild)return;
                const text=span.textContent,lower=text.toLowerCase();let idx=lower.indexOf(q);
                while(idx!==-1){
                    try{
                        const range=document.createRange();range.setStart(span.firstChild,idx);range.setEnd(span.firstChild,Math.min(idx+q.length,text.length));
                        Array.from(range.getClientRects()).forEach(rect=>{
                            if(rect.width<1||rect.height<1)return;
                            const el=document.createElement('div');el.className='rpvr-search-hl';
                            el.style.left=(rect.left-sr.left)+'px';el.style.top=(rect.top-sr.top)+'px';
                            el.style.width=rect.width+'px';el.style.height=rect.height+'px';
                            annotLayer.appendChild(el);searchHighlights.push(el);
                        });
                    }catch(_){}
                    idx=lower.indexOf(q,idx+1);
                }
            });
            searchHighlights.forEach((el,i)=>el.classList.toggle('active-match',i===searchIndex));
            if(searchHighlights[searchIndex])searchHighlights[searchIndex].scrollIntoView({behavior:'smooth',block:'center'});
        }
        async function doSearch(query){
            if(!pdfDoc||!query.trim()){clearSearchHighlights();currentQuery='';document.getElementById('rpvr-search-status').textContent='Ketik untuk mencari...';return;}
            document.getElementById('rpvr-search-status').textContent='Mencari...';
            searchResults=[];currentQuery=query;const q=query.toLowerCase();
            for(let p=1;p<=pdfDoc.numPages;p++){const page=await pdfDoc.getPage(p);const c=await page.getTextContent();const text=c.items.map(i=>i.str).join(' ');const lt=text.toLowerCase();let idx=lt.indexOf(q);while(idx!==-1){searchResults.push({page:p,excerpt:text.substring(Math.max(0,idx-35),idx+q.length+50).trim()});idx=lt.indexOf(q,idx+1);}}
            const list=document.getElementById('rpvr-search-results');list.innerHTML='';
            if(!searchResults.length){document.getElementById('rpvr-search-status').textContent=`Tidak ditemukan: "${query}"`;clearSearchHighlights();return;}
            document.getElementById('rpvr-search-status').textContent=`${searchResults.length} hasil`;
            searchIndex=0;
            searchResults.slice(0,40).forEach((r,i)=>{const el=document.createElement('div');el.style.cssText='padding:.35rem .5rem;background:#1f1f1f;border-radius:6px;cursor:pointer;font-size:11px;color:#9ca3af;display:flex;gap:.5rem;align-items:baseline;border:1px solid transparent;margin-bottom:2px;';el.innerHTML=`<span style="color:#FF6B18;font-weight:700;flex-shrink:0;">Hal.${r.page}</span><span>${esc(r.excerpt).replace(new RegExp(query.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'),'gi'),m=>`<mark style="background:rgba(255,107,24,.35);color:#fff;border-radius:2px;padding:0 1px;">${m}</mark>`)}</span>`;el.addEventListener('click',()=>{searchIndex=i;if(r.page!==pageNum)renderPdfPage(r.page).then(()=>applySearchHighlights());else{applySearchHighlights();}});list.appendChild(el);});
            if(searchResults[0].page===pageNum)applySearchHighlights();else renderPdfPage(searchResults[0].page);
        }
        document.getElementById('rpvr-search-input')?.addEventListener('input',function(){clearTimeout(searchDebounce);searchDebounce=setTimeout(()=>doSearch(this.value),450);});
        document.getElementById('rpvr-sclose')?.addEventListener('click',closeSearch);
        document.getElementById('rpvr-snext')?.addEventListener('click',()=>{if(!searchResults.length)return;searchIndex=(searchIndex+1)%searchResults.length;const r=searchResults[searchIndex];if(r.page!==pageNum)renderPdfPage(r.page).then(()=>applySearchHighlights());else applySearchHighlights();});
        document.getElementById('rpvr-sprev')?.addEventListener('click',()=>{if(!searchResults.length)return;searchIndex=(searchIndex-1+searchResults.length)%searchResults.length;const r=searchResults[searchIndex];if(r.page!==pageNum)renderPdfPage(r.page).then(()=>applySearchHighlights());else applySearchHighlights();});
        document.getElementById('rpvr-search')?.addEventListener('click',e=>{if(e.target===document.getElementById('rpvr-search'))closeSearch();});
        document.getElementById('rpvr-search-btn')?.addEventListener('click',openSearch);

        /* ── KEYBOARD ────────────────────────────────────────── */
        document.addEventListener('keydown', e=>{
            if(['INPUT','TEXTAREA'].includes(e.target.tagName))return;
            if((e.ctrlKey||e.metaKey)&&e.key==='f'){e.preventDefault();openSearch();return;}
            switch(e.key){
                case'ArrowLeft':if(pageNum>1){pageNum--;renderPdfPage(pageNum);}break;
                case'ArrowRight':if(pdfDoc&&pageNum<pdfDoc.numPages){pageNum++;renderPdfPage(pageNum);}break;
                case'+':case'=':doZoom(1);break;case'-':doZoom(-1);break;
                case'Escape':closeSearch();tooltip.style.display='none';break;
            }
        });

        /* ── RESIZE ──────────────────────────────────────────── */
        let resT=null,lastW=wrap.clientWidth;
        window.addEventListener('resize',()=>{
            const w=wrap.clientWidth;if(Math.abs(w-lastW)<20)return;lastW=w;
            clearTimeout(resT);resT=setTimeout(()=>{if(!pdfDoc)return;baseScale=1.0;renderPdfPage(pageNum);},250);
        });

        /* ── LOAD PDF ────────────────────────────────────────── */
        const task = pdfjsLib.getDocument({
            url: CFG.pdfUrl, withCredentials:false, verbosity:0, rangeChunkSize:65536
        });
        task.onProgress = d=>{if(d.total>0&&loadSub)loadSub.textContent=`Mengunduh... ${Math.round(d.loaded/d.total*100)}%`;};
        task.promise.then(async doc=>{
            pdfDoc=doc;
            document.getElementById('rpvr-page-total').textContent=doc.numPages;
            document.getElementById('rpvr-page-input').max=doc.numPages;
            await renderPdfPage(1);
            await loadAnnotations();
            console.log('[RPVR] ready');
        }).catch(err=>{
            console.error('[RPVR] load error:',err);
            loadingEl.innerHTML=`<div style="font-size:2rem">⚠️</div><p style="color:#ef4444;font-weight:700;font-size:13px;">Gagal memuat PDF</p><p style="color:#6b7280;font-size:11px;">${err.message}</p>`;
        });

        // Sync freeCanvas on canvas resize
        if(canvas){new MutationObserver(()=>syncFC()).observe(canvas,{attributes:true,attributeFilter:['width','height']});}

    })();
    </script>
</div>