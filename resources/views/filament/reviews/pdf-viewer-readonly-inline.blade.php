{{--
resources/views/filament/reviews/pdf-viewer-readonly-inline.blade.php
--}}
@php
$reviewerName = $review->reviewer?->name ?? 'Reviewer';
$versionNo = $review->publicationVersion?->version_number ?? 1;
$decision = $review->decision;
$decisionLabel = match($decision) {
'accepted' => ['Diterima ✅', '#059669', '#D1FAE5'],
'revision_required' => ['Perlu Revisi ✏️', '#D97706', '#FEF3C7'],
'rejected' => ['Ditolak ❌', '#DC2626', '#FEE2E2'],
default => ['Dalam Review ⏳', '#7C3AED', '#EDE9FE'],
};
$annotCount = \App\Models\PdfAnnotation::where('review_id', $review->id)->count();
$uid = 'rpvri_' . $review->id;
@endphp

<style>
    @keyframes {
            {
            $uid
        }
    }

    _spin {
        to {
            transform: rotate(360deg);
        }
    }

    #{{ $uid }
    }

    -wrap {
        font-family: ui-sans-serif, system-ui, sans-serif;
        background: #1A1A1A;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 80vh;
        min-height: 560px;
    }

    #{{ $uid }
    }

    -text-layer span {
        position: absolute;
        white-space: pre;
        color: transparent;
        line-height: 1;
        transform-origin: 0% 0%;
        cursor: text;
    }

    #{{ $uid }
    }

    -canvas-wrap {
        scroll-behavior: smooth;
    }

    . {
            {
            $uid
        }
    }

    -search-hl {
        position: absolute;
        background: rgba(255, 215, 0, .45);
        border-radius: 2px;
        pointer-events: none;
        z-index: 7;
    }

    . {
            {
            $uid
        }
    }

    -search-hl.active-match {
        background: rgba(255, 107, 24, .6);
        outline: 2px solid #FF6B18;
    }

    . {
            {
            $uid
        }
    }

    -panel-item {
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

    . {
            {
            $uid
        }
    }

    -panel-item:hover {
        border-color: #3d3d3d;
        background: #1f1f1f;
    }

    . {
            {
            $uid
        }
    }

    -panel-item.active-item {
        border-color: #FF6B18;
        background: rgba(255, 107, 24, .08);
    }

    . {
            {
            $uid
        }
    }

    -sticky-note {
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

    . {
            {
            $uid
        }
    }

    -sticky-note[data-color="yellow"] {
        background: #FEF9C3;
        border: 1.5px solid #FDE047;
    }

    . {
            {
            $uid
        }
    }

    -sticky-note[data-color="green"] {
        background: #DCFCE7;
        border: 1.5px solid #86EFAC;
    }

    . {
            {
            $uid
        }
    }

    -sticky-note[data-color="blue"] {
        background: #DBEAFE;
        border: 1.5px solid #93C5FD;
    }

    . {
            {
            $uid
        }
    }

    -sticky-note[data-color="orange"] {
        background: #FFEDD5;
        border: 1.5px solid #FDBA74;
    }

    . {
            {
            $uid
        }
    }

    -sticky-note[data-color="pink"] {
        background: #FCE7F3;
        border: 1.5px solid #F9A8D4;
    }

    . {
            {
            $uid
        }
    }

    -sticky-note[data-color="purple"] {
        background: #EDE9FE;
        border: 1.5px solid #C4B5FD;
    }

    . {
            {
            $uid
        }
    }

    -sticky-note[data-color="red"] {
        background: #FEE2E2;
        border: 1.5px solid #FCA5A5;
    }

    . {
            {
            $uid
        }
    }

    -sticky-note[data-color="cyan"] {
        background: #CFFAFE;
        border: 1.5px solid #67E8F9;
    }

    . {
            {
            $uid
        }
    }

    -sticky-note[data-color="black"] {
        background: #1F2937;
        border: 1.5px solid #374151;
    }

    . {
            {
            $uid
        }
    }

    -sticky-note[data-color="white"] {
        background: #F9FAFB;
        border: 1.5px solid #D1D5DB;
    }

    . {
            {
            $uid
        }
    }

    -sticky-note .rpvri-sn-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .25rem .4rem;
        background: rgba(0, 0, 0, .1);
        font-size: 11px;
        font-weight: 700;
        color: rgba(0, 0, 0, .7);
    }

    . {
            {
            $uid
        }
    }

    -sticky-note[data-color="black"] .rpvri-sn-header {
        color: #9CA3AF;
    }

    . {
            {
            $uid
        }
    }

    -sticky-note .rpvri-sn-body {
        padding: .4rem .5rem;
        font-size: 12px;
        color: rgba(0, 0, 0, .85);
        word-break: break-word;
        white-space: pre-wrap;
    }

    . {
            {
            $uid
        }
    }

    -sticky-note[data-color="black"] .rpvri-sn-body {
        color: #D1D5DB;
    }
</style>

<div id="{{ $uid }}-wrap">

    {{-- META HEADER --}}
    <div style="background:#111;border-bottom:1px solid #2d2d2d;padding:.65rem 1rem;
                display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;flex-shrink:0;">
        <div
            style="display:flex;align-items:center;gap:.5rem;background:#1f1f1f;border:1px solid #3d3d3d;border-radius:8px;padding:.3rem .65rem;">
            <div
                style="width:26px;height:26px;background:linear-gradient(135deg,#FF6B18,#e55d10);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0;">
                {{ strtoupper(substr($reviewerName, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:9px;color:#6b7280;line-height:1;">Direview oleh</div>
                <div style="font-size:11px;font-weight:700;color:#fff;line-height:1.3;">{{ $reviewerName }}</div>
            </div>
        </div>
        <div style="background:#1f1f1f;border:1px solid #3d3d3d;border-radius:8px;padding:.3rem .65rem;">
            <div style="font-size:9px;color:#6b7280;line-height:1;">Versi</div>
            <div style="font-size:11px;font-weight:700;color:#a78bfa;line-height:1.3;">v{{ $versionNo }}</div>
        </div>
        <div
            style="background:{{ $decisionLabel[2] }};border:1.5px solid {{ $decisionLabel[1] }};border-radius:8px;padding:.3rem .65rem;">
            <div style="font-size:9px;color:{{ $decisionLabel[1] }};font-weight:700;line-height:1;">Keputusan</div>
            <div style="font-size:11px;font-weight:700;color:{{ $decisionLabel[1] }};line-height:1.3;">{{
                $decisionLabel[0] }}</div>
        </div>
        <div style="background:#1f1f1f;border:1px solid #3d3d3d;border-radius:8px;padding:.3rem .65rem;">
            <div style="font-size:9px;color:#6b7280;line-height:1;">Total Anotasi</div>
            <div style="font-size:11px;font-weight:700;color:#FF6B18;line-height:1.3;">{{ $annotCount }} catatan</div>
        </div>
        <div
            style="margin-left:auto;display:flex;align-items:center;gap:.35rem;background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.3);border-radius:8px;padding:.3rem .65rem;">
            <svg style="width:13px;height:13px;flex-shrink:0;" fill="none" stroke="#60a5fa" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span style="font-size:10px;font-weight:700;color:#60a5fa;">Mode Lihat Saja</span>
        </div>
    </div>

    {{-- TOOLBAR --}}
    <div
        style="background:#262626;border-bottom:1px solid #3d3d3d;padding:.35rem .75rem;display:flex;align-items:center;gap:.4rem;flex-shrink:0;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:.3rem;background:#333;border-radius:7px;padding:.25rem .45rem;">
            <button id="{{ $uid }}-prev"
                style="width:26px;height:26px;border-radius:5px;border:none;background:#4d4d4d;color:#fff;cursor:pointer;font-size:14px;">‹</button>
            <input type="number" id="{{ $uid }}-page-input"
                style="width:36px;text-align:center;background:#1a1a1a;border:1.5px solid #4d4d4d;color:#fff;border-radius:5px;font-size:12px;font-weight:700;padding:.15rem .25rem;outline:none;"
                value="1" min="1">
            <span style="color:#555;font-size:11px;">/</span>
            <span id="{{ $uid }}-page-total" style="color:#9ca3af;font-size:12px;font-weight:600;">—</span>
            <button id="{{ $uid }}-next"
                style="width:26px;height:26px;border-radius:5px;border:none;background:#4d4d4d;color:#fff;cursor:pointer;font-size:14px;">›</button>
        </div>
        <button id="{{ $uid }}-zoom-out"
            style="padding:.25rem .55rem;border-radius:5px;border:none;background:#3d3d3d;color:#d1d5db;cursor:pointer;font-size:14px;font-weight:700;">−</button>
        <span id="{{ $uid }}-zoom-val"
            style="color:#d1d5db;font-size:11px;font-weight:700;min-width:36px;text-align:center;">100%</span>
        <button id="{{ $uid }}-zoom-in"
            style="padding:.25rem .55rem;border-radius:5px;border:none;background:#3d3d3d;color:#d1d5db;cursor:pointer;font-size:14px;font-weight:700;">+</button>
        <button id="{{ $uid }}-search-btn"
            style="display:flex;align-items:center;gap:.3rem;padding:.25rem .6rem;border-radius:5px;border:none;background:#3d3d3d;color:#d1d5db;cursor:pointer;font-size:11px;font-weight:600;">
            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Cari
        </button>
        <button id="{{ $uid }}-panel-btn"
            style="position:relative;display:flex;align-items:center;gap:.3rem;padding:.25rem .6rem;border-radius:5px;border:none;background:#3d3d3d;color:#d1d5db;cursor:pointer;font-size:11px;font-weight:600;">
            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <line x1="8" y1="6" x2="21" y2="6" stroke-width="2" />
                <line x1="8" y1="12" x2="21" y2="12" stroke-width="2" />
                <line x1="8" y1="18" x2="21" y2="18" stroke-width="2" />
                <circle cx="3" cy="6" r="1.5" fill="currentColor" />
                <circle cx="3" cy="12" r="1.5" fill="currentColor" />
                <circle cx="3" cy="18" r="1.5" fill="currentColor" />
            </svg>
            Anotasi
            <span id="{{ $uid }}-badge"
                style="display:none;background:#FF6B18;color:#fff;font-size:9px;font-weight:700;min-width:15px;height:15px;border-radius:99px;align-items:center;justify-content:center;padding:0 2px;">0</span>
        </button>
        <div style="flex:1;"></div>
        <span id="{{ $uid }}-progress-txt" style="font-size:10px;color:#4b5563;white-space:nowrap;"></span>
    </div>

    {{-- Progress bar --}}
    <div style="height:2px;background:#333;flex-shrink:0;">
        <div id="{{ $uid }}-progress-bar"
            style="height:100%;background:linear-gradient(90deg,#FF6B18,#e55d10);width:0%;transition:width .3s;"></div>
    </div>

    {{-- MAIN AREA --}}
    <div style="flex:1;display:flex;overflow:hidden;position:relative;">

        {{-- Canvas wrap --}}
        <div id="{{ $uid }}-canvas-wrap"
            style="flex:1;overflow:auto;display:flex;justify-content:center;align-items:flex-start;background:#404040;position:relative;">

            {{-- Loading --}}
            <div id="{{ $uid }}-loading"
                style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.75rem;background:#1a1a1a;z-index:20;">
                <div
                    style="width:34px;height:34px;border:3px solid #333;border-top-color:#FF6B18;border-radius:50%;animation:{{ $uid }}_spin .8s linear infinite;">
                </div>
                <p style="color:#fff;font-size:13px;font-weight:600;margin:0;">Memuat dokumen...</p>
                <p id="{{ $uid }}-load-sub" style="color:#6b7280;font-size:11px;margin:0;">Harap tunggu sebentar</p>
                <button id="{{ $uid }}-retry-btn" type="button"
                    style="display:none;margin-top:.5rem;padding:.35rem .8rem;background:#FF6B18;color:#fff;border:none;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;">🔄
                    Muat Ulang</button>
            </div>

            {{-- Stage --}}
            <div id="{{ $uid }}-stage"
                style="position:relative;display:none;margin:1rem;box-shadow:0 8px 32px rgba(0,0,0,.5);">
                <canvas id="{{ $uid }}-canvas"></canvas>
                <div id="{{ $uid }}-text-layer"
                    style="position:absolute;inset:0;overflow:hidden;pointer-events:none;user-select:text;-webkit-user-select:text;">
                </div>
                <div id="{{ $uid }}-annotation-layer"
                    style="position:absolute;inset:0;pointer-events:none;overflow:visible;z-index:5;"></div>
                <canvas id="{{ $uid }}-freehand-canvas"
                    style="position:absolute;inset:0;pointer-events:none;z-index:10;touch-action:none;"></canvas>
            </div>
        </div>

        {{-- Annotation Panel --}}
        <div id="{{ $uid }}-panel"
            style="width:0;overflow:hidden;background:#111;border-left:1px solid #2d2d2d;display:flex;flex-direction:column;transition:width .25s ease;flex-shrink:0;">
            <div
                style="padding:.65rem .75rem;border-bottom:1px solid #2d2d2d;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <span style="font-size:12px;font-weight:700;color:#fff;">📝 Anotasi Reviewer</span>
                <button id="{{ $uid }}-panel-close"
                    style="background:none;border:none;color:#6b7280;cursor:pointer;font-size:15px;line-height:1;padding:2px 4px;">✕</button>
            </div>
            <div style="padding:.4rem .65rem;border-bottom:1px solid #1f1f1f;flex-shrink:0;">
                <select id="{{ $uid }}-panel-filter"
                    style="width:100%;background:#1f1f1f;border:1.5px solid #3d3d3d;color:#d1d5db;border-radius:5px;font-size:11px;padding:.2rem .35rem;outline:none;cursor:pointer;">
                    <option value="all">Semua halaman</option>
                    <option value="current">Halaman ini saja</option>
                </select>
            </div>
            <div id="{{ $uid }}-panel-list"
                style="flex:1;overflow-y:auto;padding:.35rem;display:flex;flex-direction:column;gap:3px;">
                <div style="text-align:center;color:#4b5563;font-size:11px;padding:1.25rem;">Memuat...</div>
            </div>
            <div
                style="padding:.5rem .65rem;border-top:1px solid #2d2d2d;display:flex;gap:.4rem;flex-wrap:wrap;flex-shrink:0;">
                <div style="flex:1;background:#1f1f1f;border-radius:5px;padding:.35rem .4rem;text-align:center;">
                    <div id="{{ $uid }}-stat-total" style="font-size:15px;font-weight:700;color:#FF6B18;">0</div>
                    <div style="font-size:9px;color:#6b7280;">Total</div>
                </div>
                <div style="flex:1;background:#1f1f1f;border-radius:5px;padding:.35rem .4rem;text-align:center;">
                    <div id="{{ $uid }}-stat-page" style="font-size:15px;font-weight:700;color:#60a5fa;">0</div>
                    <div style="font-size:9px;color:#6b7280;">Halaman ini</div>
                </div>
                <div style="flex:1;background:#1f1f1f;border-radius:5px;padding:.35rem .4rem;text-align:center;">
                    <div id="{{ $uid }}-stat-pages" style="font-size:15px;font-weight:700;color:#4ade80;">0</div>
                    <div style="font-size:9px;color:#6b7280;">Hal. berisi</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search Overlay (posisi:absolute dalam wrap) --}}
    <div id="{{ $uid }}-search"
        style="position:absolute;inset:0;z-index:9999;background:rgba(0,0,0,.6);display:none;align-items:flex-start;justify-content:center;padding-top:60px;">
        <div
            style="background:#1a1a1a;border:1.5px solid #3d3d3d;border-radius:12px;padding:.875rem;width:400px;max-width:calc(100% - 2rem);box-shadow:0 16px 48px rgba(0,0,0,.7);">
            <div style="display:flex;gap:.35rem;">
                <input type="text" id="{{ $uid }}-search-input"
                    style="flex:1;background:#2d2d2d;border:1.5px solid #3d3d3d;color:#fff;border-radius:7px;padding:.4rem .65rem;font-size:13px;outline:none;"
                    placeholder="Cari kata atau kalimat...">
                <button id="{{ $uid }}-sprev"
                    style="width:30px;height:30px;background:#2d2d2d;border:1px solid #3d3d3d;border-radius:6px;color:#9ca3af;cursor:pointer;font-size:13px;">↑</button>
                <button id="{{ $uid }}-snext"
                    style="width:30px;height:30px;background:#2d2d2d;border:1px solid #3d3d3d;border-radius:6px;color:#9ca3af;cursor:pointer;font-size:13px;">↓</button>
                <button id="{{ $uid }}-sclose"
                    style="width:30px;height:30px;background:#2d2d2d;border:1px solid #3d3d3d;border-radius:6px;color:#9ca3af;cursor:pointer;font-size:13px;">✕</button>
            </div>
            <div id="{{ $uid }}-search-status" style="font-size:11px;color:#6b7280;margin-top:.35rem;">Ketik untuk
                mencari...</div>
            <div id="{{ $uid }}-search-results"
                style="margin-top:.4rem;max-height:200px;overflow-y:auto;display:flex;flex-direction:column;gap:2px;">
            </div>
        </div>
    </div>

    {{-- Tooltip (posisi:absolute dalam wrap) --}}
    <div id="{{ $uid }}-tooltip"
        style="position:absolute;z-index:9998;background:#1a1a1a;border:1.5px solid #3d3d3d;border-radius:9px;padding:.55rem .75rem;min-width:190px;max-width:300px;box-shadow:0 8px 24px rgba(0,0,0,.5);display:none;pointer-events:auto;">
        <div id="{{ $uid }}-tip-reviewer" style="font-size:10px;color:#FF6B18;font-weight:700;margin-bottom:.2rem;">
        </div>
        <div id="{{ $uid }}-tip-text" style="font-size:12px;color:#d1d5db;word-break:break-word;margin-bottom:.35rem;">
        </div>
        <button id="{{ $uid }}-tip-close"
            style="padding:.2rem .5rem;background:#2d2d2d;border:1px solid #3d3d3d;color:#9ca3af;border-radius:5px;font-size:11px;cursor:pointer;width:100%;">✕
            Tutup</button>
    </div>

</div>{{-- /#uid-wrap --}}

<script>
    window['{{ $uid }}_CFG'] = {
    pdfUrl  : @json($pdfUrl),
    apiUrl  : @json($apiUrl),
    reviewId: @json($review->id),
    reviewer: @json($reviewerName),
    uid     : '{{ $uid }}',
};
</script>
<script>
    (function(){
'use strict';
var CFG=window['{{ $uid }}_CFG'];
if(!CFG){console.error('[RPVRI] config missing');return;}
var GUARD='_rpvri_'+CFG.reviewId;
if(window[GUARD]){console.log('[RPVRI] already running');return;}
var PDFJS_CDN='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
var WORKER_CDN='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
var retryTimer=setTimeout(function(){
    var stage=document.getElementById(CFG.uid+'-stage');
    var btn=document.getElementById(CFG.uid+'-retry-btn');
    var sub=document.getElementById(CFG.uid+'-load-sub');
    if(stage&&stage.style.display==='none'&&btn){btn.style.display='block';if(sub)sub.textContent='Memakan waktu lebih lama...';}
},12000);
window[CFG.uid+'_retry']=function(){
    clearTimeout(retryTimer);window[GUARD]=false;
    var l=document.getElementById(CFG.uid+'-loading');
    if(l){l.style.display='';l.innerHTML='<div style="width:34px;height:34px;border:3px solid #333;border-top-color:#FF6B18;border-radius:50%;animation:'+CFG.uid+'_spin .8s linear infinite;"></div><p style="color:#fff;font-size:13px;font-weight:600;margin:0;">Memuat ulang...</p><p id="'+CFG.uid+'-load-sub" style="color:#6b7280;font-size:11px;margin:0;">Harap tunggu sebentar</p>';}
    var s=document.getElementById(CFG.uid+'-stage');if(s)s.style.display='none';
    boot();
};
var rb=document.getElementById(CFG.uid+'-retry-btn');
if(rb)rb.onclick=function(){window[CFG.uid+'_retry']();};
function showFatalError(msg){
    var l=document.getElementById(CFG.uid+'-loading');if(!l)return;
    l.innerHTML='<div style="font-size:2rem">⚠️</div><p style="color:#ef4444;font-weight:700;font-size:13px;margin:0;">'+msg+'</p>'
        +'<button type="button" onclick="window[\''+CFG.uid+'_retry\']()" style="margin-top:.75rem;padding:.35rem .8rem;background:#FF6B18;color:#fff;border:none;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;">🔄 Coba Lagi</button>';
}
function boot(){
    if(typeof pdfjsLib!=='undefined'){pdfjsLib.GlobalWorkerOptions.workerSrc=WORKER_CDN;pdfjsLib.verbosity=0;init();}
    else{var s=document.createElement('script');s.src=PDFJS_CDN;s.crossOrigin='anonymous';
        s.onload=function(){pdfjsLib.GlobalWorkerOptions.workerSrc=WORKER_CDN;pdfjsLib.verbosity=0;init();};
        s.onerror=function(){showFatalError('Gagal memuat library PDF. Periksa koneksi internet.');};
        document.head.appendChild(s);}
}
function init(){
    if(window[GUARD]){console.log('[RPVRI] already running');return;}
    window[GUARD]=true;
    var u=CFG.uid;
    function $i(id){return document.getElementById(u+'-'+id);}
    var COLORS={yellow:'#FFD700',green:'#4ADE80',red:'#EF4444',blue:'#60A5FA',orange:'#FF6B18',black:'#111111',white:'#FFFFFF',pink:'#F472B6',purple:'#A78BFA',cyan:'#22D3EE'};
    function hex(n){return COLORS[n]||'#FFD700';}
    var pdfDoc=null,pageNum=1,pageRendering=false,pendingPage=null,pendingResolvers=[];
    var baseScale=1.0,zoomFactor=1.0,baseScaleComputed=false;
    var ZOOM_MIN=0.5,ZOOM_MAX=4.0,ZOOM_STEP=0.25,DPR=window.devicePixelRatio||1;
    var annots=[],panelOpen=false,filterMode='all';
    var searchResults=[],searchIndex=-1,searchHLs=[],searchQuery='',searchDebounce=null,activeAnnotId=null;
    var wrap=$i('canvas-wrap'),stage=$i('stage'),canvas=$i('canvas'),ctx=canvas.getContext('2d');
    var textLayer=$i('text-layer'),annotLayer=$i('annotation-layer');
    var freeCanvas=$i('freehand-canvas'),freeCtx=freeCanvas?freeCanvas.getContext('2d'):null;
    var loadingEl=$i('loading'),loadSub=$i('load-sub'),tooltip=$i('tooltip'),panel=$i('panel');
    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');}
    function syncFC(){if(!freeCanvas)return;var w=stage.offsetWidth,h=stage.offsetHeight;if(freeCanvas.width!==w||freeCanvas.height!==h){freeCanvas.width=w;freeCanvas.height=h;}freeCanvas.style.width=w+'px';freeCanvas.style.height=h+'px';}
    async function loadAnnotations(){
        try{var r=await fetch(CFG.apiUrl,{credentials:'same-origin',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
            if(!r.ok)throw new Error(r.status);var j=await r.json();var rows=Array.isArray(j.data)?j.data:[];
            annots=rows.map(function(a){
                if(!a.rect&&a.rect_x!=null)a.rect={x:+a.rect_x,y:+a.rect_y,w:+a.rect_w,h:+a.rect_h};
                if(a.type==='shape'&&(a.shape_type==='arrow'||a.shape_type==='line')){if(a.arrow_x1==null&&Array.isArray(a.path_points)&&a.path_points.length>=2){a.arrow_x1=+a.path_points[0][0];a.arrow_y1=+a.path_points[0][1];a.arrow_x2=+a.path_points[1][0];a.arrow_y2=+a.path_points[1][1];}}
                return a;
            });
            renderAnnotations();buildPanel();updateBadge();updateStats();
        }catch(e){console.error('[RPVRI] load annots:',e);}
    }
    function renderAnnotations(){
        annotLayer.innerHTML='';annotLayer.style.pointerEvents='none';syncFC();
        if(freeCtx)freeCtx.clearRect(0,0,freeCanvas.width,freeCanvas.height);
        stage.querySelectorAll('.'+u+'-sticky-note').forEach(function(e){e.remove();});
        var sc=baseScale*zoomFactor;
        annots.filter(function(a){return a.page===pageNum;}).forEach(function(a){
            if(a.type==='highlight'||a.type==='comment')rHL(a,sc);
            else if(a.type==='underline')rUL(a,sc);
            else if(a.type==='strikethrough')rST(a,sc);
            else if(a.type==='freehand')rFH(a,sc);
            else if(a.type==='shape')rSH(a,sc);
            else if(a.type==='sticky')rSticky(a,sc);
        });
        updateStats();if(searchResults.length>0&&searchQuery)applySearchHL();
    }
    function rHL(a,s){if(!a.rect)return;var el=document.createElement('div'),act=activeAnnotId==a.id;el.dataset.annotId=String(a.id);el.style.cssText='position:absolute;left:'+(a.rect.x*s)+'px;top:'+(a.rect.y*s)+'px;width:'+(a.rect.w*s)+'px;height:'+(a.rect.h*s)+'px;background:'+hex(a.color)+';opacity:'+(act?.75:.38)+';border-radius:2px;pointer-events:auto;cursor:pointer;z-index:5;outline:'+(act?'2px solid #FF6B18':'none')+';transition:opacity .15s;';if(a.type==='comment'&&a.comment){var dot=document.createElement('span');dot.style.cssText='position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:#60A5FA;border-radius:50%;pointer-events:none;';el.appendChild(dot);}attachClick(el,a);annotLayer.appendChild(el);}
    function rUL(a,s){if(!a.rect)return;var el=document.createElement('div');el.dataset.annotId=String(a.id);var t=Math.max(1.5,2*s);el.style.cssText='position:absolute;left:'+(a.rect.x*s)+'px;top:'+((a.rect.y+a.rect.h)*s-t)+'px;width:'+(a.rect.w*s)+'px;height:'+t+'px;background:'+hex(a.color)+';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';attachClick(el,a);annotLayer.appendChild(el);}
    function rST(a,s){if(!a.rect)return;var el=document.createElement('div');el.dataset.annotId=String(a.id);var t=Math.max(1.5,2*s),top=a.rect.y*s+a.rect.h*s*0.62-t/2;el.style.cssText='position:absolute;left:'+(a.rect.x*s)+'px;top:'+top+'px;width:'+(a.rect.w*s)+'px;height:'+t+'px;background:'+hex(a.color)+';pointer-events:auto;cursor:pointer;z-index:5;opacity:.9;border-radius:1px;';attachClick(el,a);annotLayer.appendChild(el);}
    function rFH(a,s){if(!a.path_points||!a.path_points.length||!freeCtx)return;var pts=a.path_points;freeCtx.save();freeCtx.strokeStyle=hex(a.color);freeCtx.lineWidth=(a.stroke_width||2)*s;freeCtx.lineCap='round';freeCtx.lineJoin='round';freeCtx.globalAlpha=.92;freeCtx.beginPath();freeCtx.moveTo(pts[0][0]*s,pts[0][1]*s);for(var i=1;i<pts.length;i++)freeCtx.lineTo(pts[i][0]*s,pts[i][1]*s);freeCtx.stroke();freeCtx.restore();if(a.rect&&(a.rect.w>0||a.rect.h>0)){var hit=document.createElement('div');hit.dataset.annotId=String(a.id);hit.style.cssText='position:absolute;left:'+((a.rect.x-8)*s)+'px;top:'+((a.rect.y-8)*s)+'px;width:'+((a.rect.w+16)*s)+'px;height:'+((a.rect.h+16)*s)+'px;background:transparent;pointer-events:auto;cursor:pointer;z-index:6;';attachClick(hit,a);annotLayer.appendChild(hit);}}
    function rSH(a,s){if(!a.rect)return;var col=hex(a.color),sw=Math.max(1,(a.stroke_width||2)*s),st=a.shape_type||'rect';var el=document.createElement('div');el.dataset.annotId=String(a.id);if(st==='arrow'||st==='line'){var ax1=a.arrow_x1!=null?a.arrow_x1*s:a.rect.x*s,ay1=a.arrow_y1!=null?a.arrow_y1*s:(a.rect.y+a.rect.h/2)*s,ax2=a.arrow_x2!=null?a.arrow_x2*s:(a.rect.x+a.rect.w)*s,ay2=a.arrow_y2!=null?a.arrow_y2*s:(a.rect.y+a.rect.h/2)*s;var bx=Math.min(ax1,ax2)-sw*2,by=Math.min(ay1,ay2)-sw*2,bw=Math.abs(ax2-ax1)+sw*4,bh=Math.abs(ay2-ay1)+sw*4,lx1=ax1-bx,ly1=ay1-by,lx2=ax2-bx,ly2=ay2-by;el.style.cssText='position:absolute;left:'+bx+'px;top:'+by+'px;width:'+bw+'px;height:'+bh+'px;pointer-events:auto;cursor:pointer;z-index:5;';var svg='';if(st==='line'){svg='<line x1="'+lx1+'" y1="'+ly1+'" x2="'+lx2+'" y2="'+ly2+'" stroke="'+col+'" stroke-width="'+sw+'" stroke-linecap="round"/>';}else{var dx=lx2-lx1,dy=ly2-ly1,len=Math.sqrt(dx*dx+dy*dy);if(len>1){var hl=Math.min(len*.35,Math.max(10,sw*5)),ang=Math.atan2(dy,dx),hx1=lx2-hl*Math.cos(ang-Math.PI/6),hy1=ly2-hl*Math.sin(ang-Math.PI/6),hx2=lx2-hl*Math.cos(ang+Math.PI/6),hy2=ly2-hl*Math.sin(ang+Math.PI/6);svg='<line x1="'+lx1+'" y1="'+ly1+'" x2="'+lx2+'" y2="'+ly2+'" stroke="'+col+'" stroke-width="'+sw+'" stroke-linecap="round"/><polyline points="'+hx1+','+hy1+' '+lx2+','+ly2+' '+hx2+','+hy2+'" fill="none" stroke="'+col+'" stroke-width="'+sw+'" stroke-linecap="round" stroke-linejoin="round"/>';}}el.innerHTML='<svg xmlns="http://www.w3.org/2000/svg" width="'+bw+'" height="'+bh+'" style="overflow:visible;display:block;pointer-events:none">'+svg+'</svg>';}else{var x=a.rect.x*s,y=a.rect.y*s,w=Math.max(4,a.rect.w*s),h=Math.max(4,a.rect.h*s);el.style.cssText='position:absolute;left:'+x+'px;top:'+y+'px;width:'+w+'px;height:'+h+'px;pointer-events:auto;cursor:pointer;z-index:5;';var svg2='';if(st==='rect')svg2='<rect x="'+(sw/2)+'" y="'+(sw/2)+'" width="'+Math.max(1,w-sw)+'" height="'+Math.max(1,h-sw)+'" rx="2" fill="none" stroke="'+col+'" stroke-width="'+sw+'"/>';else if(st==='ellipse')svg2='<ellipse cx="'+(w/2)+'" cy="'+(h/2)+'" rx="'+Math.max(1,w/2-sw/2)+'" ry="'+Math.max(1,h/2-sw/2)+'" fill="none" stroke="'+col+'" stroke-width="'+sw+'"/>';el.innerHTML='<svg xmlns="http://www.w3.org/2000/svg" width="'+w+'" height="'+h+'" style="overflow:visible;display:block;pointer-events:none">'+svg2+'</svg>';}attachClick(el,a);annotLayer.appendChild(el);}
    function rSticky(a,s){if(!a.rect)return;var note=document.createElement('div');note.className=u+'-sticky-note';note.dataset.annotId=String(a.id);note.dataset.color=a.color||'yellow';note.style.left=(a.rect.x*s)+'px';note.style.top=(a.rect.y*s)+'px';note.innerHTML='<div class="rpvri-sn-header"><span>📌 '+esc(CFG.reviewer).substring(0,14)+'</span></div><div class="rpvri-sn-body">'+esc(a.comment)+'</div>';note.addEventListener('click',function(ev){ev.stopPropagation();showTip(a,ev.clientX,ev.clientY);});stage.appendChild(note);}
    function attachClick(el,a){el.addEventListener('click',function(ev){ev.stopPropagation();showTip(a,ev.clientX,ev.clientY);});el.addEventListener('touchend',function(ev){ev.stopPropagation();if(ev.cancelable)ev.preventDefault();var t=ev.changedTouches[0];showTip(a,t.clientX,t.clientY);},{passive:false});}
    function showTip(a,cx,cy){
        var ic={highlight:'✏️',underline:'__',strikethrough:'~~',freehand:'🖊',shape:'⬛',comment:'💬',sticky:'📌'};
        activeAnnotId=a.id;
        var rev=$i('tip-reviewer'),txt=$i('tip-text');
        if(rev)rev.textContent=(ic[a.type]||'•')+' '+a.type+' · oleh '+CFG.reviewer;
        var msg=a.comment?a.comment.substring(0,120):(a.selected_text?'"'+a.selected_text.substring(0,120)+'"':'Anotasi '+a.type+' di hal.'+a.page);
        if(txt)txt.textContent=msg;
        tooltip.style.display='block';
        var wRect=document.getElementById(u+'-wrap').getBoundingClientRect();
        var left=cx-wRect.left-160,top=cy-wRect.top+8;
        if(left+300>wRect.width-8)left=wRect.width-308;
        if(top+120>wRect.height-8)top=(cy-wRect.top)-128;
        tooltip.style.left=Math.max(4,left)+'px';tooltip.style.top=Math.max(4,top)+'px';
        document.querySelectorAll('.'+u+'-panel-item').forEach(function(el){el.classList.toggle('active-item',el.dataset.annotId==a.id);});
        renderAnnotations();
    }
    $i('tip-close')&&$i('tip-close').addEventListener('click',function(){tooltip.style.display='none';activeAnnotId=null;renderAnnotations();});
    document.addEventListener('click',function(e){if(tooltip.style.display==='block'&&!tooltip.contains(e.target)&&!e.target.closest('[data-annot-id],.'+u+'-sticky-note')){tooltip.style.display='none';activeAnnotId=null;}});
    function togglePanel(open){panelOpen=open;panel.style.width=open?'272px':'0';}
    $i('panel-btn')&&$i('panel-btn').addEventListener('click',function(){togglePanel(!panelOpen);});
    $i('panel-close')&&$i('panel-close').addEventListener('click',function(){togglePanel(false);});
    $i('panel-filter')&&$i('panel-filter').addEventListener('change',function(){filterMode=this.value;buildPanel();});
    function buildPanel(){
        var list=$i('panel-list');if(!list)return;
        var filtered=filterMode==='current'?annots.filter(function(a){return a.page===pageNum;}):[].concat(annots);
        if(!filtered.length){list.innerHTML='<div style="text-align:center;color:#4b5563;font-size:11px;padding:1.25rem;">'+(filterMode==='current'?'Tidak ada anotasi di halaman ini.':'Belum ada anotasi.')+'</div>';return;}
        var ic={highlight:'✏️',underline:'__',strikethrough:'~~',freehand:'🖊',shape:'⬛',comment:'💬',sticky:'📌'};
        list.innerHTML='';
        filtered.slice().sort(function(a,b){return a.page-b.page||a.id-b.id;}).forEach(function(a){
            var el=document.createElement('div');el.className=u+'-panel-item';el.dataset.annotId=String(a.id);
            if(activeAnnotId==a.id)el.classList.add('active-item');
            var txt=a.comment||a.selected_text||a.shape_type||'—';
            el.innerHTML='<div style="width:9px;height:9px;border-radius:50%;background:'+hex(a.color)+';flex-shrink:0;margin-top:3px;"></div><div style="flex:1;min-width:0;"><div style="display:flex;align-items:center;gap:.3rem;margin-bottom:.12rem;"><span style="font-size:9px;font-weight:700;color:#9ca3af;">'+(ic[a.type]||'•')+' '+a.type+'</span><span style="font-size:9px;color:#FF6B18;margin-left:auto;">Hal.'+a.page+'</span></div><div style="font-size:10px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+esc(txt).substring(0,80)+'</div></div>';
            el.addEventListener('click',function(){tooltip.style.display='none';if(a.page!==pageNum){renderPdfPage(a.page).then(function(){activeAnnotId=a.id;renderAnnotations();showTipById(a);});}else{activeAnnotId=a.id;renderAnnotations();showTipById(a);}});
            list.appendChild(el);
        });
    }
    function showTipById(a){var el=document.querySelector('[data-annot-id="'+a.id+'"]');if(el){var r=el.getBoundingClientRect();showTip(a,r.left+r.width/2,r.top);el.scrollIntoView({behavior:'smooth',block:'center'});}}
    function updateBadge(){var b=$i('badge'),n=annots.length;if(b){b.textContent=n>99?'99+':String(n);b.style.display=n>0?'flex':'none';}}
    function updateStats(){var on=annots.filter(function(a){return a.page===pageNum;}).length,pgs=new Set(annots.map(function(a){return a.page;})).size;var st=$i('stat-total');if(st)st.textContent=annots.length;var sp=$i('stat-page');if(sp)sp.textContent=on;var spp=$i('stat-pages');if(spp)spp.textContent=pgs;}
    function renderPdfPage(num){if(pageRendering){pendingPage=num;return new Promise(function(resolve){pendingResolvers.push(resolve);});}return doRenderPage(num);}
    async function doRenderPage(num){
        pageRendering=true;pageNum=num;
        var page=await pdfDoc.getPage(num);
        if(!baseScaleComputed){var cw=wrap.clientWidth||800,nw=page.getViewport({scale:1}).width;baseScale=Math.max(0.5,Math.min((cw-32)/nw,2.5));baseScaleComputed=true;}
        var cs=baseScale*zoomFactor,vpCss=page.getViewport({scale:cs}),vpR=page.getViewport({scale:cs*DPR});
        canvas.width=Math.floor(vpR.width);canvas.height=Math.floor(vpR.height);canvas.style.width=Math.floor(vpCss.width)+'px';canvas.style.height=Math.floor(vpCss.height)+'px';stage.style.width=Math.floor(vpCss.width)+'px';stage.style.height=Math.floor(vpCss.height)+'px';
        await page.render({canvasContext:ctx,viewport:vpR}).promise.catch(function(e){console.warn(e);});
        pageRendering=false;
        if(pendingPage!==null){var pp=pendingPage;pendingPage=null;await doRenderPage(pp);pendingResolvers.splice(0).forEach(function(fn){fn();});return;}
        textLayer.innerHTML='';textLayer.style.width=Math.floor(vpCss.width)+'px';textLayer.style.height=Math.floor(vpCss.height)+'px';
        var content=await page.getTextContent();
        content.items.forEach(function(item){if(!item.str||!item.str.trim())return;var tx=pdfjsLib.Util.transform(vpCss.transform,item.transform),fh=Math.sqrt(tx[2]*tx[2]+tx[3]*tx[3]),angle=Math.atan2(tx[1],tx[0]);var span=document.createElement('span');span.textContent=item.str;span.style.fontSize=fh+'px';span.style.left=tx[4]+'px';span.style.top=(tx[5]-fh)+'px';span.style.transformOrigin='0% 0%';textLayer.appendChild(span);var tw2=item.width*cs,mw=span.getBoundingClientRect().width,t=angle!==0?'rotate('+(-angle)+'rad)':'';if(mw>1&&tw2>0)t+=' scaleX('+(tw2/mw)+')';if(t.trim())span.style.transform=t.trim();});
        stage.style.display='block';loadingEl.style.display='none';clearTimeout(retryTimer);
        var pi=$i('page-input');if(pi)pi.value=num;var pr=$i('prev');if(pr)pr.disabled=num<=1;var nx=$i('next');if(nx)nx.disabled=!pdfDoc||num>=pdfDoc.numPages;
        var pct=pdfDoc?(num/pdfDoc.numPages*100):0;var pb=$i('progress-bar');if(pb)pb.style.width=pct+'%';var zv=$i('zoom-val');if(zv)zv.textContent=Math.round(zoomFactor*100)+'%';var pt=$i('progress-txt');if(pt)pt.textContent='Hal. '+num+'/'+(pdfDoc?pdfDoc.numPages:'?')+' · '+Math.round(pct)+'%';
        if(wrap)wrap.scrollTo({top:0,behavior:'smooth'});syncFC();renderAnnotations();if(panelOpen)buildPanel();if(searchQuery)applySearchHL();
        pendingResolvers.splice(0).forEach(function(fn){fn();});
    }
    $i('prev')&&$i('prev').addEventListener('click',function(){if(pageNum>1)renderPdfPage(pageNum-1);});
    $i('next')&&$i('next').addEventListener('click',function(){if(pdfDoc&&pageNum<pdfDoc.numPages)renderPdfPage(pageNum+1);});
    $i('page-input')&&$i('page-input').addEventListener('change',function(){var n=parseInt(this.value);if(pdfDoc&&n>=1&&n<=pdfDoc.numPages)renderPdfPage(n);else this.value=pageNum;});
    function doZoom(dir){zoomFactor=dir>0?Math.min(zoomFactor+ZOOM_STEP,ZOOM_MAX):Math.max(zoomFactor-ZOOM_STEP,ZOOM_MIN);baseScaleComputed=false;if(pdfDoc)renderPdfPage(pageNum);}
    $i('zoom-in')&&$i('zoom-in').addEventListener('click',function(){doZoom(1);});
    $i('zoom-out')&&$i('zoom-out').addEventListener('click',function(){doZoom(-1);});
    function clearSearchHL(){annotLayer.querySelectorAll('.'+u+'-search-hl').forEach(function(e){e.remove();});searchHLs=[];}
    function applySearchHL(){
        clearSearchHL();if(!searchQuery||!pdfDoc)return;var q=searchQuery.toLowerCase(),sr=stage.getBoundingClientRect();
        Array.from(textLayer.querySelectorAll('span')).forEach(function(span){if(!span.firstChild)return;var text=span.textContent,lower=text.toLowerCase(),si=lower.indexOf(q);while(si!==-1){try{var range=document.createRange();range.setStart(span.firstChild,si);range.setEnd(span.firstChild,Math.min(si+q.length,text.length));Array.from(range.getClientRects()).forEach(function(rect){if(rect.width<1||rect.height<1)return;var el=document.createElement('div');el.className=u+'-search-hl';el.style.left=(rect.left-sr.left)+'px';el.style.top=(rect.top-sr.top)+'px';el.style.width=rect.width+'px';el.style.height=rect.height+'px';el.style.position='absolute';annotLayer.appendChild(el);searchHLs.push(el);});}catch(_){}si=lower.indexOf(q,si+1);}});
        searchHLs.forEach(function(el,i){el.classList.toggle('active-match',i===searchIndex);});
        if(searchHLs[searchIndex])searchHLs[searchIndex].scrollIntoView({behavior:'smooth',block:'center'});
    }
    async function doSearch(query){
        if(!pdfDoc||!query.trim()){clearSearchHL();searchQuery='';var ss=$i('search-status');if(ss)ss.textContent='Ketik untuk mencari...';return;}
        var ss=$i('search-status');if(ss)ss.textContent='Mencari...';searchResults=[];searchQuery=query;var q=query.toLowerCase();
        for(var pgn=1;pgn<=pdfDoc.numPages;pgn++){var pg2=await pdfDoc.getPage(pgn),pgc=await pg2.getTextContent(),pgt=pgc.items.map(function(i){return i.str;}).join(' '),pgl=pgt.toLowerCase(),pgi=pgl.indexOf(q);while(pgi!==-1){searchResults.push({page:pgn,excerpt:pgt.substring(Math.max(0,pgi-35),pgi+q.length+50).trim()});pgi=pgl.indexOf(q,pgi+1);}}
        var list=$i('search-results');if(list)list.innerHTML='';if(!searchResults.length){if(ss)ss.textContent='Tidak ditemukan: "'+query+'"';clearSearchHL();return;}
        if(ss)ss.textContent=searchResults.length+' hasil';searchIndex=0;
        var esc2=query.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');
        searchResults.slice(0,40).forEach(function(r,i){var el=document.createElement('div');el.style.cssText='padding:.3rem .45rem;background:#1f1f1f;border-radius:5px;cursor:pointer;font-size:10px;color:#9ca3af;display:flex;gap:.4rem;align-items:baseline;border:1px solid transparent;margin-bottom:2px;';el.innerHTML='<span style="color:#FF6B18;font-weight:700;flex-shrink:0;">Hal.'+r.page+'</span><span>'+esc(r.excerpt).replace(new RegExp(esc2,'gi'),function(m){return'<mark style="background:rgba(255,107,24,.35);color:#fff;border-radius:2px;padding:0 1px;">'+m+'</mark>';})+'</span>';el.addEventListener('click',function(){searchIndex=i;if(r.page!==pageNum)renderPdfPage(r.page).then(function(){applySearchHL();});else applySearchHL();});if(list)list.appendChild(el);});
        if(searchResults[0].page===pageNum)applySearchHL();else renderPdfPage(searchResults[0].page).then(function(){applySearchHL();});
    }
    function openSearch(){var ov=$i('search');if(ov)ov.style.display='flex';var inp=$i('search-input');if(inp)setTimeout(function(){inp.focus();},50);}
    function closeSearch(){var ov=$i('search');if(ov)ov.style.display='none';clearSearchHL();searchQuery='';searchResults=[];searchIndex=-1;var i=$i('search-input');if(i)i.value='';var rl=$i('search-results');if(rl)rl.innerHTML='';var ss=$i('search-status');if(ss)ss.textContent='Ketik untuk mencari...';}
    $i('search-input')&&$i('search-input').addEventListener('input',function(){clearTimeout(searchDebounce);var v=this.value;searchDebounce=setTimeout(function(){doSearch(v);},450);});
    $i('sclose')&&$i('sclose').addEventListener('click',closeSearch);
    $i('snext')&&$i('snext').addEventListener('click',function(){if(!searchResults.length)return;searchIndex=(searchIndex+1)%searchResults.length;var r=searchResults[searchIndex];if(r.page!==pageNum)renderPdfPage(r.page).then(function(){applySearchHL();});else applySearchHL();});
    $i('sprev')&&$i('sprev').addEventListener('click',function(){if(!searchResults.length)return;searchIndex=(searchIndex-1+searchResults.length)%searchResults.length;var r=searchResults[searchIndex];if(r.page!==pageNum)renderPdfPage(r.page).then(function(){applySearchHL();});else applySearchHL();});
    $i('search')&&$i('search').addEventListener('click',function(e){if(e.target===$i('search'))closeSearch();});
    $i('search-btn')&&$i('search-btn').addEventListener('click',openSearch);
    var wrapEl=document.getElementById(u+'-wrap');
    wrapEl&&wrapEl.addEventListener('keydown',function(e){if(['INPUT','TEXTAREA'].includes(e.target.tagName))return;if((e.ctrlKey||e.metaKey)&&e.key==='f'){e.preventDefault();e.stopPropagation();openSearch();return;}switch(e.key){case'ArrowLeft':if(pageNum>1)renderPdfPage(pageNum-1);break;case'ArrowRight':if(pdfDoc&&pageNum<pdfDoc.numPages)renderPdfPage(pageNum+1);break;case'+':case'=':doZoom(1);break;case'-':doZoom(-1);break;case'Escape':closeSearch();tooltip.style.display='none';break;}});
    var resT=null,lastW=wrap?wrap.clientWidth:0;
    window.addEventListener('resize',function(){var w=wrap?wrap.clientWidth:0;if(Math.abs(w-lastW)<8)return;lastW=w;clearTimeout(resT);resT=setTimeout(function(){if(!pdfDoc)return;baseScaleComputed=false;renderPdfPage(pageNum);},250);});
    if(canvas)new MutationObserver(function(){syncFC();}).observe(canvas,{attributes:true,attributeFilter:['width','height']});
    var task=pdfjsLib.getDocument({url:CFG.pdfUrl,withCredentials:false,verbosity:0,rangeChunkSize:65536});
    task.onProgress=function(d){if(d.total>0&&loadSub)loadSub.textContent='Mengunduh... '+Math.min(100,Math.round(d.loaded/d.total*100))+'%';};
    task.promise.then(async function(doc){
        pdfDoc=doc;clearTimeout(retryTimer);
        var pt=$i('page-total');if(pt)pt.textContent=doc.numPages;var pi=$i('page-input');if(pi)pi.max=doc.numPages;
        await renderPdfPage(1);await loadAnnotations();
        console.log('[RPVRI] ready, uid=',u,'reviewId=',CFG.reviewId);
    }).catch(function(err){console.error('[RPVRI] load error:',err);showFatalError('Gagal memuat PDF: '+err.message);});
}
boot();
})();
</script>