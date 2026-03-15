{{--
resources/views/filament/reviews/pdf-viewer.blade.php

Diembed di ReviewForm Step 2 via:
View::make('filament.reviews.pdf-viewer')->columnSpanFull()

Depends on:
- public/js/pdf-viewer.js
- public/js/pdf-annotations.js
- public/css/pdf-viewer.css
- Route: manuscripts.view -> /manuscripts/{version}
- Route: api/review-annotations/{reviewId}/...

Cara kerja:
1. Form Filament menyimpan publication_version_id via livewire
2. Blade ini membaca state dari $this (Filament page component)
Di Filament v3, $this di dalam View::make() adalah page-nya langsung,
bukan sub-component. Tidak perlu getLivewire().
3. Semua anotasi dikirim ke ReviewPdfAnnotationController dengan review_id
sehingga terisolasi per-review
--}}

@php
use App\Models\PublicationVersion;
use App\Models\Review;

// Di Filament v3, $this di dalam View::make() sudah merupakan
// page component itu sendiri (EditReview / CreateReview).
// Saat edit: $this->record ada | Saat create: hanya $this->data

// Ambil review record (null saat create baru)
$review = $this->record ?? null;

// Ambil publication_version_id dari form state
$formState = $this->data ?? [];
$versionId = $formState['publication_version_id'] ?? null;
$reviewId = $review?->id ?? null;

$version = null;
$pdfUrl = null;
$publicationTitle = null;
$publicationSlug = null;
$reviewerName = auth()->user()?->name;

if ($versionId) {
$version = PublicationVersion::with('publication.publicationType')
->find($versionId);

if ($version) {
$publicationTitle = $version->publication?->title;
$publicationSlug = $version->publication?->slug;
$pdfUrl = $version->pdf_file_path
? route('manuscripts.view', $version)
: null;
}
}

// API endpoint untuk annotasi berbasis review_id
// Jika review belum disimpan (create baru), JS akan tampilkan warning
$annotApiBase = $reviewId
? url("/api/review-annotations/{$reviewId}")
: null;
@endphp
<div id="review-pdf-viewer-wrap" class="relative w-full overflow-hidden rounded-xl"
    style="background:#1A1A1A; min-height: 700px;" x-data="{
        versionId: @js($versionId),
        reviewId: @js($reviewId),
        pdfReady: false,
    }" x-init="
        $watch('versionId', (val) => {
            if (val) window._reviewViewer?.reloadPdf?.();
        });
    ">

    @if (!$versionId || !$pdfUrl)
    {{-- ══ EMPTY STATE: belum pilih naskah ══ --}}
    <div class="flex flex-col items-center justify-center gap-4 text-center" style="min-height:400px; padding: 2rem;">
        <div style="font-size:3rem">📄</div>
        <p style="color:#fff; font-weight:700; font-size:1rem;">
            Pilih Naskah di Step 1
        </p>
        <p style="color:#6B7280; font-size:.875rem; max-width:340px; line-height:1.5;">
            Kembali ke step sebelumnya dan pilih versi publikasi yang akan direview.
            PDF akan muncul di sini beserta alat anotasi lengkap.
        </p>
        <div style="display:flex; gap:.5rem; flex-wrap:wrap; justify-content:center; margin-top:.5rem;">
            @foreach(['✏️ Highlight','💬 Komentar','📌 Sticky Note','🖊 Pen Bebas','⬛ Shape','🔤 Teks'] as $tool)
            <span
                style="background:#2d2d2d; color:#9CA3AF; font-size:11px; padding:.3rem .65rem; border-radius:99px; border:1px solid #3d3d3d;">{{
                $tool }}</span>
            @endforeach
        </div>
    </div>

    @elseif (!$reviewId)
    {{-- ══ WARNING: review belum disimpan ══ --}}
    <div class="flex flex-col items-center justify-center gap-4 text-center" style="min-height:400px; padding:2rem;">
        <div style="font-size:3rem">⚠️</div>
        <p style="color:#F59E0B; font-weight:700; font-size:1rem;">
            Simpan Draft Dulu
        </p>
        <p style="color:#6B7280; font-size:.875rem; max-width:380px; line-height:1.5;">
            Anotasi membutuhkan ID review. Silakan simpan form ini sebagai draft terlebih dahulu,
            lalu buka kembali untuk mulai memberi anotasi pada PDF.
        </p>
        <p style="color:#4B5563; font-size:11px;">
            PDF: <strong style="color:#9CA3AF;">{{ $publicationTitle }}</strong>
        </p>
    </div>

    @else
    {{-- ══ PDF VIEWER LENGKAP ══ --}}

    {{-- CSS inline agar tidak perlu push ke layout Filament --}}
    <style>
        /* ─── Reset kontainer dalam Filament ─────────────────── */
        #review-pdf-viewer-wrap * {
            box-sizing: border-box;
        }

        /* ─── PDF Controls (toolbar) ─────────────────────────── */
        #rpv-toolbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: #262626;
            border-bottom: 1px solid #3d3d3d;
            padding: .5rem .75rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .rpv-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .3rem;
            padding: .35rem .65rem;
            border-radius: 8px;
            border: 1.5px solid transparent;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all .15s;
            line-height: 1;
            background: #3d3d3d;
            color: #d1d5db;
        }

        .rpv-btn:hover {
            background: #4d4d4d;
            color: #fff;
        }

        .rpv-btn:active {
            transform: scale(.93);
        }

        .rpv-btn:disabled {
            opacity: .35;
            cursor: not-allowed;
        }

        .rpv-btn.primary {
            background: #FF6B18;
            border-color: #FF6B18;
            color: #fff;
        }

        .rpv-btn.primary:hover {
            background: #e55d10;
        }

        /* Page controls */
        .rpv-page-group {
            display: flex;
            align-items: center;
            gap: .35rem;
            background: #333;
            border-radius: 8px;
            padding: .3rem .5rem;
        }

        .rpv-page-input {
            width: 40px;
            text-align: center;
            background: #1a1a1a;
            border: 1.5px solid #4d4d4d;
            color: #fff;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            padding: .2rem .3rem;
            outline: none;
        }

        .rpv-page-input:focus {
            border-color: #FF6B18;
        }

        .rpv-page-sep {
            color: #555;
            font-size: 11px;
        }

        .rpv-page-total {
            color: #9ca3af;
            font-size: 12px;
            font-weight: 600;
        }

        /* Zoom display */
        .rpv-zoom-val {
            color: #d1d5db;
            font-size: 11px;
            font-weight: 700;
            min-width: 38px;
            text-align: center;
        }

        /* Title */
        .rpv-title {
            flex: 1;
            min-width: 0;
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Progress bar */
        .rpv-progress-track {
            height: 2px;
            background: #333;
            position: relative;
        }

        .rpv-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #FF6B18, #e55d10);
            transition: width .3s ease;
            width: 0%;
        }

        /* ─── PDF Canvas Area ──────────────────────────────── */
        #rpv-canvas-wrap {
            overflow: auto;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            background: #404040;
            /* Tinggi viewer — bisa disesuaikan */
            height: calc(100vh - 280px);
            min-height: 500px;
        }

        #rpv-stage {
            position: relative;
            display: inline-block;
            margin: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .5);
        }

        #rpv-stage.freehand-mode {
            cursor: crosshair !important;
        }

        #rpv-stage.shape-mode {
            cursor: crosshair !important;
        }

        #rpv-stage.eraser-mode {
            cursor: none !important;
        }

        #rpv-stage.pan-mode {
            cursor: grab !important;
        }

        #rpv-stage.pan-mode:active {
            cursor: grabbing !important;
        }

        #rpv-stage.select-mode {
            cursor: default !important;
        }

        #rpv-stage.text-tool-mode {
            cursor: text !important;
        }

        #rpv-canvas {
            display: block;
        }

        #rpv-text-layer {
            position: absolute;
            inset: 0;
            pointer-events: none;
            user-select: text;
            -webkit-user-select: text;
            overflow: hidden;
        }

        #rpv-text-layer span {
            position: absolute;
            white-space: pre;
            color: transparent;
            line-height: 1;
            transform-origin: 0% 0%;
        }

        #rpv-annotation-layer {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: visible;
            z-index: 5;
        }

        #rpv-freehand-canvas {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 10;
        }

        /* ─── Loading ─────────────────────────────────────── */
        #rpv-loading {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .75rem;
            background: #1a1a1a;
            z-index: 20;
        }

        #rpv-loading.hidden {
            display: none;
        }

        .rpv-spinner {
            width: 36px;
            height: 36px;
            border: 3px solid #333;
            border-top-color: #FF6B18;
            border-radius: 50%;
            animation: rpv-spin 0.8s linear infinite;
        }

        @keyframes rpv-spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ─── Annotation Bottom Bar ───────────────────────── */
        #rpv-annot-bar {
            position: sticky;
            bottom: 0;
            z-index: 40;
            background: #111;
            border-top: 1px solid #2d2d2d;
            padding: .4rem .6rem;
            padding-bottom: max(.4rem, env(safe-area-inset-bottom, .4rem));
        }

        .rpv-ab-tools {
            display: flex;
            align-items: center;
            gap: 3px;
            overflow-x: auto;
            scrollbar-width: none;
            flex-wrap: nowrap;
        }

        .rpv-ab-tools::-webkit-scrollbar {
            display: none;
        }

        .rpv-ab-sep {
            width: 1px;
            height: 22px;
            background: #2d2d2d;
            margin: 0 3px;
            flex-shrink: 0;
        }

        .rpv-tool {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            border: 1.5px solid transparent;
            background: transparent;
            color: #777;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            transition: all .15s;
            position: relative;
        }

        .rpv-tool:active {
            transform: scale(.88);
        }

        .rpv-tool.active {
            background: rgba(255, 107, 24, .15);
            border-color: #FF6B18;
            color: #FF6B18;
        }

        .rpv-tool[data-tool="eraser"].active {
            background: rgba(239, 68, 68, .15);
            border-color: #ef4444;
            color: #f87171;
        }

        .rpv-tool[data-tool="select"].active {
            background: rgba(96, 165, 250, .15);
            border-color: #60a5fa;
            color: #60a5fa;
        }

        .rpv-tool[data-tool="pan"].active {
            background: rgba(74, 222, 128, .15);
            border-color: #4ade80;
            color: #4ade80;
        }

        .rpv-action {
            width: 32px;
            height: 32px;
            border-radius: 7px;
            border: none;
            background: #1f1f1f;
            color: #777;
            cursor: pointer;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all .15s;
        }

        .rpv-action:hover {
            background: #2d2d2d;
            color: #fff;
        }

        .rpv-action:disabled {
            opacity: .3;
            cursor: not-allowed;
        }

        .rpv-colors {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .rpv-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid transparent;
            cursor: pointer;
            flex-shrink: 0;
            transition: transform .15s, border-color .15s;
        }

        .rpv-color:active {
            transform: scale(.8);
        }

        .rpv-color.selected {
            border-color: #fff;
            transform: scale(1.18);
            box-shadow: 0 0 0 2px rgba(255, 255, 255, .25);
        }

        .rpv-color[data-color="yellow"] {
            background: #FFD700;
        }

        .rpv-color[data-color="green"] {
            background: #4ADE80;
        }

        .rpv-color[data-color="red"] {
            background: #EF4444;
        }

        .rpv-color[data-color="blue"] {
            background: #60A5FA;
        }

        .rpv-color[data-color="orange"] {
            background: #FF6B18;
        }

        .rpv-color[data-color="pink"] {
            background: #F472B6;
        }

        .rpv-color[data-color="purple"] {
            background: #A78BFA;
        }

        .rpv-color[data-color="cyan"] {
            background: #22D3EE;
        }

        .rpv-color[data-color="black"] {
            background: #222;
            border-color: #555;
        }

        .rpv-color[data-color="white"] {
            background: #fff;
            border-color: #555;
        }

        .rpv-sizes {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
        }

        .rpv-size {
            border-radius: 50%;
            background: #555;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .15s;
        }

        .rpv-size.selected {
            background: #FF6B18;
        }

        .rpv-size[data-size="2"] {
            width: 6px;
            height: 6px;
        }

        .rpv-size[data-size="4"] {
            width: 9px;
            height: 9px;
        }

        .rpv-size[data-size="8"] {
            width: 13px;
            height: 13px;
        }

        .rpv-size[data-size="14"] {
            width: 17px;
            height: 17px;
        }

        .rpv-shapes {
            display: none;
            align-items: center;
            gap: 2px;
            flex-shrink: 0;
        }

        .rpv-shapes.show {
            display: flex;
        }

        .rpv-shape {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1.5px solid transparent;
            background: #1f1f1f;
            color: #777;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            flex-shrink: 0;
            transition: all .15s;
        }

        .rpv-shape.active {
            border-color: #FF6B18;
            color: #FF6B18;
            background: rgba(255, 107, 24, .12);
        }

        /* ─── Sync indicator ──────────────────────────────── */
        #rpv-sync {
            position: fixed;
            bottom: 4.5rem;
            right: 1rem;
            z-index: 9999;
            background: #1a1a1a;
            border: 1.5px solid #FF6B18;
            color: #FF6B18;
            border-radius: 99px;
            padding: .3rem .75rem;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: .4rem;
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s;
        }

        #rpv-sync.show {
            opacity: 1;
        }

        .rpv-sync-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            animation: rpv-blink 1s ease-in-out infinite;
        }

        @keyframes rpv-blink {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .3
            }
        }

        /* ─── Tooltip ─────────────────────────────────────── */
        #rpv-tooltip {
            position: fixed;
            z-index: 9998;
            background: #1a1a1a;
            border: 1.5px solid #3d3d3d;
            border-radius: 10px;
            padding: .6rem .8rem;
            min-width: 200px;
            max-width: 300px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .5);
            display: none;
        }

        #rpv-tooltip.show {
            display: block;
        }

        .rpv-tip-text {
            font-size: 12px;
            color: #d1d5db;
            margin-bottom: .4rem;
            word-break: break-word;
        }

        .rpv-tip-actions {
            display: flex;
            gap: .4rem;
        }

        .rpv-tip-del {
            flex: 1;
            padding: .3rem;
            background: rgba(239, 68, 68, .15);
            border: 1px solid #ef4444;
            color: #f87171;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
        }

        .rpv-tip-close {
            padding: .3rem .6rem;
            background: #2d2d2d;
            border: 1px solid #3d3d3d;
            color: #9ca3af;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
        }

        /* ─── Comment / Sticky popup ──────────────────────── */
        .rpv-popup {
            position: fixed;
            z-index: 9997;
            background: #1a1a1a;
            border: 2px solid #FF6B18;
            border-radius: 14px;
            padding: .875rem;
            width: 280px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .6);
            display: none;
        }

        .rpv-popup.show {
            display: block;
        }

        .rpv-popup-title {
            font-size: 12px;
            font-weight: 700;
            color: #FF6B18;
            margin: 0 0 .5rem;
        }

        .rpv-popup textarea {
            width: 100%;
            background: #2d2d2d;
            border: 1.5px solid #3d3d3d;
            color: #fff;
            border-radius: 8px;
            padding: .5rem;
            font-size: 13px;
            resize: none;
            outline: none;
            height: 72px;
            display: block;
            box-sizing: border-box;
        }

        .rpv-popup textarea:focus {
            border-color: #FF6B18;
        }

        .rpv-popup-actions {
            display: flex;
            gap: .4rem;
            margin-top: .5rem;
        }

        .rpv-popup-save {
            flex: 1;
            padding: .5rem;
            background: #FF6B18;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .rpv-popup-cancel {
            padding: .5rem .75rem;
            background: #2d2d2d;
            color: #9ca3af;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
        }

        /* ─── Sticky note ─────────────────────────────────── */
        .rpv-sticky-note {
            position: absolute;
            z-index: 9;
            min-width: 160px;
            max-width: 220px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .4);
            cursor: move;
            user-select: none;
        }

        .rpv-sticky-note[data-color="yellow"] {
            background: #FFD700;
        }

        .rpv-sticky-note[data-color="green"] {
            background: #4ADE80;
        }

        .rpv-sticky-note[data-color="orange"] {
            background: #FF6B18;
        }

        .rpv-sticky-note[data-color="blue"] {
            background: #60A5FA;
        }

        .rpv-sticky-note[data-color="pink"] {
            background: #F472B6;
        }

        .rpv-sticky-note[data-color="purple"] {
            background: #A78BFA;
        }

        .rpv-sn-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .25rem .4rem;
            background: rgba(0, 0, 0, .12);
            font-size: 11px;
            font-weight: 700;
            color: rgba(0, 0, 0, .7);
        }

        .rpv-sn-del {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            color: rgba(0, 0, 0, .5);
            padding: 0 2px;
            line-height: 1;
        }

        .rpv-sn-body {
            padding: .4rem .5rem;
            font-size: 12px;
            color: rgba(0, 0, 0, .85);
            word-break: break-word;
            white-space: pre-wrap;
        }

        /* ─── Freetext ────────────────────────────────────── */
        .rpv-freetext {
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

        /* ─── Search overlay ──────────────────────────────── */
        #rpv-search {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, .5);
            display: none;
            align-items: flex-start;
            justify-content: center;
            padding-top: 80px;
        }

        #rpv-search.show {
            display: flex;
        }

        #rpv-search-box {
            background: #1a1a1a;
            border: 1.5px solid #3d3d3d;
            border-radius: 14px;
            padding: .875rem;
            width: 420px;
            max-width: calc(100vw - 2rem);
            box-shadow: 0 16px 48px rgba(0, 0, 0, .7);
        }

        .rpv-search-row {
            display: flex;
            gap: .4rem;
        }

        #rpv-search-input {
            flex: 1;
            background: #2d2d2d;
            border: 1.5px solid #3d3d3d;
            color: #fff;
            border-radius: 8px;
            padding: .45rem .7rem;
            font-size: 13px;
            outline: none;
        }

        #rpv-search-input:focus {
            border-color: #FF6B18;
        }

        .rpv-snav {
            width: 32px;
            height: 32px;
            background: #2d2d2d;
            border: 1px solid #3d3d3d;
            border-radius: 7px;
            color: #9ca3af;
            cursor: pointer;
            flex-shrink: 0;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rpv-snav:hover {
            background: #3d3d3d;
            color: #fff;
        }

        #rpv-search-status {
            font-size: 11px;
            color: #6b7280;
            margin-top: .4rem;
        }

        #rpv-search-results {
            margin-top: .5rem;
            max-height: 220px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .rpv-sri {
            padding: .35rem .5rem;
            background: #1f1f1f;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            color: #9ca3af;
            display: flex;
            gap: .5rem;
            align-items: baseline;
            border: 1px solid transparent;
        }

        .rpv-sri:hover,
        .rpv-sri.active-sri {
            background: #2d2d2d;
            border-color: #FF6B18;
            color: #fff;
        }

        .rpv-sri .pg {
            color: #FF6B18;
            font-weight: 700;
            flex-shrink: 0;
        }

        .rpv-sri mark {
            background: rgba(255, 107, 24, .35);
            color: #fff;
            border-radius: 2px;
            padding: 0 1px;
        }

        /* ─── Search highlight ────────────────────────────── */
        .rpv-search-hl {
            position: absolute;
            background: rgba(255, 215, 0, .45);
            border-radius: 2px;
            pointer-events: none;
            z-index: 7;
        }

        .rpv-search-hl.active-match {
            background: rgba(255, 107, 24, .6);
            outline: 2px solid #FF6B18;
        }

        /* ─── Panel ───────────────────────────────────────── */
        #rpv-panel {
            position: absolute;
            top: 48px;
            right: 0;
            width: 280px;
            max-height: calc(100% - 96px);
            background: #1a1a1a;
            border: 1.5px solid #3d3d3d;
            border-radius: 12px;
            z-index: 45;
            display: none;
            flex-direction: column;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .5);
            margin: .5rem;
        }

        #rpv-panel.open {
            display: flex;
        }

        .rpv-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .6rem .8rem;
            border-bottom: 1px solid #2d2d2d;
        }

        .rpv-panel-title {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
        }

        .rpv-panel-close {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
        }

        .rpv-panel-list {
            flex: 1;
            overflow-y: auto;
            padding: .4rem;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .rpv-panel-empty {
            text-align: center;
            color: #4b5563;
            font-size: 12px;
            padding: 1.5rem;
        }

        .rpv-panel-item {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem;
            background: #1f1f1f;
            border-radius: 8px;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .rpv-panel-item:hover {
            border-color: #3d3d3d;
        }

        .rpv-panel-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .rpv-panel-body {
            flex: 1;
            min-width: 0;
        }

        .rpv-panel-type {
            font-size: 10px;
            font-weight: 700;
            color: #9ca3af;
        }

        .rpv-panel-pg {
            font-size: 10px;
            color: #FF6B18;
            float: right;
        }

        .rpv-panel-text {
            font-size: 11px;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rpv-panel-del {
            background: none;
            border: none;
            color: #4b5563;
            cursor: pointer;
            font-size: 13px;
            flex-shrink: 0;
            padding: 2px;
        }

        .rpv-panel-del:hover {
            color: #ef4444;
        }

        .rpv-panel-footer {
            padding: .5rem;
            border-top: 1px solid #2d2d2d;
        }

        .rpv-panel-clear {
            width: 100%;
            padding: .4rem;
            background: rgba(239, 68, 68, .1);
            border: 1px solid #ef4444;
            color: #f87171;
            border-radius: 7px;
            font-size: 11px;
            cursor: pointer;
        }

        /* ─── Badge ───────────────────────────────────────── */
        .rpv-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #FF6B18;
            color: #fff;
            font-size: 8px;
            font-weight: 700;
            min-width: 14px;
            height: 14px;
            border-radius: 99px;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
        }

        .rpv-badge.show {
            display: flex;
        }

        /* ─── Eraser cursor ───────────────────────────────── */
        #rpv-eraser-cursor {
            position: fixed;
            width: 18px;
            height: 18px;
            border: 2px solid #ef4444;
            border-radius: 50%;
            pointer-events: none;
            z-index: 99999;
            transform: translate(-50%, -50%);
            display: none;
        }

        /* ─── Mode: Sepia / Night ─────────────────────────── */
        #review-pdf-viewer-wrap.mode-sepia #rpv-canvas-wrap {
            filter: sepia(.55) contrast(.95);
        }

        #review-pdf-viewer-wrap.mode-night #rpv-canvas-wrap {
            filter: invert(1) hue-rotate(180deg) contrast(.9);
        }
    </style>

    {{-- ══ TOOLBAR ══ --}}
    <div id="rpv-toolbar">
        {{-- Title --}}
        <span class="rpv-title" title="{{ $publicationTitle }}">
            📄 {{ Str::limit($publicationTitle ?? 'Naskah', 45) }}
        </span>

        {{-- Page nav --}}
        <div class="rpv-page-group">
            <button type="button" class="rpv-btn" id="rpv-prev" title="Halaman sebelumnya (←)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <input type="number" id="rpv-page-input" class="rpv-page-input" value="1" min="1">
            <span class="rpv-page-sep">/</span>
            <span class="rpv-page-total" id="rpv-page-total">—</span>
            <button type="button" class="rpv-btn" id="rpv-next" title="Halaman berikutnya (→)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        {{-- Zoom --}}
        <button type="button" class="rpv-btn" id="rpv-zoom-out" title="Perkecil (-)">−</button>
        <span class="rpv-zoom-val" id="rpv-zoom-val">100%</span>
        <button type="button" class="rpv-btn" id="rpv-zoom-in" title="Perbesar (+)">+</button>

        {{-- Reading mode --}}
        <div style="display:flex; gap:2px;">
            <button type="button" class="rpv-btn active" data-rpv-mode="normal" title="Normal">☀️</button>
            <button type="button" class="rpv-btn" data-rpv-mode="sepia" title="Sepia">📜</button>
            <button type="button" class="rpv-btn" data-rpv-mode="night" title="Night">🌙</button>
        </div>

        {{-- Search --}}
        <button type="button" class="rpv-btn" id="rpv-search-btn" title="Cari (Ctrl+F)">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span>Cari</span>
        </button>

        {{-- Download --}}
        <a href="{{ route('manuscripts.download', $version) }}" class="rpv-btn primary" title="Download PDF">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span>Download</span>
        </a>
    </div>

    {{-- Progress bar --}}
    <div class="rpv-progress-track">
        <div class="rpv-progress-fill" id="rpv-progress"></div>
    </div>

    {{-- ══ PDF CANVAS AREA ══ --}}
    <div id="rpv-canvas-wrap">
        {{-- Loading state --}}
        <div id="rpv-loading">
            <div class="rpv-spinner"></div>
            <p style="color:#fff; font-size:13px; font-weight:600; margin:0;">Memuat dokumen...</p>
            <p style="color:#6b7280; font-size:11px; margin:0;" id="rpv-load-sub">Harap tunggu sebentar</p>
        </div>

        {{-- Stage (canvas + layers) --}}
        <div id="rpv-stage" style="display:none;">
            <canvas id="rpv-canvas"></canvas>
            <div id="rpv-text-layer"></div>
            <div id="rpv-annotation-layer"></div>
            <canvas id="rpv-freehand-canvas"></canvas>
        </div>
    </div>

    {{-- ══ ANNOTATION BAR (bottom) ══ --}}
    <div id="rpv-annot-bar">
        {{-- Active tool label --}}
        <div style="font-size:9px; font-weight:700; color:#FF6B18; letter-spacing:.05em; text-transform:uppercase; margin-bottom:.3rem; padding:0 .2rem;"
            id="rpv-active-label">
            ✏️ Highlight
        </div>

        <div class="rpv-ab-tools" id="rpv-ab-tools">

            {{-- Group 1: Navigation --}}
            <button type="button" class="rpv-tool" data-tool="pan" title="Hand — Geser PDF">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path
                        d="M18 11V6.5a1.5 1.5 0 00-3 0V11m0 0V8.5a1.5 1.5 0 00-3 0V11m0 0V10a1.5 1.5 0 00-3 0v6c0 2.21 1.79 4 4 4h2a4 4 0 004-4v-5a1.5 1.5 0 00-3 0"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
            <button type="button" class="rpv-tool" data-tool="select" title="Pilih/Edit anotasi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M5 3l14 9-7 1-3 7L5 3z" stroke-linejoin="round" />
                </svg>
            </button>

            <div class="rpv-ab-sep"></div>

            {{-- Group 2: Text markup --}}
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

            {{-- Group 3: Drawing --}}
            <button type="button" class="rpv-tool" data-tool="freehand" title="Pen bebas">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M12 20h9" />
                    <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
                </svg>
            </button>
            <button type="button" class="rpv-tool" data-tool="shape" title="Shape (kotak/lingkaran/panah)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <circle cx="17.5" cy="6.5" r="3.5" />
                    <path d="M3 20h4M5 18v4M14 15l5 5m0-5l-5 5" />
                </svg>
            </button>
            <button type="button" class="rpv-tool" data-tool="text" title="Teks bebas">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <polyline points="4 7 4 4 20 4 20 7" />
                    <line x1="9" y1="20" x2="15" y2="20" />
                    <line x1="12" y1="4" x2="12" y2="20" />
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

            {{-- Group 4: Eraser --}}
            <button type="button" class="rpv-tool" data-tool="eraser" title="Hapus anotasi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="width:16px;height:16px;">
                    <path d="M20 20H7L3 16l10-10 7 7-3 3" />
                    <path d="M6.5 17.5l5-5" />
                </svg>
            </button>

            <div class="rpv-ab-sep"></div>

            {{-- Shape sub-picker --}}
            <div class="rpv-shapes" id="rpv-shapes">
                <button type="button" class="rpv-shape active" data-shape="rect" title="Kotak">⬛</button>
                <button type="button" class="rpv-shape" data-shape="ellipse" title="Lingkaran">⭕</button>
                <button type="button" class="rpv-shape" data-shape="arrow" title="Panah">➡</button>
                <button type="button" class="rpv-shape" data-shape="line" title="Garis">—</button>
                <div class="rpv-ab-sep"></div>
            </div>

            {{-- Size picker (untuk freehand/shape/text) --}}
            <div class="rpv-sizes" id="rpv-sizes" style="display:none;">
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
        </div>
    </div>

    {{-- ══ OVERLAYS ══ --}}

    {{-- Tooltip --}}
    <div id="rpv-tooltip">
        <div class="rpv-tip-text" id="rpv-tip-text"></div>
        <div class="rpv-tip-actions">
            <button type="button" class="rpv-tip-del" id="rpv-tip-del">🗑 Hapus</button>
            <button type="button" class="rpv-tip-close" id="rpv-tip-close">✕ Tutup</button>
        </div>
    </div>

    {{-- Comment popup --}}
    <div class="rpv-popup" id="rpv-comment-pop">
        <p class="rpv-popup-title">💬 Tambah Komentar Review</p>
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

    {{-- Annotation Panel --}}
    <div id="rpv-panel">
        <div class="rpv-panel-header">
            <span class="rpv-panel-title">📝 Anotasi Review Saya</span>
            <button type="button" class="rpv-panel-close" id="rpv-panel-close">✕</button>
        </div>
        <div class="rpv-panel-list" id="rpv-panel-list">
            <div class="rpv-panel-empty">Belum ada anotasi.</div>
        </div>
        <div class="rpv-panel-footer">
            <button type="button" class="rpv-panel-clear" id="rpv-panel-clear">🗑 Hapus semua di halaman ini</button>
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

    {{-- Sync indicator --}}
    <div id="rpv-sync">
        <div class="rpv-sync-dot"></div>
        <span id="rpv-sync-txt">Menyimpan...</span>
    </div>

    {{-- Eraser cursor --}}
    <div id="rpv-eraser-cursor"></div>

    {{-- ══ SCRIPT CONFIG ══ --}}
    <script>
        // Config untuk review PDF viewer — TERPISAH dari window.PDF_CONFIG
            // agar tidak konflik dengan pdf-viewer.js jika keduanya dimuat
            window.RPV_CONFIG = {
                pdfUrl    : @json($pdfUrl),
                reviewId  : @json($reviewId),
                apiBase   : @json($annotApiBase),
                reviewerName : @json($reviewerName),
            };
    </script>

    {{-- pdf.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    {{-- ══ SELF-CONTAINED REVIEW VIEWER SCRIPT ══ --}}
    {{--
    Script ini adalah versi mandiri (standalone) yang tidak bergantung
    pada pdf-viewer.js atau pdf-annotations.js yang dipakai di read.blade.php.
    Semua logika PDF rendering + anotasi ada di sini, dikonfigurasi
    ulang untuk konteks review dengan reviewId.
    --}}
    <script>
        (function () {
            'use strict';

            // Tunggu pdf.js siap
            if (typeof pdfjsLib === 'undefined') {
                console.error('[RPV] pdfjsLib not found');
                return;
            }
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            pdfjsLib.verbosity = 0;

            const CFG = window.RPV_CONFIG;
            if (!CFG || !CFG.pdfUrl) {
                console.error('[RPV] RPV_CONFIG missing');
                return;
            }

            /* ── CONFIG ──────────────────────────────────────────── */
            const API       = CFG.apiBase;      // null jika review belum disimpan
            const REVIEW_ID = CFG.reviewId;

            const VALID_TYPES  = ['highlight','underline','strikethrough','freehand','comment','sticky','shape','text'];
            const VALID_COLORS = ['yellow','green','red','blue','orange','black','white','pink','purple','cyan'];
            const VALID_SHAPES = ['rect','ellipse','arrow','line'];

            const COLORS = {
                yellow:'#FFD700', green:'#4ADE80', red:'#EF4444', blue:'#60A5FA',
                orange:'#FF6B18', black:'#111111', white:'#FFFFFF',
                pink:'#F472B6', purple:'#A78BFA', cyan:'#22D3EE',
            };
            const hex = n => COLORS[n] || '#FFD700';

            function csrf() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
            function hdrs() {
                return {
                    'Content-Type':'application/json', 'Accept':'application/json',
                    'X-CSRF-TOKEN':csrf(), 'X-Requested-With':'XMLHttpRequest',
                };
            }

            /* ── STATE ───────────────────────────────────────────── */
            let pdfDoc = null, pageNum = 1, pageRendering = false, pendingPage = null;
            let baseScale = 1.0;
            const ZOOM_MIN = 0.5, ZOOM_MAX = 4.0, ZOOM_STEP = 0.25;
            let zoomFactor = 1.0;
            const DPR = window.devicePixelRatio || 1;

            let annots = [], undoStack = [], redoStack = [];
            let activeTool = 'highlight', activeColor = 'yellow', activeSize = 2, activeShape = 'rect';
            let isDrawing = false, drawStart = null, freePoints = [], shapePreviewEl = null;
            let pendingRect = null, pendingText = null, stickyPos = null, textPos = null;
            let selectedId = null, isPanning = false;
            let panSX = 0, panSY = 0, panScrollX = 0, panScrollY = 0;
            let renderPending = false, syncT = null, searchDebounce = null;
            let searchResults = [], searchIndex = -1, searchHighlights = [];
            let currentQuery = '';

            /* ── DOM ─────────────────────────────────────────────── */
            const wrap       = document.getElementById('rpv-canvas-wrap');
            const stage      = document.getElementById('rpv-stage');
            const mainCanvas = document.getElementById('rpv-canvas');
            const ctx        = mainCanvas.getContext('2d');
            const textLayer  = document.getElementById('rpv-text-layer');
            const annotLayer = document.getElementById('rpv-annotation-layer');
            const freeCanvas = document.getElementById('rpv-freehand-canvas');
            const freeCtx    = freeCanvas?.getContext('2d');
            const loadingEl  = document.getElementById('rpv-loading');
            const loadSubEl  = document.getElementById('rpv-load-sub');
            const tooltip    = document.getElementById('rpv-tooltip');
            const tipTxt     = document.getElementById('rpv-tip-text');
            const syncEl     = document.getElementById('rpv-sync');
            const syncTxtEl  = document.getElementById('rpv-sync-txt');
            const eraserCur  = document.getElementById('rpv-eraser-cursor');

            /* ── UTILS ───────────────────────────────────────────── */
            function snack(msg, color = '#FF6B18') {
                const el = Object.assign(document.createElement('div'), { textContent: msg });
                el.style.cssText = `position:fixed;top:1rem;left:50%;transform:translateX(-50%);background:#1A1A1A;border:1px solid ${color};color:#fff;padding:.45rem 1rem;border-radius:99px;font-size:13px;font-weight:600;z-index:99999;transition:opacity .4s;pointer-events:none;white-space:nowrap;`;
                document.body.appendChild(el);
                setTimeout(() => { el.style.opacity = 0; setTimeout(() => el.remove(), 400); }, 2200);
            }

            function showSync(msg, ok = false) {
                if (!syncEl) return;
                if (syncTxtEl) syncTxtEl.textContent = msg;
                syncEl.style.borderColor = ok ? '#22c55e' : '#FF6B18';
                syncEl.style.color = ok ? '#22c55e' : '#FF6B18';
                syncEl.classList.add('show');
                clearTimeout(syncT);
                syncT = setTimeout(() => syncEl.classList.remove('show'), ok ? 1800 : 4000);
            }

            function stageXY(e) {
                const r = stage.getBoundingClientRect();
                const s = e.changedTouches?.[0] ?? e.touches?.[0] ?? e;
                return { x: s.clientX - r.left, y: s.clientY - r.top };
            }

            function esc(s) {
                return String(s || '')
                    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                    .replace(/\n/g,'<br>');
            }

            function syncFC() {
                if (!freeCanvas) return;
                const w = stage.offsetWidth, h = stage.offsetHeight;
                if (freeCanvas.width !== w || freeCanvas.height !== h) {
                    freeCanvas.width = w; freeCanvas.height = h;
                }
                freeCanvas.style.width = w + 'px'; freeCanvas.style.height = h + 'px';
            }

            /* ── PAYLOAD SANITIZER ───────────────────────────────── */
            function sanitize(raw) {
                const type  = VALID_TYPES.includes(raw.type)   ? raw.type  : 'highlight';
                const color = VALID_COLORS.includes(raw.color) ? raw.color : 'yellow';
                const p = {
                    page         : parseInt(raw.page) || pageNum,
                    type, color,
                    rect_x       : raw.rect?.x ?? raw.rect_x ?? null,
                    rect_y       : raw.rect?.y ?? raw.rect_y ?? null,
                    rect_w       : raw.rect?.w ?? raw.rect_w ?? null,
                    rect_h       : raw.rect?.h ?? raw.rect_h ?? null,
                    selected_text: raw.selected_text || null,
                    comment      : raw.comment || null,
                    path_points  : Array.isArray(raw.path_points) ? raw.path_points : null,
                    shape_type   : VALID_SHAPES.includes(raw.shape_type) ? raw.shape_type : null,
                    stroke_width : (typeof raw.stroke_width === 'number' && raw.stroke_width > 0) ? raw.stroke_width : 2,
                    fill_opacity : (typeof raw.fill_opacity === 'number') ? raw.fill_opacity : 0,
                };
                if (p.type === 'shape' && !p.shape_type) p.shape_type = 'rect';
                return p;
            }

            /* ── API ─────────────────────────────────────────────── */
            async function apiLoad() {
                if (!API) return [];
                try {
                    const r = await fetch(API, {
                        credentials: 'same-origin',
                        headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }
                    });
                    if (!r.ok) throw new Error(r.status);
                    const j = await r.json();
                    return Array.isArray(j.data) ? j.data : [];
                } catch (e) { console.error('[RPV] load:', e); return []; }
            }

            async function apiSave(payload) {
                if (!API) {
                    snack('⚠️ Simpan draft review dulu sebelum beri anotasi!', '#F59E0B');
                    return null;
                }
                const clean = sanitize(payload);
                showSync('Menyimpan...');
                try {
                    const r = await fetch(API, {
                        method: 'POST', credentials: 'same-origin',
                        headers: hdrs(), body: JSON.stringify(clean)
                    });
                    const j = await r.json();
                    if (!r.ok) { showSync('Gagal: ' + (j.message || r.status)); return null; }
                    showSync('Tersimpan ✓', true);
                    return j.data || null;
                } catch (e) { console.error('[RPV] save:', e); showSync('Error jaringan'); return null; }
            }

            async function apiPatch(id, payload) {
                if (!API) return;
                try {
                    await fetch(`${API}/${id}`, {
                        method: 'PUT', credentials: 'same-origin',
                        headers: hdrs(), body: JSON.stringify(payload)
                    });
                } catch (e) { console.error('[RPV] patch:', e); }
            }

            async function apiDel(id) {
                if (!API) return;
                showSync('Menghapus...');
                try {
                    await fetch(`${API}/${id}`, { method:'DELETE', credentials:'same-origin', headers:hdrs() });
                    showSync('Dihapus ✓', true);
                } catch (e) { console.error('[RPV] del:', e); }
            }

            async function apiDelPage(page) {
                if (!API) return;
                showSync('Membersihkan...');
                try {
                    await fetch(`${API}/page/${page}`, { method:'DELETE', credentials:'same-origin', headers:hdrs() });
                    showSync('Selesai ✓', true);
                } catch (e) { console.error('[RPV] delPage:', e); }
            }

            /* ── LOAD ANNOTATIONS ────────────────────────────────── */
            async function loadAll() {
                annots = await apiLoad();
                scheduleRender(); updateBadge(); updateUndoRedo();
            }

            /* ── RENDER ──────────────────────────────────────────── */
            function scheduleRender() {
                if (renderPending) return; renderPending = true;
                requestAnimationFrame(() => { renderPending = false; doRender(); });
            }

            function doRender() {
                const scale = baseScale * zoomFactor;
                annotLayer.innerHTML = '';
                annotLayer.style.pointerEvents = 'none';
                syncFC();
                if (freeCtx) freeCtx.clearRect(0, 0, freeCanvas.width, freeCanvas.height);
                stage.querySelectorAll('.rpv-sticky-note,.rpv-freetext').forEach(e => e.remove());

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
                updateBadge();
                if (searchResults.length > 0) applySearchHighlights();
            }

            function rHL(a, s) {
                if (!a.rect) return;
                const el = document.createElement('div'), sel = selectedId == a.id;
                el.dataset.annotId = String(a.id);
                el.style.cssText = `position:absolute;left:${a.rect.x*s}px;top:${a.rect.y*s}px;width:${a.rect.w*s}px;height:${a.rect.h*s}px;background:${hex(a.color)};opacity:${sel?.75:.38};border-radius:2px;pointer-events:auto;cursor:pointer;z-index:5;outline:${sel?'2px solid #FF6B18':'none'};`;
                if (a.type==='comment' && a.comment) {
                    const dot = document.createElement('span');
                    dot.style.cssText = 'position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:#60A5FA;border-radius:50%;pointer-events:none;';
                    el.appendChild(dot);
                }
                attachEv(el, a); annotLayer.appendChild(el);
            }
            function rUL(a, s) {
                if (!a.rect) return;
                const el = document.createElement('div'); el.dataset.annotId = String(a.id);
                const t = Math.max(1.5, 2*s);
                el.style.cssText = `position:absolute;left:${a.rect.x*s}px;top:${(a.rect.y+a.rect.h)*s-t}px;width:${a.rect.w*s}px;height:${t}px;background:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;`;
                attachEv(el, a); annotLayer.appendChild(el);
            }
            function rST(a, s) {
                if (!a.rect) return;
                const el = document.createElement('div'); el.dataset.annotId = String(a.id);
                const t = Math.max(1.5, 2*s);
                el.style.cssText = `position:absolute;left:${a.rect.x*s}px;top:${(a.rect.y+a.rect.h/2)*s-t/2}px;width:${a.rect.w*s}px;height:${t}px;background:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;`;
                attachEv(el, a); annotLayer.appendChild(el);
            }
            function rFH(a, s) {
                if (!a.path_points?.length || !freeCtx) return;
                const pts = a.path_points;
                freeCtx.save();
                freeCtx.strokeStyle = hex(a.color); freeCtx.lineWidth = (a.stroke_width||2)*s;
                freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = .92;
                freeCtx.beginPath(); freeCtx.moveTo(pts[0][0]*s, pts[0][1]*s);
                for (let i=1;i<pts.length;i++) freeCtx.lineTo(pts[i][0]*s, pts[i][1]*s);
                freeCtx.stroke(); freeCtx.restore();
                if (a.rect && (a.rect.w>0||a.rect.h>0)) {
                    const hit = document.createElement('div'); hit.dataset.annotId = String(a.id);
                    hit.style.cssText = `position:absolute;left:${(a.rect.x-8)*s}px;top:${(a.rect.y-8)*s}px;width:${(a.rect.w+16)*s}px;height:${(a.rect.h+16)*s}px;background:transparent;pointer-events:auto;cursor:pointer;z-index:6;`;
                    attachEv(hit, a); annotLayer.appendChild(hit);
                }
            }
            function rSH(a, s) {
                if (!a.rect) return;
                const x=a.rect.x*s, y=a.rect.y*s, w=Math.max(4,a.rect.w*s), h=Math.max(4,a.rect.h*s);
                const sw=Math.max(1,(a.stroke_width||2)*s), col=hex(a.color), sel=selectedId==a.id;
                const wrap2=document.createElement('div'); wrap2.dataset.annotId=String(a.id);
                wrap2.style.cssText=`position:absolute;left:${x}px;top:${y}px;width:${w}px;height:${h}px;pointer-events:auto;cursor:pointer;z-index:5;outline:${sel?'2px dashed #FF6B18':'none'};`;
                const st=a.shape_type||'rect'; let svg='';
                if (st==='rect') svg=`<rect x="${sw/2}" y="${sw/2}" width="${Math.max(1,w-sw)}" height="${Math.max(1,h-sw)}" rx="2" fill="none" stroke="${col}" stroke-width="${sw}"/>`;
                else if (st==='ellipse') svg=`<ellipse cx="${w/2}" cy="${h/2}" rx="${Math.max(1,w/2-sw/2)}" ry="${Math.max(1,h/2-sw/2)}" fill="none" stroke="${col}" stroke-width="${sw}"/>`;
                else if (st==='arrow') { const hh=Math.max(4,h*.35),hx=Math.max(sw*3,w*.25); svg=`<line x1="${sw}" y1="${h/2}" x2="${w-hx+sw}" y2="${h/2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/><polygon points="${w-sw/2},${h/2} ${w-hx},${h/2-hh} ${w-hx},${h/2+hh}" fill="${col}"/>`; }
                else if (st==='line') svg=`<line x1="${sw}" y1="${h/2}" x2="${w-sw}" y2="${h/2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/>`;
                wrap2.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" style="overflow:visible;display:block;pointer-events:none">${svg}</svg>`;
                attachEv(wrap2, a); annotLayer.appendChild(wrap2);
            }
            function rSticky(a, s) {
                if (!a.rect) return;
                const note = document.createElement('div');
                note.className = 'rpv-sticky-note'; note.dataset.annotId = String(a.id); note.dataset.color = a.color||'yellow';
                note.style.left = (a.rect.x*s)+'px'; note.style.top = (a.rect.y*s)+'px';
                note.innerHTML = `<div class="rpv-sn-header"><span>📌</span><button class="rpv-sn-del">×</button></div><div class="rpv-sn-body">${esc(a.comment)}</div>`;
                note.querySelector('.rpv-sn-del').addEventListener('click', ev => { ev.stopPropagation(); removeAnnot(a.id); });
                note.addEventListener('click', ev => {
                    if (activeTool==='eraser') { ev.stopPropagation(); removeAnnot(a.id); return; }
                    ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY);
                });
                makeDraggable(note, a, s); stage.appendChild(note);
            }
            function rText(a, s) {
                if (!a.rect) return;
                const fontSize = Math.max(10, (a.stroke_width||14)) * s;
                const el = document.createElement('div');
                el.className = 'rpv-freetext'; el.dataset.annotId = String(a.id);
                el.style.cssText = `position:absolute;left:${a.rect.x*s}px;top:${a.rect.y*s}px;font-size:${fontSize}px;line-height:1.4;color:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:8;white-space:pre-wrap;word-break:break-word;max-width:${300*s}px;font-family:sans-serif;font-weight:600;text-shadow:0 1px 3px rgba(0,0,0,.35);user-select:none;`;
                el.textContent = a.comment || '';
                el.addEventListener('click', ev => {
                    if (activeTool==='eraser') { ev.stopPropagation(); removeAnnot(a.id); return; }
                    ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY);
                });
                stage.appendChild(el);
            }

            function attachEv(el, a) {
                el.addEventListener('click', ev => {
                    ev.stopPropagation();
                    if (activeTool==='eraser') { removeAnnot(a.id); return; }
                    if (activeTool==='select') { selectedId = selectedId==a.id?null:String(a.id); scheduleRender(); return; }
                    showTip(a, ev.clientX, ev.clientY);
                });
                el.addEventListener('touchend', ev => {
                    ev.stopPropagation(); if (ev.cancelable) ev.preventDefault();
                    const t = ev.changedTouches[0];
                    if (activeTool==='eraser') { removeAnnot(a.id); return; }
                    if (activeTool==='select') { selectedId = selectedId==a.id?null:String(a.id); scheduleRender(); return; }
                    showTip(a, t.clientX, t.clientY);
                }, { passive:false });
            }

            function makeDraggable(el, annotData, s) {
                let ox=0,oy=0,dragging=false,moved=false;
                function onDown(e) {
                    if (e.target.classList.contains('rpv-sn-del')||e.target.classList.contains('rpv-sn-body')) return;
                    dragging=true; moved=false;
                    const src=e.touches?.[0]??e; ox=src.clientX-el.offsetLeft; oy=src.clientY-el.offsetTop;
                    el.style.zIndex='20'; e.stopPropagation(); if(e.cancelable)e.preventDefault();
                }
                function onMove(e) {
                    if (!dragging) return; moved=true;
                    const src=e.touches?.[0]??e; el.style.left=(src.clientX-ox)+'px'; el.style.top=(src.clientY-oy)+'px';
                    if(e.cancelable)e.preventDefault();
                }
                async function onUp() {
                    if (!dragging) return; dragging=false; el.style.zIndex='9'; if (!moved) return;
                    const newX=parseFloat(el.style.left)/s, newY=parseFloat(el.style.top)/s;
                    const idx=annots.findIndex(a=>String(a.id)===String(annotData.id));
                    if (idx>=0&&annots[idx].rect) { annots[idx].rect.x=newX; annots[idx].rect.y=newY; }
                    await apiPatch(annotData.id, { rect_x:newX, rect_y:newY, rect_w:annotData.rect?.w||180, rect_h:annotData.rect?.h||90 });
                }
                el.addEventListener('mousedown', onDown, {passive:false});
                el.addEventListener('touchstart', onDown, {passive:false});
                document.addEventListener('mousemove', onMove, {passive:false});
                document.addEventListener('touchmove', onMove, {passive:false});
                document.addEventListener('mouseup', onUp);
                document.addEventListener('touchend', onUp);
            }

            /* ── TOOLTIP ─────────────────────────────────────────── */
            function showTip(a, cx, cy) {
                const ic = {highlight:'✏️',underline:'__',strikethrough:'~~',freehand:'🖊',shape:'⬛',comment:'💬',sticky:'📌',text:'🔤'};
                let txt = `${ic[a.type]||'•'} ${a.type}`;
                if (a.comment) txt = `${ic[a.type]||'•'} ${a.comment.substring(0,80)}`;
                else if (a.selected_text) txt = `${ic[a.type]||'•'} "${a.selected_text.substring(0,60)}"`;
                if (tipTxt) { tipTxt.textContent=txt; tipTxt.dataset.annotId=String(a.id); }
                tooltip.classList.add('show');
                const vw=window.innerWidth, vh=window.innerHeight;
                tooltip.style.left=Math.max(4, Math.min(cx-135, vw-278))+'px';
                tooltip.style.top=((cy+120>vh)?Math.max(4,cy-120):cy+8)+'px';
            }
            document.getElementById('rpv-tip-close')?.addEventListener('click', () => tooltip.classList.remove('show'));
            document.getElementById('rpv-tip-del')?.addEventListener('click', async () => {
                const id=tipTxt?.dataset.annotId; tooltip.classList.remove('show'); if(id) await removeAnnot(id);
            });
            document.addEventListener('click', e => {
                if (tooltip&&!tooltip.contains(e.target)&&!e.target.closest('[data-annot-id],.rpv-sticky-note,.rpv-freetext'))
                    tooltip.classList.remove('show');
            });

            /* ── ADD / REMOVE ────────────────────────────────────── */
            async function addAnnot(payload) {
                const saved = await apiSave(payload); if (!saved) return null;
                annots.push(saved);
                undoStack.push({ action:'add', data:saved }); redoStack=[];
                updateUndoRedo(); scheduleRender(); return saved;
            }
            async function removeAnnot(id) {
                const a=annots.find(x=>String(x.id)===String(id)); if (!a) return;
                await apiDel(a.id);
                annots=annots.filter(x=>String(x.id)!==String(id));
                if (selectedId===String(id)) selectedId=null;
                undoStack.push({action:'del',data:a}); redoStack=[];
                updateUndoRedo(); scheduleRender(); snack('🗑 Anotasi dihapus');
            }

            /* ── UNDO / REDO ─────────────────────────────────────── */
            function updateUndoRedo() {
                const u=document.getElementById('rpv-undo'); if(u) u.disabled=!undoStack.length;
                const r=document.getElementById('rpv-redo'); if(r) r.disabled=!redoStack.length;
            }
            async function doUndo() {
                if (!undoStack.length) return;
                const op=undoStack.pop();
                if (op.action==='add') {
                    const a=annots.find(x=>String(x.id)===String(op.data.id));
                    if (a) { await apiDel(a.id); annots=annots.filter(x=>String(x.id)!==String(a.id)); redoStack.push({action:'readd',data:a}); }
                } else if (op.action==='del') {
                    const saved=await apiSave(op.data); if(saved) { annots.push(saved); redoStack.push({action:'redel',data:saved}); }
                }
                updateUndoRedo(); scheduleRender();
            }
            async function doRedo() {
                if (!redoStack.length) return;
                const op=redoStack.pop();
                if (op.action==='readd') {
                    const saved=await apiSave(op.data); if(saved) { annots.push(saved); undoStack.push({action:'add',data:saved}); }
                } else if (op.action==='redel') {
                    const a=annots.find(x=>String(x.id)===String(op.data.id));
                    if (a) { await apiDel(a.id); annots=annots.filter(x=>String(x.id)!==String(a.id)); undoStack.push({action:'del',data:a}); }
                }
                updateUndoRedo(); scheduleRender();
            }
            document.getElementById('rpv-undo')?.addEventListener('click', doUndo);
            document.getElementById('rpv-redo')?.addEventListener('click', doRedo);

            /* ── BADGE & PANEL ───────────────────────────────────── */
            function updateBadge() {
                const n=annots.length, badge=document.getElementById('rpv-badge');
                if (badge) { badge.textContent=n>99?'99+':String(n); badge.classList.toggle('show',n>0); }
            }
            document.getElementById('rpv-panel-btn')?.addEventListener('click', e => {
                e.stopPropagation(); document.getElementById('rpv-panel')?.classList.toggle('open'); buildPanel();
            });
            document.getElementById('rpv-panel-close')?.addEventListener('click', () => document.getElementById('rpv-panel')?.classList.remove('open'));
            document.getElementById('rpv-panel-clear')?.addEventListener('click', async () => {
                if (!confirm(`Hapus semua anotasi di halaman ${pageNum}?`)) return;
                await apiDelPage(pageNum);
                annots=annots.filter(a=>a.page!==pageNum); undoStack=[]; redoStack=[];
                updateUndoRedo(); scheduleRender(); buildPanel(); snack(`🗑 Halaman ${pageNum} dibersihkan`);
            });
            function buildPanel() {
                const list=document.getElementById('rpv-panel-list'); if (!list) return;
                if (!annots.length) { list.innerHTML='<div class="rpv-panel-empty">Belum ada anotasi.</div>'; return; }
                list.innerHTML='';
                const ic={highlight:'✏️',underline:'__',strikethrough:'~~',freehand:'🖊',shape:'⬛',comment:'💬',sticky:'📌',text:'🔤'};
                [...annots].sort((a,b)=>a.page-b.page||a.id-b.id).forEach(a=>{
                    const el=document.createElement('div'); el.className='rpv-panel-item';
                    el.innerHTML=`<div class="rpv-panel-dot" style="background:${hex(a.color)}"></div><div class="rpv-panel-body"><span class="rpv-panel-type">${ic[a.type]||'•'} ${a.type}</span><span class="rpv-panel-pg">Hal.${a.page}</span><div class="rpv-panel-text">${esc(a.comment||a.selected_text||a.shape_type||'—')}</div></div><button class="rpv-panel-del">🗑</button>`;
                    el.querySelector('.rpv-panel-del').addEventListener('click', async ev=>{ev.stopPropagation(); await removeAnnot(a.id); buildPanel();});
                    el.addEventListener('click',()=>{if(a.page!==pageNum)renderPage(a.page); document.getElementById('rpv-panel')?.classList.remove('open');});
                    list.appendChild(el);
                });
            }

            /* ── TOOL MANAGEMENT ─────────────────────────────────── */
            function setTool(tool) {
                activeTool = tool;
                stage.classList.remove('freehand-mode','shape-mode','eraser-mode','pan-mode','select-mode','text-tool-mode');
                if (tool==='freehand') stage.classList.add('freehand-mode');
                if (tool==='shape') stage.classList.add('shape-mode');
                if (tool==='eraser') stage.classList.add('eraser-mode');
                if (tool==='pan') stage.classList.add('pan-mode');
                if (tool==='select') stage.classList.add('select-mode');
                if (tool==='text') stage.classList.add('text-tool-mode');
                const needsSel=['highlight','comment','underline','strikethrough'].includes(tool);
                textLayer.style.pointerEvents=needsSel?'auto':'none';
                textLayer.style.userSelect=needsSel?'text':'none';
                textLayer.style.webkitUserSelect=needsSel?'text':'none';
                if (freeCanvas) freeCanvas.style.pointerEvents=['freehand','shape'].includes(tool)?'auto':'none';
                if (eraserCur) eraserCur.style.display=tool==='eraser'?'block':'none';
                if (tool!=='select'&&selectedId) { selectedId=null; scheduleRender(); }

                // Update active tool label
                const LABELS={pan:'🖐 Hand',select:'↖ Pilih',highlight:'✏️ Highlight',underline:'__ Underline',strikethrough:'~~ Strikethrough',comment:'💬 Komentar',freehand:'🖊 Pen Bebas',shape:'⬛ Shape',text:'🔤 Teks',eraser:'🧹 Hapus'};
                const lbl=document.getElementById('rpv-active-label'); if(lbl) lbl.textContent=LABELS[tool]||tool;

                // Show/hide size & shape sub-pickers
                document.getElementById('rpv-sizes')?.style.setProperty('display', ['freehand','shape','text'].includes(tool)?'flex':'none');
                document.getElementById('rpv-shapes')?.classList.toggle('show', tool==='shape');
            }

            /* ─ Tool clicks ─ */
            document.querySelectorAll('.rpv-tool[data-tool]').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.rpv-tool[data-tool]').forEach(b=>b.classList.remove('active'));
                    btn.classList.add('active'); setTool(btn.dataset.tool);
                });
            });

            /* ─ Color ─ */
            document.querySelectorAll('.rpv-color').forEach(sw => {
                sw.addEventListener('click', () => {
                    document.querySelectorAll('.rpv-color').forEach(s=>s.classList.remove('selected'));
                    sw.classList.add('selected'); activeColor=sw.dataset.color;
                });
            });

            /* ─ Size ─ */
            document.querySelectorAll('.rpv-size').forEach(d => {
                d.addEventListener('click', () => {
                    document.querySelectorAll('.rpv-size').forEach(x=>x.classList.remove('selected'));
                    d.classList.add('selected'); activeSize=+d.dataset.size;
                });
            });

            /* ─ Shape ─ */
            document.querySelectorAll('.rpv-shape').forEach(b => {
                b.addEventListener('click', () => {
                    document.querySelectorAll('.rpv-shape').forEach(x=>x.classList.remove('active'));
                    b.classList.add('active'); activeShape=b.dataset.shape;
                });
            });

            /* ── TEXT SELECTION ──────────────────────────────────── */
            function getSelInfo() {
                const sel=window.getSelection();
                if (!sel||sel.isCollapsed||!sel.rangeCount) return null;
                const range=sel.getRangeAt(0);
                if (!textLayer?.contains(range.commonAncestorContainer)) return null;
                const sr=stage.getBoundingClientRect(), s=baseScale*zoomFactor;
                const rects=Array.from(range.getClientRects()).filter(r=>r.width>.5&&r.height>.5);
                if (!rects.length) return null;
                const L=Math.min(...rects.map(r=>r.left)), T=Math.min(...rects.map(r=>r.top));
                const R=Math.max(...rects.map(r=>r.right)), B=Math.max(...rects.map(r=>r.bottom));
                return { rect:{x:(L-sr.left)/s,y:(T-sr.top)/s,w:(R-L)/s,h:(B-T)/s}, text:sel.toString().substring(0,1000), br:range.getBoundingClientRect() };
            }

            let selTimer=null;
            function onSelEnd(e) {
                if (e.target.closest('.rpv-popup,#rpv-annot-bar,#rpv-panel')) return;
                clearTimeout(selTimer);
                selTimer=setTimeout(async ()=>{
                    const info=getSelInfo(); if(!info||info.rect.w<2) return;
                    const base={page:pageNum,color:activeColor,rect_x:info.rect.x,rect_y:info.rect.y,rect_w:info.rect.w,rect_h:info.rect.h,selected_text:info.text};
                    if (activeTool==='highlight') { await addAnnot({...base,type:'highlight'}); window.getSelection()?.removeAllRanges(); snack('✏️ Highlight!'); }
                    else if (activeTool==='underline') { await addAnnot({...base,type:'underline'}); window.getSelection()?.removeAllRanges(); snack('__ Underline!'); }
                    else if (activeTool==='strikethrough') { await addAnnot({...base,type:'strikethrough'}); window.getSelection()?.removeAllRanges(); snack('~~ Strikethrough!'); }
                    else if (activeTool==='comment') {
                        pendingRect=info.rect; pendingText=info.text;
                        openPopup(document.getElementById('rpv-comment-pop'), info.br.left, info.br.bottom+8);
                        const t=document.getElementById('rpv-comment-txt'); if(t){t.value='';t.focus();}
                    }
                }, 80);
            }
            document.addEventListener('mouseup', onSelEnd);
            document.addEventListener('touchend', e=>{
                if (!['highlight','comment','underline','strikethrough'].includes(activeTool)) return;
                onSelEnd(e);
            }, {passive:true});

            function openPopup(popup, cx, cy) {
                if (!popup) return;
                const vw=window.innerWidth,vh=window.innerHeight,pw=284,ph=170;
                popup.style.left=Math.max(4,Math.min(cx-pw/2,vw-pw-4))+'px';
                popup.style.top=Math.max(4,(cy+ph>vh?cy-ph-8:cy))+'px';
                popup.classList.add('show');
            }

            /* Comment popup handlers */
            document.getElementById('rpv-comment-save')?.addEventListener('click', async ()=>{
                const txt=document.getElementById('rpv-comment-txt')?.value.trim();
                if (!txt||!pendingRect) {snack('Tulis komentar dulu!');return;}
                document.getElementById('rpv-comment-txt').value='';
                document.getElementById('rpv-comment-pop')?.classList.remove('show');
                await addAnnot({page:pageNum,type:'comment',color:activeColor,rect_x:pendingRect.x,rect_y:pendingRect.y,rect_w:pendingRect.w,rect_h:pendingRect.h,selected_text:pendingText||'',comment:txt});
                window.getSelection()?.removeAllRanges(); pendingRect=null; pendingText=null; snack('💬 Komentar disimpan!');
            });
            document.getElementById('rpv-comment-cancel')?.addEventListener('click', ()=>{
                document.getElementById('rpv-comment-pop')?.classList.remove('show'); pendingRect=null; pendingText=null; window.getSelection()?.removeAllRanges();
            });

            /* Sticky popup handlers */
            document.getElementById('rpv-sticky-save')?.addEventListener('click', async ()=>{
                const txt=document.getElementById('rpv-sticky-txt')?.value.trim();
                if (!txt||!stickyPos) {snack('Tulis catatan dulu!');return;}
                document.getElementById('rpv-sticky-txt').value='';
                document.getElementById('rpv-sticky-pop')?.classList.remove('show');
                await addAnnot({page:pageNum,type:'sticky',color:activeColor,rect_x:stickyPos.x,rect_y:stickyPos.y,rect_w:180,rect_h:90,comment:txt});
                stickyPos=null; snack('📌 Sticky note ditempel!');
            });
            document.getElementById('rpv-sticky-cancel')?.addEventListener('click', ()=>{
                document.getElementById('rpv-sticky-pop')?.classList.remove('show'); stickyPos=null;
            });

            /* ── FREEHAND ─────────────────────────────────────────── */
            function fhStart(e){if(activeTool!=='freehand')return;if(e.cancelable)e.preventDefault();isDrawing=true;freePoints=[];const p=stageXY(e),s=baseScale*zoomFactor;freePoints.push([p.x/s,p.y/s]);}
            function fhMove(e){if(!isDrawing||activeTool!=='freehand')return;if(e.cancelable)e.preventDefault();const p=stageXY(e),s=baseScale*zoomFactor;freePoints.push([p.x/s,p.y/s]);if(!freeCtx||freePoints.length<2)return;const last=freePoints[freePoints.length-2],cur=freePoints[freePoints.length-1];freeCtx.save();freeCtx.strokeStyle=hex(activeColor);freeCtx.lineWidth=activeSize*s;freeCtx.lineCap='round';freeCtx.lineJoin='round';freeCtx.globalAlpha=.92;freeCtx.beginPath();freeCtx.moveTo(last[0]*s,last[1]*s);freeCtx.lineTo(cur[0]*s,cur[1]*s);freeCtx.stroke();freeCtx.restore();}
            async function fhEnd(e){if(!isDrawing||activeTool!=='freehand')return;if(e.cancelable)e.preventDefault();isDrawing=false;if(freePoints.length<2)return;const xs=freePoints.map(p=>p[0]),ys=freePoints.map(p=>p[1]),bx=Math.min(...xs),by=Math.min(...ys);await addAnnot({page:pageNum,type:'freehand',color:activeColor,stroke_width:activeSize,path_points:freePoints,rect_x:bx,rect_y:by,rect_w:Math.max(...xs)-bx,rect_h:Math.max(...ys)-by});}

            /* ── SHAPE ───────────────────────────────────────────── */
            function shStart(e){if(activeTool!=='shape')return;if(e.cancelable)e.preventDefault();isDrawing=true;drawStart=stageXY(e);shapePreviewEl=document.createElement('div');shapePreviewEl.style.cssText=`position:absolute;pointer-events:none;z-index:25;border:${activeSize}px solid ${hex(activeColor)};${activeShape==='ellipse'?'border-radius:50%;':''}left:${drawStart.x}px;top:${drawStart.y}px;width:0;height:0;`;stage.appendChild(shapePreviewEl);}
            function shMove(e){if(!isDrawing||activeTool!=='shape'||!shapePreviewEl||!drawStart)return;if(e.cancelable)e.preventDefault();const c=stageXY(e);Object.assign(shapePreviewEl.style,{left:Math.min(drawStart.x,c.x)+'px',top:Math.min(drawStart.y,c.y)+'px',width:Math.abs(c.x-drawStart.x)+'px',height:Math.abs(c.y-drawStart.y)+'px'});}
            async function shEnd(e){if(!isDrawing||activeTool!=='shape')return;if(e.cancelable)e.preventDefault();isDrawing=false;shapePreviewEl?.remove();shapePreviewEl=null;const c=stageXY(e),s=baseScale*zoomFactor;if(!drawStart)return;const x=Math.min(drawStart.x,c.x)/s,y=Math.min(drawStart.y,c.y)/s,w=Math.abs(c.x-drawStart.x)/s,h=Math.abs(c.y-drawStart.y)/s;drawStart=null;if(w<4&&h<4)return;await addAnnot({page:pageNum,type:'shape',color:activeColor,shape_type:activeShape,stroke_width:activeSize,rect_x:x,rect_y:y,rect_w:w,rect_h:activeShape==='line'?1:h});}

            if (freeCanvas) {
                freeCanvas.addEventListener('mousedown',e=>{fhStart(e);shStart(e);},{passive:false});
                freeCanvas.addEventListener('mousemove',e=>{fhMove(e);shMove(e);},{passive:false});
                freeCanvas.addEventListener('mouseup',e=>{fhEnd(e);shEnd(e);},{passive:false});
                freeCanvas.addEventListener('mouseleave',e=>{fhEnd(e);shEnd(e);},{passive:false});
                freeCanvas.addEventListener('touchstart',e=>{fhStart(e);shStart(e);},{passive:false});
                freeCanvas.addEventListener('touchmove',e=>{fhMove(e);shMove(e);},{passive:false});
                freeCanvas.addEventListener('touchend',e=>{fhEnd(e);shEnd(e);},{passive:false});
            }

            /* ── ERASER CURSOR ───────────────────────────────────── */
            document.addEventListener('mousemove', e=>{
                if (!eraserCur) return;
                eraserCur.style.display=activeTool==='eraser'?'block':'none';
                if (activeTool==='eraser') { eraserCur.style.left=e.clientX+'px'; eraserCur.style.top=e.clientY+'px'; }
            });

            /* ── STAGE CLICK (sticky / text / select / eraser) ───── */
            stage.addEventListener('click', e => {
                const hitAnnot=e.target.closest('[data-annot-id],.rpv-sticky-note,.rpv-freetext');
                if (activeTool==='sticky') {
                    if (hitAnnot) return;
                    if (e.target.closest('.rpv-popup,#rpv-annot-bar')) return;
                    const p=stageXY(e),s=baseScale*zoomFactor; stickyPos={x:p.x/s,y:p.y/s};
                    openPopup(document.getElementById('rpv-sticky-pop'), e.clientX, e.clientY);
                    const t=document.getElementById('rpv-sticky-txt'); if(t){t.value='';setTimeout(()=>t.focus(),30);} return;
                }
                if (activeTool==='text') {
                    if (hitAnnot) return;
                    if (e.target.closest('.rpv-popup,#rpv-annot-bar')) return;
                    const p=stageXY(e),s=baseScale*zoomFactor; textPos={x:p.x/s,y:p.y/s};
                    ensureTextPopup(); openPopup(document.getElementById('rpv-freetext-popup'), e.clientX, e.clientY);
                    setTimeout(()=>document.getElementById('rpv-freetext-input')?.focus(), 30); return;
                }
                if (activeTool==='select') { if (!hitAnnot) { selectedId=null; scheduleRender(); } return; }
                if (activeTool==='eraser') { if (!hitAnnot) snack('Klik anotasi untuk menghapus','#60A5FA'); return; }
            });

            /* ── FREE TEXT POPUP ─────────────────────────────────── */
            function sizeToPx(s) { return {2:10,4:14,8:20,14:28}[s]||14; }
            function ensureTextPopup() {
                if (document.getElementById('rpv-freetext-popup')) return;
                const p=document.createElement('div'); p.id='rpv-freetext-popup';
                p.className='rpv-popup';
                p.innerHTML=`<p class="rpv-popup-title">🔤 Tambah Teks ke Naskah</p><textarea id="rpv-freetext-input" placeholder="Contoh: Perlu diperbaiki..."></textarea><div style="font-size:10px;color:#666;margin-top:.3rem">Ukuran: <span id="rpv-freetext-size-lbl">${sizeToPx(activeSize)}px</span></div><div class="rpv-popup-actions"><button class="rpv-popup-save" id="rpv-freetext-save">✓ Tambah</button><button class="rpv-popup-cancel" id="rpv-freetext-cancel">Batal</button></div>`;
                document.body.appendChild(p);
                document.getElementById('rpv-freetext-save').addEventListener('click', async ()=>{
                    const inp=document.getElementById('rpv-freetext-input');
                    const txt=inp?.value.trim();
                    if (!txt||!textPos) {snack('Ketik teks dulu!');return;}
                    const fs=sizeToPx(activeSize); if(inp)inp.value=''; p.classList.remove('show');
                    await addAnnot({page:pageNum,type:'text',color:activeColor,stroke_width:fs,rect_x:textPos.x,rect_y:textPos.y,rect_w:200,rect_h:fs*2,comment:txt});
                    textPos=null; snack('🔤 Teks ditambahkan!');
                });
                document.getElementById('rpv-freetext-cancel').addEventListener('click', ()=>{p.classList.remove('show');textPos=null;});
            }

            /* ── PAN ─────────────────────────────────────────────── */
            stage.addEventListener('mousedown', e=>{if(activeTool!=='pan')return;isPanning=true;panSX=e.clientX;panSY=e.clientY;panScrollX=wrap?.scrollLeft||0;panScrollY=wrap?.scrollTop||0;if(e.cancelable)e.preventDefault();},{passive:false});
            document.addEventListener('mousemove', e=>{if(!isPanning||activeTool!=='pan')return;if(wrap){wrap.scrollLeft=panScrollX+(panSX-e.clientX);wrap.scrollTop=panScrollY+(panSY-e.clientY);}});
            document.addEventListener('mouseup', ()=>{isPanning=false;});

            /* ── SEARCH ──────────────────────────────────────────── */
            function openSearch(){document.getElementById('rpv-search')?.classList.add('show');document.getElementById('rpv-search-input')?.focus();}
            function closeSearch(){document.getElementById('rpv-search')?.classList.remove('show');clearSearchHighlights();currentQuery='';searchResults=[];searchIndex=-1;const i=document.getElementById('rpv-search-input');if(i)i.value='';document.getElementById('rpv-search-results').innerHTML='';document.getElementById('rpv-search-status').textContent='Ketik untuk mencari...';}
            function clearSearchHighlights(){annotLayer.querySelectorAll('.rpv-search-hl').forEach(e=>e.remove());searchHighlights=[];}
            function applySearchHighlights(){
                clearSearchHighlights(); if(!currentQuery||!pdfDoc)return;
                const q=currentQuery.toLowerCase(), sr=stage.getBoundingClientRect();
                Array.from(textLayer.querySelectorAll('span')).forEach(span=>{
                    if (!span.firstChild) return;
                    const text=span.textContent, lower=text.toLowerCase(); let idx=lower.indexOf(q);
                    let gi=0;
                    while (idx!==-1) {
                        try {
                            const range=document.createRange(); range.setStart(span.firstChild,idx); range.setEnd(span.firstChild,Math.min(idx+q.length,text.length));
                            Array.from(range.getClientRects()).forEach(rect=>{
                                if(rect.width<1||rect.height<1)return;
                                const el=document.createElement('div'); el.className='rpv-search-hl'; el.dataset.matchIdx=gi;
                                el.style.left=(rect.left-sr.left)+'px'; el.style.top=(rect.top-sr.top)+'px'; el.style.width=rect.width+'px'; el.style.height=rect.height+'px';
                                annotLayer.appendChild(el); searchHighlights.push(el);
                            });
                        } catch(_){}
                        gi++; idx=lower.indexOf(q,idx+1);
                    }
                });
                searchHighlights.forEach((el,i)=>el.classList.toggle('active-match',i===searchIndex));
                if (searchHighlights[searchIndex]) searchHighlights[searchIndex].scrollIntoView({behavior:'smooth',block:'center'});
            }
            async function doSearch(query){
                if(!pdfDoc||!query.trim()){document.getElementById('rpv-search-status').textContent='Ketik untuk mencari...';clearSearchHighlights();currentQuery='';return;}
                document.getElementById('rpv-search-status').textContent='Mencari...';
                searchResults=[]; currentQuery=query; const q=query.toLowerCase();
                for(let p=1;p<=pdfDoc.numPages;p++){const page=await pdfDoc.getPage(p);const content=await page.getTextContent();const text=content.items.map(i=>i.str).join(' ');const lt=text.toLowerCase();let idx=lt.indexOf(q);while(idx!==-1){searchResults.push({page:p,excerpt:text.substring(Math.max(0,idx-35),idx+q.length+50).trim()});idx=lt.indexOf(q,idx+1);}}
                const list=document.getElementById('rpv-search-results'); list.innerHTML='';
                if(!searchResults.length){document.getElementById('rpv-search-status').textContent=`Tidak ditemukan: "${query}"`;clearSearchHighlights();return;}
                document.getElementById('rpv-search-status').textContent=`${searchResults.length} hasil`;
                searchIndex=0;
                searchResults.slice(0,40).forEach((r,i)=>{const el=document.createElement('div');el.className='rpv-sri'+(i===0?' active-sri':'');const hl=r.excerpt.replace(new RegExp(query.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'),'gi'),m=>`<mark>${m}</mark>`);el.innerHTML=`<span class="pg">Hal.${r.page}</span><span>${hl}</span>`;el.addEventListener('click',()=>{searchIndex=i;document.querySelectorAll('.rpv-sri').forEach((x,j)=>x.classList.toggle('active-sri',j===i));if(r.page!==pageNum)renderPage(r.page);else{applySearchHighlights();}});list.appendChild(el);});
                if(searchResults[0].page===pageNum)applySearchHighlights();else renderPage(searchResults[0].page);
            }
            document.getElementById('rpv-search-input')?.addEventListener('input',function(){clearTimeout(searchDebounce);searchDebounce=setTimeout(()=>doSearch(this.value),450);});
            document.getElementById('rpv-sclose')?.addEventListener('click',closeSearch);
            document.getElementById('rpv-snext')?.addEventListener('click',()=>{if(!searchResults.length)return;searchIndex=(searchIndex+1)%searchResults.length;const r=searchResults[searchIndex];if(r.page!==pageNum)renderPage(r.page);else applySearchHighlights();});
            document.getElementById('rpv-sprev')?.addEventListener('click',()=>{if(!searchResults.length)return;searchIndex=(searchIndex-1+searchResults.length)%searchResults.length;const r=searchResults[searchIndex];if(r.page!==pageNum)renderPage(r.page);else applySearchHighlights();});
            document.getElementById('rpv-search')?.addEventListener('click',e=>{if(e.target===document.getElementById('rpv-search'))closeSearch();});
            document.getElementById('rpv-search-btn')?.addEventListener('click',openSearch);

            /* ── PDF RENDER ──────────────────────────────────────── */
            function getScale(){return baseScale*zoomFactor;}

            function computeBase(page) {
                const cw=wrap.clientWidth||800, padding=16, nw=page.getViewport({scale:1}).width;
                baseScale=Math.max(0.5, Math.min((cw-padding*2)/nw, 2.5));
            }

            function renderPage(num) {
                if (num<1||(pdfDoc&&num>pdfDoc.numPages)) return;
                if (pageRendering) { pendingPage=num; return; }
                pageRendering=true; pageNum=num;

                // Close popups on page change
                document.querySelectorAll('.rpv-popup').forEach(p=>p.classList.remove('show'));
                tooltip.classList.remove('show');
                pendingRect=null; pendingText=null; stickyPos=null; textPos=null;
                window.getSelection()?.removeAllRanges();

                pdfDoc.getPage(num).then(async page=>{
                    if (baseScale===1.0) computeBase(page);
                    const cssScale=getScale(), renderScale=cssScale*DPR;
                    const vpCss=page.getViewport({scale:cssScale}), vpRender=page.getViewport({scale:renderScale});

                    mainCanvas.width=Math.floor(vpRender.width); mainCanvas.height=Math.floor(vpRender.height);
                    mainCanvas.style.width=Math.floor(vpCss.width)+'px'; mainCanvas.style.height=Math.floor(vpCss.height)+'px';
                    stage.style.width=Math.floor(vpCss.width)+'px'; stage.style.height=Math.floor(vpCss.height)+'px';

                    await page.render({canvasContext:ctx,viewport:vpRender}).promise.catch(e=>console.warn(e.message));

                    pageRendering=false;
                    if (pendingPage!==null) { const p=pendingPage; pendingPage=null; renderPage(p); return; }

                    // Text layer
                    textLayer.innerHTML=''; textLayer.style.width=vpCss.width+'px'; textLayer.style.height=vpCss.height+'px';
                    const content=await page.getTextContent();
                    content.items.forEach(item=>{
                        if (!item.str||!item.str.trim()) return;
                        const tx=pdfjsLib.Util.transform(vpCss.transform,item.transform);
                        const fh=Math.sqrt(tx[2]*tx[2]+tx[3]*tx[3]), angle=Math.atan2(tx[1],tx[0]);
                        const span=document.createElement('span'); span.textContent=item.str; span.style.fontSize=fh+'px'; span.style.left=tx[4]+'px'; span.style.top=(tx[5]-fh)+'px';
                        textLayer.appendChild(span);
                        const tw=item.width*vpCss.scale, mw=span.getBoundingClientRect().width;
                        let t=angle!==0?`rotate(${-angle}rad)`:''
                        if (mw>1&&tw>0) t+=` scaleX(${tw/mw})`;
                        if (t.trim()) span.style.transform=t.trim();
                    });

                    scheduleRender();

                    // Update UI
                    stage.style.display='block'; loadingEl.classList.add('hidden');
                    document.getElementById('rpv-page-input').value=num;
                    document.getElementById('rpv-prev').disabled=num<=1;
                    document.getElementById('rpv-next').disabled=!pdfDoc||num>=pdfDoc.numPages;
                    const pct=pdfDoc?(num/pdfDoc.numPages*100):0;
                    document.getElementById('rpv-progress').style.width=pct+'%';
                    const zv=document.getElementById('rpv-zoom-val'); if(zv)zv.textContent=Math.round(zoomFactor*100)+'%';
                    wrap.scrollTo({top:0,behavior:'smooth'});

                }).catch(e=>{
                    console.error('[RPV] render error:',e);
                    pageRendering=false; loadingEl.classList.add('hidden'); stage.style.display='block';
                });
            }

            /* ── NAVIGATION ──────────────────────────────────────── */
            document.getElementById('rpv-prev')?.addEventListener('click',()=>{if(pageNum>1){pageNum--;renderPage(pageNum);}});
            document.getElementById('rpv-next')?.addEventListener('click',()=>{if(pdfDoc&&pageNum<pdfDoc.numPages){pageNum++;renderPage(pageNum);}});
            document.getElementById('rpv-page-input')?.addEventListener('change',function(){const n=parseInt(this.value);if(pdfDoc&&n>=1&&n<=pdfDoc.numPages)renderPage(n);else this.value=pageNum;});

            /* ── ZOOM ────────────────────────────────────────────── */
            function zoomIn(){zoomFactor=Math.min(zoomFactor+ZOOM_STEP,ZOOM_MAX);baseScale=1.0;if(pdfDoc)pdfDoc.getPage(pageNum).then(p=>{computeBase(p);renderPage(pageNum);});}
            function zoomOut(){zoomFactor=Math.max(zoomFactor-ZOOM_STEP,ZOOM_MIN);baseScale=1.0;if(pdfDoc)pdfDoc.getPage(pageNum).then(p=>{computeBase(p);renderPage(pageNum);});}
            document.getElementById('rpv-zoom-in')?.addEventListener('click',zoomIn);
            document.getElementById('rpv-zoom-out')?.addEventListener('click',zoomOut);

            /* ── READING MODE ────────────────────────────────────── */
            document.querySelectorAll('[data-rpv-mode]').forEach(btn=>{
                btn.addEventListener('click',()=>{
                    document.querySelectorAll('[data-rpv-mode]').forEach(b=>b.classList.remove('active'));
                    btn.classList.add('active');
                    const wrap2=document.getElementById('review-pdf-viewer-wrap');
                    wrap2?.classList.remove('mode-sepia','mode-night');
                    if (btn.dataset.rpvMode!=='normal') wrap2?.classList.add('mode-'+btn.dataset.rpvMode);
                });
            });

            /* ── KEYBOARD ────────────────────────────────────────── */
            document.addEventListener('keydown', e=>{
                if (['INPUT','TEXTAREA'].includes(e.target.tagName)) return;
                if ((e.ctrlKey||e.metaKey)&&e.key==='f') {e.preventDefault();openSearch();return;}
                if ((e.ctrlKey||e.metaKey)&&!e.shiftKey&&e.key==='z') {e.preventDefault();doUndo();return;}
                if ((e.ctrlKey||e.metaKey)&&(e.key==='y'||(e.shiftKey&&e.key==='z'))) {e.preventDefault();doRedo();return;}
                if ((e.key==='Delete'||e.key==='Backspace')&&selectedId) {removeAnnot(selectedId);selectedId=null;return;}
                switch(e.key) {
                    case 'ArrowLeft': if(pageNum>1){pageNum--;renderPage(pageNum);} break;
                    case 'ArrowRight': if(pdfDoc&&pageNum<pdfDoc.numPages){pageNum++;renderPage(pageNum);} break;
                    case '+': case '=': zoomIn(); break;
                    case '-': zoomOut(); break;
                    case 'Escape': closeSearch(); break;
                }
            });

            /* ── RESIZE ──────────────────────────────────────────── */
            let resizeT=null; let lastW=wrap.clientWidth;
            window.addEventListener('resize',()=>{
                const w=wrap.clientWidth; if(Math.abs(w-lastW)<20)return; lastW=w;
                clearTimeout(resizeT); resizeT=setTimeout(()=>{if(!pdfDoc)return;baseScale=1.0;pdfDoc.getPage(pageNum).then(p=>{computeBase(p);renderPage(pageNum);});},250);
            });

            /* ── LOAD PDF ────────────────────────────────────────── */
            const task = pdfjsLib.getDocument({
                url: CFG.pdfUrl,
                withCredentials: false,
                verbosity: 0,
                rangeChunkSize: 65536,
            });

            task.onProgress = function(data) {
                if (data.total>0 && loadSubEl) {
                    const pct=Math.round((data.loaded/data.total)*100);
                    loadSubEl.textContent=`Mengunduh... ${pct}%`;
                }
            };

            task.promise.then(async doc=>{
                pdfDoc=doc;
                document.getElementById('rpv-page-total').textContent=doc.numPages;
                document.getElementById('rpv-page-input').max=doc.numPages;
                renderPage(1);
                await loadAll();
            }).catch(err=>{
                console.error('[RPV] PDF load error:',err);
                loadingEl.innerHTML=`<div style="font-size:2rem">⚠️</div><p style="color:#ef4444;font-weight:700;">Gagal memuat PDF</p><p style="color:#6b7280;font-size:12px;">${err.message}</p>`;
            });

            // Sync freeCanvas size on canvas mutation
            if (mainCanvas) {
                new MutationObserver(()=>{syncFC();}).observe(mainCanvas,{attributes:true,attributeFilter:['width','height']});
            }

            console.log('[RPV] Review PDF Viewer ready, reviewId=', REVIEW_ID, 'api=', API);

        })();
    </script>

    @endif

</div>