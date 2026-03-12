@php
/** @var \App\Models\Review $review */

$decision = $review->decision ?? null;
$decisionColor = match ($decision) {
'revision_required' => ['bg' => '#f59e0b', 'light' => '#fffbeb', 'border' => '#fcd34d', 'text' => '#92400e'],
'accepted' => ['bg' => '#16a34a', 'light' => '#f0fdf4', 'border' => '#86efac', 'text' => '#14532d'],
'rejected' => ['bg' => '#dc2626', 'light' => '#fff1f2', 'border' => '#fca5a5', 'text' => '#7f1d1d'],
default => ['bg' => '#6b7280', 'light' => '#f9fafb', 'border' => '#e5e7eb', 'text' => '#374151'],
};
$decisionLabel = $decision ? strtoupper(str_replace('_', ' ', $decision)) : 'PENDING';
$versionNumber = $review->publicationVersion?->version_number ?? '-';
$reviewerName = $review->reviewer?->name ?? '-';
$reviewedAt = $review->created_at
? \Carbon\Carbon::parse($review->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB'
: '-';

$notes = $review->notes ?? collect();
$overallComment = $review->overall_comment;
@endphp

<div class="rvx">

    {{-- ── HEADER ── --}}
    <div class="rvx-header"
        style="background: linear-gradient(135deg, {{ $decisionColor['bg'] }}dd 0%, {{ $decisionColor['bg'] }} 100%);">
        <div class="rvx-badges">
            <span class="rvx-badge" style="background: rgba(0,0,0,0.18);">
                {{ $decisionLabel }}
            </span>
            <span class="rvx-badge" style="background: rgba(255,255,255,0.18);">
                Version v{{ $versionNumber }}
            </span>
        </div>
        <div class="rvx-title">
            Reviewed by <span class="rvx-strong">{{ $reviewerName }}</span>
        </div>
        <div class="rvx-subtitle">
            {{ $reviewedAt }}
        </div>
    </div>

    {{-- ── BODY ── --}}
    <div class="rvx-body">

        {{-- Overall Comment --}}
        <div class="rvx-section">
            <div class="rvx-section-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                </svg>
                Overall Comment
            </div>
            @if(filled($overallComment))
            <div class="rvx-prose"
                style="border-color: {{ $decisionColor['border'] }}; background: {{ $decisionColor['light'] }}; color: {{ $decisionColor['text'] }};">
                {!! $overallComment !!}
            </div>
            @else
            <div class="rvx-muted">No overall comment provided.</div>
            @endif
        </div>

        {{-- Notes per section --}}
        <div class="rvx-section">
            <div class="rvx-section-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                Notes per Section
                @if($notes->count())
                <span class="rvx-count">{{ $notes->count() }}</span>
                @endif
            </div>

            @if($notes->count())
            <div class="rvx-notes">
                @foreach($notes as $note)
                <div class="rvx-note" style="border-color: {{ $decisionColor['border'] }};">
                    @if($note->section)
                    <div class="rvx-note-head">
                        <span class="rvx-note-section"
                            style="background: {{ $decisionColor['light'] }}; color: {{ $decisionColor['text'] }}; border-color: {{ $decisionColor['border'] }};">
                            {{ strtoupper($note->section) }}
                        </span>
                    </div>
                    @endif
                    <div class="rvx-note-body">{!! $note->note ?? '-' !!}</div>
                </div>
                @endforeach
            </div>
            @else
            <div class="rvx-muted">No section notes provided.</div>
            @endif
        </div>

    </div>{{-- end rvx-body --}}
</div>

<style>
    .rvx {
        font-family: inherit;
        padding: 0.125rem;
    }

    /* ── Header ── */
    .rvx-header {
        border-radius: 16px;
        padding: 1.1rem 1.25rem 1.25rem;
        color: #fff;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .rvx-badges {
        display: flex;
        gap: 0.4rem;
        flex-wrap: wrap;
        margin-bottom: 0.65rem;
    }

    .rvx-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        color: #fff;
    }

    .rvx-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.2rem;
        line-height: 1.4;
    }

    .rvx-strong {
        font-weight: 900;
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .rvx-subtitle {
        font-size: 0.8rem;
        opacity: 0.85;
    }

    /* ── Body ── */
    .rvx-body {
        margin-top: 0.75rem;
        border-radius: 16px;
        border: 1px solid #f3f4f6;
        padding: 1rem 1.1rem;
        background: #fff;
        box-shadow: 0 4px 16px rgba(17, 24, 39, 0.05);
        display: flex;
        flex-direction: column;
        gap: 1.1rem;
    }

    /* dark mode */
    @media (prefers-color-scheme: dark) {
        .rvx-body {
            background: #1f2937;
            border-color: #374151;
        }

        .rvx-section-title {
            color: #f9fafb;
        }

        .rvx-note {
            background: #111827;
            border-color: #374151 !important;
        }

        .rvx-note-body {
            color: #d1d5db;
        }

        .rvx-muted {
            color: #6b7280;
        }
    }

    .rvx-section {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .rvx-section-title {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8rem;
        font-weight: 800;
        color: #111827;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .rvx-section-title svg {
        width: 1rem;
        height: 1rem;
        opacity: 0.6;
        flex-shrink: 0;
    }

    .rvx-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        color: #374151;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 800;
        padding: 0.1rem 0.45rem;
        margin-left: 0.25rem;
    }

    /* Overall comment */
    .rvx-prose {
        border-radius: 12px;
        border: 1px solid;
        padding: 0.85rem 1rem;
        font-size: 0.9rem;
        line-height: 1.7;
        white-space: pre-wrap;
        word-break: break-word;
    }

    /* Notes */
    .rvx-notes {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .rvx-note {
        border: 1px solid;
        border-radius: 12px;
        padding: 0.75rem 0.9rem;
        background: #fff;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .rvx-note-head {
        display: flex;
        align-items: center;
    }

    .rvx-note-section {
        display: inline-flex;
        align-items: center;
        border: 1px solid;
        padding: 0.15rem 0.55rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.06em;
    }

    .rvx-note-body {
        font-size: 0.875rem;
        color: #374151;
        line-height: 1.65;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .rvx-muted {
        font-size: 0.875rem;
        color: #9ca3af;
        font-style: italic;
    }

    /* ── Mobile first responsive ── */
    @media (max-width: 480px) {
        .rvx-header {
            padding: 0.9rem 1rem 1rem;
            border-radius: 14px;
        }

        .rvx-title {
            font-size: 0.95rem;
        }

        .rvx-body {
            padding: 0.85rem 0.9rem;
            border-radius: 14px;
        }

        .rvx-prose {
            padding: 0.75rem 0.85rem;
            font-size: 0.85rem;
        }

        .rvx-note {
            padding: 0.65rem 0.75rem;
        }

        .rvx-note-body {
            font-size: 0.825rem;
        }
    }
</style>