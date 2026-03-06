@php
$pvId = $get('publication_version_id');

$version = $pvId
? \App\Models\PublicationVersion::query()->with('publication')->find($pvId)
: null;

if (! $version) {
$pdfUrl = null;
$downloadUrl = null;
$title = 'Publication';
$versionNumber = '-';
} else {
$pdfUrl = route('manuscripts.view', $version);
$downloadUrl = route('manuscripts.download', $version);
$title = $version->publication?->title ?? 'Publication';
$versionNumber = $version->version_number ?? '-';
}
@endphp

@if(! $version)
<div class="text-sm text-gray-500">Pilih Publication Version untuk melihat PDF.</div>
@else
<div class="pvx">
    <div class="pvx-header">
        <div class="pvx-meta">
            <div class="pvx-kicker">PDF Preview</div>
            <div class="pvx-title">{{ $title }}</div>
            <div class="pvx-subtitle">Version: {{ $versionNumber }}</div>
        </div>

        <div class="pvx-actions">
            <a class="pvx-btn" href="{{ $downloadUrl }}" target="_blank" rel="noopener">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                    style="margin-right:0.3rem;flex-shrink:0">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                Download PDF
            </a>

            <button class="pvx-btn" type="button" onclick="pvxFullscreen()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                    style="margin-right:0.3rem;flex-shrink:0">
                    <polyline points="15 3 21 3 21 9" />
                    <polyline points="9 21 3 21 3 15" />
                    <line x1="21" y1="3" x2="14" y2="10" />
                    <line x1="3" y1="21" x2="10" y2="14" />
                </svg>
                Fullscreen
            </button>
        </div>
    </div>

    <div class="pvx-frame">
        <iframe id="pvx-iframe" src="{{ $pdfUrl }}" title="Manuscript PDF" loading="lazy" allowfullscreen></iframe>
    </div>
</div>

<script>
    function pvxFullscreen() {
        const iframe = document.getElementById('pvx-iframe');
        if (iframe.requestFullscreen) {
            iframe.requestFullscreen();
        } else if (iframe.webkitRequestFullscreen) {
            iframe.webkitRequestFullscreen();
        } else if (iframe.msRequestFullscreen) {
            iframe.msRequestFullscreen();
        }
    }
</script>

<style>
    /* ── Container ─────────────────────────────────────── */
    .pvx {
        display: grid;
        gap: 1rem;
    }

    /* ── Header ────────────────────────────────────────── */
    .pvx-header {
        display: flex;
        flex-wrap: wrap;
        /* wrap ke bawah kalau sempit */
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-radius: 18px;
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: white;
        box-shadow: 0 14px 35px rgba(249, 115, 22, 0.18);
    }

    /* ── Meta (kicker + title + subtitle) ──────────────── */
    .pvx-meta {
        flex: 1 1 60%;
        /* ambil ruang lebih banyak */
        min-width: 0;
        /* cegah overflow teks */
    }

    .pvx-kicker {
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.95;
    }

    .pvx-title {
        margin-top: 0.25rem;
        font-size: 1rem;
        font-weight: 900;
        line-height: 1.3;
        word-break: break-word;
        /* patahkan kata panjang */
        overflow-wrap: break-word;
    }

    .pvx-subtitle {
        margin-top: 0.2rem;
        font-size: 0.85rem;
        opacity: 0.9;
    }

    /* ── Actions ───────────────────────────────────────── */
    .pvx-actions {
        display: flex;
        flex-direction: column;
        /* tombol susun vertikal di mobile */
        gap: 0.5rem;
        flex-shrink: 0;
        width: 100%;
        /* full-width di mobile */
    }

    .pvx-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        /* full-width button di mobile */
        padding: 0.6rem 1rem;
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 800;
        color: #111827;
        background: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.25);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
        white-space: nowrap;
    }

    .pvx-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    /* ── Frame / iframe ────────────────────────────────── */
    .pvx-frame {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 12px 30px rgba(17, 24, 39, 0.06);
    }

    .pvx-frame iframe {
        width: 100%;
        height: 65vh;
        /* lebih pendek di mobile agar masih bisa scroll */
        border: 0;
        display: block;
    }

    /* ── Responsive: tablet ke atas (≥ 640px) ─────────── */
    @media (min-width: 640px) {
        .pvx-actions {
            flex-direction: row;
            /* tombol sejajar horizontal */
            width: auto;
        }

        .pvx-btn {
            width: auto;
        }

        .pvx-title {
            font-size: 1.1rem;
        }

        .pvx-frame iframe {
            height: 75vh;
        }
    }

    /* ── Responsive: desktop (≥ 1024px) ───────────────── */
    @media (min-width: 1024px) {
        .pvx-header {
            align-items: flex-end;
        }

        .pvx-title {
            font-size: 1.15rem;
        }

        .pvx-frame iframe {
            height: 85vh;
        }
    }
</style>
@endif