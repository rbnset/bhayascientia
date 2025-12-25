@php
/** @var \App\Models\Review $review */

$decision = $review->decision ?? '-';
$decisionLabel = strtoupper(str_replace('_', ' ', $decision));

$decisionColor = match ($decision) {
'revision_required' => '#f59e0b', // amber
'accepted' => '#16a34a', // green
'rejected' => '#dc2626', // red
default => '#6b7280', // gray
};

$versionNumber = data_get($review, 'publicationVersion.version_number', '-');
$reviewerName = data_get($review, 'reviewer.name', '-');
$reviewedAt = optional($review->created_at)?->format('d M Y H:i');

$notes = $review->notes ?? collect();
$attachments = $review->attachments ?? collect();
@endphp

<div class="rvx">
    <div class="rvx-header">
        <div class="rvx-badges">
            <span class="rvx-badge" style="background: {{ $decisionColor }};">
                {{ $decisionLabel }}
            </span>

            <span class="rvx-badge rvx-badge--soft">
                Version: {{ $versionNumber }}
            </span>
        </div>

        <div class="rvx-title">
            Review oleh <span class="rvx-strong">{{ $reviewerName }}</span>
        </div>

        <div class="rvx-subtitle">
            {{ $reviewedAt ? "Reviewed at: {$reviewedAt}" : 'Reviewed at: -' }}
        </div>
    </div>

    <div class="rvx-body">
        <div class="rvx-section">
            <div class="rvx-section-title">Komentar umum</div>
            <div class="rvx-prose">
                {{ filled($review->overall_comment) ? $review->overall_comment : '-' }}
            </div>
        </div>

        <div class="rvx-section">
            <div class="rvx-section-title">Catatan per bagian</div>

            @if($notes->count())
            <div class="rvx-notes">
                @foreach($notes as $note)
                <div class="rvx-note">
                    <div class="rvx-note-head">
                        <span class="rvx-note-section">
                            {{ strtoupper($note->section ?? '-') }}
                        </span>
                    </div>
                    <div class="rvx-note-body">
                        {{ $note->note ?? '-' }}
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="rvx-muted">Tidak ada catatan per bagian.</div>
            @endif
        </div>

        <div class="rvx-section">
            <div class="rvx-section-title">Lampiran</div>

            @if($attachments->count())
            <div class="rvx-attachments">
                @foreach($attachments as $attachment)
                @php
                $url = \Illuminate\Support\Facades\Storage::disk('public')->url($attachment->file_path);
                @endphp
                <a class="rvx-attachment" href="{{ $url }}" target="_blank" rel="noopener">
                    Download / Buka PDF
                </a>
                @endforeach
            </div>
            @else
            <div class="rvx-muted">Tidak ada lampiran.</div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Review Preview (simple, orange, seragam) */
    .rvx {
        padding: 0.25rem;
    }

    .rvx-header {
        border-radius: 18px;
        padding: 1.25rem 1.25rem;
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: #fff;
        box-shadow: 0 18px 40px rgba(249, 115, 22, 0.18);
    }

    .rvx-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .rvx-badge {
        display: inline-flex;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 900;
        letter-spacing: 0.04em;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .rvx-badge--soft {
        background: rgba(255, 255, 255, 0.16);
        font-weight: 800;
    }

    .rvx-title {
        font-size: 1.1rem;
        font-weight: 900;
        margin-bottom: 0.25rem;
    }

    .rvx-strong {
        font-weight: 900;
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .rvx-subtitle {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .rvx-body {
        margin-top: 1rem;
        background: #fff;
        border: 1px solid #f3f4f6;
        border-radius: 18px;
        padding: 1.25rem 1.25rem;
        box-shadow: 0 12px 30px rgba(17, 24, 39, 0.06);
    }

    .rvx-section {
        margin-top: 1rem;
    }

    .rvx-section:first-child {
        margin-top: 0;
    }

    .rvx-section-title {
        font-size: 0.85rem;
        font-weight: 900;
        color: #111827;
        margin-bottom: 0.55rem;
    }

    .rvx-prose {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 14px;
        padding: 0.9rem;
        color: #111827;
        line-height: 1.65;
        white-space: pre-wrap;
    }

    .rvx-notes {
        display: grid;
        gap: 0.75rem;
    }

    .rvx-note {
        border: 1px solid #f3f4f6;
        border-radius: 14px;
        padding: 0.9rem;
        background: #fff;
    }

    .rvx-note-head {
        margin-bottom: 0.4rem;
    }

    .rvx-note-section {
        display: inline-flex;
        background: #fffbeb;
        border: 1px solid rgba(249, 115, 22, 0.20);
        color: #9a3412;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 900;
    }

    .rvx-note-body {
        color: #374151;
        line-height: 1.6;
        white-space: pre-wrap;
    }

    .rvx-attachments {
        display: grid;
        gap: 0.6rem;
    }

    .rvx-attachment {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: #ffffff;
        font-weight: 900;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        box-shadow: 0 10px 22px rgba(249, 115, 22, 0.22);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .rvx-attachment:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 26px rgba(249, 115, 22, 0.28);
        color: #ffffff;
    }

    .rvx-muted {
        color: #9ca3af;
        font-size: 0.95rem;
    }
</style>