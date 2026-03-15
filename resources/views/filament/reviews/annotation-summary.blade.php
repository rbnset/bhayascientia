{{--
resources/views/filament/reviews/annotation-summary.blade.php

FIXED v1.1:

BUG A — $record tidak tersedia di semua konteks Filament
View::make() tidak otomatis inject $record di semua context.
FIX: gunakan $record ?? $this->record ?? null dengan fallback aman.

BUG B — Arrow function fn() membutuhkan PHP 7.4+
Jika server PHP 7.3 → fatal error.
FIX: ganti dengan function() { return ...; }

BUG D & G — $annot->color bisa null/empty
$COLORS[$annot->color] → PHP warning jika color null.
FIX: $COLORS[$annot->color ?? 'yellow'] ?? '#9CA3AF'

BUG E — List overflow tanpa padding-bottom
Item terakhir terpotong oleh overflow:auto tanpa padding bawah.
FIX: tambah padding-bottom:.5rem pada container list.
--}}
@php
use App\Models\PdfAnnotation;
use Illuminate\Support\Str;

/* BUG A FIX: fallback bertingkat untuk berbagai context Filament */
$review = $record
?? (isset($this) && property_exists($this, 'record') ? $this->record : null)
?? null;

$annots = $review
? PdfAnnotation::where('review_id', $review->id)
->orderBy('page')->orderBy('created_at')
->get()
: collect();

$total = $annots->count();
$byType = $annots->groupBy('type');
$byPage = $annots->groupBy('page');
$pagesCount = $byPage->count();
$reviewerName = $review->reviewer?->name ?? 'Reviewer';

$typeIcons = [
'highlight' => ['✏️', 'Highlight', '#FFD700', '#1a1a00'],
'underline' => ['__', 'Underline', '#60A5FA', '#001a2e'],
'strikethrough' => ['~~', 'Strikethrough','#F87171', '#2e0000'],
'freehand' => ['🖊', 'Pen Bebas', '#A78BFA', '#1a0030'],
'comment' => ['💬', 'Komentar', '#34D399', '#00200f'],
'sticky' => ['📌', 'Sticky Note', '#FBBF24', '#201500'],
'shape' => ['⬛', 'Shape', '#6B7280', '#111'],
'text' => ['🔤', 'Teks Bebas', '#F472B6', '#20001a'],
];

$COLORS = [
'yellow' => '#FFD700', 'green' => '#4ADE80', 'red' => '#EF4444',
'blue' => '#60A5FA', 'orange' => '#FF6B18', 'black' => '#374151',
'white' => '#D1D5DB', 'pink' => '#F472B6', 'purple' => '#A78BFA',
'cyan' => '#22D3EE',
];
@endphp

<div style="font-family: ui-sans-serif, system-ui, sans-serif;">

    @if($total === 0)
    <p style="color:#9CA3AF; font-style:italic; font-size:13px; padding:.5rem 0;">
        Reviewer belum memberikan anotasi pada PDF naskah ini.
    </p>
    @else

    {{-- ── Stat cards ── --}}
    <div style="display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1rem;">

        <div
            style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:.75rem 1rem;min-width:100px;text-align:center;">
            <div style="font-size:22px;font-weight:800;color:#FF6B18;">{{ $total }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:2px;">Total Anotasi</div>
        </div>

        <div
            style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:.75rem 1rem;min-width:100px;text-align:center;">
            <div style="font-size:22px;font-weight:800;color:#6366f1;">{{ $pagesCount }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:2px;">Halaman Berisi Catatan</div>
        </div>

        {{-- BUG B FIX: ganti arrow function fn() dengan function() biasa --}}
        @php
        $topType = $byType->sortByDesc(function ($g) { return $g->count(); })->keys()->first();
        @endphp
        @if($topType)
        <div
            style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:.75rem 1rem;min-width:100px;text-align:center;">
            <div style="font-size:22px;">{{ $typeIcons[$topType][0] ?? '•' }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:2px;">Jenis Terbanyak</div>
            <div style="font-size:10px;font-weight:700;color:#374151;">{{ $typeIcons[$topType][1] ?? $topType }}</div>
        </div>
        @endif
    </div>

    {{-- ── Type breakdown ── --}}
    <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1.25rem;">
        @foreach($byType as $type => $group)
        @php $ti = $typeIcons[$type] ?? ['•', $type, '#9CA3AF', '#111']; @endphp
        <div
            style="display:flex;align-items:center;gap:.35rem;background:{{ $ti[3] }};border:1px solid {{ $ti[2] }}40;border-radius:99px;padding:.25rem .65rem;">
            <span style="font-size:12px;">{{ $ti[0] }}</span>
            <span style="font-size:11px;font-weight:700;color:{{ $ti[2] }};">{{ $ti[1] }}</span>
            <span
                style="font-size:11px;font-weight:800;color:#fff;background:{{ $ti[2] }};border-radius:99px;padding:0 5px;min-width:18px;text-align:center;">{{
                $group->count() }}</span>
        </div>
        @endforeach
    </div>

    {{-- ── Anotasi terbaru (maks 8) ── --}}
    <div style="margin-bottom:.75rem;">
        <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:.5rem;">
            Pratinjau Anotasi Reviewer
        </div>
        {{-- BUG E FIX: tambah padding-bottom agar item terakhir tidak terpotong --}}
        <div
            style="display:flex;flex-direction:column;gap:.4rem;max-height:320px;overflow-y:auto;padding-right:2px;padding-bottom:.5rem;">
            @foreach($annots->take(8) as $annot)
            @php
            $ti = $typeIcons[$annot->type] ?? ['•', $annot->type, '#9CA3AF', '#111'];
            /* BUG D & G FIX: fallback untuk color null/empty */
            $col = $COLORS[$annot->color ?? 'yellow'] ?? '#9CA3AF';
            /* BUG D FIX: eksplisit cek truthy untuk comment */
            $txt = $annot->comment
            ? $annot->comment
            : ($annot->selected_text
            ? '"' . Str::limit($annot->selected_text, 80) . '"'
            : '—');
            @endphp
            <div
                style="display:flex;align-items:flex-start;gap:.6rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .75rem;border-left:3px solid {{ $col }};">
                <div
                    style="width:24px;height:24px;border-radius:50%;background:{{ $col }}20;border:1.5px solid {{ $col }};display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;margin-top:1px;">
                    {{ $ti[0] }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.15rem;">
                        <span
                            style="font-size:10px;font-weight:700;color:{{ $ti[2] }};background:{{ $ti[3] }};border-radius:4px;padding:1px 5px;">{{
                            $ti[1] }}</span>
                        <span style="font-size:10px;color:#9CA3AF;">Hal. {{ $annot->page }}</span>
                        <span
                            style="margin-left:auto;width:10px;height:10px;border-radius:50%;background:{{ $col }};flex-shrink:0;"
                            title="{{ $annot->color ?? 'yellow' }}"></span>
                    </div>
                    <div style="font-size:12px;color:#374151;word-break:break-word;line-height:1.4;">
                        {{ Str::limit($txt, 120) }}
                    </div>
                </div>
            </div>
            @endforeach

            @if($total > 8)
            <div
                style="text-align:center;font-size:11px;color:#9CA3AF;padding:.5rem;background:#f8fafc;border-radius:8px;border:1px dashed #e2e8f0;">
                ... dan {{ $total - 8 }} anotasi lainnya. Klik <strong>"Lihat Anotasi Reviewer"</strong> di atas untuk
                melihat semua.
            </div>
            @endif
        </div>
    </div>

    {{-- ── Call to action ── --}}
    <div
        style="background:#fff7ed;border:1.5px solid #fed7aa;border-radius:10px;padding:.875rem 1rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
        <svg style="width:20px;height:20px;flex-shrink:0;" fill="none" stroke="#EA580C" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
        </svg>
        <div style="flex:1;min-width:200px;">
            <div style="font-size:13px;font-weight:700;color:#7C2D12;">Lihat anotasi lengkap di PDF</div>
            <div style="font-size:12px;color:#92400E;margin-top:2px;">
                Klik tombol <strong>"Lihat Anotasi Reviewer"</strong> di header halaman ini untuk membuka PDF beserta
                semua anotasi dari reviewer secara visual.
            </div>
        </div>
        <div
            style="display:flex;align-items:center;gap:.4rem;background:#EA580C;color:#fff;border-radius:8px;padding:.4rem .875rem;font-size:12px;font-weight:700;flex-shrink:0;white-space:nowrap;">
            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Buka PDF Viewer
        </div>
    </div>

    @endif
</div>