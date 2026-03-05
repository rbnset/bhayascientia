@php
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

$title = $get('title') ?? '-';
$status = $get('status') ?? '-';
$abstract = $get('abstract');

$authorPubs = collect($get('authorPublications') ?? [])
->filter(fn($row) => is_array($row) && !empty($row['author_id']))
->values()
->sortBy(fn($row) => (int) ($row['order'] ?? 999))
->values();

$authorIds = $authorPubs->pluck('author_id')->filter()->unique()->values();
$categoryIds = collect($get('categories') ?? [])->filter()->values();
$keywordIds = collect($get('keywords') ?? [])->filter()->values();

// ✅ FIX: eager load relasi user agar accessor name bisa resolve
$authorMap = $authorIds->isEmpty()
? collect()
: \App\Models\Author::with('user')
->whereIn('id', $authorIds)
->get()
->mapWithKeys(function ($author) {
// Accessor name: prioritaskan authors.name, fallback ke users.name
$name = filled($author->getRawOriginal('name'))
? $author->getRawOriginal('name')
: ($author->user?->name ?? null);
return [$author->id => filled($name) ? $name : 'Author #' . $author->id];
});

$categoryNames = $categoryIds->isEmpty()
? collect()
: \App\Models\Category::whereIn('id', $categoryIds)->pluck('name', 'id');

$keywordNames = $keywordIds->isEmpty()
? collect()
: \App\Models\Keyword::whereIn('id', $keywordIds)->pluck('name', 'id');

$statusLabel = strtoupper(str_replace('_', ' ', $status));

$coverState = $get('cover_image_path');

$resolveCoverUrl = function ($value) {
if ($value instanceof TemporaryUploadedFile) {
return $value->temporaryUrl();
}
if (is_string($value) && filled($value)) {
return Storage::disk('public')->url($value);
}
return null;
};

$coverUrl = is_array($coverState)
? $resolveCoverUrl($coverState[0] ?? null)
: $resolveCoverUrl($coverState);

$publication = $record ?? null;
$latestVersion = $publication?->versions()->orderByDesc('version_number')->first();
$downloadUrl = $latestVersion ? route('manuscripts.download', $latestVersion) : null;

$abstractHtml = filled($abstract) ? str($abstract)->sanitizeHtml() : null;
@endphp

<div class="bookx">
    <div class="bookx-wrap">

        {{-- ══════════════════════════════════════
        COVER COLUMN
        ══════════════════════════════════════ --}}
        <div class="bookx-cover">
            @if($coverUrl)
            <img src="{{ $coverUrl }}" alt="Cover image preview" loading="lazy" />
            @else
            <div class="bookx-cover-fallback">
                <div class="bookx-fallback-icon">
                    <x-heroicon-o-photo class="w-10 h-10 text-orange-600" />
                </div>
                <p class="bookx-fallback-text">
                    Upload cover untuk melihat preview seperti buku.
                </p>
            </div>
            @endif

            <div class="bookx-cover-badge">{{ $statusLabel }}</div>

            <div class="bookx-cover-actions">
                @if($downloadUrl)
                <a class="bookx-download" href="{{ $downloadUrl }}" target="_blank" rel="noopener">
                    <svg xmlns="http://www.w3.org/2000/svg" class="bookx-download-icon" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    Download Manuscript
                </a>
                @else
                <div class="bookx-download-disabled">
                    Manuscript belum ada — upload di Publication Versions.
                </div>
                @endif
            </div>
        </div>

        {{-- ══════════════════════════════════════
        BODY COLUMN
        ══════════════════════════════════════ --}}
        <div class="bookx-body">
            <div class="bookx-kicker">Publication Preview</div>

            <h2 class="bookx-title">{{ $title }}</h2>

            {{-- Authors --}}
            <div class="bookx-authors">
                @if($authorPubs->count())
                @foreach($authorPubs as $row)
                @php
                $id = $row['author_id'];
                $name = $authorMap[$id] ?? ('Author #' . $id);
                $isCorr = (bool) ($row['is_corresponding'] ?? false);
                @endphp
                <span class="bookx-author">
                    {{ $name }}
                    @if($isCorr)
                    <span class="bookx-corresponding">• corresponding</span>
                    @endif
                </span>
                @if(!$loop->last)
                <span class="bookx-sep">/</span>
                @endif
                @endforeach
                @else
                <span class="bookx-muted">Belum ada author dipilih.</span>
                @endif
            </div>

            {{-- Stats grid --}}
            <div class="bookx-meta">
                <div class="bookx-meta-item">
                    <div class="bookx-meta-label">Authors</div>
                    <div class="bookx-meta-value">{{ $authorPubs->count() }}</div>
                </div>
                <div class="bookx-meta-item">
                    <div class="bookx-meta-label">Categories</div>
                    <div class="bookx-meta-value">{{ $categoryIds->count() }}</div>
                </div>
                <div class="bookx-meta-item">
                    <div class="bookx-meta-label">Keywords</div>
                    <div class="bookx-meta-value">{{ $keywordIds->count() }}</div>
                </div>
                <div class="bookx-meta-item">
                    <div class="bookx-meta-label">Method</div>
                    <div class="bookx-meta-value">{{ $get('method_id') ? 'Yes' : 'No' }}</div>
                </div>
            </div>

            {{-- Categories --}}
            <div class="bookx-section">
                <div class="bookx-section-title">Categories</div>
                <div class="bookx-tags">
                    @forelse($categoryIds as $id)
                    <span class="bookx-tag">{{ $categoryNames[$id] ?? 'Unknown' }}</span>
                    @empty
                    <span class="bookx-muted">-</span>
                    @endforelse
                </div>
            </div>

            {{-- Keywords --}}
            <div class="bookx-section">
                <div class="bookx-section-title">Keywords</div>
                <div class="bookx-tags">
                    @forelse($keywordIds as $id)
                    <span class="bookx-tag">{{ $keywordNames[$id] ?? 'Unknown' }}</span>
                    @empty
                    <span class="bookx-muted">-</span>
                    @endforelse
                </div>
            </div>

            {{-- Abstract --}}
            <div class="bookx-section">
                <div class="bookx-section-title">Abstract / Summary</div>
                <div class="bookx-abstract fi-prose">
                    @if($abstractHtml)
                    {!! $abstractHtml !!}
                    @else
                    <span class="bookx-muted">-</span>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /* ══════════════════════════════════════════
       MOBILE-FIRST BASE
    ══════════════════════════════════════════ */
    .bookx {
        display: flex;
        justify-content: center;
        padding: 0.5rem;
    }

    .bookx-wrap {
        width: 100%;
        max-width: 980px;
        display: flex;
        flex-direction: column;
        /* mobile: stack vertikal */
        gap: 1.25rem;
    }

    /* ══════════════════════════════════════════
       COVER
    ══════════════════════════════════════════ */
    .bookx-cover {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        box-shadow: 0 12px 28px rgba(17, 24, 39, 0.10);
        width: 100%;
    }

    .bookx-cover img {
        width: 100%;
        height: 260px;
        /* mobile default */
        object-fit: cover;
        display: block;
    }

    .bookx-cover-badge {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        background: rgba(249, 115, 22, 0.95);
        color: #fff;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .bookx-cover-fallback {
        height: 260px;
        /* sama dengan img mobile */
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        gap: 0.65rem;
        text-align: center;
    }

    .bookx-fallback-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background: #ffedd5;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #fed7aa;
        flex-shrink: 0;
    }

    .bookx-fallback-text {
        color: #9a3412;
        font-size: 0.88rem;
        line-height: 1.5;
        margin: 0;
    }

    .bookx-cover-actions {
        padding: 0.8rem;
        background: #ffffff;
        border-top: 1px solid #fed7aa;
    }

    .bookx-download {
        display: flex;
        width: 100%;
        justify-content: center;
        align-items: center;
        gap: 0.45rem;
        text-decoration: none;
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: #ffffff;
        font-weight: 800;
        font-size: 0.9rem;
        border-radius: 12px;
        padding: 0.7rem 1rem;
        box-shadow: 0 8px 18px rgba(249, 115, 22, 0.22);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        touch-action: manipulation;
    }

    .bookx-download:hover,
    .bookx-download:focus-visible {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(249, 115, 22, 0.30);
        color: #ffffff;
    }

    .bookx-download:active {
        transform: translateY(0);
    }

    .bookx-download-icon {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
    }

    .bookx-download-disabled {
        width: 100%;
        text-align: center;
        padding: 0.7rem 1rem;
        border-radius: 12px;
        background: #fff7ed;
        border: 1px dashed #fed7aa;
        color: #9a3412;
        font-weight: 600;
        font-size: 0.85rem;
        line-height: 1.5;
        box-sizing: border-box;
    }

    /* ══════════════════════════════════════════
       BODY
    ══════════════════════════════════════════ */
    .bookx-body {
        background: #ffffff;
        border: 1px solid #f3f4f6;
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: 0 8px 24px rgba(17, 24, 39, 0.05);
    }

    .bookx-kicker {
        color: #9a3412;
        font-weight: 800;
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 0.4rem;
    }

    .bookx-title {
        color: #111827;
        font-size: 1.35rem;
        /* lebih kecil di mobile */
        font-weight: 900;
        line-height: 1.25;
        margin: 0 0 0.65rem;
        word-break: break-word;
    }

    /* ── Authors ── */
    .bookx-authors {
        color: #6b7280;
        font-size: 0.9rem;
        line-height: 1.7;
        margin-bottom: 1rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.25rem 0;
    }

    .bookx-author {
        font-weight: 700;
        color: #374151;
        font-size: 0.9rem;
    }

    .bookx-corresponding {
        font-weight: 700;
        color: #f97316;
        font-size: 0.8rem;
    }

    .bookx-sep {
        opacity: 0.30;
        margin: 0 0.3rem;
    }

    .bookx-muted {
        color: #9ca3af;
        font-size: 0.88rem;
    }

    /* ── Stats grid ── */
    .bookx-meta {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        /* 2 kolom di mobile */
        gap: 0.65rem;
        margin-bottom: 1.1rem;
    }

    .bookx-meta-item {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 14px;
        padding: 0.7rem 0.8rem;
    }

    .bookx-meta-label {
        font-size: 0.68rem;
        font-weight: 800;
        color: #9a3412;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .bookx-meta-value {
        margin-top: 0.2rem;
        font-size: 1.05rem;
        font-weight: 900;
        color: #111827;
    }

    /* ── Sections ── */
    .bookx-section {
        margin-top: 0.9rem;
    }

    .bookx-section-title {
        font-size: 0.82rem;
        font-weight: 900;
        color: #111827;
        margin-bottom: 0.45rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .bookx-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .bookx-tag {
        background: #fffbeb;
        border: 1px solid rgba(249, 115, 22, 0.20);
        color: #9a3412;
        padding: 0.22rem 0.55rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.5;
    }

    /* ── Abstract ── */
    .bookx-abstract {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 14px;
        padding: 0.9rem 1rem;
        color: #111827;
        font-size: 0.92rem;
        line-height: 1.8;
        text-align: justify;
        text-justify: inter-word;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    .bookx-abstract :where(p, ul, ol, blockquote, h1, h2, h3, h4, pre, table) {
        margin-top: 0.7rem;
        margin-bottom: 0.7rem;
    }

    .bookx-abstract :where(ul, ol) {
        padding-left: 1.2rem;
    }

    .bookx-abstract :where(li) {
        margin: 0.2rem 0;
    }

    .bookx-abstract :where(blockquote) {
        border-left: 3px solid rgba(249, 115, 22, 0.40);
        padding-left: 0.85rem;
        color: #374151;
        font-style: italic;
    }

    .bookx-abstract :where(a) {
        color: #c2410c;
        text-decoration: underline;
        word-break: break-all;
    }

    /* ══════════════════════════════════════════
       TABLET  ≥ 640px
    ══════════════════════════════════════════ */
    @media (min-width: 640px) {
        .bookx {
            padding: 1rem;
        }

        .bookx-body {
            padding: 1.5rem;
        }

        .bookx-title {
            font-size: 1.5rem;
        }

        .bookx-cover img,
        .bookx-cover-fallback {
            height: 360px;
        }

        .bookx-meta {
            grid-template-columns: repeat(4, 1fr);
            /* 4 kolom di tablet */
        }
    }

    /* ══════════════════════════════════════════
       DESKTOP  ≥ 860px  →  side-by-side layout
    ══════════════════════════════════════════ */
    @media (min-width: 860px) {
        .bookx {
            padding: 0;
        }

        .bookx-wrap {
            display: grid;
            grid-template-columns: 300px 1fr;
            flex-direction: unset;
            gap: 1.5rem;
            align-items: start;
        }

        .bookx-cover img,
        .bookx-cover-fallback {
            height: 460px;
        }

        .bookx-title {
            font-size: 1.6rem;
        }

        .bookx-body {
            padding: 1.5rem;
        }
    }

    /* ══════════════════════════════════════════
       WIDE DESKTOP  ≥ 1024px
    ══════════════════════════════════════════ */
    @media (min-width: 1024px) {
        .bookx-wrap {
            grid-template-columns: 320px 1fr;
        }

        .bookx-title {
            font-size: 1.75rem;
        }
    }
</style>