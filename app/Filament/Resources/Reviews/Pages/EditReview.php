<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\PublicationVersionResource;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\User;
use App\Notifications\PublicationAccepted;
use App\Notifications\PublicationRejected;
use App\Notifications\PublicationRevisionRequired;
use App\Notifications\ReviewDecisionForAuthor;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;

class EditReview extends EditRecord
{
    protected static string $resource = ReviewResource::class;

    // ─────────────────────────────────────────────────────────────
    // Role helpers
    // ─────────────────────────────────────────────────────────────

    protected function isReviewer(): bool
    {
        return (bool) auth()->user()?->hasRole('reviewer');
    }

    protected function isAuthor(): bool
    {
        return (bool) auth()->user()?->hasRole('author');
    }

    protected function isAdmin(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(['admin', 'super_admin']);
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Author redirect ke ViewReview
        if ($this->isAuthor()) {
            $this->redirect(
                ReviewResource::getUrl('view', ['record' => $record])
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    protected function latestAttachment(): ?\App\Models\ReviewAttachment
    {
        return $this->record->attachments()->latest()->first();
    }

    // ─────────────────────────────────────────────────────────────
    // Status banner — pesan disesuaikan per role & decision
    // ─────────────────────────────────────────────────────────────

    protected function renderStatusBanner(): HtmlString
    {
        $decision = $this->record->decision ?? null;
        $role     = match (true) {
            $this->isReviewer() => 'reviewer',
            $this->isAuthor()   => 'author',
            default             => 'admin',
        };

        // [decision][role] => config
        $map = [
            null => [
                'reviewer' => [
                    'color'   => '#3B82F6',
                    'bg'      => '#EFF6FF',
                    'border'  => '#BFDBFE',
                    'icon'    => '🔍',
                    'label'   => 'Sedang Direview',
                    'title'   => 'Review sedang berlangsung',
                    'message' => 'Baca naskah di Step 2, isi catatan per bagian dan komentar umum, lalu tentukan keputusan di Step 3.',
                ],
                'author' => [
                    'color'   => '#8B5CF6',
                    'bg'      => '#F5F3FF',
                    'border'  => '#DDD6FE',
                    'icon'    => '⏳',
                    'label'   => 'Dalam Review',
                    'title'   => 'Naskah Anda sedang direview',
                    'message' => 'Reviewer sedang memeriksa naskah Anda. Harap tunggu hasil keputusan dari reviewer.',
                ],
                'admin' => [
                    'color'   => '#3B82F6',
                    'bg'      => '#EFF6FF',
                    'border'  => '#BFDBFE',
                    'icon'    => '🔍',
                    'label'   => 'Sedang Direview',
                    'title'   => 'Proses review berlangsung',
                    'message' => 'Reviewer sedang meninjau naskah. Pantau progress di halaman ini.',
                ],
            ],
            'revision_required' => [
                'reviewer' => [
                    'color'   => '#F59E0B',
                    'bg'      => '#FFFBEB',
                    'border'  => '#FDE68A',
                    'icon'    => '🔄',
                    'label'   => 'Perlu Revisi',
                    'title'   => 'Anda meminta revisi',
                    'message' => 'Keputusan revisi sudah dikirim ke author. Tunggu author mengirim ulang naskah yang telah diperbaiki.',
                ],
                'author' => [
                    'color'   => '#F59E0B',
                    'bg'      => '#FFFBEB',
                    'border'  => '#FDE68A',
                    'icon'    => '✏️',
                    'label'   => 'Revisi Diperlukan',
                    'title'   => 'Naskah Anda perlu direvisi',
                    'message' => 'Reviewer memberikan catatan revisi. Pelajari komentar di bawah, perbaiki naskah, lalu upload revisi melalui tombol di atas.',
                ],
                'admin' => [
                    'color'   => '#F59E0B',
                    'bg'      => '#FFFBEB',
                    'border'  => '#FDE68A',
                    'icon'    => '🔄',
                    'label'   => 'Perlu Revisi',
                    'title'   => 'Reviewer meminta revisi',
                    'message' => 'Author telah dinotifikasi dan perlu mengirim ulang naskah yang diperbaiki.',
                ],
            ],
            'accepted' => [
                'reviewer' => [
                    'color'   => '#10B981',
                    'bg'      => '#ECFDF5',
                    'border'  => '#A7F3D0',
                    'icon'    => '✅',
                    'label'   => 'Diterima',
                    'title'   => 'Anda menerima naskah ini',
                    'message' => 'Keputusan penerimaan sudah dikirim ke author. Naskah menunggu jadwal terbit dari editor.',
                ],
                'author' => [
                    'color'   => '#10B981',
                    'bg'      => '#ECFDF5',
                    'border'  => '#A7F3D0',
                    'icon'    => '🎉',
                    'label'   => 'Diterima',
                    'title'   => 'Selamat! Naskah Anda diterima',
                    'message' => 'Naskah Anda telah diterima oleh reviewer. Tim editor akan segera menjadwalkan penerbitan.',
                ],
                'admin' => [
                    'color'   => '#10B981',
                    'bg'      => '#ECFDF5',
                    'border'  => '#A7F3D0',
                    'icon'    => '✅',
                    'label'   => 'Diterima',
                    'title'   => 'Naskah diterima reviewer',
                    'message' => 'Naskah telah diterima. Jadwalkan penerbitan di halaman publikasi.',
                ],
            ],
            'rejected' => [
                'reviewer' => [
                    'color'   => '#EF4444',
                    'bg'      => '#FEF2F2',
                    'border'  => '#FECACA',
                    'icon'    => '❌',
                    'label'   => 'Ditolak',
                    'title'   => 'Anda menolak naskah ini',
                    'message' => 'Keputusan penolakan sudah dikirim ke author beserta catatan dari Anda.',
                ],
                'author' => [
                    'color'   => '#EF4444',
                    'bg'      => '#FEF2F2',
                    'border'  => '#FECACA',
                    'icon'    => '😞',
                    'label'   => 'Ditolak',
                    'title'   => 'Naskah tidak dapat diterima',
                    'message' => 'Mohon maaf, naskah Anda tidak dapat diterima. Pelajari catatan reviewer di bawah sebagai bahan perbaikan untuk submission berikutnya.',
                ],
                'admin' => [
                    'color'   => '#EF4444',
                    'bg'      => '#FEF2F2',
                    'border'  => '#FECACA',
                    'icon'    => '❌',
                    'label'   => 'Ditolak',
                    'title'   => 'Naskah ditolak reviewer',
                    'message' => 'Author telah dinotifikasi mengenai penolakan ini.',
                ],
            ],
        ];

        $cfg = $map[$decision][$role] ?? $map[null]['admin'];

        $publication = $this->record->publicationVersion?->publication;
        $pubTitle    = e($publication?->title ?? '-');
        $pubType     = $publication?->publicationType?->name ?? 'Publikasi';
        $version     = $this->record->publicationVersion?->version_number
            ? 'v' . $this->record->publicationVersion->version_number
            : '-';
        $reviewer    = $this->record->reviewer?->name ?? '-';

        // Info row berbeda per role
        $infoRow = match ($role) {
            'reviewer' => "<span>📄 <strong>{$pubType}</strong> · {$version}</span><span>📝 {$pubTitle}</span>",
            'author'   => "<span>📄 <strong>{$pubType}</strong> · {$version}</span><span>👤 Reviewer: <strong>{$reviewer}</strong></span>",
            default    => "<span>📄 <strong>{$pubType}</strong> · {$version}</span><span>📝 {$pubTitle}</span><span>👤 Reviewer: <strong>{$reviewer}</strong></span>",
        };

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
                        '>{$infoRow}</div>
                    </div>
                </div>
            </div>
        ");
    }

    // ─────────────────────────────────────────────────────────────
    // Form — banner di atas wizard
    // ─────────────────────────────────────────────────────────────

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return parent::form($schema)
            ->components(array_merge(
                [
                    \Filament\Forms\Components\Placeholder::make('status_banner')
                        ->label('')
                        ->content(fn() => $this->renderStatusBanner())
                        ->columnSpanFull(),
                ],
                \App\Filament\Resources\Reviews\Schemas\ReviewForm::configure($schema)->getComponents()
            ));
    }

    // ─────────────────────────────────────────────────────────────
    // Saved notification
    // ─────────────────────────────────────────────────────────────

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Review berhasil disimpan')
            ->body('Perubahan review berhasil disimpan.');
    }

    // ─────────────────────────────────────────────────────────────
    // Header actions
    // ─────────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            // ── Lihat Manuskrip PDF ───────────────────────────────
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

            // ── Download PDF Anotasi (semua role) ─────────────────
            Action::make('downloadAnnotatedPdf')
                ->label('Download PDF Anotasi')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn() => filled($this->latestAttachment()?->file_path))
                ->action(function () {
                    $attachment = $this->latestAttachment();
                    abort_unless($attachment && filled($attachment->file_path), 404);
                    return Storage::disk('local')->download($attachment->file_path);
                }),

            // ── Upload Revisi — khusus AUTHOR ─────────────────────
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

            // ── Lihat Revisi Terbaru — khusus REVIEWER & ADMIN ────
            Action::make('lihatRevisiTerbaru')
                ->label(function () {
                    $latest = $this->record->publicationVersion?->publication
                        ?->versions()->latest('version_number')->first();
                    return $latest
                        ? 'Lihat Revisi Terbaru (v' . $latest->version_number . ')'
                        : 'Lihat Revisi Terbaru';
                })
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('primary')
                ->visible(
                    fn() => ($this->isReviewer() || $this->isAdmin()) &&
                        $this->record->decision === 'revision_required'
                )
                ->url(function () {
                    $latest = $this->record->publicationVersion?->publication
                        ?->versions()->latest('version_number')->first();
                    return $latest
                        ? PublicationVersionResource::getUrl('pdf', ['record' => $latest])
                        : null;
                })
                ->openUrlInNewTab()
                ->tooltip('Buka PDF revisi terbaru dari author'),

            // ── Delete — admin & reviewer saja ───────────────────
            DeleteAction::make()
                ->visible(fn() => !$this->isAuthor())
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Review dihapus')
                        ->body('Data review berhasil dihapus.')
                ),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // After save
    // ─────────────────────────────────────────────────────────────

    protected function afterSave(): void
    {
        if ($this->isAuthor()) return;

        $review      = $this->record;
        $publication = $review->publicationVersion?->publication;

        if (!$publication) return;

        // Sync status publikasi
        $newStatus = match ($review->decision) {
            'revision_required' => 'revision_required',
            'accepted'          => 'accepted',
            'rejected'          => 'rejected',
            default             => null,
        };

        if ($newStatus) {
            $publication->update(['status' => $newStatus]);
        }

        // Kirim notifikasi spesifik ke author
        $authorUserIds = $publication->authors()
            ->pluck('authors.user_id')
            ->filter()->unique()->values();

        if ($authorUserIds->isEmpty()) return;

        $recipients = User::whereIn('id', $authorUserIds)->get();

        match ($review->decision) {
            'revision_required' => \Illuminate\Support\Facades\Notification::send(
                $recipients,
                new PublicationRevisionRequired($review)
            ),
            'accepted' => \Illuminate\Support\Facades\Notification::send(
                $recipients,
                new PublicationAccepted($review)
            ),
            'rejected' => \Illuminate\Support\Facades\Notification::send(
                $recipients,
                new PublicationRejected($review)
            ),
            default => \Illuminate\Support\Facades\Notification::send(
                $recipients,
                new ReviewDecisionForAuthor($review)
            ),
        };
    }
}
