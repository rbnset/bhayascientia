/**
 * pdf-annotations.js — v4.0
 * public/js/pdf-annotations.js
 *
 * FITUR (setara review-pdf-viewer.js v6.0):
 * FEAT-1  Highlight, Underline, Strikethrough, Comment
 * FEAT-5  Copy-text mode (seleksi teks, salin ke clipboard)
 * FEAT-8  Edit anotasi inline (popup edit komentar/sticky)
 * FEAT-9  Freehand brush mode (ukuran & opasitas berbeda)
 * FEAT-10 Shape preview SVG realtime saat drag
 * FEAT-11 Arrow & line shape presisi (arrow_x1/y1/x2/y2)
 * FEAT-13 Sticky note drag-and-drop + edit + animasi hapus
 * FEAT-14 Teks bebas (free text) on-canvas
 * FEAT-15 Panel anotasi dengan edit & delete per item
 * FEAT-16 Undo / Redo (Ctrl+Z / Ctrl+Y)
 * FEAT-17 Eraser cursor custom
 * FEAT-18 Read-only mode (jika RPV_CONFIG.readOnly)
 * FIX-5   ResizeObserver untuk syncFC, tidak trigger loop
 * FIX-6   applyTextLayerPE dipanggil ulang setelah renderPage
 */
(function () {
    'use strict';

    /* ── BOOTSTRAP ────────────────────────────────────────────────── */
    function bootstrap() {
        if (window._pdfViewer) { init(window._pdfViewer); return; }

        let resolved = false;
        function tryInit() {
            if (resolved) return;
            if (window._pdfViewer) { resolved = true; init(window._pdfViewer); }
        }

        window.addEventListener('pdf-viewer-ready', tryInit, { once: true });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                (typeof requestIdleCallback !== 'undefined') ? requestIdleCallback(tryInit) : setTimeout(tryInit, 0);
            }, { once: true });
        } else {
            (typeof requestIdleCallback !== 'undefined') ? requestIdleCallback(tryInit) : setTimeout(tryInit, 0);
        }

        setTimeout(function () {
            if (!resolved) console.error('[annot] _pdfViewer tidak tersedia setelah 15s.');
        }, 15000);
    }

    /* ── MAIN INIT ────────────────────────────────────────────────── */
    function init(V) {
        if (window._pdfAnnotations) { console.log('[annot] already init'); return; }

        /* ── CONFIG ──────────────────────────────────────────────── */
        const slug = window.PDF_CONFIG?.slug || 'unknown';
        const IS_RO = !!(window.PDF_CONFIG?.readOnly); /* read-only mode */
        const API = '/api/annotations/' + slug;

        const VALID_TYPES = ['highlight', 'underline', 'strikethrough', 'freehand', 'comment', 'sticky', 'shape', 'text', 'copy-text'];
        const VALID_COLORS = ['yellow', 'green', 'red', 'blue', 'orange', 'black', 'white', 'pink', 'purple', 'cyan'];
        const VALID_SHAPES = ['rect', 'ellipse', 'arrow', 'line'];

        const COLORS = {
            yellow: '#FFD700', green: '#4ADE80', red: '#EF4444', blue: '#60A5FA',
            orange: '#FF6B18', black: '#111111', white: '#FFFFFF',
            pink: '#F472B6', purple: '#A78BFA', cyan: '#22D3EE',
        };
        const STICKY_BG = {
            yellow: '#FEF9C3', green: '#DCFCE7', red: '#FEE2E2', blue: '#DBEAFE',
            orange: '#FFEDD5', pink: '#FCE7F3', purple: '#EDE9FE', cyan: '#CFFAFE',
            black: '#1F2937', white: '#F9FAFB',
        };
        const STICKY_BORDER = {
            yellow: '#FDE047', green: '#86EFAC', red: '#FCA5A5', blue: '#93C5FD',
            orange: '#FDBA74', pink: '#F9A8D4', purple: '#C4B5FD', cyan: '#67E8F9',
            black: '#374151', white: '#D1D5DB',
        };
        const hex = function (n) { return COLORS[n] || '#FFD700'; };

        function csrf() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
        function hdrs() {
            return {
                'Content-Type': 'application/json', 'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest',
            };
        }
        function esc(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        }

        /* ── STATE ───────────────────────────────────────────────── */
        let annots = [];
        let undoStack = [], redoStack = [];

        let activeTool = IS_RO ? 'select' : 'highlight';
        let activeColor = 'yellow';
        let activeSize = 2;
        let activeShape = 'rect';

        let isDrawing = false, drawStart = null;
        let freePoints = [], shapePreviewSVGEl = null;
        let pendingRect = null, pendingText = null;
        let stickyPos = null, textPos = null;
        let selectedId = null;
        let isPanning = false, panSX = 0, panSY = 0, panScrollX = 0, panScrollY = 0;
        let renderPending = false;

        /* ── DOM ─────────────────────────────────────────────────── */
        const stage = V.stage;
        const annotLayer = V.annotLayer;
        const textLayer = V.textLayer;
        const canvasWrap = document.getElementById('pdf-canvas-wrapper');
        const mainCanvas = document.getElementById('pdf-canvas');
        const freeCanvas = document.getElementById('freehand-canvas');
        const freeCtx = freeCanvas ? freeCanvas.getContext('2d') : null;

        const commentPop = document.getElementById('comment-popup');
        const commentTxt = document.getElementById('comment-text');
        const commentSave = document.getElementById('comment-save');
        const commentCancel = document.getElementById('comment-cancel');
        const stickyPop = document.getElementById('sticky-popup');
        const stickyTxt = document.getElementById('sticky-text');
        const stickySave = document.getElementById('sticky-save');
        const stickyCancel = document.getElementById('sticky-cancel');
        const annotTip = document.getElementById('annot-tooltip');
        const tipTxt = document.getElementById('annot-tooltip-text');
        const tipDel = document.getElementById('annot-tooltip-del');
        const tipClose = document.getElementById('annot-tooltip-close');
        const tipEdit = document.getElementById('annot-tooltip-edit');
        const annotPanel = document.getElementById('annot-panel');
        const apList = document.getElementById('ap-list');
        const apClose = document.getElementById('ap-close-btn');
        const apClear = document.getElementById('ap-clear-btn');
        const panelBadge = document.getElementById('ab-panel-badge');
        const panelBtn = document.getElementById('aft-panel-btn');
        const undoBtn = document.getElementById('aft-undo');
        const redoBtn = document.getElementById('aft-redo');
        const syncEl = document.getElementById('annot-sync-indicator');
        const syncTxtEl = document.getElementById('annot-sync-text');
        const eraserCur = document.getElementById('eraser-cursor');

        /* FIX-3: overflow-x hidden */
        if (canvasWrap) canvasWrap.style.overflowX = 'hidden';

        /* ── UTILS ───────────────────────────────────────────────── */
        function snack(msg, col) { V.snack(msg, col); }

        function stageXY(e) {
            const r = stage.getBoundingClientRect();
            const s = e.changedTouches?.[0] ?? e.touches?.[0] ?? e;
            return { x: s.clientX - r.left, y: s.clientY - r.top };
        }

        /* FIX-5: syncFC dengan guard */
        function syncFC() {
            if (!freeCanvas) return;
            const w = stage.offsetWidth, h = stage.offsetHeight;
            if (freeCanvas.width !== w || freeCanvas.height !== h) {
                freeCanvas.width = w; freeCanvas.height = h;
            }
            freeCanvas.style.width = w + 'px';
            freeCanvas.style.height = h + 'px';
        }

        /* FIX-6: pointer-events textLayer */
        function applyTextLayerPE() {
            const needsSel = ['highlight', 'comment', 'underline', 'strikethrough', 'copy-text'].includes(activeTool);
            if (!textLayer) return;
            textLayer.style.pointerEvents = needsSel ? 'auto' : 'none';
            textLayer.style.userSelect = needsSel ? 'text' : 'none';
            textLayer.style.webkitUserSelect = needsSel ? 'text' : 'none';
        }

        /* ── SYNC INDICATOR ──────────────────────────────────────── */
        let syncT = null;
        function showSync(msg, ok) {
            ok = !!ok;
            if (!syncEl) return;
            if (syncTxtEl) syncTxtEl.textContent = msg;
            syncEl.style.borderColor = ok ? '#22c55e' : '#FF6B18';
            syncEl.style.color = ok ? '#22c55e' : '#FF6B18';
            syncEl.classList.add('show');
            clearTimeout(syncT);
            syncT = setTimeout(function () { syncEl.classList.remove('show'); }, ok ? 1800 : 4000);
        }

        /* ── PAYLOAD SANITIZER ───────────────────────────────────── */
        function sanitize(raw) {
            let type = (raw.type === 'brush') ? 'freehand' : raw.type;
            if (!VALID_TYPES.includes(type)) type = 'highlight';
            const color = VALID_COLORS.includes(raw.color) ? raw.color : 'yellow';
            const p = {
                page: parseInt(raw.page) || V.pageNum,
                type: type,
                color: color,
                rect_x: raw.rect ? raw.rect.x : (raw.rect_x ?? null),
                rect_y: raw.rect ? raw.rect.y : (raw.rect_y ?? null),
                rect_w: raw.rect ? raw.rect.w : (raw.rect_w ?? null),
                rect_h: raw.rect ? raw.rect.h : (raw.rect_h ?? null),
                selected_text: raw.selected_text || null,
                comment: raw.comment || null,
                path_points: Array.isArray(raw.path_points) ? raw.path_points : null,
                shape_type: VALID_SHAPES.includes(raw.shape_type) ? raw.shape_type : null,
                stroke_width: (typeof raw.stroke_width === 'number' && raw.stroke_width > 0) ? raw.stroke_width : 2,
                fill_opacity: (typeof raw.fill_opacity === 'number') ? raw.fill_opacity : 0,
                arrow_x1: typeof raw.arrow_x1 === 'number' ? raw.arrow_x1 : null,
                arrow_y1: typeof raw.arrow_y1 === 'number' ? raw.arrow_y1 : null,
                arrow_x2: typeof raw.arrow_x2 === 'number' ? raw.arrow_x2 : null,
                arrow_y2: typeof raw.arrow_y2 === 'number' ? raw.arrow_y2 : null,
            };
            if (p.type === 'shape' && !p.shape_type) p.shape_type = 'rect';
            return p;
        }

        function normalizeAnnot(a) {
            if (!a.rect && a.rect_x != null)
                a.rect = { x: +a.rect_x, y: +a.rect_y, w: +a.rect_w, h: +a.rect_h };
            if (a.type === 'shape' && (a.shape_type === 'arrow' || a.shape_type === 'line')) {
                if (a.arrow_x1 == null && Array.isArray(a.path_points) && a.path_points.length >= 2) {
                    a.arrow_x1 = +a.path_points[0][0]; a.arrow_y1 = +a.path_points[0][1];
                    a.arrow_x2 = +a.path_points[1][0]; a.arrow_y2 = +a.path_points[1][1];
                }
            }
            return a;
        }

        /* ── API ─────────────────────────────────────────────────── */
        async function apiLoad() {
            try {
                const r = await fetch(API, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!r.ok) throw new Error(r.status);
                const j = await r.json();
                return (Array.isArray(j.data) ? j.data : []).map(normalizeAnnot);
            } catch (e) { console.error('[annot] load:', e); return []; }
        }

        async function apiSave(payload) {
            const clean = sanitize(payload);
            showSync('Menyimpan...');
            try {
                const r = await fetch(API, {
                    method: 'POST', credentials: 'same-origin',
                    headers: hdrs(), body: JSON.stringify(clean),
                });
                const j = await r.json();
                if (!r.ok) { showSync('Gagal: ' + (j.message || r.status)); return null; }
                showSync('Tersimpan ✓', true);
                const saved = j.data || null;
                if (saved) {
                    normalizeAnnot(saved);
                    /* fallback arrow coords jika server tidak kembalikan */
                    if (saved.type === 'shape' && saved.arrow_x1 == null && clean.arrow_x1 != null) {
                        saved.arrow_x1 = clean.arrow_x1; saved.arrow_y1 = clean.arrow_y1;
                        saved.arrow_x2 = clean.arrow_x2; saved.arrow_y2 = clean.arrow_y2;
                    }
                }
                return saved;
            } catch (e) { console.error('[annot] save:', e); showSync('Error jaringan'); return null; }
        }

        async function apiPatch(id, payload) {
            try {
                await fetch(API + '/' + id, {
                    method: 'PUT', credentials: 'same-origin',
                    headers: hdrs(), body: JSON.stringify(payload),
                });
            } catch (e) { console.error('[annot] patch:', e); }
        }

        async function apiDel(id) {
            showSync('Menghapus...');
            try {
                await fetch(API + '/' + id, { method: 'DELETE', credentials: 'same-origin', headers: hdrs() });
                showSync('Dihapus ✓', true);
            } catch (e) { console.error('[annot] del:', e); }
        }

        async function apiDelPage(pg) {
            showSync('Membersihkan...');
            try {
                await fetch(API + '/page/' + pg, { method: 'DELETE', credentials: 'same-origin', headers: hdrs() });
                showSync('Selesai ✓', true);
            } catch (e) { console.error('[annot] delPage:', e); }
        }

        async function loadAll() {
            annots = await apiLoad();
            console.log('[annot] loaded', annots.length);
            scheduleRender(); updateBadge(); updateUndoRedo();
        }

        /* ── RENDER ──────────────────────────────────────────────── */
        function scheduleRender() {
            if (renderPending) return; renderPending = true;
            requestAnimationFrame(function () { renderPending = false; doRender(); });
        }

        function doRender() {
            const s = V.getScale();
            if (!annotLayer) return;
            annotLayer.innerHTML = '';
            annotLayer.style.pointerEvents = 'none';
            syncFC();
            if (freeCtx && freeCanvas) freeCtx.clearRect(0, 0, freeCanvas.width, freeCanvas.height);
            stage.querySelectorAll('.sticky-note,.annot-freetext').forEach(function (e) { e.remove(); });

            annots.filter(function (a) { return a.page === V.pageNum; }).forEach(function (a) {
                switch (a.type) {
                    case 'highlight': case 'comment': rHL(a, s); break;
                    case 'underline': rUL(a, s); break;
                    case 'strikethrough': rST(a, s); break;
                    case 'freehand': rFH(a, s); break;
                    case 'shape': rSH(a, s); break;
                    case 'sticky': rSticky(a, s); break;
                    case 'text': rText(a, s); break;
                }
            });
            updateBadge();
        }

        /* ── RENDER FUNCTIONS ────────────────────────────────────── */
        function rHL(a, s) {
            if (!a.rect) return;
            const el = document.createElement('div'), sel = selectedId == a.id;
            el.dataset.annotId = String(a.id);
            el.style.cssText =
                'position:absolute;left:' + (a.rect.x * s) + 'px;top:' + (a.rect.y * s) +
                'px;width:' + (a.rect.w * s) + 'px;height:' + (a.rect.h * s) +
                'px;background:' + hex(a.color) + ';opacity:' + (sel ? .75 : .38) +
                ';border-radius:2px;pointer-events:auto;cursor:pointer;z-index:5;' +
                'outline:' + (sel ? '2px solid #FF6B18' : 'none') + ';transition:opacity .12s;';
            if (a.type === 'comment' && a.comment) {
                const dot = document.createElement('span');
                dot.style.cssText = 'position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:#60A5FA;border-radius:50%;pointer-events:none;';
                el.appendChild(dot);
            }
            attachEv(el, a); annotLayer.appendChild(el);
        }

        function rUL(a, s) {
            if (!a.rect) return;
            const el = document.createElement('div'); el.dataset.annotId = String(a.id);
            const t = Math.max(1.5, 2 * s);
            el.style.cssText = 'position:absolute;left:' + (a.rect.x * s) + 'px;top:' + ((a.rect.y + a.rect.h) * s - t) + 'px;width:' + (a.rect.w * s) + 'px;height:' + t + 'px;background:' + hex(a.color) + ';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';
            attachEv(el, a); annotLayer.appendChild(el);
        }

        function rST(a, s) {
            if (!a.rect) return;
            const el = document.createElement('div'); el.dataset.annotId = String(a.id);
            const t = Math.max(1.5, 2 * s), top = a.rect.y * s + a.rect.h * s * 0.62 - t / 2;
            el.style.cssText = 'position:absolute;left:' + (a.rect.x * s) + 'px;top:' + top + 'px;width:' + (a.rect.w * s) + 'px;height:' + t + 'px;background:' + hex(a.color) + ';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';
            attachEv(el, a); annotLayer.appendChild(el);
        }

        function rFH(a, s) {
            if (!a.path_points || !a.path_points.length || !freeCtx) return;
            const pts = a.path_points;
            freeCtx.save();
            freeCtx.strokeStyle = hex(a.color); freeCtx.lineWidth = (a.stroke_width || 2) * s;
            freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = .92;
            freeCtx.beginPath(); freeCtx.moveTo(pts[0][0] * s, pts[0][1] * s);
            for (let i = 1; i < pts.length; i++) freeCtx.lineTo(pts[i][0] * s, pts[i][1] * s);
            freeCtx.stroke(); freeCtx.restore();
            if (a.rect && (a.rect.w > 0 || a.rect.h > 0)) {
                const hit = document.createElement('div'); hit.dataset.annotId = String(a.id);
                hit.style.cssText = 'position:absolute;left:' + ((a.rect.x - 8) * s) + 'px;top:' + ((a.rect.y - 8) * s) + 'px;width:' + ((a.rect.w + 16) * s) + 'px;height:' + ((a.rect.h + 16) * s) + 'px;background:transparent;pointer-events:auto;cursor:pointer;z-index:6;';
                attachEv(hit, a); annotLayer.appendChild(hit);
            }
        }

        function rSH(a, s) {
            if (!a.rect) return;
            const col = hex(a.color), sel = selectedId == a.id;
            const sw = Math.max(1, (a.stroke_width || 2) * s);
            const st = a.shape_type || 'rect';
            const el = document.createElement('div'); el.dataset.annotId = String(a.id);

            if (st === 'arrow' || st === 'line') {
                /* FEAT-11: Arrow & line presisi */
                const ax1 = a.arrow_x1 != null ? a.arrow_x1 * s : a.rect.x * s;
                const ay1 = a.arrow_y1 != null ? a.arrow_y1 * s : (a.rect.y + a.rect.h / 2) * s;
                const ax2 = a.arrow_x2 != null ? a.arrow_x2 * s : (a.rect.x + a.rect.w) * s;
                const ay2 = a.arrow_y2 != null ? a.arrow_y2 * s : (a.rect.y + a.rect.h / 2) * s;
                const bx = Math.min(ax1, ax2) - sw * 2, by = Math.min(ay1, ay2) - sw * 2;
                const bw = Math.abs(ax2 - ax1) + sw * 4, bh = Math.abs(ay2 - ay1) + sw * 4;
                const lx1 = ax1 - bx, ly1 = ay1 - by, lx2 = ax2 - bx, ly2 = ay2 - by;
                el.style.cssText = 'position:absolute;left:' + bx + 'px;top:' + by + 'px;width:' + bw + 'px;height:' + bh + 'px;pointer-events:auto;cursor:pointer;z-index:5;outline:' + (sel ? '2px dashed #FF6B18' : 'none') + ';';
                let svg = '';
                if (st === 'line') {
                    svg = '<line x1="' + lx1 + '" y1="' + ly1 + '" x2="' + lx2 + '" y2="' + ly2 + '" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round"/>';
                } else {
                    const dx = lx2 - lx1, dy = ly2 - ly1, len = Math.sqrt(dx * dx + dy * dy);
                    if (len > 1) {
                        const hLen = Math.min(len * .35, Math.max(10, sw * 5)), ang = Math.atan2(dy, dx);
                        const hx1 = lx2 - hLen * Math.cos(ang - Math.PI / 6), hy1 = ly2 - hLen * Math.sin(ang - Math.PI / 6);
                        const hx2 = lx2 - hLen * Math.cos(ang + Math.PI / 6), hy2 = ly2 - hLen * Math.sin(ang + Math.PI / 6);
                        svg = '<line x1="' + lx1 + '" y1="' + ly1 + '" x2="' + lx2 + '" y2="' + ly2 + '" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round"/><polyline points="' + hx1 + ',' + hy1 + ' ' + lx2 + ',' + ly2 + ' ' + hx2 + ',' + hy2 + '" fill="none" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round" stroke-linejoin="round"/>';
                    }
                }
                el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="' + bw + '" height="' + bh + '" style="overflow:visible;display:block;pointer-events:none">' + svg + '</svg>';
            } else {
                const x = a.rect.x * s, y = a.rect.y * s;
                const w = Math.max(4, a.rect.w * s), h = Math.max(4, a.rect.h * s);
                el.style.cssText = 'position:absolute;left:' + x + 'px;top:' + y + 'px;width:' + w + 'px;height:' + h + 'px;pointer-events:auto;cursor:pointer;z-index:5;outline:' + (sel ? '2px dashed #FF6B18' : 'none') + ';';
                let svg = '';
                if (st === 'rect')
                    svg = '<rect x="' + (sw / 2) + '" y="' + (sw / 2) + '" width="' + Math.max(1, w - sw) + '" height="' + Math.max(1, h - sw) + '" rx="2" fill="none" stroke="' + col + '" stroke-width="' + sw + '"/>';
                else if (st === 'ellipse')
                    svg = '<ellipse cx="' + (w / 2) + '" cy="' + (h / 2) + '" rx="' + Math.max(1, w / 2 - sw / 2) + '" ry="' + Math.max(1, h / 2 - sw / 2) + '" fill="none" stroke="' + col + '" stroke-width="' + sw + '"/>';
                el.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="' + w + '" height="' + h + '" style="overflow:visible;display:block;pointer-events:none">' + svg + '</svg>';
            }
            attachEv(el, a); annotLayer.appendChild(el);
        }

        /* FEAT-13: Sticky note */
        function rSticky(a, s) {
            if (!a.rect) return;
            const note = document.createElement('div');
            note.className = 'sticky-note'; note.dataset.annotId = String(a.id); note.dataset.color = a.color || 'yellow';
            note.style.left = (a.rect.x * s) + 'px'; note.style.top = (a.rect.y * s) + 'px';
            note.innerHTML =
                '<div class="sn-header"><span>📌</span>' +
                '<div style="display:flex;gap:3px;">' +
                '<button type="button" class="sn-edit" title="Edit">✏️</button>' +
                '<button type="button" class="sn-del" title="Hapus">×</button>' +
                '</div></div>' +
                '<div class="sn-body">' + esc(a.comment) + '</div>';
            note.querySelector('.sn-del').addEventListener('click', function (ev) { ev.stopPropagation(); stickyAnim(note, a.id); });
            note.querySelector('.sn-edit').addEventListener('click', function (ev) { ev.stopPropagation(); openEditPopup(a); });
            note.addEventListener('click', function (ev) {
                if (activeTool === 'eraser') { ev.stopPropagation(); stickyAnim(note, a.id); return; }
                ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY);
            });
            makeDraggable(note, a, s); stage.appendChild(note);
        }

        function stickyAnim(el, id) {
            el.style.transition = 'opacity .18s,transform .18s';
            el.style.opacity = '0'; el.style.transform = 'scale(.85)';
            setTimeout(async function () { el.remove(); await removeAnnot(id); }, 180);
        }

        function rText(a, s) {
            if (!a.rect) return;
            const fontSize = Math.max(10, (a.stroke_width || 14)) * s;
            const el = document.createElement('div');
            el.className = 'annot-freetext'; el.dataset.annotId = String(a.id);
            el.style.cssText = 'position:absolute;left:' + (a.rect.x * s) + 'px;top:' + (a.rect.y * s) + 'px;font-size:' + fontSize + 'px;line-height:1.4;color:' + hex(a.color) + ';pointer-events:auto;cursor:pointer;z-index:8;white-space:pre-wrap;word-break:break-word;max-width:' + (300 * s) + 'px;font-family:sans-serif;font-weight:600;text-shadow:0 1px 3px rgba(0,0,0,.35);user-select:none;';
            el.textContent = a.comment || '';
            el.addEventListener('click', function (ev) {
                if (activeTool === 'eraser') { ev.stopPropagation(); removeAnnot(a.id); return; }
                ev.stopPropagation(); showTip(a, ev.clientX, ev.clientY);
            });
            stage.appendChild(el);
        }

        function attachEv(el, a) {
            el.addEventListener('click', function (ev) {
                ev.stopPropagation();
                if (activeTool === 'eraser') { removeAnnot(a.id); return; }
                if (activeTool === 'select') { selectedId = selectedId == a.id ? null : String(a.id); scheduleRender(); return; }
                showTip(a, ev.clientX, ev.clientY);
            });
            el.addEventListener('touchend', function (ev) {
                ev.stopPropagation(); if (ev.cancelable) ev.preventDefault();
                const t = ev.changedTouches[0];
                if (activeTool === 'eraser') { removeAnnot(a.id); return; }
                if (activeTool === 'select') { selectedId = selectedId == a.id ? null : String(a.id); scheduleRender(); return; }
                showTip(a, t.clientX, t.clientY);
            }, { passive: false });
        }

        /* FEAT-13: drag sticky */
        function makeDraggable(el, annotData, s) {
            let ox = 0, oy = 0, dragging = false, moved = false;
            function onDown(e) {
                if (['sn-del', 'sn-edit', 'sn-body'].some(function (c) { return e.target.classList.contains(c); })) return;
                dragging = true; moved = false;
                const src = e.touches ? e.touches[0] : e;
                ox = src.clientX - el.offsetLeft; oy = src.clientY - el.offsetTop;
                el.style.zIndex = '20'; e.stopPropagation(); if (e.cancelable) e.preventDefault();
            }
            function onMove(e) {
                if (!dragging) return; moved = true;
                const src = e.touches ? e.touches[0] : e;
                el.style.left = (src.clientX - ox) + 'px'; el.style.top = (src.clientY - oy) + 'px';
                if (e.cancelable) e.preventDefault();
            }
            async function onUp() {
                if (!dragging) return; dragging = false; el.style.zIndex = '9'; if (!moved) return;
                const nx = parseFloat(el.style.left) / s, ny = parseFloat(el.style.top) / s;
                const idx = annots.findIndex(function (a) { return String(a.id) === String(annotData.id); });
                if (idx >= 0 && annots[idx].rect) { annots[idx].rect.x = nx; annots[idx].rect.y = ny; }
                await apiPatch(annotData.id, { rect_x: nx, rect_y: ny, rect_w: annotData.rect ? annotData.rect.w : 180, rect_h: annotData.rect ? annotData.rect.h : 90 });
            }
            el.addEventListener('mousedown', onDown, { passive: false });
            el.addEventListener('touchstart', onDown, { passive: false });
            document.addEventListener('mousemove', onMove, { passive: false });
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('mouseup', onUp);
            document.addEventListener('touchend', onUp);
        }

        /* ── TOOLTIP ─────────────────────────────────────────────── */
        function showTip(a, cx, cy) {
            if (!annotTip) return;
            const ic = { highlight: '✏️', underline: '__', strikethrough: '~~', freehand: '🖊', shape: '⬛', comment: '💬', sticky: '📌', text: '🔤' };
            let txt = (ic[a.type] || '•') + ' ' + a.type;
            if (a.comment) txt = (ic[a.type] || '•') + ' ' + a.comment.substring(0, 80);
            else if (a.selected_text) txt = (ic[a.type] || '•') + ' "' + a.selected_text.substring(0, 60) + '"';
            if (tipTxt) { tipTxt.textContent = txt; tipTxt.dataset.annotId = String(a.id); }
            if (tipEdit) {
                tipEdit.style.display = ['comment', 'sticky', 'text'].includes(a.type) ? '' : 'none';
                tipEdit.dataset.annotId = String(a.id);
            }
            annotTip.classList.add('show');
            const vw = window.innerWidth, vh = window.innerHeight;
            annotTip.style.left = Math.max(4, Math.min(cx - 135, vw - 278)) + 'px';
            annotTip.style.top = ((cy + 140 > vh) ? Math.max(4, cy - 140) : cy + 8) + 'px';
        }
        if (tipClose) tipClose.addEventListener('click', function () { annotTip && annotTip.classList.remove('show'); });
        if (tipDel) tipDel.addEventListener('click', async function () {
            const id = tipTxt && tipTxt.dataset.annotId;
            if (annotTip) annotTip.classList.remove('show');
            if (id) await removeAnnot(id);
        });
        if (tipEdit) tipEdit.addEventListener('click', function () {
            const id = tipEdit.dataset.annotId;
            if (annotTip) annotTip.classList.remove('show');
            if (id) { const a = annots.find(function (x) { return String(x.id) === id; }); if (a) openEditPopup(a); }
        });
        document.addEventListener('click', function (e) {
            if (annotTip && annotTip.classList.contains('show')) {
                if (annotTip.contains(e.target)) return;
                if (e.target.closest('[data-annot-id],.sticky-note,.annot-freetext')) return;
                annotTip.classList.remove('show');
            }
        });

        /* ── EDIT POPUP (FEAT-8) ─────────────────────────────────── */
        function openEditPopup(a) {
            let pop = document.getElementById('annot-edit-popup');
            if (!pop) {
                pop = document.createElement('div'); pop.id = 'annot-edit-popup';
                pop.style.cssText = 'position:fixed;z-index:99995;background:#1a1a1a;border:2px solid #FF6B18;border-radius:14px;padding:.875rem;width:min(300px,90vw);box-shadow:0 12px 40px rgba(0,0,0,.6);display:none;';
                pop.innerHTML =
                    '<p style="font-size:12px;font-weight:700;color:#FF6B18;margin:0 0 .5rem;">✏️ Edit Anotasi</p>' +
                    '<textarea id="annot-edit-txt" style="width:100%;background:#2d2d2d;border:1.5px solid #3d3d3d;color:#fff;border-radius:8px;padding:.5rem;font-size:13px;resize:none;outline:none;height:80px;display:block;box-sizing:border-box;"></textarea>' +
                    '<div style="display:flex;gap:.4rem;margin-top:.5rem;">' +
                    '<button type="button" id="annot-edit-save" style="flex:1;padding:.5rem;background:#FF6B18;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Simpan</button>' +
                    '<button type="button" id="annot-edit-cancel" style="padding:.5rem .75rem;background:#2d2d2d;color:#9ca3af;border:none;border-radius:8px;font-size:12px;cursor:pointer;">Batal</button>' +
                    '</div>';
                document.body.appendChild(pop);
                document.getElementById('annot-edit-cancel').addEventListener('click', function () { pop.style.display = 'none'; });
                document.addEventListener('click', function (e) {
                    if (pop.style.display !== 'none' && !pop.contains(e.target) && !e.target.closest('#annot-tooltip')) pop.style.display = 'none';
                });
            }
            const txt = document.getElementById('annot-edit-txt'); txt.value = a.comment || '';
            pop.style.left = Math.max(4, Math.min(window.innerWidth / 2 - 150, window.innerWidth - 304)) + 'px';
            pop.style.top = Math.max(4, window.innerHeight / 2 - 85) + 'px';
            pop.style.display = 'block';
            setTimeout(function () { txt.focus(); txt.select(); }, 40);
            const oldBtn = document.getElementById('annot-edit-save');
            const newBtn = oldBtn.cloneNode(true); oldBtn.parentNode.replaceChild(newBtn, oldBtn);
            newBtn.addEventListener('click', async function () {
                const v = txt.value.trim(); if (!v) { snack('Tidak boleh kosong!'); return; }
                pop.style.display = 'none';
                await apiPatch(a.id, { comment: v });
                const idx = annots.findIndex(function (x) { return String(x.id) === String(a.id); });
                if (idx >= 0) annots[idx].comment = v;
                scheduleRender(); snack('✓ Diperbarui', '#22c55e');
            });
        }

        /* ── ADD / REMOVE ────────────────────────────────────────── */
        async function addAnnot(payload) {
            if (IS_RO) { snack('Mode baca — anotasi tidak diizinkan', '#60A5FA'); return null; }
            const saved = await apiSave(payload); if (!saved) return null;
            annots.push(saved);
            undoStack.push({ action: 'add', data: saved }); redoStack = [];
            updateUndoRedo(); scheduleRender(); return saved;
        }

        async function removeAnnot(id) {
            const a = annots.find(function (x) { return String(x.id) === String(id); }); if (!a) return;
            await apiDel(a.id);
            annots = annots.filter(function (x) { return String(x.id) !== String(id); });
            if (selectedId === String(id)) selectedId = null;
            undoStack.push({ action: 'del', data: a }); redoStack = [];
            updateUndoRedo(); scheduleRender(); snack('🗑 Anotasi dihapus');
        }

        /* ── UNDO / REDO (FEAT-16) ───────────────────────────────── */
        function updateUndoRedo() {
            if (undoBtn) undoBtn.disabled = !undoStack.length;
            if (redoBtn) redoBtn.disabled = !redoStack.length;
        }
        async function doUndo() {
            if (!undoStack.length) return;
            const op = undoStack.pop();
            if (op.action === 'add') {
                const a = annots.find(function (x) { return String(x.id) === String(op.data.id); });
                if (a) { await apiDel(a.id); annots = annots.filter(function (x) { return String(x.id) !== String(a.id); }); redoStack.push({ action: 'readd', data: a }); }
            } else if (op.action === 'del') {
                const saved = await apiSave(op.data);
                if (saved) { annots.push(saved); redoStack.push({ action: 'redel', data: saved }); }
            }
            updateUndoRedo(); scheduleRender();
        }
        async function doRedo() {
            if (!redoStack.length) return;
            const op = redoStack.pop();
            if (op.action === 'readd') {
                const saved = await apiSave(op.data);
                if (saved) { annots.push(saved); undoStack.push({ action: 'add', data: saved }); }
            } else if (op.action === 'redel') {
                const a = annots.find(function (x) { return String(x.id) === String(op.data.id); });
                if (a) { await apiDel(a.id); annots = annots.filter(function (x) { return String(x.id) !== String(a.id); }); undoStack.push({ action: 'del', data: a }); }
            }
            updateUndoRedo(); scheduleRender();
        }
        if (undoBtn) undoBtn.addEventListener('click', doUndo);
        if (redoBtn) redoBtn.addEventListener('click', doRedo);
        document.addEventListener('keydown', function (e) {
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') { e.preventDefault(); doUndo(); }
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); doRedo(); }
            if ((e.key === 'Delete' || e.key === 'Backspace') && selectedId) { removeAnnot(selectedId); selectedId = null; }
        });

        /* ── BADGE & PANEL ───────────────────────────────────────── */
        function updateBadge() {
            const n = annots.length;
            if (panelBadge) { panelBadge.textContent = n > 99 ? '99+' : String(n); panelBadge.classList.toggle('show', n > 0); }
            window.dispatchEvent(new CustomEvent('annot-count-change', { detail: { count: n } }));
        }
        if (panelBtn) panelBtn.addEventListener('click', function (e) { e.stopPropagation(); annotPanel && annotPanel.classList.toggle('open'); buildPanel(); });
        if (apClose) apClose.addEventListener('click', function () { annotPanel && annotPanel.classList.remove('open'); });
        if (apClear) apClear.addEventListener('click', async function () {
            if (!confirm('Hapus semua anotasi di halaman ' + V.pageNum + '?')) return;
            await apiDelPage(V.pageNum);
            annots = annots.filter(function (a) { return a.page !== V.pageNum; });
            undoStack = []; redoStack = []; updateUndoRedo(); scheduleRender(); buildPanel();
            snack('🗑 Halaman ' + V.pageNum + ' dibersihkan');
        });

        function buildPanel() {
            if (!apList) return;
            if (!annots.length) { apList.innerHTML = '<div class="ap-empty">Belum ada anotasi.</div>'; return; }
            apList.innerHTML = '';
            const ic = { highlight: '✏️', underline: '__', strikethrough: '~~', freehand: '🖊', shape: '⬛', comment: '💬', sticky: '📌', text: '🔤' };
            annots.slice().sort(function (a, b) { return a.page - b.page || a.id - b.id; }).forEach(function (a) {
                const el = document.createElement('div'); el.className = 'ap-item';
                el.innerHTML =
                    '<div class="ap-dot" style="background:' + hex(a.color) + '"></div>' +
                    '<div class="ap-item-body"><span class="ap-item-type">' + (ic[a.type] || '•') + ' ' + a.type + '</span>' +
                    '<span class="ap-item-pg">Hal.' + a.page + '</span>' +
                    '<div class="ap-item-text">' + esc(a.comment || a.selected_text || a.shape_type || '—') + '</div></div>' +
                    '<div style="display:flex;gap:2px;flex-shrink:0;">' +
                    '<button type="button" data-pe="' + a.id + '" style="background:none;border:none;color:#4b5563;cursor:pointer;font-size:11px;padding:2px 3px;">✏️</button>' +
                    '<button type="button" data-pd="' + a.id + '" style="background:none;border:none;color:#4b5563;cursor:pointer;font-size:12px;padding:2px 3px;">🗑</button>' +
                    '</div>';
                el.querySelector('[data-pd="' + a.id + '"]').addEventListener('click', async function (ev) { ev.stopPropagation(); await removeAnnot(a.id); buildPanel(); });
                el.querySelector('[data-pe="' + a.id + '"]').addEventListener('click', function (ev) { ev.stopPropagation(); openEditPopup(a); });
                el.addEventListener('click', function () {
                    if (a.page !== V.pageNum) V.queueRender(a.page);
                    if (annotPanel) annotPanel.classList.remove('open');
                });
                apList.appendChild(el);
            });
        }

        /* ── TOOL MANAGEMENT ──────────────────────────────────────── */
        function setTool(tool) {
            activeTool = tool;
            stage.classList.remove('freehand-mode', 'shape-mode', 'eraser-mode', 'pan-mode', 'select-mode', 'copy-text-mode', 'text-tool-mode');
            if (tool === 'freehand' || tool === 'brush') stage.classList.add('freehand-mode');
            if (tool === 'shape') stage.classList.add('shape-mode');
            if (tool === 'eraser') stage.classList.add('eraser-mode');
            if (tool === 'pan') stage.classList.add('pan-mode');
            if (tool === 'select') stage.classList.add('select-mode');
            if (tool === 'copy-text') stage.classList.add('copy-text-mode');
            if (tool === 'text') stage.classList.add('text-tool-mode');

            /* FIX-6 */
            applyTextLayerPE();

            if (freeCanvas)
                freeCanvas.style.pointerEvents = ['freehand', 'brush', 'shape'].includes(tool) ? 'auto' : 'none';
            if (eraserCur)
                eraserCur.style.display = tool === 'eraser' ? 'block' : 'none';
            if (tool !== 'select' && selectedId) { selectedId = null; scheduleRender(); }

            const LABELS = {
                pan: '🖐 Hand', select: '↖ Pilih', highlight: '✏️ Highlight',
                underline: '__ Underline', strikethrough: '~~ Strikethrough',
                comment: '💬 Komentar', freehand: '🖊 Pen', brush: '🖌️ Brush',
                shape: '⬛ Shape', eraser: '🧹 Hapus', sticky: '📌 Sticky',
                'copy-text': '📋 Salin Teks', text: '🔤 Teks',
            };
            const lbl = document.getElementById('ab-active-label'); if (lbl) lbl.textContent = LABELS[tool] || tool;
            const sz = document.getElementById('ab-sizes'); if (sz) sz.classList.toggle('show', ['freehand', 'brush', 'shape', 'text'].includes(tool));
            const sh = document.getElementById('ab-shapes'); if (sh) sh.classList.toggle('show', tool === 'shape');
        }

        /* bind tool buttons dari bottom bar */
        document.querySelectorAll('.ab-tool[data-tool]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.ab-tool[data-tool]').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active'); setTool(btn.dataset.tool);
            });
        });
        /* bind dari custom event (jika ada sheet/tap overlay yang dispatch) */
        window.addEventListener('annot-tool-change', function (e) { setTool(e.detail.tool); });
        window.addEventListener('annot-color-change', function (e) { activeColor = e.detail.color; });
        window.addEventListener('annot-size-change', function (e) { activeSize = +e.detail.size; });
        window.addEventListener('annot-shape-change', function (e) { activeShape = e.detail.shape; });

        /* color swatches */
        document.querySelectorAll('.ab-color').forEach(function (sw) {
            sw.addEventListener('click', function () {
                document.querySelectorAll('.ab-color').forEach(function (s) { s.classList.remove('selected'); });
                sw.classList.add('selected'); activeColor = sw.dataset.color;
            });
        });
        /* size dots */
        document.querySelectorAll('.ab-size').forEach(function (d) {
            d.addEventListener('click', function () {
                document.querySelectorAll('.ab-size').forEach(function (x) { x.classList.remove('selected'); });
                d.classList.add('selected'); activeSize = +d.dataset.size;
            });
        });
        /* shape picker */
        document.querySelectorAll('.ab-shape').forEach(function (b) {
            b.addEventListener('click', function () {
                document.querySelectorAll('.ab-shape').forEach(function (x) { x.classList.remove('active'); });
                b.classList.add('active'); activeShape = b.dataset.shape;
            });
        });

        /* ── TEXT SELECTION (highlight/underline/strikethrough/comment/copy-text) ── */
        function getSelInfo() {
            const sel = window.getSelection();
            if (!sel || sel.isCollapsed || !sel.rangeCount) return null;
            const range = sel.getRangeAt(0);
            if (!textLayer || !textLayer.contains(range.commonAncestorContainer)) return null;
            const sr = stage.getBoundingClientRect(), s = V.getScale();
            const rects = Array.from(range.getClientRects()).filter(function (r) { return r.width > .5 && r.height > .5; });
            if (!rects.length) return null;
            const L = Math.min.apply(null, rects.map(function (r) { return r.left; }));
            const T = Math.min.apply(null, rects.map(function (r) { return r.top; }));
            const R = Math.max.apply(null, rects.map(function (r) { return r.right; }));
            const B = Math.max.apply(null, rects.map(function (r) { return r.bottom; }));
            return {
                rect: { x: (L - sr.left) / s, y: (T - sr.top) / s, w: (R - L) / s, h: (B - T) / s },
                text: sel.toString().substring(0, 1000),
                br: range.getBoundingClientRect(),
            };
        }

        let selTimer = null;
        function onSelEnd(e) {
            if (e.target.closest && e.target.closest('#comment-popup,#sticky-popup,#annot-edit-popup,#annot-bottom-bar,#annot-panel')) return;
            clearTimeout(selTimer);
            selTimer = setTimeout(async function () {
                const info = getSelInfo(); if (!info || info.rect.w < 2) return;

                /* FEAT-14: copy-text mode */
                if (activeTool === 'copy-text') {
                    if (info.text && info.text.trim()) {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(info.text.trim())
                                .then(function () { snack('📋 Teks disalin!', '#22c55e'); })
                                .catch(function () { snack('📋 Teks dipilih — Ctrl+C untuk salin', '#60A5FA'); });
                        } else { snack('📋 Teks dipilih — Ctrl+C untuk salin', '#60A5FA'); }
                    }
                    return;
                }

                const base = { page: V.pageNum, color: activeColor, rect_x: info.rect.x, rect_y: info.rect.y, rect_w: info.rect.w, rect_h: info.rect.h, selected_text: info.text };
                if (activeTool === 'highlight') {
                    await addAnnot(Object.assign({ type: 'highlight' }, base));
                    window.getSelection() && window.getSelection().removeAllRanges(); snack('✏️ Highlight!');
                } else if (activeTool === 'underline') {
                    await addAnnot(Object.assign({ type: 'underline' }, base));
                    window.getSelection() && window.getSelection().removeAllRanges(); snack('__ Underline!');
                } else if (activeTool === 'strikethrough') {
                    await addAnnot(Object.assign({ type: 'strikethrough' }, base));
                    window.getSelection() && window.getSelection().removeAllRanges(); snack('~~ Strikethrough!');
                } else if (activeTool === 'comment') {
                    pendingRect = info.rect; pendingText = info.text;
                    openPopup(commentPop, info.br.left, info.br.bottom + 8);
                    if (commentTxt) { commentTxt.value = ''; commentTxt.focus(); }
                }
            }, 80);
        }
        document.addEventListener('mouseup', onSelEnd);
        document.addEventListener('touchend', function (e) {
            if (!['highlight', 'comment', 'underline', 'strikethrough', 'copy-text'].includes(activeTool)) return;
            onSelEnd(e);
        }, { passive: true });

        function openPopup(popup, cx, cy) {
            if (!popup) return;
            const vw = window.innerWidth, vh = window.innerHeight, pw = 284, ph = 170;
            popup.style.left = Math.max(4, Math.min(cx - pw / 2, vw - pw - 4)) + 'px';
            popup.style.top = Math.max(4, (cy + ph > vh ? cy - ph - 8 : cy)) + 'px';
            popup.classList.add('show');
        }

        /* comment popup */
        if (commentSave) commentSave.addEventListener('click', async function () {
            const txt = commentTxt ? commentTxt.value.trim() : '';
            if (!txt || !pendingRect) { snack('Tulis komentar dulu!'); return; }
            const rect = Object.assign({}, pendingRect), selTxt = pendingText;
            if (commentTxt) commentTxt.value = ''; if (commentPop) commentPop.classList.remove('show');
            pendingRect = null; pendingText = null;
            await addAnnot({ page: V.pageNum, type: 'comment', color: activeColor, rect_x: rect.x, rect_y: rect.y, rect_w: rect.w, rect_h: rect.h, selected_text: selTxt || '', comment: txt });
            window.getSelection() && window.getSelection().removeAllRanges(); snack('💬 Komentar disimpan!');
        });
        if (commentCancel) commentCancel.addEventListener('click', function () {
            if (commentPop) commentPop.classList.remove('show');
            pendingRect = null; pendingText = null;
            window.getSelection() && window.getSelection().removeAllRanges();
        });

        /* sticky popup */
        if (stickySave) stickySave.addEventListener('click', async function () {
            const txt = stickyTxt ? stickyTxt.value.trim() : '';
            if (!txt) { snack('Tulis catatan dulu!'); return; }
            if (!stickyPos) { snack('Klik area PDF dulu!'); return; }
            const pos = Object.assign({}, stickyPos);
            if (stickyTxt) stickyTxt.value = ''; if (stickyPop) stickyPop.classList.remove('show');
            stickyPos = null;
            await addAnnot({ page: V.pageNum, type: 'sticky', color: activeColor, rect_x: pos.x, rect_y: pos.y, rect_w: 180, rect_h: 90, comment: txt });
            snack('📌 Sticky note ditempel!');
        });
        if (stickyCancel) stickyCancel.addEventListener('click', function () { if (stickyPop) stickyPop.classList.remove('show'); stickyPos = null; });

        /* ── FREEHAND (FEAT-9) ─────────────────────────────────────── */
        function getFHSize() { return activeTool === 'brush' ? Math.max(6, activeSize * 3.5) : activeSize; }
        function getFHAlpha() { return activeTool === 'brush' ? .5 : .92; }

        function fhStart(e) {
            if (activeTool !== 'freehand' && activeTool !== 'brush') return;
            if (e.cancelable) e.preventDefault();
            isDrawing = true; freePoints = [];
            const p = stageXY(e), s = V.getScale(); freePoints.push([p.x / s, p.y / s]);
        }
        function fhMove(e) {
            if (!isDrawing || (activeTool !== 'freehand' && activeTool !== 'brush')) return;
            if (e.cancelable) e.preventDefault();
            const p = stageXY(e), s = V.getScale(); freePoints.push([p.x / s, p.y / s]);
            if (!freeCtx || freePoints.length < 2) return;
            const last = freePoints[freePoints.length - 2], cur = freePoints[freePoints.length - 1];
            freeCtx.save();
            freeCtx.strokeStyle = hex(activeColor); freeCtx.lineWidth = getFHSize() * s;
            freeCtx.lineCap = 'round'; freeCtx.lineJoin = 'round'; freeCtx.globalAlpha = getFHAlpha();
            freeCtx.beginPath(); freeCtx.moveTo(last[0] * s, last[1] * s); freeCtx.lineTo(cur[0] * s, cur[1] * s);
            freeCtx.stroke(); freeCtx.restore();
        }
        async function fhEnd(e) {
            if (!isDrawing || (activeTool !== 'freehand' && activeTool !== 'brush')) return;
            if (e.cancelable) e.preventDefault(); isDrawing = false;
            if (freePoints.length < 2) return;
            const xs = freePoints.map(function (p) { return p[0]; });
            const ys = freePoints.map(function (p) { return p[1]; });
            const bx = Math.min.apply(null, xs), by = Math.min.apply(null, ys);
            await addAnnot({ page: V.pageNum, type: 'freehand', color: activeColor, stroke_width: getFHSize(), path_points: freePoints, rect_x: bx, rect_y: by, rect_w: Math.max.apply(null, xs) - bx, rect_h: Math.max.apply(null, ys) - by });
        }

        /* ── SHAPE (FEAT-10, FEAT-11) ─────────────────────────────── */
        function getOrCreateSVG() {
            if (!shapePreviewSVGEl) {
                shapePreviewSVGEl = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                shapePreviewSVGEl.style.cssText = 'position:absolute;inset:0;pointer-events:none;z-index:26;overflow:visible;';
                shapePreviewSVGEl.setAttribute('width', stage.offsetWidth);
                shapePreviewSVGEl.setAttribute('height', stage.offsetHeight);
                stage.appendChild(shapePreviewSVGEl);
            }
            return shapePreviewSVGEl;
        }
        function clearShapePreview() { if (shapePreviewSVGEl) shapePreviewSVGEl.innerHTML = ''; }
        function destroyShapePreview() { if (shapePreviewSVGEl) { shapePreviewSVGEl.parentNode && shapePreviewSVGEl.parentNode.removeChild(shapePreviewSVGEl); shapePreviewSVGEl = null; } }

        function updateShapePreview(x1, y1, x2, y2) {
            const svg = getOrCreateSVG();
            const col = hex(activeColor), sw = Math.max(1, activeSize);
            const w = Math.abs(x2 - x1), h = Math.abs(y2 - y1);
            const mx = Math.min(x1, x2), my = Math.min(y1, y2);
            let inner = '';
            if (activeShape === 'rect')
                inner = '<rect x="' + (mx + sw / 2) + '" y="' + (my + sw / 2) + '" width="' + Math.max(1, w - sw) + '" height="' + Math.max(1, h - sw) + '" rx="2" fill="none" stroke="' + col + '" stroke-width="' + sw + '" stroke-dasharray="4 3"/>';
            else if (activeShape === 'ellipse')
                inner = '<ellipse cx="' + (mx + w / 2) + '" cy="' + (my + h / 2) + '" rx="' + Math.max(1, w / 2 - sw / 2) + '" ry="' + Math.max(1, h / 2 - sw / 2) + '" fill="none" stroke="' + col + '" stroke-width="' + sw + '" stroke-dasharray="4 3"/>';
            else if (activeShape === 'line')
                inner = '<line x1="' + x1 + '" y1="' + y1 + '" x2="' + x2 + '" y2="' + y2 + '" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round" stroke-dasharray="4 3"/>';
            else if (activeShape === 'arrow') {
                const dx = x2 - x1, dy = y2 - y1, len = Math.sqrt(dx * dx + dy * dy);
                if (len >= 4) {
                    const hLen = Math.min(len * .35, Math.max(12, sw * 5)), ang = Math.atan2(dy, dx);
                    const ax1 = x2 - hLen * Math.cos(ang - Math.PI / 6), ay1 = y2 - hLen * Math.sin(ang - Math.PI / 6);
                    const ax2 = x2 - hLen * Math.cos(ang + Math.PI / 6), ay2 = y2 - hLen * Math.sin(ang + Math.PI / 6);
                    inner = '<line x1="' + x1 + '" y1="' + y1 + '" x2="' + x2 + '" y2="' + y2 + '" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round" stroke-dasharray="4 3"/><polyline points="' + ax1 + ',' + ay1 + ' ' + x2 + ',' + y2 + ' ' + ax2 + ',' + ay2 + '" fill="none" stroke="' + col + '" stroke-width="' + sw + '" stroke-linecap="round" stroke-linejoin="round"/>';
                }
            }
            svg.innerHTML = inner;
        }

        let shX1 = 0, shY1 = 0;
        function shStart(e) {
            if (activeTool !== 'shape') return; if (e.cancelable) e.preventDefault();
            isDrawing = true; const p = stageXY(e); drawStart = p; shX1 = p.x; shY1 = p.y;
            getOrCreateSVG();
        }
        function shMove(e) {
            if (!isDrawing || activeTool !== 'shape' || !drawStart) return;
            if (e.cancelable) e.preventDefault();
            const c = stageXY(e); updateShapePreview(shX1, shY1, c.x, c.y);
        }
        async function shEnd(e) {
            if (!isDrawing || activeTool !== 'shape') return;
            if (e.cancelable) e.preventDefault(); isDrawing = false; clearShapePreview();
            const c = stageXY(e), s = V.getScale(); if (!drawStart) return;
            const x1 = shX1 / s, y1 = shY1 / s, x2 = c.x / s, y2 = c.y / s; drawStart = null;
            if (Math.abs(x2 - x1) < 2 && Math.abs(y2 - y1) < 2) return;
            const rx = Math.min(x1, x2), ry = Math.min(y1, y2);
            await addAnnot({ page: V.pageNum, type: 'shape', color: activeColor, shape_type: activeShape, stroke_width: activeSize, rect_x: rx, rect_y: ry, rect_w: Math.abs(x2 - x1), rect_h: Math.abs(y2 - y1), path_points: [[x1, y1], [x2, y2]], arrow_x1: x1, arrow_y1: y1, arrow_x2: x2, arrow_y2: y2 });
        }

        if (freeCanvas) {
            freeCanvas.addEventListener('mousedown', function (e) { fhStart(e); shStart(e); }, { passive: false });
            freeCanvas.addEventListener('mousemove', function (e) { fhMove(e); shMove(e); }, { passive: false });
            freeCanvas.addEventListener('mouseup', function (e) { fhEnd(e); shEnd(e); }, { passive: false });
            freeCanvas.addEventListener('mouseleave', function (e) { fhEnd(e); shEnd(e); }, { passive: false });
            freeCanvas.addEventListener('touchstart', function (e) { fhStart(e); shStart(e); }, { passive: false });
            freeCanvas.addEventListener('touchmove', function (e) { fhMove(e); shMove(e); }, { passive: false });
            freeCanvas.addEventListener('touchend', function (e) { fhEnd(e); shEnd(e); }, { passive: false });
        }

        /* freeCanvas init */
        if (freeCanvas) {
            freeCanvas.style.pointerEvents = 'none';
            freeCanvas.style.position = 'absolute';
            freeCanvas.style.inset = '0';
            freeCanvas.style.zIndex = '10';
        }

        /* ── ERASER CURSOR (FEAT-17) ──────────────────────────────── */
        document.addEventListener('mousemove', function (e) {
            if (!eraserCur) return;
            eraserCur.style.display = activeTool === 'eraser' ? 'block' : 'none';
            if (activeTool === 'eraser') { eraserCur.style.left = e.clientX + 'px'; eraserCur.style.top = e.clientY + 'px'; }
        });

        /* ── STAGE CLICK (sticky, text, select, eraser) ──────────── */
        stage.addEventListener('click', function (e) {
            if (e.target === freeCanvas) return;
            const hit = e.target.closest && (e.target.closest('[data-annot-id]') || e.target.closest('.sticky-note,.annot-freetext'));

            if (activeTool === 'sticky') {
                if (hit || (e.target.closest && e.target.closest('#comment-popup,#sticky-popup,#annot-bottom-bar'))) return;
                const p = stageXY(e), s = V.getScale(); stickyPos = { x: p.x / s, y: p.y / s };
                openPopup(stickyPop, e.clientX, e.clientY);
                if (stickyTxt) { stickyTxt.value = ''; setTimeout(function () { stickyTxt.focus(); }, 30); }
                return;
            }
            if (activeTool === 'text') {
                if (hit || (e.target.closest && e.target.closest('#annot-freetext-popup,#annot-bottom-bar'))) return;
                const p = stageXY(e), s = V.getScale(); textPos = { x: p.x / s, y: p.y / s };
                ensureTextPopup(); openPopup(document.getElementById('annot-freetext-popup'), e.clientX, e.clientY);
                setTimeout(function () { document.getElementById('annot-freetext-input') && document.getElementById('annot-freetext-input').focus(); }, 30);
                return;
            }
            if (activeTool === 'select' && !hit) { selectedId = null; scheduleRender(); return; }
            if (activeTool === 'eraser' && !hit) { snack('Klik anotasi untuk menghapus', '#60A5FA'); return; }
        });

        /* ── FREE TEXT POPUP ─────────────────────────────────────── */
        function sizeToPx(s) { return ({ 2: 10, 4: 14, 8: 20, 14: 28 })[s] || 14; }
        function ensureTextPopup() {
            if (document.getElementById('annot-freetext-popup')) return;
            const p = document.createElement('div'); p.id = 'annot-freetext-popup';
            p.style.cssText = 'position:fixed;background:#1a1a1a;border:2px solid #FF6B18;border-radius:14px;padding:.875rem;width:280px;z-index:20006;box-shadow:0 12px 40px rgba(0,0,0,.6);display:none;';
            p.innerHTML =
                '<p style="font-size:12px;font-weight:700;color:#FF6B18;margin:0 0 .5rem">🔤 Tambah Teks ke PDF</p>' +
                '<textarea id="annot-freetext-input" placeholder="Contoh: Penting! atau Catatan..." style="width:100%;background:#2d2d2d;border:1.5px solid #3d3d3d;color:white;border-radius:8px;padding:.5rem;font-size:13px;resize:none;outline:none;height:72px;display:block;box-sizing:border-box;"></textarea>' +
                '<div style="margin-top:.4rem;font-size:10px;color:#888;">Ukuran: <span id="annot-freetext-size-label">' + sizeToPx(activeSize) + 'px</span></div>' +
                '<div style="display:flex;gap:.4rem;margin-top:.5rem">' +
                '<button id="annot-freetext-save" style="flex:1;padding:.5rem;background:#FF6B18;color:white;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">✓ Tambah</button>' +
                '<button id="annot-freetext-cancel" style="padding:.5rem .75rem;background:#2d2d2d;color:#aaa;border:none;border-radius:8px;font-size:12px;cursor:pointer;">Batal</button>' +
                '</div>';
            document.body.appendChild(p);
            document.getElementById('annot-freetext-save').addEventListener('click', async function () {
                const inp = document.getElementById('annot-freetext-input');
                const txt = inp ? inp.value.trim() : '';
                if (!txt) { snack('Ketik teks dulu!'); return; }
                if (!textPos) { snack('Klik area PDF dulu!'); return; }
                if (inp) inp.value = ''; p.style.display = 'none';
                await addAnnot({ page: V.pageNum, type: 'text', color: activeColor, stroke_width: sizeToPx(activeSize), rect_x: textPos.x, rect_y: textPos.y, rect_w: 200, rect_h: sizeToPx(activeSize) * 2, comment: txt });
                textPos = null; snack('🔤 Teks ditambahkan!');
            });
            document.getElementById('annot-freetext-cancel').addEventListener('click', function () { p.style.display = 'none'; textPos = null; });
        }
        window.addEventListener('annot-size-change', function () {
            const lbl = document.getElementById('annot-freetext-size-label');
            if (lbl) lbl.textContent = sizeToPx(activeSize) + 'px';
        });

        /* ── PAN MODE ─────────────────────────────────────────────── */
        stage.addEventListener('mousedown', function (e) {
            if (activeTool !== 'pan') return;
            isPanning = true; panSX = e.clientX; panSY = e.clientY;
            panScrollX = canvasWrap ? canvasWrap.scrollLeft : 0;
            panScrollY = canvasWrap ? canvasWrap.scrollTop : 0;
            if (e.cancelable) e.preventDefault();
        }, { passive: false });
        document.addEventListener('mousemove', function (e) {
            if (!isPanning || activeTool !== 'pan') return;
            if (canvasWrap) { canvasWrap.scrollLeft = panScrollX + (panSX - e.clientX); canvasWrap.scrollTop = panScrollY + (panSY - e.clientY); }
        });
        document.addEventListener('mouseup', function () { isPanning = false; });

        /* ── FIX-5: ResizeObserver (tidak trigger render loop) ────── */
        if (typeof ResizeObserver !== 'undefined' && mainCanvas) {
            new ResizeObserver(function () { syncFC(); scheduleRender(); }).observe(mainCanvas);
        }

        /* ── PAGE CHANGE HOOK ─────────────────────────────────────── */
        V.onPageChange = function () {
            /* close semua popup */
            [commentPop, stickyPop].forEach(function (p) { if (p) p.classList.remove('show'); });
            const fp = document.getElementById('annot-freetext-popup'); if (fp) fp.style.display = 'none';
            const ep = document.getElementById('annot-edit-popup'); if (ep) ep.style.display = 'none';
            if (annotTip) annotTip.classList.remove('show');
            destroyShapePreview();
            pendingRect = null; pendingText = null; stickyPos = null; textPos = null;
            window.getSelection && window.getSelection().removeAllRanges();
            /* FIX-6 */
            applyTextLayerPE();
            scheduleRender();
        };

        /* ── INIT SETELAH PDF SIAP ───────────────────────────────── */
        V.onReady(async function () {
            syncFC();
            if (!IS_RO) setTool('highlight');
            else setTool('select');
            await loadAll();
        });

        window._pdfAnnotations = true;
        console.log('[annot] v4.0 ready, slug=', slug, IS_RO ? '[read-only]' : '');
    } /* end init() */

    bootstrap();
})();
