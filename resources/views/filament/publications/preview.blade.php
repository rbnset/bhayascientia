@php
use Illuminate\Support\Facades\Storage;

$title = $get('title') ?? '-';
$status = $get('status') ?? '-';
$abstract = $get('abstract');

// Repeater state (pivot-like)
$authorPubs = collect($get('authorPublications') ?? [])
->filter(fn ($row) => is_array($row) && ! empty($row['author_id']))
->values();

// Urutkan sesuai order pivot bila ada [web:917]
$authorPubs = $authorPubs->sortBy(fn ($row) => (int) ($row['order'] ?? 999))->values();

// IDs author
$authorIds = $authorPubs->pluck('author_id')->filter()->unique()->values();

$categoryIds = collect($get('categories') ?? [])->filter()->values();
$keywordIds = collect($get('keywords') ?? [])->filter()->values();

// Map nama
$authorNames = $authorIds->isEmpty()
? collect()
: \App\Models\Author::whereIn('id', $authorIds)->pluck('name', 'id');

$categoryNames = $categoryIds->isEmpty()
? collect()
: \App\Models\Category::whereIn('id', $categoryIds)->pluck('name', 'id');

$keywordNames = $keywordIds->isEmpty()
? collect()
: \App\Models\Keyword::whereIn('id', $keywordIds)->pluck('name', 'id');

$statusLabel = strtoupper(str_replace('_', ' ', $status));

$coverPath = $get('cover_image_path');
$coverUrl = $coverPath ? Storage::disk('public')->url($coverPath) : null;

// Manuscript (dari record publication saat edit/view)
$publication = $record ?? null;

$latestVersion = $publication?->versions()
->orderByDesc('version_number')
->first();

$downloadUrl = $latestVersion
? route('manuscripts.download', $latestVersion)
: null;
@endphp

<div class="bookx">
    <div class="bookx-wrap">
        {{-- Cover --}}
        <div class="bookx-cover">
            @if($coverUrl)
            <img src="{{ $coverUrl }}" alt="Cover image preview" loading="lazy" />
            @else
            <div class="bookx-cover-fallback">
                <div class="bookx-fallback-icon">
                    <x-heroicon-o-photo class="w-10 h-10 text-orange-600" />
                </div>
                <div class="bookx-fallback-text">
                    Upload cover untuk melihat preview seperti buku.
                </div>
            </div>
            @endif

            <div class="bookx-cover-badge">
                {{ $statusLabel }}
            </div>

            <div class="bookx-cover-actions">
                @if($downloadUrl)
                <a class="bookx-download" href="{{ $downloadUrl }}" target="_blank" rel="noopener">
                    Download manuscript (latest version)
                </a>
                @else
                <div class="bookx-download-disabled">
                    Manuscript belum ada (upload dulu di Publication Versions).
                </div>
                @endif
            </div>
        </div>

        {{-- Content --}}
        <div class="bookx-body">
            <div class="bookx-kicker">Publication Preview</div>

            <h2 class="bookx-title">{{ $title }}</h2>

            <div class="bookx-authors">
                @if($authorPubs->count())
                @foreach($authorPubs as $row)
                @php
                $id = $row['author_id'];
                $name = $authorNames[$id] ?? 'Unknown';
                $isCorresponding = (bool) ($row['is_corresponding'] ?? false);
                @endphp

                <span class="bookx-author">
                    {{ $name }}
                    @if($isCorresponding)
                    <span class="bookx-corresponding">• corresponding</span>
                    @endif
                </span>

                @if(! $loop->last)
                <span class="bookx-sep">/</span>
                @endif
                @endforeach
                @else
                <span class="bookx-muted">Belum ada author dipilih.</span>
                @endif
            </div>

            <div class="bookx-meta">
                <div class="bookx-meta-item">
                    <div class="bookx-meta-label">Categories</div>
                    <div class="bookx-meta-value">{{ $categoryIds->count() }}</div>
                </div>

                <div class="bookx-meta-item">
                    <div class="bookx-meta-label">Keywords</div>
                    <div class="bookx-meta-value">{{ $keywordIds->count() }}</div>
                </div>

                <div class="bookx-meta-item">
                    <div class="bookx-meta-label">Authors</div>
                    <div class="bookx-meta-value">{{ $authorPubs->count() }}</div>
                </div>

                <div class="bookx-meta-item">
                    <div class="bookx-meta-label">Method</div>
                    <div class="bookx-meta-value">{{ $get('method_id') ? 'Yes' : 'No' }}</div>
                </div>
            </div>

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

            <div class="bookx-section">
                <div class="bookx-section-title">Abstract / Summary</div>
                <div class="bookx-abstract">
                    {{ filled($abstract) ? $abstract : '-' }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bookx {
        display: flex;
        justify-content: center;
    }

    .bookx-wrap {
        width: 100%;
        max-width: 980px;
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    .bookx-cover {
        position: relative;
        border-radius: 18px;
        overflow: hidden;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        box-shadow: 0 18px 40px rgba(17, 24, 39, 0.12);
    }

    .bookx-cover img {
        width: 100%;
        height: 460px;
        object-fit: cover;
        display: block;
    }

    .bookx-cover-badge {
        position: absolute;
        top: 0.9rem;
        left: 0.9rem;
        background: rgba(249, 115, 22, 0.95);
        color: white;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
    }

    .bookx-cover-fallback {
        height: 460px;
        display: grid;
        place-content: center;
        text-align: center;
        padding: 1.25rem;
        gap: 0.75rem;
    }

    .bookx-fallback-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto;
        border-radius: 16px;
        background: #ffedd5;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #fed7aa;
    }

    .bookx-fallback-text {
        color: #9a3412;
        font-size: 0.9rem;
    }

    /* NEW: actions under cover */
    .bookx-cover-actions {
        padding: 0.85rem;
        background: #ffffff;
        border-top: 1px solid #fed7aa;
    }

    .bookx-download {
        display: inline-flex;
        width: 100%;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: #ffffff;
        font-weight: 900;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        box-shadow: 0 10px 22px rgba(249, 115, 22, 0.25);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .bookx-download:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 26px rgba(249, 115, 22, 0.30);
        color: #ffffff;
    }

    .bookx-download-disabled {
        width: 100%;
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: #fff7ed;
        border: 1px dashed #fed7aa;
        color: #9a3412;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .bookx-body {
        background: #ffffff;
        border: 1px solid #f3f4f6;
        border-radius: 18px;
        padding: 1.5rem 1.5rem;
        box-shadow: 0 12px 30px rgba(17, 24, 39, 0.06);
    }

    .bookx-kicker {
        color: #9a3412;
        font-weight: 800;
        font-size: 0.78rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .bookx-title {
        color: #111827;
        font-size: 1.6rem;
        font-weight: 900;
        line-height: 1.2;
        margin: 0 0 0.75rem;
    }

    .bookx-authors {
        color: #6b7280;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .bookx-author {
        font-weight: 700;
        color: #374151;
    }

    .bookx-corresponding {
        font-weight: 700;
        color: #f97316;
    }

    .bookx-sep {
        opacity: 0.35;
        margin: 0 0.35rem;
    }

    .bookx-muted {
        color: #9ca3af;
    }

    .bookx-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .bookx-meta-item {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 14px;
        padding: 0.75rem 0.8rem;
    }

    .bookx-meta-label {
        font-size: 0.72rem;
        font-weight: 800;
        color: #9a3412;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .bookx-meta-value {
        margin-top: 0.25rem;
        font-size: 1.1rem;
        font-weight: 900;
        color: #111827;
    }

    .bookx-section {
        margin-top: 1rem;
    }

    .bookx-section-title {
        font-size: 0.85rem;
        font-weight: 900;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .bookx-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .bookx-tag {
        background: #fffbeb;
        border: 1px solid rgba(249, 115, 22, 0.20);
        color: #9a3412;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.82rem;
    }

    .bookx-abstract {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 14px;
        padding: 1rem;
        color: #111827;
        font-size: 0.95rem;
        line-height: 1.65;
        white-space: pre-wrap;
    }

    @media (max-width: 860px) {
        .bookx-wrap {
            grid-template-columns: 1fr;
        }

        .bookx-cover img,
        .bookx-cover-fallback {
            height: 320px;
        }

        .bookx-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>