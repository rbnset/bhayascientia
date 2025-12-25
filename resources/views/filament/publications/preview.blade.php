@php
$title = $get('title') ?? '-';
$status = $get('status') ?? '-';
$abstract = $get('abstract');

$authors = $get('authorPublications') ?? [];
$authorIds = collect($authors)->pluck('author_id')->filter()->values();

$categoryIds = collect($get('categories') ?? [])->filter()->values();
$keywordIds = collect($get('keywords') ?? [])->filter()->values();

$authorNames = $authorIds->isEmpty()
? collect()
: \App\Models\Author::whereIn('id', $authorIds)->pluck('name', 'id');

$categoryNames = $categoryIds->isEmpty()
? collect()
: \App\Models\Category::whereIn('id', $categoryIds)->pluck('name', 'id');

$keywordNames = $keywordIds->isEmpty()
? collect()
: \App\Models\Keyword::whereIn('id', $keywordIds)->pluck('name', 'id');

$authorsOrdered = $authorIds->map(fn ($id) => $authorNames[$id] ?? 'Unknown')->all();

$uploaderAuthorId = \App\Models\Author::where('user_id', auth()->id())->value('id');

$statusLabel = strtoupper(str_replace('_', ' ', $status));
@endphp

<div class="pub-preview-wrapper">
    <div class="pub-preview-card pub-preview-card--amber">
        <div class="pub-preview-header">
            <div class="pub-preview-badge">
                {{ $statusLabel }}
            </div>

            <div class="pub-preview-icon">
                <x-heroicon-o-document-text class="w-10 h-10 text-white" />
            </div>

            <h2 class="pub-preview-title">
                {{ $title }}
            </h2>

            @if($authorsOrdered)
            <p class="pub-preview-subtitle">
                @foreach($authorIds as $id)
                @php $name = $authorNames[$id] ?? 'Unknown'; @endphp
                <span class="pub-author-chip">
                    {{ $name }}
                    @if($uploaderAuthorId && (int) $uploaderAuthorId === (int) $id)
                    <span class="pub-corresponding">(Corresponding)</span>
                    @endif
                </span>
                @endforeach
            </p>
            @else
            <p class="pub-preview-subtitle text-white/80">
                Belum ada author dipilih.
            </p>
            @endif
        </div>

        <div class="pub-preview-body">
            <div class="pub-preview-grid">
                <div class="pub-preview-stat">
                    <div class="pub-preview-stat-value">{{ $categoryIds->count() }}</div>
                    <div class="pub-preview-stat-label">Categories</div>
                </div>

                <div class="pub-preview-stat">
                    <div class="pub-preview-stat-value">{{ $keywordIds->count() }}</div>
                    <div class="pub-preview-stat-label">Keywords</div>
                </div>

                <div class="pub-preview-stat">
                    <div class="pub-preview-stat-value">{{ $authorIds->count() }}</div>
                    <div class="pub-preview-stat-label">Authors</div>
                </div>

                <div class="pub-preview-stat">
                    <div class="pub-preview-stat-value">{{ $get('method_id') ? 'Yes' : 'No' }}</div>
                    <div class="pub-preview-stat-label">Method</div>
                </div>
            </div>

            <div class="pub-preview-section">
                <div class="pub-preview-section-title">Categories</div>
                <div class="pub-preview-tags">
                    @forelse($categoryIds as $id)
                    <span class="pub-tag">{{ $categoryNames[$id] ?? 'Unknown' }}</span>
                    @empty
                    <span class="pub-tag pub-tag-muted">-</span>
                    @endforelse
                </div>
            </div>

            <div class="pub-preview-section">
                <div class="pub-preview-section-title">Keywords</div>
                <div class="pub-preview-tags">
                    @forelse($keywordIds as $id)
                    <span class="pub-tag">{{ $keywordNames[$id] ?? 'Unknown' }}</span>
                    @empty
                    <span class="pub-tag pub-tag-muted">-</span>
                    @endforelse
                </div>
            </div>

            <div class="pub-preview-section">
                <div class="pub-preview-section-title">Abstract / Summary</div>
                <div class="pub-preview-abstract">
                    {{ filled($abstract) ? $abstract : '-' }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ORANGE/AMBER style (selaras dengan default primary Filament = amber) */
    .pub-preview-wrapper {
        display: flex;
        justify-content: center;
    }

    .pub-preview-card {
        border-radius: 20px;
        overflow: hidden;
        max-width: 720px;
        width: 100%;
        color: white;
    }

    .pub-preview-card--amber {
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        /* amber -> orange */
        box-shadow: 0 10px 30px rgba(245, 158, 11, 0.25);
    }

    .pub-preview-header {
        padding: 1.75rem;
        text-align: center;
        position: relative;
    }

    .pub-preview-body {
        background: white;
        color: #111827;
        padding: 1.75rem;
        border-radius: 20px 20px 0 0;
        margin-top: -18px;
    }

    .pub-preview-icon {
        width: 72px;
        height: 72px;
        background: rgba(255, 255, 255, 0.18);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        backdrop-filter: blur(10px);
    }

    .pub-preview-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(255, 255, 255, 0.18);
        padding: 0.5rem 0.9rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 800;
        backdrop-filter: blur(10px);
    }

    .pub-preview-title {
        font-size: 1.25rem;
        font-weight: 900;
        margin: 0.25rem 0 0.5rem;
    }

    .pub-preview-subtitle {
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .pub-author-chip {
        display: inline-block;
        background: rgba(255, 255, 255, 0.18);
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        margin: 0.15rem 0.2rem;
    }

    .pub-corresponding {
        font-weight: 800;
        opacity: 0.95;
    }

    .pub-preview-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .pub-preview-stat {
        background: #f9fafb;
        border-radius: 12px;
        padding: 0.9rem;
        text-align: center;
    }

    .pub-preview-stat-value {
        font-size: 1.25rem;
        font-weight: 900;
        color: #f59e0b;
    }

    /* amber */
    .pub-preview-stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.2rem;
    }

    .pub-preview-section {
        margin-top: 1rem;
    }

    .pub-preview-section-title {
        font-size: 0.85rem;
        font-weight: 900;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .pub-preview-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .pub-tag {
        background: #fffbeb;
        color: #92400e;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.8rem;
    }

    /* amber-50 bg + amber-800 text */
    .pub-tag-muted {
        background: #f3f4f6;
        color: #9ca3af;
    }

    .pub-preview-abstract {
        background: #fffbeb;
        border-radius: 12px;
        padding: 0.9rem;
        font-size: 0.9rem;
        color: #92400e;
        white-space: pre-wrap;
    }

    @media (max-width: 640px) {
        .pub-preview-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>