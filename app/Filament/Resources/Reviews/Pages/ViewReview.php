<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\PublicationVersionResource;
use App\Filament\Resources\Reviews\ReviewResource;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    // ─────────────────────────────────────────────────────────────
    // Role helpers
    // ─────────────────────────────────────────────────────────────

    protected function isAuthor(): bool
    {
        return (bool) auth()->user()?->hasRole('author');
    }

    protected function isReviewer(): bool
    {
        return (bool) auth()->user()?->hasRole('reviewer');
    }

    protected function isAdmin(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(['admin', 'super_admin']);
    }

    // ─────────────────────────────────────────────────────────────
    // Mount — reviewer & admin redirect ke edit
    // ─────────────────────────────────────────────────────────────

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->isReviewer() || $this->isAdmin()) {
            $this->redirect(
                ReviewResource::getUrl('edit', ['record' => $record])
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Status banner
    // ─────────────────────────────────────────────────────────────

    protected function renderStatusBanner(): HtmlString
    {
        $decision = $this->record->decision ?? null;

        $map = [
            null => [
                'color'   => '#8B5CF6',
                'bg'      => '#F5F3FF',
                'border'  => '#DDD6FE',
                'icon'    => '⏳',
                'label'   => 'Dalam Review',
                'title'   => 'Naskah Anda sedang direview',
                'message' => 'Reviewer sedang memeriksa naskah Anda. Harap tunggu hasil keputusan.',
            ],
            'revision_required' => [
                'color'   => '#F59E0B',
                'bg'      => '#FFFBEB',
                'border'  => '#FDE68A',
                'icon'    => '✏️',
                'label'   => 'Revisi Diperlukan',
                'title'   => 'Naskah Anda perlu direvisi',
                'message' => 'Reviewer memberikan catatan revisi. Pelajari komentar di bawah, perbaiki naskah, lalu klik tombol <strong>Upload Revisi</strong> di atas.',
            ],
            'accepted' => [
                'color'   => '#10B981',
                'bg'      => '#ECFDF5',
                'border'  => '#A7F3D0',
                'icon'    => '🎉',
                'label'   => 'Diterima',
                'title'   => 'Selamat! Naskah Anda diterima',
                'message' => 'Naskah Anda telah diterima oleh reviewer. Tim editor akan segera menjadwalkan penerbitan.',
            ],
            'rejected' => [
                'color'   => '#EF4444',
                'bg'      => '#FEF2F2',
                'border'  => '#FECACA',
                'icon'    => '😞',
                'label'   => 'Ditolak',
                'title'   => 'Naskah tidak dapat diterima',
                'message' => 'Mohon maaf, naskah Anda tidak dapat diterima. Pelajari catatan reviewer di bawah sebagai bahan perbaikan untuk submission berikutnya.',
            ],
        ];

        $cfg      = $map[$decision] ?? $map[null];
        $pubType  = $this->record->publicationVersion?->publication?->publicationType?->name ?? 'Publikasi';
        $version  = $this->record->publicationVersion?->version_number
            ? 'v' . $this->record->publicationVersion->version_number
            : '-';
        $reviewer = $this->record->reviewer?->name ?? '-';

        return new HtmlString("
            <div style='
                background:{$cfg['bg']};
                border:1.5px solid {$cfg['border']};
                border-left:5px solid {$cfg['color']};
                border-radius:10px;
                padding:16px 20px;
                margin-bottom:4px;
            '>
                <div style='display:flex;align-items:flex-start;gap:12px;'>
                    <span style='font-size:24px;line-height:1;flex-shrink:0;'>{$cfg['icon']}</span>
                    <div style='flex:1;'>
                        <div style='display:flex;align-items:center;gap:8px;margin-bottom:6px;'>
                            <span style='
                                background:{$cfg['color']};color:white;font-size:11px;
                                font-weight:700;padding:2px 10px;border-radius:20px;
                                text-transform:uppercase;letter-spacing:0.5px;
                            '>{$cfg['label']}</span>
                        </div>
                        <div style='font-size:14px;font-weight:600;color:#1F2937;margin-bottom:4px;'>{$cfg['title']}</div>
                        <div style='font-size:13px;color:#4B5563;line-height:1.6;'>{$cfg['message']}</div>
                        <div style='
                            display:flex;flex-wrap:wrap;gap:16px;
                            margin-top:10px;padding-top:10px;
                            border-top:1px solid {$cfg['border']};
                            font-size:12px;color:#6B7280;
                        '>
                            <span>📄 <strong>{$pubType}</strong> · {$version}</span>
                            <span>👤 Reviewer: <strong>{$reviewer}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        ");
    }

    // ─────────────────────────────────────────────────────────────
    // Infolist
    // ─────────────────────────────────────────────────────────────

    public function infolist(Schema $schema): Schema
    {
        $hasDecision = filled($this->record->decision);
        $hasNotes    = $this->record->notes()->exists();
        $hasComment  = filled($this->record->overall_comment);

        return $schema->components([

            // ── Status Banner ─────────────────────────────────────
            \Filament\Forms\Components\Placeholder::make('status_banner')
                ->label('')
                ->content(fn() => $this->renderStatusBanner())
                ->columnSpanFull(),

            // ── Hasil Review — full width, 4 kolom di desktop ────
            Section::make('Hasil Review')
                ->icon('heroicon-o-clipboard-document-check')
                ->columnSpanFull()
                ->columns(['default' => 2, 'lg' => 4])
                ->schema([
                    TextEntry::make('reviewer.name')
                        ->label('Reviewer')
                        ->weight(\Filament\Support\Enums\FontWeight::Medium),

                    TextEntry::make('decision')
                        ->label('Keputusan')
                        ->badge()
                        ->color(fn(?string $state): string => match ($state) {
                            'accepted'          => 'success',
                            'rejected'          => 'danger',
                            'revision_required' => 'warning',
                            default             => 'gray',
                        })
                        ->formatStateUsing(fn(?string $state): string => match ($state) {
                            'accepted'          => 'Diterima ✅',
                            'rejected'          => 'Ditolak ❌',
                            'revision_required' => 'Perlu Revisi ✏️',
                            default             => 'Menunggu keputusan...',
                        }),

                    TextEntry::make('publicationVersion.version_number')
                        ->label('Versi yang Direview')
                        ->formatStateUsing(fn($state) => 'v' . $state),

                    TextEntry::make('updated_at')
                        ->label('Tanggal Keputusan')
                        ->dateTime('d M Y, H:i')
                        ->placeholder('Belum ada keputusan'),
                ]),

            // ── Menunggu review — tampil jika belum ada keputusan ─
            Section::make('')
                ->columnSpanFull()
                ->visible(fn() => !$hasDecision)
                ->schema([
                    TextEntry::make('waiting_info')
                        ->label('')
                        ->default(
                            'Reviewer belum memberikan keputusan. ' .
                                'Halaman ini akan diperbarui setelah proses review selesai. ' .
                                'Anda akan mendapat notifikasi ketika hasilnya tersedia.'
                        )
                        ->extraAttributes([
                            'style' => 'color:#6B7280;font-style:italic;text-align:center;padding:16px 0;'
                        ])
                        ->columnSpanFull(),
                ]),

            // ── Catatan & Komentar — 2 kolom, hanya jika ada keputusan
            Grid::make()
                ->columns(['default' => 1, 'lg' => 2])
                ->columnSpanFull()
                ->visible(fn() => $hasDecision && ($hasNotes || $hasComment))
                ->schema([

                    // Kolom kiri — Catatan per Bagian
                    Section::make('Catatan per Bagian')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->columnSpan(1)
                        ->schema([
                            // Jika ada catatan
                            RepeatableEntry::make('notes')
                                ->label('')
                                ->columnSpanFull()
                                ->visible(fn() => $hasNotes)
                                ->schema([
                                    TextEntry::make('section')
                                        ->label('Bagian')
                                        ->badge()
                                        ->color('primary')
                                        ->formatStateUsing(fn(string $state): string => match ($state) {
                                            'title'        => 'Title',
                                            'abstract'     => 'Abstract',
                                            'introduction' => 'Introduction',
                                            'methods'      => 'Methods',
                                            'results'      => 'Results',
                                            'discussion'   => 'Discussion',
                                            'conclusion'   => 'Conclusion',
                                            'references'   => 'References',
                                            default        => $state,
                                        }),

                                    TextEntry::make('note')
                                        ->label('Catatan')
                                        ->html()
                                        ->columnSpanFull()
                                        ->extraAttributes(['class' => 'text-justify']),
                                ]),

                            // Jika tidak ada catatan
                            TextEntry::make('notes_empty')
                                ->label('')
                                ->default('Reviewer tidak memberikan catatan per bagian.')
                                ->visible(fn() => !$hasNotes)
                                ->extraAttributes([
                                    'style' => 'color:#9CA3AF;font-style:italic;'
                                ]),
                        ]),

                    // Kolom kanan — Komentar Umum
                    Section::make('Komentar Umum Reviewer')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->columnSpan(1)
                        ->schema([
                            // Jika ada komentar
                            TextEntry::make('overall_comment')
                                ->label('')
                                ->html()
                                ->columnSpanFull()
                                ->visible(fn() => $hasComment)
                                ->extraAttributes(['class' => 'text-justify']),

                            // Jika tidak ada komentar
                            TextEntry::make('comment_empty')
                                ->label('')
                                ->default('Reviewer tidak memberikan komentar umum.')
                                ->visible(fn() => !$hasComment)
                                ->extraAttributes([
                                    'style' => 'color:#9CA3AF;font-style:italic;'
                                ]),
                        ]),
                ]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Header actions
    // ─────────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('previewPdf')
                ->label(function () {
                    $v = $this->record->publicationVersion?->version_number;
                    return $v ? "Lihat Manuskrip (v{$v})" : 'Lihat Manuskrip';
                })
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->visible(fn() => filled($this->record->publicationVersion?->pdf_file_path))
                ->url(fn() => PublicationVersionResource::getUrl('pdf', [
                    'record' => $this->record->publicationVersion,
                ]))
                ->openUrlInNewTab(),

            Action::make('downloadAnnotatedPdf')
                ->label('Download PDF Anotasi')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn() => filled($this->record->attachments?->first()?->file_path))
                ->action(function () {
                    $attachment = $this->record->attachments()->latest()->first();
                    abort_unless($attachment && filled($attachment->file_path), 404);
                    return Storage::disk('local')->download($attachment->file_path);
                }),

            Action::make('uploadRevisi')
                ->label('Upload Revisi')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->visible(
                    fn() =>
                    $this->isAuthor() &&
                        $this->record->decision === 'revision_required'
                )
                ->url(fn() => PublicationResource::getUrl('edit', [
                    'record' => $this->record->publicationVersion?->publication,
                ]))
                ->tooltip('Perbaiki naskah lalu upload revisi di halaman publikasi'),
        ];
    }
}
