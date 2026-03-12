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

$authorMap = $authorIds->isEmpty()
? collect()
: \App\Models\Author::with('user')
->whereIn('id', $authorIds)
->get()
->mapWithKeys(function ($author) {
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
// Kasus 1: TemporaryUploadedFile — embed sebagai base64 agar tidak butuh HTTP request
if ($value instanceof TemporaryUploadedFile) {
try {
$tmpPath = $value->getRealPath();
if ($tmpPath && file_exists($tmpPath)) {
$mime = $value->getMimeType() ?? 'image/jpeg';
$base64 = base64_encode(file_get_contents($tmpPath));
return "data:{$mime};base64,{$base64}";
}
} catch (\Throwable $e) {
// fallback ke null
}
return null;
}

// Kasus 2: String path — sudah tersimpan di storage public
if (is_string($value) && filled($value)) {
if (Storage::disk('public')->exists($value)) {
return Storage::disk('public')->url($value);
}
}

return null;
};

$coverUrl = null;
if (is_array($coverState)) {
foreach ($coverState as $item) {
$resolved = $resolveCoverUrl($item);
if ($resolved) {
$coverUrl = $resolved;
break;
}
}
} else {
$coverUrl = $resolveCoverUrl($coverState);
}

$publication = $record ?? null;
$latestVersion = $publication?->versions()->orderByDesc('version_number')->first();
$downloadUrl = $latestVersion ? route('manuscripts.download', $latestVersion) : null;
$abstractHtml = filled($abstract) ? str($abstract)->sanitizeHtml() : null;
@endphp

<div class="bookx">
    <div class="bookx-wrap">
        {{-- COVER COLUMN --}}
        <div class="bookx-cover">
            @if($coverUrl)
            <img src="{{ $coverUrl }}" alt="Cover image preview" loading="lazy" class="bookx-cover-img" />
            @else
            <div class="bookx-cover-fallback">
                <div class="bookx-fallback-icon">
                    <x-heroicon-o-photo class="bookx-fallback-icon-svg" />
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
                @endif
            </div>
        </div>

        {{-- BODY COLUMN --}}
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
    /* ════════════════════════════════════════════════
       MOBILE-FIRST RESPONSIVE + LIGHT/DARK MODE
       Uses CSS custom properties & prefers-color-scheme
    ════════════════════════════════════════════════ */

    :root {
        /* Light Mode (default) */
        --bg-primary: #ffffff;
        --bg-secondary: #fff7ed;
        --bg-abstract: #fff7ed;
        --border-primary: #fed7aa;
        --border-secondary: #f3f4f6;
        --text-primary: #111827;
        --text-secondary: #374151;
        --text-muted: #6b7280;
        --text-muted-light: #9ca3af;
        --text-orange: #9a3412;
        --accent-primary: #f97316;
        --accent-gradient: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        --shadow-light: 0 8px 24px rgba(17, 24, 39, 0.05);
        --shadow-heavy: 0 12px 28px rgba(17, 24, 39, 0.10);
        --shadow-accent: 0 8px 18px rgba(249, 115, 22, 0.22);
    }

    @media (prefers-color-scheme: dark) {
        :root {
            /* Dark Mode */
            --bg-primary: #1f2937;
            --bg-secondary: #374151;
            --bg-abstract: #374151;
            --border-primary: #6b7280;
            --border-secondary: #4b5563;
            --text-primary: #f9fafb;
            --text-secondary: #d1d5db;
            --text-muted: #9ca3af;
            --text-muted-light: #6b7280;
            --text-orange: #f59e0b;
            --accent-primary: #fb923c;
            --accent-gradient: linear-gradient(135deg, #f59e0b 0%, #fb923c 100%);
            --shadow-light: 0 8px 24px rgba(0, 0, 0, 0.3);
            --shadow-heavy: 0 12px 28px rgba(0, 0, 0, 0.4);
            --shadow-accent: 0 8px 18px rgba(245, 158, 11, 0.3);
        }
    }

    /* Override for forced dark mode (if using class="dark") */
    .dark {
        --bg-primary: #1f2937;
        --bg-secondary: #374151;
        --bg-abstract: #374151;
        --border-primary: #6b7280;
        --border-secondary: #4b5563;
        --text-primary: #f9fafb;
        --text-secondary: #d1d5db;
        --text-muted: #9ca3af;
        --text-muted-light: #6b7280;
        --text-orange: #f59e0b;
        --accent-primary: #fb923c;
        --accent-gradient: linear-gradient(135deg, #f59e0b 0%, #fb923c 100%);
        --shadow-light: 0 8px 24px rgba(0, 0, 0, 0.3);
        --shadow-heavy: 0 12px 28px rgba(0, 0, 0, 0.4);
        --shadow-accent: 0 8px 18px rgba(245, 158, 11, 0.3);
    }

    /* ════════════════════════════════════════════════
       MOBILE-FIRST BASE STYLES
    ════════════════════════════════════════════════ */
    .bookx {
        display: flex;
        justify-content: center;
        padding: clamp(0.5rem, 2vw, 1rem);
        width: 100%;
        min-height: 100vh;
    }

    .bookx-wrap {
        width: 100%;
        max-width: 1200px;
        display: flex;
        flex-direction: column;
        gap: clamp(1rem, 3vw, 1.5rem);
    }

    /* ════════════════════════════════════════════════
       COVER
    ════════════════════════════════════════════════ */
    .bookx-cover {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        background: var(--bg-secondary);
        border: 2px solid var(--border-primary);
        box-shadow: var(--shadow-heavy);
        width: 100%;
        aspect-ratio: 3/4;
        max-height: 400px;
    }

    .bookx-cover-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .bookx-cover-badge {
        position: absolute;
        top: clamp(0.75rem, 2vw, 1rem);
        left: clamp(0.75rem, 2vw, 1rem);
        background: rgba(249, 115, 22, 0.95);
        color: white;
        padding: 0.4rem 0.75rem;
        border-radius: 9999px;
        font-size: clamp(0.7rem, 2vw, 0.8rem);
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        backdrop-filter: blur(10px);
    }

    .bookx-cover-fallback {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: clamp(1rem, 4vw, 1.5rem);
        gap: 0.75rem;
        text-align: center;
    }

    .bookx-fallback-icon {
        width: clamp(50px, 12vw, 70px);
        height: clamp(50px, 12vw, 70px);
        border-radius: 16px;
        background: color-mix(in srgb, var(--bg-primary) 70%, transparent);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--border-primary);
    }

    .bookx-fallback-icon-svg {
        width: 2rem;
        height: 2rem;
        color: var(--accent-primary);
    }

    .bookx-fallback-text {
        color: var(--text-orange);
        font-size: clamp(0.85rem, 2.5vw, 0.95rem);
        line-height: 1.5;
        margin: 0;
        max-width: 85%;
    }

    .bookx-cover-actions {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: clamp(0.75rem, 3vw, 1rem);
        background: var(--bg-primary);
        border-top: 2px solid var(--border-primary);
    }

    .bookx-download {
        display: flex;
        width: 100%;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        background: var(--accent-gradient);
        color: white;
        font-weight: 800;
        font-size: clamp(0.85rem, 2.5vw, 0.95rem);
        border-radius: 16px;
        padding: clamp(0.75rem, 2.5vw, 1rem) clamp(1rem, 4vw, 1.25rem);
        box-shadow: var(--shadow-accent);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        touch-action: manipulation;
        border: none;
    }

    .bookx-download:hover,
    .bookx-download:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px rgba(249, 115, 22, 0.35);
        color: white;
    }

    .bookx-download:active {
        transform: translateY(0);
    }

    .bookx-download-icon {
        width: 1.25rem;
        height: 1.25rem;
        flex-shrink: 0;
    }

    .bookx-download-disabled {
        width: 100%;
        text-align: center;
        padding: clamp(0.75rem, 2.5vw, 1rem) 1rem;
        border-radius: 16px;
        background: var(--bg-secondary);
        border: 2px dashed var(--border-primary);
        color: var(--text-orange);
        font-weight: 600;
        font-size: clamp(0.8rem, 2.2vw, 0.9rem);
        line-height: 1.5;
    }

    /* ════════════════════════════════════════════════
       BODY
    ════════════════════════════════════════════════ */
    .bookx-body {
        background: var(--bg-primary);
        border: 2px solid var(--border-secondary);
        border-radius: 20px;
        padding: clamp(1.25rem, 4vw, 2rem);
        box-shadow: var(--shadow-light);
    }

    .bookx-kicker {
        color: var(--accent-primary);
        font-weight: 800;
        font-size: clamp(0.7rem, 2vw, 0.8rem);
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 0.75rem;
    }

    .bookx-title {
        color: var(--text-primary);
        font-size: clamp(1.25rem, 4vw, 1.875rem);
        font-weight: 900;
        line-height: 1.2;
        margin: 0 0 clamp(0.75rem, 2.5vw, 1rem);
        word-break: break-word;
        hyphens: auto;
    }

    /* Authors */
    .bookx-authors {
        color: var(--text-muted);
        font-size: clamp(0.875rem, 2.5vw, 1rem);
        line-height: 1.7;
        margin-bottom: clamp(1rem, 3vw, 1.5rem);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.25rem 0.125rem;
    }

    .bookx-author {
        font-weight: 700;
        color: var(--text-secondary);
    }

    .bookx-corresponding {
        font-weight: 700;
        color: var(--accent-primary);
        font-size: 0.875em;
    }

    .bookx-sep {
        opacity: 0.5;
        margin: 0 0.375rem;
        font-weight: 400;
    }

    .bookx-muted {
        color: var(--text-muted-light);
        font-size: 0.9em;
    }

    /* Stats Grid */
    .bookx-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: clamp(0.75rem, 2.5vw, 1rem);
        margin-bottom: clamp(1.25rem, 4vw, 1.75rem);
    }

    .bookx-meta-item {
        background: var(--bg-secondary);
        border: 2px solid var(--border-primary);
        border-radius: 16px;
        padding: clamp(0.75rem, 2.5vw, 1rem);
        text-align: center;
    }

    .bookx-meta-label {
        font-size: clamp(0.65rem, 1.8vw, 0.75rem);
        font-weight: 800;
        color: var(--text-orange);
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }

    .bookx-meta-value {
        font-size: clamp(1rem, 3vw, 1.25rem);
        font-weight: 900;
        color: var(--text-primary);
    }

    /* Sections */
    .bookx-section {
        margin-top: clamp(1.25rem, 4vw, 1.75rem);
    }

    .bookx-section:first-child {
        margin-top: 0;
    }

    .bookx-section-title {
        font-size: clamp(0.8rem, 2.2vw, 0.875rem);
        font-weight: 900;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .bookx-tags {
        display: flex;
        flex-wrap: wrap;
        gap: clamp(0.375rem, 1.5vw, 0.5rem);
    }

    .bookx-tag {
        background: color-mix(in srgb, var(--bg-secondary) 100%, transparent);
        border: 1px solid var(--border-primary);
        color: var(--text-orange);
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: clamp(0.75rem, 2vw, 0.825rem);
        font-weight: 600;
        line-height: 1.4;
        white-space: nowrap;
    }

    /* Abstract */
    .bookx-abstract {
        background: var(--bg-abstract);
        border: 2px solid var(--border-primary);
        border-radius: 16px;
        padding: clamp(1rem, 3vw, 1.25rem);
        color: var(--text-primary);
        font-size: clamp(0.875rem, 2.5vw, 0.95rem);
        line-height: 1.75;
        text-align: justify;
    }

    .bookx-abstract :where(p, ul, ol, blockquote, h1, h2, h3, h4, pre, table) {
        margin-top: 1rem;
        margin-bottom: 1rem;
    }

    .bookx-abstract :where(ul, ol) {
        padding-left: 1.5rem;
    }

    .bookx-abstract :where(li) {
        margin: 0.25rem 0;
    }

    .bookx-abstract :where(blockquote) {
        border-left: 4px solid var(--accent-primary);
        padding-left: 1rem;
        color: var(--text-secondary);
        font-style: italic;
        background: color-mix(in srgb, transparent 80%, currentColor);
    }

    .bookx-abstract :where(a) {
        color: var(--accent-primary);
        text-decoration: underline;
        word-break: break-all;
    }

    /* ════════════════════════════════════════════════
       TABLET & DESKTOP - SIDE-BY-SIDE LAYOUT
    ════════════════════════════════════════════════ */
    @media (min-width: 768px) {
        .bookx-wrap {
            flex-direction: row;
            align-items: start;
        }

        .bookx-cover {
            flex: 0 0 280px;
            max-height: 420px;
        }

        .bookx-body {
            flex: 1;
            margin-left: clamp(1.5rem, 4vw, 2rem);
        }
    }

    @media (min-width: 1024px) {
        .bookx-cover {
            flex: 0 0 320px;
            max-height: 480px;
        }
    }

    @media (min-width: 1200px) {
        .bookx-meta {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    /* ════════════════════════════════════════════════
       PRINT STYLES
    ════════════════════════════════════════════════ */
    @media print {
        .bookx {
            box-shadow: none;
            padding: 0;
        }

        .bookx-wrap,
        .bookx-cover,
        .bookx-body {
            box-shadow: none;
            border: 1px solid #ccc;
            break-inside: avoid;
        }

        .bookx-download {
            display: none;
        }
    }

    /* ════════════════════════════════════════════════
       REDUCED MOTION
    ════════════════════════════════════════════════ */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>