{{--
resources/views/filament/reviews/pdf-viewer-readonly.blade.php

Tampilan read-only untuk author:
- Hanya menampilkan PDF + anotasi reviewer yang sudah ada
- Tidak ada toolbar anotasi, tidak ada tools
- Hanya navigasi halaman & zoom
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

// Ambil semua anotasi reviewer untuk review ini
$annotations = $reviewId
? PdfAnnotation::where('review_id', $reviewId)->get()->toArray()
: [];

$annotApiBase = $reviewId
? url("/api/review-annotations/{$reviewId}")
: null;
@endphp

@if (!$pdfUrl)
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                gap:1rem;text-align:center;padding:3rem 2rem;min-height:300px;">
    <div style="font-size:2.5rem;">📄</div>
    <p style="color:#6B7280;font-size:.875rem;margin:0;">PDF tidak tersedia.</p>
</div>
@else

<link rel="stylesheet"
    href="{{ asset('css/review-pdf-viewer.css') }}?v={{ filemtime(public_path('css/review-pdf-viewer.css')) }}">

{{-- Override CSS: sembunyikan elemen yang tidak diperlukan author --}}
<style>
    #rpv-ro-wrap #rpv-annot-bar,
    #rpv-ro-wrap #rpv-sync,
    #rpv-ro-wrap #rpv-eraser-cursor,
    #rpv-ro-wrap #rpv-export-overlay,
    #rpv-ro-wrap #rpv-mobile-fab,
    #rpv-ro-wrap #rpv-panel-footer,
    #rpv-ro-wrap .rpv-tip-del,
    #rpv-ro-wrap .rpv-tip-edit {
        display: none !important;
    }

    #rpv-ro-wrap #rpv-panel .rpv-panel-footer {
        display: none !important;
    }

    /* Toolbar read-only: lebih compact */
    #rpv-ro-wrap #rpv-toolbar {
        gap: 6px;
    }
</style>

<div id="rpv-ro-wrap">

    {{-- ══ TOOLBAR (navigasi + zoom saja) ══ --}}
    <div id="rpv-toolbar">
        <span class="rpv-title" title="{{ $publicationTitle }}">
            📄 {{ Str::limit($publicationTitle ?? 'Naskah', 38) }}
        </span>

        <span style="background:#374151;color:#9CA3AF;font-size:10px;font-weight:700;
                     padding:2px 10px;border-radius:20px;border:1px solid #4B5563;
                     letter-spacing:.5px;text-transform:uppercase;flex-shrink:0;">
            👁 Anotasi Reviewer
        </span>

        <div class="rpv-page-group">
            <button type="button" class="rpv-btn" id="rpv-ro-prev">‹</button>
            <input type="number" id="rpv-ro-page-input" class="rpv-page-input" value="1" min="1">
            <span class="rpv-page-sep">/</span>
            <span class="rpv-page-total" id="rpv-ro-page-total">—</span>
            <button type="button" class="rpv-btn" id="rpv-ro-next">›</button>
        </div>

        <button type="button" class="rpv-btn rpv-desktop-only" id="rpv-ro-zoom-out">−</button>
        <span class="rpv-zoom-val rpv-desktop-only" id="rpv-ro-zoom-val">100%</span>
        <button type="button" class="rpv-btn rpv-desktop-only" id="rpv-ro-zoom-in">+</button>

        {{-- Tombol panel anotasi --}}
        <button type="button" class="rpv-btn" id="rpv-ro-panel-btn" style="position:relative;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;">
                <line x1="8" y1="6" x2="21" y2="6" />
                <line x1="8" y1="12" x2="21" y2="12" />
                <line x1="8" y1="18" x2="21" y2="18" />
                <circle cx="3" cy="6" r="1" fill="currentColor" />
                <circle cx="3" cy="12" r="1" fill="currentColor" />
                <circle cx="3" cy="18" r="1" fill="currentColor" />
            </svg>
            <span class="rpv-desktop-only">Anotasi</span>
            <span class="rpv-badge" id="rpv-ro-badge">0</span>
        </button>
    </div>

    <div class="rpv-progress-track">
        <div class="rpv-progress-fill" id="rpv-ro-progress"></div>
    </div>

    {{-- ══ CANVAS AREA ══ --}}
    <div id="rpv-canvas-wrap">

        <div id="rpv-loading">
            <div class="rpv-spinner"></div>
            <p style="color:#fff;font-size:13px;font-weight:600;margin:0;">Memuat dokumen...</p>
            <p style="color:#6b7280;font-size:11px;margin:0;">Harap tunggu sebentar</p>
        </div>

        <div id="rpv-stage">
            <canvas id="rpv-canvas"></canvas>
            <div id="rpv-text-layer"></div>
            <div id="rpv-annotation-layer"></div>
            <canvas id="rpv-freehand-canvas" style="pointer-events:none;position:absolute;inset:0;z-index:10;"></canvas>
        </div>

        {{-- Panel anotasi — view only, tanpa tombol hapus --}}
        <div id="rpv-panel">
            <div class="rpv-panel-header">
                <span class="rpv-panel-title">👁 Anotasi Reviewer</span>
                <button type="button" class="rpv-panel-close" id="rpv-ro-panel-close">✕</button>
            </div>
            <div class="rpv-panel-list" id="rpv-ro-panel-list">
                <div class="rpv-panel-empty">Belum ada anotasi.</div>
            </div>
            {{-- Sengaja tidak ada panel-footer (hapus semua) --}}
        </div>

    </div>

    {{-- Tooltip view-only: hanya tutup, tanpa hapus/edit --}}
    <div id="rpv-tooltip">
        <div class="rpv-tip-text" id="rpv-ro-tip-text"></div>
        <div class="rpv-tip-actions">
            <button type="button" class="rpv-tip-close" id="rpv-ro-tip-close">✕ Tutup</button>
        </div>
    </div>

</div>{{-- /#rpv-ro-wrap --}}

{{-- ══ CONFIG & SCRIPT ══ --}}
<script>
    window.RPV_RO_CONFIG = {
        pdfUrl     : @json($pdfUrl),
        reviewId   : @json($reviewId),
        apiBase    : @json($annotApiBase),
        annotations: @json($annotations),  {{-- anotasi di-pass langsung dari server --}}
    };
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" crossorigin="anonymous"></script>
<script>
    (function () {
    var WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    var CFG    = window.RPV_RO_CONFIG;
    if (!CFG || !CFG.pdfUrl) return;

    pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER;
    pdfjsLib.verbosity = 0;

    /* ── Color map ── */
    var COLORS = {
        yellow:'#FFD700', green:'#4ADE80', red:'#EF4444', blue:'#60A5FA',
        orange:'#FF6B18', black:'#111111', white:'#FFFFFF',
        pink:'#F472B6', purple:'#A78BFA', cyan:'#22D3EE'
    };
    function hex(n) { return COLORS[n] || '#FFD700'; }
    function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>'); }

    /* ── DOM ── */
    var wrap       = document.getElementById('rpv-canvas-wrap');
    var stage      = document.getElementById('rpv-stage');
    var mainCanvas = document.getElementById('rpv-canvas');
    var ctx        = mainCanvas.getContext('2d');
    var textLayer  = document.getElementById('rpv-text-layer');
    var annotLayer = document.getElementById('rpv-annotation-layer');
    var freeCanvas = document.getElementById('rpv-freehand-canvas');
    var freeCtx    = freeCanvas ? freeCanvas.getContext('2d') : null;
    var loadingEl  = document.getElementById('rpv-loading');
    var tooltip    = document.getElementById('rpv-tooltip');

    /* ── State ── */
    var annots = [];
    var pdfDoc = null, pageNum = 1, pageRendering = false, pendingPage = null;
    var baseScale = 1, zoomFactor = 1, needsRecompute = true;
    var DPR = window.devicePixelRatio || 1;
    var ZOOM_MIN = 0.5, ZOOM_MAX = 4, ZOOM_STEP = 0.25;

    /* ── Normalize anotasi dari server ── */
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

    /* ── Sync freehand canvas size ── */
    function syncFC() {
        if (!freeCanvas) return;
        var w = stage.offsetWidth, h = stage.offsetHeight;
        if (freeCanvas.width !== w || freeCanvas.height !== h) {
            freeCanvas.width = w; freeCanvas.height = h;
        }
        freeCanvas.style.width = w + 'px'; freeCanvas.style.height = h + 'px';
    }

    /* ── Render semua anotasi di halaman saat ini ── */
    function doRender() {
        var s = baseScale * zoomFactor;
        annotLayer.innerHTML = '';
        annotLayer.style.pointerEvents = 'auto'; /* bisa klik untuk tooltip */
        syncFC();
        if (freeCtx) freeCtx.clearRect(0, 0, freeCanvas.width, freeCanvas.height);
        stage.querySelectorAll('.rpv-sticky-note').forEach(function (e) { e.remove(); });

        annots.filter(function (a) { return a.page === pageNum; }).forEach(function (a) {
            if (a.type === 'highlight' || a.type === 'comment') rHL(a, s);
            else if (a.type === 'underline')      rUL(a, s);
            else if (a.type === 'strikethrough')  rST(a, s);
            else if (a.type === 'freehand')       rFH(a, s);
            else if (a.type === 'shape')          rSH(a, s);
            else if (a.type === 'sticky')         rSticky(a, s);
        });
        updateBadge();
    }

    /* ── Render helpers (copy dari viewer utama, tanpa attachEv write) ── */
    function rHL(a, s) {
        if (!a.rect) return;
        var el = document.createElement('div');
        el.dataset.annotId = String(a.id);
        el.style.cssText = 'position:absolute;left:'+(a.rect.x*s)+'px;top:'+(a.rect.y*s)+'px;width:'+(a.rect.w*s)+'px;height:'+(a.rect.h*s)+'px;background:'+hex(a.color)+';opacity:.38;border-radius:2px;pointer-events:auto;cursor:pointer;z-index:5;';
        if (a.type === 'comment' && a.comment) {
            var dot = document.createElement('span');
            dot.style.cssText = 'position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:#60A5FA;border-radius:50%;pointer-events:none;';
            el.appendChild(dot);
        }
        el.addEventListener('click', function (ev) { ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY); });
        annotLayer.appendChild(el);
    }

    function rUL(a, s) {
        if (!a.rect) return;
        var el = document.createElement('div'); el.dataset.annotId = String(a.id);
        var t = Math.max(1.5, 2*s);
        el.style.cssText = 'position:absolute;left:'+(a.rect.x*s)+'px;top:'+((a.rect.y+a.rect.h)*s-1)+'px;width:'+(a.rect.w*s)+'px;height:'+t+'px;background:'+hex(a.color)+';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';
        el.addEventListener('click', function (ev) { ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY); });
        annotLayer.appendChild(el);
    }

    function rST(a, s) {
        if (!a.rect) return;
        var el = document.createElement('div'); el.dataset.annotId = String(a.id);
        var t = Math.max(1.5, 2*s);
        var top = a.rect.y*s + a.rect.h*s*0.62 - t/2;
        el.style.cssText = 'position:absolute;left:'+(a.rect.x*s)+'px;top:'+top+'px;width:'+(a.rect.w*s)+'px;height:'+t+'px;background:'+hex(a.color)+';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';
        el.addEventListener('click', function (ev) { ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY); });
        annotLayer.appendChild(el);
    }

    function rFH(a, s) {
        if (!a.path_points || !a.path_points.length || !freeCtx) return;
        var pts = a.path_points;
        freeCtx.save();
        freeCtx.strokeStyle = hex(a.color); freeCtx.lineWidth = (a.stroke_width||2)*s;
        freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = .92;
        freeCtx.beginPath(); freeCtx.moveTo(pts[0][0]*s, pts[0][1]*s);
        for (var i = 1; i < pts.length; i++) freeCtx.lineTo(pts[i][0]*s, pts[i][1]*s);
        freeCtx.stroke(); freeCtx.restore();
        if (a.rect && (a.rect.w > 0 || a.rect.h > 0)) {
            var hit = document.createElement('div'); hit.dataset.annotId = String(a.id);
            hit.style.cssText = 'position:absolute;left:'+((a.rect.x-8)*s)+'px;top:'+((a.rect.y-8)*s)+'px;width:'+((a.rect.w+16)*s)+'px;height:'+((a.rect.h+16)*s)+'px;background:transparent;pointer-events:auto;cursor:pointer;z-index:6;';
            hit.addEventListener('click', function (ev) { ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY); });
            annotLayer.appendChild(hit);
        }
    }

    function rSH(a, s) {
        if (!a.rect) return;
        var col = hex(a.color), sw = Math.max(1,(a.stroke_width||2)*s), st = a.shape_type||'rect';
        var el = document.createElement('div'); el.dataset.annotId = String(a.id);

        if (st === 'arrow' || st === 'line') {
            var ax1 = a.arrow_x1!=null?a.arrow_x1*s:a.rect.x*s, ay1 = a.arrow_y1!=null?a.arrow_y1*s:(a.rect.y+a.rect.h/2)*s;
            var ax2 = a.arrow_x2!=null?a.arrow_x2*s:(a.rect.x+a.rect.w)*s, ay2 = a.arrow_y2!=null?a.arrow_y2*s:(a.rect.y+a.rect.h/2)*s;
            var bx=Math.min(ax1,ax2)-sw*2, by=Math.min(ay1,ay2)-sw*2;
            var bw=Math.abs(ax2-ax1)+sw*4, bh=Math.abs(ay2-ay1)+sw*4;
            var lx1=ax1-bx, ly1=ay1-by, lx2=ax2-bx, ly2=ay2-by;
            el.style.cssText = 'position:absolute;left:'+bx+'px;top:'+by+'px;width:'+bw+'px;height:'+bh+'px;pointer-events:auto;cursor:pointer;z-index:5;';
            var svg = '';
            if (st === 'line') {
                svg = '<line x1="'+lx1+'" y1="'+ly1+'" x2="'+lx2+'" y2="'+ly2+'" stroke="'+col+'" stroke-width="'+sw+'" stroke-linecap="round"/>';
            } else {
                var dx=lx2-lx1, dy=ly2-ly1, len=Math.sqrt(dx*dx+dy*dy);
                if (len > 1) {
                    var headLen=Math.min(len*.35,Math.max(10,sw*5)), ang=Math.atan2(dy,dx);
                    var hx1=lx2-headLen*Math.cos(ang-Math.PI/6), hy1=ly2-headLen*Math.sin(ang-Math.PI/6);
                    var hx2=lx2-headLen*Math.cos(ang+Math.PI/6), hy2=ly2-headLen*Math.sin(ang+Math.PI/6);
                    svg = '<line x1="'+lx1+'" y1="'+ly1+'" x2="'+lx2+'" y2="'+ly2+'" stroke="'+col+'" stroke-width="'+sw+'" stroke-linecap="round"/>'
                        + '<polyline points="'+hx1+','+hy1+' '+lx2+','+ly2+' '+hx2+','+hy2+'" fill="none" stroke="'+col+'" stroke-width="'+sw+'" stroke-linecap="round" stroke-linejoin="round"/>';
                }
            }
            el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="'+bw+'" height="'+bh+'" style="overflow:visible;display:block;pointer-events:none">'+svg+'</svg>';
        } else {
            var x=a.rect.x*s, y=a.rect.y*s, w=Math.max(4,a.rect.w*s), h=Math.max(4,a.rect.h*s);
            el.style.cssText = 'position:absolute;left:'+x+'px;top:'+y+'px;width:'+w+'px;height:'+h+'px;pointer-events:auto;cursor:pointer;z-index:5;';
            var svg = '';
            if (st === 'rect') svg = '<rect x="'+(sw/2)+'" y="'+(sw/2)+'" width="'+Math.max(1,w-sw)+'" height="'+Math.max(1,h-sw)+'" rx="2" fill="none" stroke="'+col+'" stroke-width="'+sw+'"/>';
            else if (st === 'ellipse') svg = '<ellipse cx="'+(w/2)+'" cy="'+(h/2)+'" rx="'+Math.max(1,w/2-sw/2)+'" ry="'+Math.max(1,h/2-sw/2)+'" fill="none" stroke="'+col+'" stroke-width="'+sw+'"/>';
            el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="'+w+'" height="'+h+'" style="overflow:visible;display:block;pointer-events:none">'+svg+'</svg>';
        }
        el.addEventListener('click', function (ev) { ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY); });
        annotLayer.appendChild(el);
    }

    function rSticky(a, s) {
        if (!a.rect) return;
        var note = document.createElement('div');
        note.className = 'rpv-sticky-note'; note.dataset.color = a.color||'yellow';
        note.style.left = (a.rect.x*s)+'px'; note.style.top = (a.rect.y*s)+'px';
        /* View-only: tidak ada tombol edit/hapus */
        note.innerHTML = '<div class="rpv-sn-header"><span>📌</span></div><div class="rpv-sn-body">'+esc(a.comment)+'</div>';
        note.addEventListener('click', function (ev) { ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY); });
        stage.appendChild(note);
    }

    /* ── Tooltip view-only ── */
    function showTip(a, cx, cy) {
        var ic = { highlight:'✏️', underline:'__', strikethrough:'~~', freehand:'🖊', shape:'⬛', comment:'💬', sticky:'📌' };
        var txt = a.comment
            ? ic[a.type] + ' ' + a.comment.substring(0, 120)
            : a.selected_text
                ? ic[a.type] + ' "' + a.selected_text.substring(0, 80) + '"'
                : ic[a.type] + ' ' + a.type;
        var tipTxt = document.getElementById('rpv-ro-tip-text');
        if (tipTxt) tipTxt.textContent = txt;
        tooltip.classList.add('show');
        var vw = window.innerWidth, vh = window.innerHeight;
        tooltip.style.left = Math.max(4, Math.min(cx - 135, vw - 278)) + 'px';
        tooltip.style.top  = ((cy + 140 > vh) ? Math.max(4, cy - 140) : cy + 8) + 'px';
    }

    var roTipClose = document.getElementById('rpv-ro-tip-close');
    if (roTipClose) roTipClose.addEventListener('click', function () { tooltip.classList.remove('show'); });
    document.addEventListener('click', function (e) {
        if (tooltip && tooltip.classList.contains('show')) {
            if (tooltip.contains(e.target)) return;
            if (e.target.closest && e.target.closest('[data-annot-id],.rpv-sticky-note')) return;
            tooltip.classList.remove('show');
        }
    });

    /* ── Badge & Panel ── */
    function updateBadge() {
        var n = annots.length;
        var badge = document.getElementById('rpv-ro-badge');
        if (badge) { badge.textContent = n > 99 ? '99+' : String(n); badge.classList.toggle('show', n > 0); }
    }

    function buildPanel() {
        var list = document.getElementById('rpv-ro-panel-list'); if (!list) return;
        if (!annots.length) { list.innerHTML = '<div class="rpv-panel-empty">Belum ada anotasi dari reviewer.</div>'; return; }
        list.innerHTML = '';
        var ic = { highlight:'✏️', underline:'__', strikethrough:'~~', freehand:'🖊', shape:'⬛', comment:'💬', sticky:'📌' };
        annots.slice().sort(function (a, b) { return a.page - b.page || a.id - b.id; }).forEach(function (a) {
            var el = document.createElement('div'); el.className = 'rpv-panel-item';
            /* Tanpa tombol edit/hapus */
            el.innerHTML = '<div class="rpv-panel-dot" style="background:'+hex(a.color)+'"></div>'
                + '<div class="rpv-panel-body">'
                + '<span class="rpv-panel-type">'+(ic[a.type]||'•')+' '+a.type+'</span>'
                + '<span class="rpv-panel-pg">Hal.'+a.page+'</span>'
                + '<div class="rpv-panel-text">'+esc(a.comment||a.selected_text||a.shape_type||'—')+'</div>'
                + '</div>';
            el.addEventListener('click', function () {
                if (a.page !== pageNum) renderPage(a.page);
                var panel = document.getElementById('rpv-panel'); if (panel) panel.classList.remove('open');
            });
            list.appendChild(el);
        });
    }

    var roPanelBtn   = document.getElementById('rpv-ro-panel-btn');
    var roPanelClose = document.getElementById('rpv-ro-panel-close');
    if (roPanelBtn) roPanelBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        var panel = document.getElementById('rpv-panel'); if (panel) panel.classList.toggle('open');
        buildPanel();
    });
    if (roPanelClose) roPanelClose.addEventListener('click', function () {
        var panel = document.getElementById('rpv-panel'); if (panel) panel.classList.remove('open');
    });

    /* ── Compute base scale ── */
    function computeBase(page) {
        var cw = wrap ? wrap.clientWidth : 800;
        var nw = page.getViewport({ scale: 1 }).width;
        baseScale = Math.max(.5, Math.min((cw - 24) / nw, 2.5));
        needsRecompute = false;
    }

    /* ── Render halaman ── */
    function renderPage(num) {
        if (num < 1 || (pdfDoc && num > pdfDoc.numPages)) return;
        if (pageRendering) { pendingPage = num; return; }
        pageRendering = true; pageNum = num;

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
            if (loadingEl) loadingEl.classList.add('hidden');

            var piEl = document.getElementById('rpv-ro-page-input'); if (piEl) piEl.value = num;
            var prev = document.getElementById('rpv-ro-prev'); if (prev) prev.disabled = num <= 1;
            var next = document.getElementById('rpv-ro-next'); if (next) next.disabled = !pdfDoc || num >= pdfDoc.numPages;
            var pct  = pdfDoc ? num / pdfDoc.numPages * 100 : 0;
            var prog = document.getElementById('rpv-ro-progress'); if (prog) prog.style.width = pct + '%';
            var zv   = document.getElementById('rpv-ro-zoom-val'); if (zv) zv.textContent = Math.round(zoomFactor * 100) + '%';
            if (wrap) wrap.scrollTo({ top: 0, behavior: 'smooth' });

            doRender();

        }).catch(function (e) {
            console.error('[RPV-RO] render error:', e);
            pageRendering = false;
            if (loadingEl) loadingEl.classList.add('hidden');
            stage.style.display = 'block';
        });
    }

    function prevPage() { if (pageNum > 1) renderPage(pageNum - 1); }
    function nextPage() { if (pdfDoc && pageNum < pdfDoc.numPages) renderPage(pageNum + 1); }
    function doZoom(dir) {
        zoomFactor = dir > 0 ? Math.min(zoomFactor + ZOOM_STEP, ZOOM_MAX) : Math.max(zoomFactor - ZOOM_STEP, ZOOM_MIN);
        needsRecompute = true;
        var zv = document.getElementById('rpv-ro-zoom-val'); if (zv) zv.textContent = Math.round(zoomFactor * 100) + '%';
        if (pdfDoc) renderPage(pageNum);
    }

    var roPrev = document.getElementById('rpv-ro-prev'); if (roPrev) roPrev.addEventListener('click', prevPage);
    var roNext = document.getElementById('rpv-ro-next'); if (roNext) roNext.addEventListener('click', nextPage);
    var roZI   = document.getElementById('rpv-ro-zoom-in');  if (roZI) roZI.addEventListener('click', function () { doZoom(1); });
    var roZO   = document.getElementById('rpv-ro-zoom-out'); if (roZO) roZO.addEventListener('click', function () { doZoom(-1); });
    var roPI   = document.getElementById('rpv-ro-page-input');
    if (roPI) roPI.addEventListener('change', function () {
        var n = parseInt(this.value);
        if (pdfDoc && n >= 1 && n <= pdfDoc.numPages) renderPage(n); else this.value = pageNum;
    });

    /* Keyboard: navigasi & zoom saja */
    document.addEventListener('keydown', function (e) {
        if (['INPUT','TEXTAREA'].includes(e.target.tagName)) return;
        if (e.key === 'ArrowLeft')  prevPage();
        if (e.key === 'ArrowRight') nextPage();
        if (e.key === '+' || e.key === '=') doZoom(1);
        if (e.key === '-') doZoom(-1);
    });

    /* Resize */
    var resT = null, lastW = wrap ? wrap.clientWidth : 0;
    window.addEventListener('resize', function () {
        var w = wrap ? wrap.clientWidth : 0; if (Math.abs(w - lastW) < 20) return; lastW = w;
        clearTimeout(resT); resT = setTimeout(function () { if (!pdfDoc) return; needsRecompute = true; renderPage(pageNum); }, 250);
    });

    /* ── Load PDF ── */
    stage.style.display = 'none';
    if (loadingEl) { loadingEl.classList.remove('hidden'); loadingEl.style.display = ''; }

    /* Anotasi sudah ada dari server (tidak perlu fetch lagi) */
    annots = normalize(CFG.annotations || []);
    updateBadge();

    pdfjsLib.getDocument({ url: CFG.pdfUrl, withCredentials: false, verbosity: 0 })
        .promise.then(function (doc) {
            pdfDoc = doc;
            var ptEl = document.getElementById('rpv-ro-page-total'); if (ptEl) ptEl.textContent = doc.numPages;
            var piEl = document.getElementById('rpv-ro-page-input'); if (piEl) piEl.max = doc.numPages;
            renderPage(1);
        }).catch(function (err) {
            console.error('[RPV-RO] load error:', err);
            if (loadingEl) loadingEl.innerHTML = '<div style="font-size:2rem">⚠️</div><p style="color:#ef4444;font-weight:700;font-size:13px;margin:0;">Gagal memuat PDF</p><p style="color:#6b7280;font-size:11px;margin:.25rem 0;">'+err.message+'</p>';
        });

})();
</script>

@endif