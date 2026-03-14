{{--
resources/views/filament/reviews/pdf-viewer.blade.php

Variabel dikirim dari ReviewForm.php via Placeholder::content():
$version => PublicationVersion model
$publication => Publication model
$slug => string slug publikasi
$pdfUrl => string URL stream PDF
--}}

@if (!isset($version) || !isset($pdfUrl) || !$pdfUrl)
<div
    class="p-8 text-center border-2 border-orange-200 border-dashed rounded-xl bg-orange-50 dark:border-orange-800 dark:bg-orange-950">
    <div class="mb-3 text-4xl">📄</div>
    <p class="text-sm font-semibold text-orange-700 dark:text-orange-300">
        Pilih Publication Version di Step 1 untuk membaca naskah.
    </p>
</div>
@else

<div id="review-pdf-wrapper" class="overflow-hidden border border-gray-200 shadow-lg rounded-2xl dark:border-gray-700"
    style="position:relative;">

    {{-- ── TOOLBAR ── --}}
    <div id="rpv-toolbar" class="flex flex-wrap items-center gap-2 px-3 py-2 bg-gray-900 border-b border-gray-700">

        {{-- Navigasi halaman --}}
        <div class="flex items-center gap-1 px-2 py-1 bg-gray-800 rounded-lg">
            <button id="rpv-prev" class="p-1 text-white rpv-btn" title="Halaman sebelumnya">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <span class="px-1 text-xs font-semibold text-white whitespace-nowrap">
                Hal. <span id="rpv-page-num">1</span> / <span id="rpv-page-count">-</span>
            </span>
            <button id="rpv-next" class="p-1 text-white rpv-btn" title="Halaman berikutnya">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        {{-- Zoom --}}
        <div class="flex items-center gap-1 px-2 py-1 bg-gray-800 rounded-lg">
            <button id="rpv-zoom-out" class="p-1 text-white rpv-btn" title="Perkecil">−</button>
            <span id="rpv-zoom-label" class="text-xs font-semibold text-center text-white w-11">100%</span>
            <button id="rpv-zoom-in" class="p-1 text-white rpv-btn" title="Perbesar">+</button>
        </div>

        <div class="w-px h-6 mx-1 bg-gray-600"></div>

        {{-- Tool buttons --}}
        <div class="flex items-center gap-1">
            <button class="rpv-tool-btn rpv-active" data-tool="highlight" title="Highlight teks">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 19l-2 2H5l1-2L15 9l2 2L9 19z" stroke-linejoin="round" />
                    <line x1="5" y1="21" x2="19" y2="21" />
                </svg>
            </button>
            <button class="rpv-tool-btn" data-tool="underline" title="Underline">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3" stroke-linecap="round" />
                    <line x1="4" y1="21" x2="20" y2="21" />
                </svg>
            </button>
            <button class="rpv-tool-btn" data-tool="strikethrough" title="Strikethrough">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.3 12H6.7M10 7.2C10 7.2 9 6 11.5 6c2.1 0 3 1 3 2.2 0 2-2 2.8-3.5 3"
                        stroke-linecap="round" />
                    <path d="M14 17c0 0 1 1-1.5 1-2.1 0-3.5-1-3.5-2.5" stroke-linecap="round" />
                </svg>
            </button>
            <button class="rpv-tool-btn" data-tool="freehand" title="Pen bebas">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 20h9" />
                    <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
                </svg>
            </button>
            <button class="rpv-tool-btn" data-tool="shape" title="Shape">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <circle cx="17.5" cy="6.5" r="3.5" />
                    <path d="M3 20h4M5 18v4M14 15l5 5m0-5l-5 5" />
                </svg>
            </button>
            <button class="rpv-tool-btn" data-tool="comment" title="Komentar">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                </svg>
            </button>
            <button class="rpv-tool-btn" data-tool="sticky" title="Sticky Note">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="9" y1="13" x2="15" y2="13" />
                    <line x1="9" y1="17" x2="13" y2="17" />
                </svg>
            </button>
            <button class="rpv-tool-btn" data-tool="eraser" title="Hapus anotasi">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 20H7L3 16l10-10 7 7-3 3" />
                    <path d="M6.5 17.5l5-5" />
                </svg>
            </button>
        </div>

        <div class="w-px h-6 mx-1 bg-gray-600"></div>

        {{-- Color picker — static HTML (hindari @foreach di dalam render()) --}}
        <div class="flex items-center gap-1">
            <button class="rpv-color rpv-color-sel" data-color="yellow" style="background:#FFD700"
                title="Yellow"></button>
            <button class="rpv-color" data-color="green" style="background:#4ADE80" title="Green"></button>
            <button class="rpv-color" data-color="red" style="background:#EF4444" title="Red"></button>
            <button class="rpv-color" data-color="blue" style="background:#60A5FA" title="Blue"></button>
            <button class="rpv-color" data-color="orange" style="background:#FF6B18" title="Orange"></button>
        </div>

        <div class="w-px h-6 mx-1 bg-gray-600"></div>

        <button id="rpv-undo" class="px-2 py-1 text-xs font-bold text-white bg-gray-700 rounded rpv-btn" disabled
            title="Undo">↩ Undo</button>
        <button id="rpv-redo" class="px-2 py-1 text-xs font-bold text-white bg-gray-700 rounded rpv-btn" disabled
            title="Redo">↪ Redo</button>

        <div class="flex-1"></div>

        <button id="rpv-export"
            class="rpv-btn flex items-center gap-1.5 px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold rounded-lg"
            title="Export PDF dengan anotasi">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export PDF + Anotasi
        </button>

        <span id="rpv-annot-count"
            class="items-center hidden gap-1 px-2 py-1 text-xs font-bold text-orange-300 border rounded-full bg-orange-500/20 border-orange-500/30">
            <span id="rpv-annot-num">0</span> anotasi
        </span>
    </div>

    {{-- ── PDF CANVAS AREA ── --}}
    <div id="rpv-canvas-wrap"
        style="height:600px;overflow:auto;background:#2d2d2d;display:flex;align-items:flex-start;justify-content:center;padding:12px;">
        <div id="rpv-loading"
            style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);display:flex;flex-direction:column;align-items:center;gap:12px;">
            <div
                style="width:40px;height:40px;border:4px solid #3d3d3d;border-top-color:#ff6b18;border-radius:50%;animation:rpv-spin .8s linear infinite;">
            </div>
            <p style="color:#ccc;font-size:13px;font-weight:600;">Memuat naskah...</p>
        </div>
        <div id="rpv-stage"
            style="position:relative;display:inline-block;flex-shrink:0;box-shadow:0 4px 32px rgba(0,0,0,.6);display:none;">
            <canvas id="rpv-canvas"></canvas>
            <div id="rpv-text-layer"
                style="position:absolute;top:0;left:0;width:100%;height:100%;overflow:hidden;line-height:1;pointer-events:auto;user-select:text;-webkit-user-select:text;">
            </div>
            <div id="rpv-annot-layer"
                style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;overflow:hidden;">
            </div>
            <canvas id="rpv-free-canvas"
                style="position:absolute;top:0;left:0;pointer-events:none;z-index:7;touch-action:none;"></canvas>
        </div>
    </div>

    <div style="height:3px;background:#1f1f1f;">
        <div id="rpv-progress" style="height:100%;background:#ff6b18;width:0%;transition:width .4s;"></div>
    </div>

    <div id="rpv-export-status"
        style="display:none;padding:8px 16px;background:#1a2e1a;border-top:1px solid #22c55e;font-size:12px;color:#86efac;font-weight:600;text-align:center;">
        ✅ PDF berhasil di-export! Silakan upload di Step 3.
    </div>
</div>

{{-- ── POPUPS ── --}}
<div id="rpv-comment-pop"
    style="position:fixed;background:#1a1a1a;border:2px solid #ff6b18;border-radius:14px;padding:.875rem;width:280px;z-index:99999;display:none;box-shadow:0 12px 40px rgba(0,0,0,.6);">
    <p style="font-size:12px;font-weight:700;color:#ff6b18;margin:0 0 .5rem">💬 Tambah Komentar</p>
    <textarea id="rpv-comment-txt" placeholder="Tulis komentar untuk teks ini..."
        style="width:100%;background:#2d2d2d;border:1.5px solid #3d3d3d;color:white;border-radius:8px;padding:.5rem;font-size:13px;resize:none;outline:none;height:72px;display:block;box-sizing:border-box;"></textarea>
    <div style="display:flex;gap:.4rem;margin-top:.5rem">
        <button id="rpv-comment-save"
            style="flex:1;padding:.45rem;background:#ff6b18;color:white;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Simpan</button>
        <button id="rpv-comment-cancel"
            style="padding:.45rem .75rem;background:#2d2d2d;color:#aaa;border:none;border-radius:8px;font-size:12px;cursor:pointer;">Batal</button>
    </div>
</div>

<div id="rpv-sticky-pop"
    style="position:fixed;background:#1a1a1a;border:2px solid #ff6b18;border-radius:14px;padding:.875rem;width:260px;z-index:99999;display:none;box-shadow:0 12px 40px rgba(0,0,0,.6);">
    <p style="font-size:12px;font-weight:700;color:#ff6b18;margin:0 0 .5rem">📌 Sticky Note</p>
    <textarea id="rpv-sticky-txt" placeholder="Tulis catatan di sini..."
        style="width:100%;background:#2d2d2d;border:1.5px solid #3d3d3d;color:white;border-radius:8px;padding:.5rem;font-size:13px;resize:none;outline:none;height:72px;display:block;box-sizing:border-box;"></textarea>
    <div style="display:flex;gap:.4rem;margin-top:.5rem">
        <button id="rpv-sticky-save"
            style="flex:1;padding:.45rem;background:#ff6b18;color:white;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Tempel</button>
        <button id="rpv-sticky-cancel"
            style="padding:.45rem .75rem;background:#2d2d2d;color:#aaa;border:none;border-radius:8px;font-size:12px;cursor:pointer;">Batal</button>
    </div>
</div>

<div id="rpv-tooltip"
    style="position:fixed;background:#1a1a1a;border:1.5px solid #ff6b18;border-radius:12px;padding:.75rem;max-width:260px;z-index:99999;display:none;box-shadow:0 8px 32px rgba(0,0,0,.5);font-size:13px;color:white;">
    <div id="rpv-tip-text" style="color:#ddd;margin-bottom:.5rem;font-size:12px;word-break:break-word;"></div>
    <div style="display:flex;gap:.4rem;">
        <button id="rpv-tip-del"
            style="flex:1;padding:.35rem;background:rgba(239,68,68,.2);color:#f87171;border:none;border-radius:7px;font-size:11px;font-weight:600;cursor:pointer;">🗑
            Hapus</button>
        <button id="rpv-tip-close"
            style="padding:.35rem .65rem;background:#2d2d2d;color:#aaa;border:none;border-radius:7px;font-size:11px;cursor:pointer;">✕</button>
    </div>
</div>

{{-- ── STYLES ── --}}
<style>
    @keyframes rpv-spin {
        to {
            transform: rotate(360deg);
        }
    }

    .rpv-btn {
        border: none;
        cursor: pointer;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .15s;
        flex-shrink: 0;
        background: transparent;
    }

    .rpv-btn:hover:not(:disabled) {
        background: rgba(255, 107, 24, .25) !important;
    }

    .rpv-btn:disabled {
        opacity: .35;
        cursor: not-allowed;
    }

    .rpv-tool-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1.5px solid transparent;
        background: transparent;
        color: #888;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all .15s;
    }

    .rpv-tool-btn:hover {
        background: rgba(255, 107, 24, .15);
        color: #fff;
        border-color: rgba(255, 107, 24, .3);
    }

    .rpv-tool-btn.rpv-active {
        background: rgba(255, 107, 24, .2);
        border-color: #ff6b18;
        color: #ff6b18;
    }

    .rpv-tool-btn[data-tool="eraser"].rpv-active {
        background: rgba(239, 68, 68, .15);
        border-color: #ef4444;
        color: #f87171;
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

    .rpv-color:hover {
        transform: scale(1.2);
    }

    .rpv-color.rpv-color-sel {
        border-color: #fff;
        transform: scale(1.2);
        box-shadow: 0 0 0 2px rgba(255, 255, 255, .25);
    }

    #rpv-text-layer span {
        color: transparent;
        position: absolute;
        white-space: pre;
        cursor: text;
        transform-origin: 0% 0%;
    }

    #rpv-text-layer span::selection {
        background: rgba(66, 133, 244, .35);
    }

    .rpv-hl {
        position: absolute;
        border-radius: 2px;
        pointer-events: auto;
        cursor: pointer;
        z-index: 5;
        opacity: .38;
        transition: opacity .12s;
    }

    .rpv-hl:hover {
        opacity: .65;
    }

    .rpv-sticky {
        position: absolute;
        z-index: 9;
        width: 160px;
        min-height: 80px;
        background: #fef9c3;
        border: 1.5px solid #fde047;
        border-radius: 4px 12px 12px 12px;
        padding: 6px 8px;
        font-size: 11px;
        color: #1a1a1a;
        box-shadow: 3px 3px 10px rgba(0, 0, 0, .25);
        cursor: move;
        user-select: none;
        line-height: 1.5;
        overflow: hidden;
    }

    .rpv-sticky-del {
        position: absolute;
        top: 4px;
        right: 6px;
        background: rgba(0, 0, 0, .1);
        border: none;
        border-radius: 50%;
        width: 16px;
        height: 16px;
        cursor: pointer;
        font-size: 11px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .rpv-sticky-del:hover {
        background: rgba(239, 68, 68, .3);
    }

    .rpv-annot-shape {
        position: absolute;
        pointer-events: auto;
        cursor: pointer;
        z-index: 5;
    }
</style>

{{-- Load PDF.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
    (function(){
'use strict';

const SLUG    = @json($slug);
const PDF_URL = @json($pdfUrl);
const API     = '/api/annotations/' + SLUG;

const VALID_TYPES  = ['highlight','underline','strikethrough','freehand','comment','sticky','shape','text'];
const VALID_COLORS = ['yellow','green','red','blue','orange','black','white','pink','purple','cyan'];
const VALID_SHAPES = ['rect','ellipse','arrow','line'];
const COLORS = {yellow:'#FFD700',green:'#4ADE80',red:'#EF4444',blue:'#60A5FA',orange:'#FF6B18',black:'#111',white:'#fff',pink:'#F472B6',purple:'#A78BFA',cyan:'#22D3EE'};
const hex = n => COLORS[n]||'#FFD700';

function csrf(){ return document.querySelector('meta[name="csrf-token"]')?.content||''; }
function hdrs(){ return{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf(),'X-Requested-With':'XMLHttpRequest'}; }

let pdfDoc=null,pageNum=1,pageRendering=false,pageNumPending=null;
let baseScale=1.0,zoomFactor=1.0;
const ZOOM_MIN=0.5,ZOOM_MAX=4,ZOOM_STEP=0.25;
const DPR=window.devicePixelRatio||1;

let annots=[],undoStack=[],redoStack=[];
let activeTool='highlight',activeColor='yellow',activeSize=2,activeShape='rect';
let isDrawing=false,drawStart=null,freePoints=[],shapePreviewEl=null;
let pendingRect=null,pendingText=null,stickyPos=null;
let renderPending=false;

const wrap       = document.getElementById('rpv-canvas-wrap');
const stage      = document.getElementById('rpv-stage');
const canvas     = document.getElementById('rpv-canvas');
const ctx        = canvas.getContext('2d');
const textLayer  = document.getElementById('rpv-text-layer');
const annotLayer = document.getElementById('rpv-annot-layer');
const freeCanvas = document.getElementById('rpv-free-canvas');
const freeCtx    = freeCanvas.getContext('2d');
const loadingEl  = document.getElementById('rpv-loading');
const commentPop = document.getElementById('rpv-comment-pop');
const stickyPop  = document.getElementById('rpv-sticky-pop');
const tooltip    = document.getElementById('rpv-tooltip');
const tipText    = document.getElementById('rpv-tip-text');

function stageXY(e){const r=stage.getBoundingClientRect();const s=e.changedTouches?.[0]??e.touches?.[0]??e;return{x:s.clientX-r.left,y:s.clientY-r.top};}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');}
function syncFC(){const w=stage.offsetWidth,h=stage.offsetHeight;if(freeCanvas.width!==w||freeCanvas.height!==h){freeCanvas.width=w;freeCanvas.height=h;}freeCanvas.style.width=w+'px';freeCanvas.style.height=h+'px';}
function getScale(){return baseScale*zoomFactor;}

function sanitize(raw){
    const type=VALID_TYPES.includes(raw.type)?raw.type:'highlight';
    const color=VALID_COLORS.includes(raw.color)?raw.color:'yellow';
    return{page:parseInt(raw.page)||pageNum,type,color,rect_x:raw.rect?.x??raw.rect_x??null,rect_y:raw.rect?.y??raw.rect_y??null,rect_w:raw.rect?.w??raw.rect_w??null,rect_h:raw.rect?.h??raw.rect_h??null,selected_text:raw.selected_text||null,comment:raw.comment||null,path_points:Array.isArray(raw.path_points)?raw.path_points:null,shape_type:VALID_SHAPES.includes(raw.shape_type)?raw.shape_type:(type==='shape'?'rect':null),stroke_width:(typeof raw.stroke_width==='number'&&raw.stroke_width>0)?raw.stroke_width:2,fill_opacity:(typeof raw.fill_opacity==='number')?raw.fill_opacity:0};
}

async function apiLoad(){try{const r=await fetch(API,{credentials:'same-origin',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});if(!r.ok)throw r.status;const j=await r.json();return Array.isArray(j.data)?j.data:[];}catch(e){console.error('[rpv] load',e);return[];}}
async function apiSave(payload){try{const r=await fetch(API,{method:'POST',credentials:'same-origin',headers:hdrs(),body:JSON.stringify(sanitize(payload))});const j=await r.json();if(!r.ok){console.error('[rpv] save',j);return null;}return j.data||null;}catch(e){console.error('[rpv] save net',e);return null;}}
async function apiPatch(id,payload){try{await fetch(`${API}/${id}`,{method:'PUT',credentials:'same-origin',headers:hdrs(),body:JSON.stringify(payload)});}catch(e){console.error('[rpv] patch',e);}}
async function apiDel(id){try{await fetch(`${API}/${id}`,{method:'DELETE',credentials:'same-origin',headers:hdrs()});}catch(e){console.error('[rpv] del',e);}}

async function loadAnnots(){annots=await apiLoad();scheduleRender();updateCount();}

function scheduleRender(){if(renderPending)return;renderPending=true;requestAnimationFrame(()=>{renderPending=false;renderAnnots();});}

function renderAnnots(){
    const scale=getScale();
    annotLayer.innerHTML='';annotLayer.style.pointerEvents='none';
    syncFC();freeCtx.clearRect(0,0,freeCanvas.width,freeCanvas.height);
    stage.querySelectorAll('.rpv-sticky,.rpv-freetext').forEach(e=>e.remove());
    annots.filter(a=>a.page===pageNum).forEach(a=>{
        switch(a.type){
            case'highlight':case'comment':rHL(a,scale);break;
            case'underline':rUL(a,scale);break;
            case'strikethrough':rST(a,scale);break;
            case'freehand':rFH(a,scale);break;
            case'shape':rShape(a,scale);break;
            case'sticky':rSticky(a,scale);break;
            case'text':rText(a,scale);break;
        }
    });
    updateCount();
}

function rHL(a,s){if(!a.rect)return;const el=document.createElement('div');el.dataset.annotId=String(a.id);el.className='rpv-hl';el.style.cssText=`left:${a.rect.x*s}px;top:${a.rect.y*s}px;width:${a.rect.w*s}px;height:${a.rect.h*s}px;background:${hex(a.color)};`;if(a.type==='comment'&&a.comment){const dot=document.createElement('span');dot.style.cssText='position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:#60A5FA;border-radius:50%;pointer-events:none;';el.appendChild(dot);}attachEv(el,a);annotLayer.appendChild(el);}
function rUL(a,s){if(!a.rect)return;const el=document.createElement('div');el.dataset.annotId=String(a.id);const t=Math.max(1.5,2*s);el.style.cssText=`position:absolute;left:${a.rect.x*s}px;top:${(a.rect.y+a.rect.h)*s-t}px;width:${a.rect.w*s}px;height:${t}px;background:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;`;attachEv(el,a);annotLayer.appendChild(el);}
function rST(a,s){if(!a.rect)return;const el=document.createElement('div');el.dataset.annotId=String(a.id);const t=Math.max(1.5,2*s);el.style.cssText=`position:absolute;left:${a.rect.x*s}px;top:${(a.rect.y+a.rect.h/2)*s-t/2}px;width:${a.rect.w*s}px;height:${t}px;background:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;`;attachEv(el,a);annotLayer.appendChild(el);}
function rFH(a,s){if(!a.path_points?.length)return;const pts=a.path_points;freeCtx.save();freeCtx.strokeStyle=hex(a.color);freeCtx.lineWidth=(a.stroke_width||2)*s;freeCtx.lineCap='round';freeCtx.lineJoin='round';freeCtx.globalAlpha=.9;freeCtx.beginPath();freeCtx.moveTo(pts[0][0]*s,pts[0][1]*s);for(let i=1;i<pts.length;i++)freeCtx.lineTo(pts[i][0]*s,pts[i][1]*s);freeCtx.stroke();freeCtx.restore();if(a.rect&&(a.rect.w>0||a.rect.h>0)){const hit=document.createElement('div');hit.dataset.annotId=String(a.id);hit.style.cssText=`position:absolute;left:${(a.rect.x-8)*s}px;top:${(a.rect.y-8)*s}px;width:${(a.rect.w+16)*s}px;height:${(a.rect.h+16)*s}px;background:transparent;pointer-events:auto;cursor:pointer;z-index:6;`;attachEv(hit,a);annotLayer.appendChild(hit);}}
function rShape(a,s){if(!a.rect)return;const x=a.rect.x*s,y=a.rect.y*s,w=Math.max(4,a.rect.w*s),h=Math.max(4,a.rect.h*s);const sw=Math.max(1,(a.stroke_width||2)*s),col=hex(a.color);const wrap2=document.createElement('div');wrap2.dataset.annotId=String(a.id);wrap2.className='rpv-annot-shape';wrap2.style.cssText=`left:${x}px;top:${y}px;width:${w}px;height:${h}px;`;const st=a.shape_type||'rect';let svg='';if(st==='rect')svg=`<rect x="${sw/2}" y="${sw/2}" width="${Math.max(1,w-sw)}" height="${Math.max(1,h-sw)}" rx="2" fill="none" stroke="${col}" stroke-width="${sw}"/>`;else if(st==='ellipse')svg=`<ellipse cx="${w/2}" cy="${h/2}" rx="${Math.max(1,w/2-sw/2)}" ry="${Math.max(1,h/2-sw/2)}" fill="none" stroke="${col}" stroke-width="${sw}"/>`;else if(st==='arrow'){const hh=Math.max(4,h*.35),hx=Math.max(sw*3,w*.25);svg=`<line x1="${sw}" y1="${h/2}" x2="${w-hx+sw}" y2="${h/2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/><polygon points="${w-sw/2},${h/2} ${w-hx},${h/2-hh} ${w-hx},${h/2+hh}" fill="${col}"/>"`;}else if(st==='line')svg=`<line x1="${sw}" y1="${h/2}" x2="${w-sw}" y2="${h/2}" stroke="${col}" stroke-width="${sw}" stroke-linecap="round"/>`;wrap2.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" style="overflow:visible;display:block;pointer-events:none">${svg}</svg>`;attachEv(wrap2,a);annotLayer.appendChild(wrap2);}
function rSticky(a,s){if(!a.rect)return;const note=document.createElement('div');note.className='rpv-sticky';note.dataset.annotId=String(a.id);note.style.left=(a.rect.x*s)+'px';note.style.top=(a.rect.y*s)+'px';note.innerHTML=`<button class="rpv-sticky-del">×</button><div style="pointer-events:none;padding-right:16px;">${esc(a.comment)}</div>`;note.querySelector('.rpv-sticky-del').addEventListener('click',ev=>{ev.stopPropagation();removeAnnot(a.id);});note.addEventListener('click',ev=>{if(activeTool==='eraser'){ev.stopPropagation();removeAnnot(a.id);}else{ev.stopPropagation();showTip(a,ev.clientX,ev.clientY);}});makeDraggable(note,a,s);stage.appendChild(note);}
function rText(a,s){if(!a.rect)return;const fontSize=Math.max(10,(a.stroke_width||14))*s;const el=document.createElement('div');el.className='rpv-freetext';el.dataset.annotId=String(a.id);el.style.cssText=`position:absolute;left:${a.rect.x*s}px;top:${a.rect.y*s}px;font-size:${fontSize}px;line-height:1.4;color:${hex(a.color)};pointer-events:auto;cursor:pointer;z-index:8;white-space:pre-wrap;word-break:break-word;max-width:${300*s}px;font-family:sans-serif;font-weight:600;user-select:none;`;el.textContent=a.comment||'';el.addEventListener('click',ev=>{if(activeTool==='eraser'){ev.stopPropagation();removeAnnot(a.id);}else{ev.stopPropagation();showTip(a,ev.clientX,ev.clientY);}});stage.appendChild(el);}

function attachEv(el,a){el.addEventListener('click',ev=>{ev.stopPropagation();if(activeTool==='eraser')removeAnnot(a.id);else showTip(a,ev.clientX,ev.clientY);});}

function makeDraggable(el,annotData,s){let ox=0,oy=0,dragging=false,moved=false;el.addEventListener('mousedown',e=>{if(e.target.classList.contains('rpv-sticky-del'))return;dragging=true;moved=false;ox=e.clientX-el.offsetLeft;oy=e.clientY-el.offsetTop;el.style.zIndex='20';e.stopPropagation();});document.addEventListener('mousemove',e=>{if(!dragging)return;moved=true;el.style.left=(e.clientX-ox)+'px';el.style.top=(e.clientY-oy)+'px';});document.addEventListener('mouseup',async()=>{if(!dragging)return;dragging=false;el.style.zIndex='9';if(!moved)return;const nx=parseFloat(el.style.left)/s,ny=parseFloat(el.style.top)/s;const idx=annots.findIndex(a=>String(a.id)===String(annotData.id));if(idx>=0&&annots[idx].rect){annots[idx].rect.x=nx;annots[idx].rect.y=ny;}await apiPatch(annotData.id,{rect_x:nx,rect_y:ny,rect_w:annotData.rect?.w||160,rect_h:annotData.rect?.h||80});});}

function showTip(a,cx,cy){const ic={highlight:'✏️',underline:'__',strikethrough:'~~',freehand:'🖊',shape:'⬛',comment:'💬',sticky:'📌',text:'🔤'};let txt=`${ic[a.type]||'•'} ${a.type}`;if(a.comment)txt=`${ic[a.type]||'•'} ${a.comment.substring(0,80)}`;else if(a.selected_text)txt=`${ic[a.type]||'•'} "${a.selected_text.substring(0,60)}"`;tipText.textContent=txt;tipText.dataset.annotId=String(a.id);tooltip.style.display='block';const vw=window.innerWidth,vh=window.innerHeight;tooltip.style.left=Math.max(4,Math.min(cx-135,vw-270))+'px';tooltip.style.top=((cy+120>vh)?Math.max(4,cy-120):cy+8)+'px';}
document.getElementById('rpv-tip-close').addEventListener('click',()=>tooltip.style.display='none');
document.getElementById('rpv-tip-del').addEventListener('click',async()=>{const id=tipText?.dataset.annotId;tooltip.style.display='none';if(id)await removeAnnot(id);});
document.addEventListener('click',e=>{if(!tooltip.contains(e.target)&&!e.target.closest('[data-annot-id],.rpv-sticky,.rpv-freetext'))tooltip.style.display='none';});

async function addAnnot(payload){const saved=await apiSave(payload);if(!saved)return null;annots.push(saved);undoStack.push({action:'add',data:saved});redoStack=[];updateUndoRedo();scheduleRender();return saved;}
async function removeAnnot(id){const a=annots.find(x=>String(x.id)===String(id));if(!a)return;await apiDel(a.id);annots=annots.filter(x=>String(x.id)!==String(id));undoStack.push({action:'del',data:a});redoStack=[];updateUndoRedo();scheduleRender();}

function updateUndoRedo(){document.getElementById('rpv-undo').disabled=!undoStack.length;document.getElementById('rpv-redo').disabled=!redoStack.length;}
async function doUndo(){if(!undoStack.length)return;const op=undoStack.pop();if(op.action==='add'){const a=annots.find(x=>String(x.id)===String(op.data.id));if(a){await apiDel(a.id);annots=annots.filter(x=>String(x.id)!==String(a.id));redoStack.push({action:'readd',data:a});}}else if(op.action==='del'){const saved=await apiSave(op.data);if(saved){annots.push(saved);redoStack.push({action:'redel',data:saved});}}updateUndoRedo();scheduleRender();}
async function doRedo(){if(!redoStack.length)return;const op=redoStack.pop();if(op.action==='readd'){const saved=await apiSave(op.data);if(saved){annots.push(saved);undoStack.push({action:'add',data:saved});}}else if(op.action==='redel'){const a=annots.find(x=>String(x.id)===String(op.data.id));if(a){await apiDel(a.id);annots=annots.filter(x=>String(x.id)!==String(a.id));undoStack.push({action:'del',data:a});}}updateUndoRedo();scheduleRender();}
document.getElementById('rpv-undo').addEventListener('click',doUndo);
document.getElementById('rpv-redo').addEventListener('click',doRedo);

function updateCount(){const n=annots.length;const badge=document.getElementById('rpv-annot-count');const num=document.getElementById('rpv-annot-num');if(badge&&num){num.textContent=n;badge.style.display=n>0?'flex':'none';}}

function setTool(tool){activeTool=tool;document.querySelectorAll('.rpv-tool-btn').forEach(b=>b.classList.toggle('rpv-active',b.dataset.tool===tool));const needsSel=['highlight','comment','underline','strikethrough'].includes(tool);textLayer.style.pointerEvents=needsSel?'auto':'none';textLayer.style.userSelect=needsSel?'text':'none';freeCanvas.style.pointerEvents=['freehand','shape'].includes(tool)?'auto':'none';}
document.querySelectorAll('.rpv-tool-btn').forEach(b=>{b.addEventListener('click',()=>setTool(b.dataset.tool));});
setTool('highlight');

document.querySelectorAll('.rpv-color').forEach(sw=>{sw.addEventListener('click',()=>{document.querySelectorAll('.rpv-color').forEach(s=>s.classList.remove('rpv-color-sel'));sw.classList.add('rpv-color-sel');activeColor=sw.dataset.color;});});

function getSelInfo(){const sel=window.getSelection();if(!sel||sel.isCollapsed||!sel.rangeCount)return null;const range=sel.getRangeAt(0);if(!textLayer.contains(range.commonAncestorContainer))return null;const sr=stage.getBoundingClientRect(),s=getScale();const rects=Array.from(range.getClientRects()).filter(r=>r.width>.5&&r.height>.5);if(!rects.length)return null;const L=Math.min(...rects.map(r=>r.left)),T=Math.min(...rects.map(r=>r.top));const R=Math.max(...rects.map(r=>r.right)),B=Math.max(...rects.map(r=>r.bottom));return{rect:{x:(L-sr.left)/s,y:(T-sr.top)/s,w:(R-L)/s,h:(B-T)/s},text:sel.toString().substring(0,1000),br:range.getBoundingClientRect()};}
let selTimer=null;
document.addEventListener('mouseup',e=>{if(e.target.closest('#rpv-comment-pop,#rpv-sticky-pop'))return;clearTimeout(selTimer);selTimer=setTimeout(async()=>{const info=getSelInfo();if(!info||info.rect.w<2)return;const base={page:pageNum,color:activeColor,rect_x:info.rect.x,rect_y:info.rect.y,rect_w:info.rect.w,rect_h:info.rect.h,selected_text:info.text};if(activeTool==='highlight'){await addAnnot({...base,type:'highlight'});window.getSelection()?.removeAllRanges();}else if(activeTool==='underline'){await addAnnot({...base,type:'underline'});window.getSelection()?.removeAllRanges();}else if(activeTool==='strikethrough'){await addAnnot({...base,type:'strikethrough'});window.getSelection()?.removeAllRanges();}else if(activeTool==='comment'){pendingRect=info.rect;pendingText=info.text;showPopup(commentPop,info.br.left,info.br.bottom+8);document.getElementById('rpv-comment-txt').value='';document.getElementById('rpv-comment-txt').focus();}},80);});

function showPopup(pop,cx,cy){const vw=window.innerWidth,vh=window.innerHeight,pw=284,ph=170;pop.style.left=Math.max(4,Math.min(cx-pw/2,vw-pw-4))+'px';pop.style.top=Math.max(4,(cy+ph>vh?cy-ph-8:cy))+'px';pop.style.display='block';}

document.getElementById('rpv-comment-save').addEventListener('click',async()=>{const txt=document.getElementById('rpv-comment-txt').value.trim();if(!txt||!pendingRect)return;commentPop.style.display='none';await addAnnot({page:pageNum,type:'comment',color:activeColor,rect_x:pendingRect.x,rect_y:pendingRect.y,rect_w:pendingRect.w,rect_h:pendingRect.h,selected_text:pendingText||'',comment:txt});window.getSelection()?.removeAllRanges();pendingRect=null;pendingText=null;});
document.getElementById('rpv-comment-cancel').addEventListener('click',()=>{commentPop.style.display='none';pendingRect=null;pendingText=null;window.getSelection()?.removeAllRanges();});

/* ── FREEHAND ── */
freeCanvas.addEventListener('mousedown',e=>{if(activeTool!=='freehand')return;isDrawing=true;freePoints=[];const p=stageXY(e),s=getScale();freePoints.push([p.x/s,p.y/s]);});
freeCanvas.addEventListener('mousemove',e=>{if(!isDrawing||activeTool!=='freehand')return;const p=stageXY(e),s=getScale();freePoints.push([p.x/s,p.y/s]);if(freePoints.length<2)return;const last=freePoints[freePoints.length-2],cur=freePoints[freePoints.length-1];freeCtx.save();freeCtx.strokeStyle=hex(activeColor);freeCtx.lineWidth=activeSize*s;freeCtx.lineCap='round';freeCtx.lineJoin='round';freeCtx.globalAlpha=.9;freeCtx.beginPath();freeCtx.moveTo(last[0]*s,last[1]*s);freeCtx.lineTo(cur[0]*s,cur[1]*s);freeCtx.stroke();freeCtx.restore();});
async function finishFreehand(){if(!isDrawing||activeTool!=='freehand')return;isDrawing=false;if(freePoints.length<2)return;const xs=freePoints.map(p=>p[0]),ys=freePoints.map(p=>p[1]),bx=Math.min(...xs),by=Math.min(...ys);await addAnnot({page:pageNum,type:'freehand',color:activeColor,stroke_width:activeSize,path_points:freePoints,rect_x:bx,rect_y:by,rect_w:Math.max(...xs)-bx,rect_h:Math.max(...ys)-by});}
freeCanvas.addEventListener('mouseup',finishFreehand);
freeCanvas.addEventListener('mouseleave',finishFreehand);

/* ── SHAPE ── */
freeCanvas.addEventListener('mousedown',e=>{if(activeTool!=='shape')return;isDrawing=true;drawStart=stageXY(e);shapePreviewEl=document.createElement('div');shapePreviewEl.style.cssText=`position:absolute;pointer-events:none;z-index:25;border:${activeSize}px solid ${hex(activeColor)};${activeShape==='ellipse'?'border-radius:50%;':''}left:${drawStart.x}px;top:${drawStart.y}px;width:0;height:0;`;stage.appendChild(shapePreviewEl);});
freeCanvas.addEventListener('mousemove',e=>{if(!isDrawing||activeTool!=='shape'||!shapePreviewEl||!drawStart)return;const c=stageXY(e);Object.assign(shapePreviewEl.style,{left:Math.min(drawStart.x,c.x)+'px',top:Math.min(drawStart.y,c.y)+'px',width:Math.abs(c.x-drawStart.x)+'px',height:Math.abs(c.y-drawStart.y)+'px'});});
freeCanvas.addEventListener('mouseup',async e=>{if(!isDrawing||activeTool!=='shape')return;isDrawing=false;shapePreviewEl?.remove();shapePreviewEl=null;const c=stageXY(e),s=getScale();if(!drawStart)return;const x=Math.min(drawStart.x,c.x)/s,y=Math.min(drawStart.y,c.y)/s,w=Math.abs(c.x-drawStart.x)/s,h=Math.abs(c.y-drawStart.y)/s;drawStart=null;if(w<4&&h<4)return;await addAnnot({page:pageNum,type:'shape',color:activeColor,shape_type:activeShape,stroke_width:activeSize,rect_x:x,rect_y:y,rect_w:w,rect_h:h});});

/* ── STICKY ── */
stage.addEventListener('click',e=>{if(activeTool!=='sticky')return;if(e.target.closest('[data-annot-id],.rpv-sticky,.rpv-freetext,#rpv-comment-pop,#rpv-sticky-pop'))return;const p=stageXY(e),s=getScale();stickyPos={x:p.x/s,y:p.y/s};showPopup(stickyPop,e.clientX,e.clientY);document.getElementById('rpv-sticky-txt').value='';document.getElementById('rpv-sticky-txt').focus();});
document.getElementById('rpv-sticky-save').addEventListener('click',async()=>{const txt=document.getElementById('rpv-sticky-txt').value.trim();if(!txt||!stickyPos)return;stickyPop.style.display='none';await addAnnot({page:pageNum,type:'sticky',color:activeColor,rect_x:stickyPos.x,rect_y:stickyPos.y,rect_w:160,rect_h:80,comment:txt});stickyPos=null;});
document.getElementById('rpv-sticky-cancel').addEventListener('click',()=>{stickyPop.style.display='none';stickyPos=null;});

/* ── PDF LOAD ── */
pdfjsLib.GlobalWorkerOptions.workerSrc='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
pdfjsLib.getDocument({url:PDF_URL,withCredentials:true,verbosity:0}).promise.then(async doc=>{
    pdfDoc=doc;
    document.getElementById('rpv-page-count').textContent=doc.numPages;
    loadingEl.style.display='none';stage.style.display='inline-block';
    await renderPDFPage(1);
    await loadAnnots();
}).catch(e=>{loadingEl.innerHTML='<p style="color:#f87171;font-weight:600;">Gagal memuat PDF. Pastikan file tersedia.</p>';console.error('[rpv] pdf load',e);});

async function renderPDFPage(num){
    if(!pdfDoc)return;pageRendering=true;
    const page=await pdfDoc.getPage(num);
    const cw=wrap.clientWidth-40;
    const nativeW=page.getViewport({scale:1}).width;
    baseScale=Math.max(0.5,Math.min(cw/nativeW,2));
    const cssScale=getScale();
    const vpCss=page.getViewport({scale:cssScale});
    const vpRender=page.getViewport({scale:cssScale*DPR});
    canvas.width=Math.floor(vpRender.width);canvas.height=Math.floor(vpRender.height);
    canvas.style.width=Math.floor(vpCss.width)+'px';canvas.style.height=Math.floor(vpCss.height)+'px';
    stage.style.width=Math.floor(vpCss.width)+'px';stage.style.height=Math.floor(vpCss.height)+'px';
    await page.render({canvasContext:ctx,viewport:vpRender}).promise;
    await renderTextLayerFn(page,vpCss);
    pageRendering=false;pageNum=num;
    document.getElementById('rpv-page-num').textContent=num;
    document.getElementById('rpv-prev').disabled=num<=1;
    document.getElementById('rpv-next').disabled=num>=pdfDoc.numPages;
    document.getElementById('rpv-progress').style.width=(num/pdfDoc.numPages*100)+'%';
    document.getElementById('rpv-zoom-label').textContent=Math.round(cssScale*100)+'%';
    wrap.scrollTo({top:0,behavior:'smooth'});
    if(pageNumPending!==null){const p2=pageNumPending;pageNumPending=null;await renderPDFPage(p2);}
    scheduleRender();
}

async function renderTextLayerFn(page,viewport){
    textLayer.innerHTML='';textLayer.style.width=viewport.width+'px';textLayer.style.height=viewport.height+'px';
    const content=await page.getTextContent();
    content.items.forEach(item=>{
        if(!item.str?.trim())return;
        const tx=pdfjsLib.Util.transform(viewport.transform,item.transform);
        const fontH=Math.sqrt(tx[2]*tx[2]+tx[3]*tx[3]);
        const angle=Math.atan2(tx[1],tx[0]);
        const span=document.createElement('span');
        span.textContent=item.str;span.style.fontSize=fontH+'px';span.style.left=tx[4]+'px';span.style.top=(tx[5]-fontH)+'px';span.style.transformOrigin='0% 0%';
        textLayer.appendChild(span);
        const tw=item.width*viewport.scale,mw=span.getBoundingClientRect().width;
        let tr=angle!==0?`rotate(${-angle}rad)`:'';
        if(mw>1&&tw>0)tr+=(tr?' ':'')+`scaleX(${tw/mw})`;
        if(tr.trim())span.style.transform=tr.trim();
    });
}

function queueRender(n){if(pageRendering)pageNumPending=n;else renderPDFPage(n);}
document.getElementById('rpv-prev').addEventListener('click',()=>{if(pageNum>1)queueRender(pageNum-1);});
document.getElementById('rpv-next').addEventListener('click',()=>{if(pdfDoc&&pageNum<pdfDoc.numPages)queueRender(pageNum+1);});
document.getElementById('rpv-zoom-in').addEventListener('click',()=>{zoomFactor=Math.min(zoomFactor+ZOOM_STEP,ZOOM_MAX);renderPDFPage(pageNum);});
document.getElementById('rpv-zoom-out').addEventListener('click',()=>{zoomFactor=Math.max(zoomFactor-ZOOM_STEP,ZOOM_MIN);renderPDFPage(pageNum);});

let resizeT=null;
new ResizeObserver(()=>{clearTimeout(resizeT);resizeT=setTimeout(()=>{if(pdfDoc)renderPDFPage(pageNum);},250);}).observe(wrap);
let zoomT=null;
new MutationObserver(()=>{clearTimeout(zoomT);zoomT=setTimeout(()=>{syncFC();scheduleRender();},60);}).observe(canvas,{attributes:true,attributeFilter:['width','height']});

/* ── EXPORT PDF ── */
document.getElementById('rpv-export').addEventListener('click',async()=>{
    if(!pdfDoc){alert('PDF belum dimuat.');return;}
    const btn=document.getElementById('rpv-export');
    btn.disabled=true;btn.textContent='⏳ Membuat PDF...';
    try{
        if(!window.jspdf) await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js');
        const{jsPDF}=window.jspdf;
        const annotByPage={};
        annots.forEach(a=>{if(!annotByPage[a.page])annotByPage[a.page]=[];annotByPage[a.page].push(a);});
        let pdf=null;
        for(let p=1;p<=pdfDoc.numPages;p++){
            const page=await pdfDoc.getPage(p);
            const viewport=page.getViewport({scale:2});
            const offCanvas=document.createElement('canvas');
            offCanvas.width=Math.floor(viewport.width);offCanvas.height=Math.floor(viewport.height);
            const offCtx=offCanvas.getContext('2d');
            await page.render({canvasContext:offCtx,viewport}).promise;
            const pageAnnots=annotByPage[p]||[];
            const scale=viewport.scale;
            pageAnnots.forEach(a=>{
                switch(a.type){
                    case'highlight':case'comment':if(a.rect){offCtx.save();offCtx.globalAlpha=.38;offCtx.fillStyle=hex(a.color);offCtx.fillRect(a.rect.x*scale,a.rect.y*scale,a.rect.w*scale,a.rect.h*scale);offCtx.restore();}break;
                    case'underline':if(a.rect){offCtx.save();offCtx.globalAlpha=.9;offCtx.fillStyle=hex(a.color);offCtx.fillRect(a.rect.x*scale,(a.rect.y+a.rect.h)*scale-2,a.rect.w*scale,2);offCtx.restore();}break;
                    case'strikethrough':if(a.rect){offCtx.save();offCtx.globalAlpha=.9;offCtx.fillStyle=hex(a.color);offCtx.fillRect(a.rect.x*scale,(a.rect.y+a.rect.h/2)*scale-1,a.rect.w*scale,2);offCtx.restore();}break;
                    case'freehand':if(a.path_points?.length){offCtx.save();offCtx.strokeStyle=hex(a.color);offCtx.lineWidth=(a.stroke_width||2)*scale;offCtx.lineCap='round';offCtx.lineJoin='round';offCtx.globalAlpha=.9;offCtx.beginPath();offCtx.moveTo(a.path_points[0][0]*scale,a.path_points[0][1]*scale);for(let i=1;i<a.path_points.length;i++)offCtx.lineTo(a.path_points[i][0]*scale,a.path_points[i][1]*scale);offCtx.stroke();offCtx.restore();}break;
                    case'shape':if(a.rect){const sw=(a.stroke_width||2)*scale,col=hex(a.color),st=a.shape_type||'rect';offCtx.save();offCtx.strokeStyle=col;offCtx.lineWidth=sw;offCtx.globalAlpha=.9;offCtx.beginPath();if(st==='rect'){offCtx.strokeRect(a.rect.x*scale+sw/2,a.rect.y*scale+sw/2,a.rect.w*scale-sw,a.rect.h*scale-sw);}else if(st==='ellipse'){const cx=(a.rect.x+a.rect.w/2)*scale,cy=(a.rect.y+a.rect.h/2)*scale,rx=a.rect.w/2*scale-sw/2,ry=a.rect.h/2*scale-sw/2;offCtx.ellipse(cx,cy,Math.max(1,rx),Math.max(1,ry),0,0,Math.PI*2);offCtx.stroke();}offCtx.restore();}break;
                    case'sticky':case'text':if(a.rect&&a.comment){const fs=Math.max(10,(a.stroke_width||14))*scale;offCtx.save();offCtx.font=`600 ${fs}px sans-serif`;offCtx.fillStyle=hex(a.color);offCtx.globalAlpha=.85;a.comment.split('\n').forEach((ln,i)=>offCtx.fillText(ln,a.rect.x*scale,(a.rect.y*scale+fs*(i+1))));offCtx.restore();}break;
                }
            });
            const imgData=offCanvas.toDataURL('image/jpeg',0.92);
            const mmW=viewport.width/2.8346,mmH=viewport.height/2.8346;
            if(!pdf){pdf=new jsPDF({orientation:viewport.width>viewport.height?'landscape':'portrait',unit:'mm',format:[mmW,mmH]});}
            else{pdf.addPage([mmW,mmH],viewport.width>viewport.height?'landscape':'portrait');}
            pdf.addImage(imgData,'JPEG',0,0,mmW,mmH,undefined,'FAST');
        }
        const filename=`annotated_{{ $slug }}_${new Date().toISOString().slice(0,10)}.pdf`;
        pdf.save(filename);
        const status=document.getElementById('rpv-export-status');
        if(status){status.style.display='block';setTimeout(()=>status.style.display='none',8000);}
    }catch(err){console.error('[rpv] export error',err);alert('Gagal export PDF: '+err.message);}
    finally{btn.disabled=false;btn.innerHTML='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Export PDF + Anotasi';}
});

function loadScript(src){return new Promise((res,rej)=>{const s=document.createElement('script');s.src=src;s.onload=res;s.onerror=rej;document.head.appendChild(s);});}

})();
</script>
@endif