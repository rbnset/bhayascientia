<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\PublicationVersionResource;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\User;
use App\Notifications\PublicationAccepted;
use App\Notifications\PublicationInReview;
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

    // ─────────────────────────────────────────────────────────────
    // ✅ Helper: ambil publication — support opini (tanpa version)
    // ─────────────────────────────────────────────────────────────

    protected function getPublication(): ?\App\Models\Publication
    {
        // Opini tanpa manuskrip: langsung dari publication_id
        if (is_null($this->record->publication_version_id)) {
            return $this->record->publication ?? null;
        }

        // Tipe lain: via publicationVersion
        return $this->record->publicationVersion?->publication ?? null;
    }

    // ─────────────────────────────────────────────────────────────
    // Mount — author redirect ke ViewReview
    // ─────────────────────────────────────────────────────────────

    public function mount(int|string $record): void
    {
        parent::mount($record);

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

    protected function hasNewerVersion(): bool
    {
        // Opini tanpa manuskrip: tidak ada versi, selalu false
        if (is_null($this->record->publication_version_id)) {
            return false;
        }

        $current = $this->record->publicationVersion?->version_number ?? 0;
        $latest  = $this->record->publicationVersion?->publication
            ?->versions()->max('version_number') ?? 0;
        return $latest > $current;
    }

    protected function latestPublicationVersion(): ?\App\Models\PublicationVersion
    {
        // Opini tanpa manuskrip: tidak ada version
        if (is_null($this->record->publication_version_id)) {
            return null;
        }

        return $this->record->publicationVersion?->publication
            ?->versions()->latest('version_number')->first();
    }

    protected function authorRecipients()
    {
        // ✅ Support opini: ambil publication via helper terpusat
        $publication   = $this->getPublication();
        $authorUserIds = $publication?->authors()
            ->pluck('authors.user_id')
            ->filter()->unique()->values();

        if (!$authorUserIds || $authorUserIds->isEmpty()) return collect();

        return User::whereIn('id', $authorUserIds)->get();
    }

    // ─────────────────────────────────────────────────────────────
    // Status banner
    // ─────────────────────────────────────────────────────────────

    protected function renderStatusBanner(): HtmlString
    {
        $decision = $this->record->decision ?? null;
        $role     = match (true) {
            $this->isReviewer() => 'reviewer',
            $this->isAuthor()   => 'author',
            default             => 'admin',
        };

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
                    'message' => 'Reviewer sedang memeriksa naskah Anda. Harap tunggu hasil keputusan.',
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

        $cfg         = $map[$decision][$role] ?? $map[null]['admin'];

        // ✅ Gunakan helper terpusat
        $publication = $this->getPublication();
        $pubTitle    = e($publication?->title ?? '-');
        $pubType     = $publication?->publicationType?->name ?? 'Publikasi';

        // ✅ Support opini: tampilkan "Opini" atau versi jika ada
        $version = is_null($this->record->publication_version_id)
            ? 'Opini'
            : ($this->record->publicationVersion?->version_number
                ? 'v' . $this->record->publicationVersion->version_number
                : '-');

        $reviewer    = $this->record->reviewer?->name ?? '-';

        $infoRow = match ($role) {
            'reviewer' => "<span>📄 <strong>{$pubType}</strong> · {$version}</span><span>📝 {$pubTitle}</span>",
            'author'   => "<span>📄 <strong>{$pubType}</strong> · {$version}</span><span>👤 Reviewer: <strong>{$reviewer}</strong></span>",
            default    => "<span>📄 <strong>{$pubType}</strong> · {$version}</span><span>📝 {$pubTitle}</span><span>👤 Reviewer: <strong>{$reviewer}</strong></span>",
        };

        // Alert revisi baru — hanya untuk reviewer & hanya jika ada versi (bukan opini murni)
        $revisionAlert = '';
        if ($this->isReviewer() && $this->hasNewerVersion()) {
            $latestNo      = $this->latestPublicationVersion()?->version_number;
            $revisionAlert = "
                <div style='
                    margin-top:12px;
                    padding:10px 14px;
                    background:#FEF3C7;
                    border:1px solid #FDE68A;
                    border-radius:8px;
                    font-size:13px;
                    color:#92400E;
                    line-height:1.6;
                '>
                    🔔 <strong>Author telah mengirim revisi baru (v{$latestNo}).</strong>
                    Gunakan tombol <strong>\"Review Revisi Terbaru (v{$latestNo})\"</strong> di header untuk mulai mereview versi terbaru.
                </div>
            ";
        }

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
                        {$revisionAlert}
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

    // Step Rules

    public function previousWizardStep(): void
    {
        parent::previousWizardStep();
        $this->dispatch('reset-pdf-viewer');
    }

    public function nextWizardStep(): void
    {
        parent::nextWizardStep();
        $this->dispatch('reset-pdf-viewer');
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

            // ── Download PDF Anotasi ──────────────────────────────
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

            // ── Lihat Revisi Terbaru PDF — REVIEWER & ADMIN ───────
            Action::make('lihatRevisiTerbaru')
                ->label(fn() => 'Lihat Revisi (v' . ($this->latestPublicationVersion()?->version_number ?? '-') . ')')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('gray')
                ->visible(
                    fn() => ($this->isReviewer() || $this->isAdmin()) &&
                        $this->hasNewerVersion()
                )
                ->url(
                    fn() => $this->latestPublicationVersion()
                        ? PublicationVersionResource::getUrl('pdf', ['record' => $this->latestPublicationVersion()])
                        : null
                )
                ->openUrlInNewTab()
                ->tooltip('Buka PDF revisi terbaru dari author'),

            // ── Review Revisi Terbaru — khusus REVIEWER ──────────
            Action::make('mulaiReviewRevisi')
                ->label(fn() => 'Review Revisi Terbaru (v' . ($this->latestPublicationVersion()?->version_number ?? '-') . ')')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn() => $this->isReviewer() && $this->hasNewerVersion())
                ->requiresConfirmation()
                ->modalHeading('Mulai Review Revisi Terbaru?')
                ->modalDescription(function () {
                    $latest  = $this->latestPublicationVersion();
                    $current = $this->record->publicationVersion?->version_number;
                    return new HtmlString(
                        "📄 Saat ini Anda melihat review untuk <strong>v{$current}</strong>.<br><br>" .
                            "Author telah mengirim revisi <strong>v{$latest?->version_number}</strong>. " .
                            "Klik lanjut untuk membuat review baru terhadap versi terbaru ini."
                    );
                })
                ->modalSubmitActionLabel('Ya, Mulai Review')
                ->modalCancelActionLabel('Batal')
                ->action(function () {
                    $latestVersion = $this->latestPublicationVersion();

                    // ✅ Gunakan helper terpusat
                    $publication   = $this->getPublication();

                    if (!$latestVersion || !$publication) return;

                    $existingReview = \App\Models\Review::query()
                        ->where('publication_version_id', $latestVersion->id)
                        ->where('reviewer_id', auth()->id())
                        ->first();

                    if ($existingReview) {
                        $this->redirect(ReviewResource::getUrl('edit', ['record' => $existingReview->id]));
                        return;
                    }

                    $newReview = \App\Models\Review::create([
                        'publication_version_id' => $latestVersion->id,
                        'reviewer_id'            => auth()->id(),
                    ]);

                    $publication->update(['status' => 'in_review']);

                    $recipients = $this->authorRecipients();
                    if ($recipients->isNotEmpty()) {
                        \Illuminate\Support\Facades\Notification::send(
                            $recipients,
                            new PublicationInReview($publication)
                        );
                    }

                    Notification::make()
                        ->success()
                        ->title('Review revisi dimulai')
                        ->body('Revisi v' . $latestVersion->version_number . ' sedang Anda review. Author telah dinotifikasi.')
                        ->send();

                    $this->redirect(ReviewResource::getUrl('edit', ['record' => $newReview->id]));
                }),

            // ── Publish Naskah — admin & reviewer, status accepted ───
            Action::make('publishNaskah')
                ->label('Terbitkan Naskah')
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->visible(
                    // ✅ Gunakan helper terpusat agar opini juga bisa publish
                    fn() => ($this->isReviewer() || $this->isAdmin()) &&
                        $this->getPublication()?->status === 'accepted'
                )
                ->modalHeading('Terbitkan Naskah')
                ->modalDescription(new HtmlString(
                    '🎉 Naskah ini sudah diterima dan siap untuk diterbitkan.<br><br>' .
                        'Tentukan tanggal dan waktu penerbitan. Naskah akan langsung dapat diakses publik ' .
                        'setelah status berubah ke <strong>Published</strong>.'
                ))
                ->modalSubmitActionLabel('Terbitkan Sekarang')
                ->modalCancelActionLabel('Batal')
                ->form([
                    \Filament\Forms\Components\DateTimePicker::make('published_at')
                        ->label('Tanggal & Waktu Terbit')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->helperText('Biarkan sekarang untuk langsung terbit, atau pilih jadwal di masa depan.'),

                    \Filament\Forms\Components\Checkbox::make('confirm_publish')
                        ->label('Saya yakin ingin menerbitkan naskah ini ke publik')
                        ->required()
                        ->accepted(),
                ])
                ->action(function (array $data) {
                    // ✅ Gunakan helper terpusat
                    $publication = $this->getPublication();
                    if (!$publication) return;

                    $publication->update([
                        'status'       => 'published',
                        'published_at' => $data['published_at'],
                    ]);

                    $recipients = $this->authorRecipients();
                    if ($recipients->isNotEmpty()) {
                        \Illuminate\Support\Facades\Notification::send(
                            $recipients,
                            new \App\Notifications\PublicationScheduledToPublish($publication)
                        );
                    }

                    Notification::make()
                        ->success()
                        ->title('Naskah berhasil diterbitkan')
                        ->body('Naskah sudah live sejak ' . \Carbon\Carbon::parse($data['published_at'])->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . '.')
                        ->send();

                    $this->redirect(request()->header('Referer') ?? static::getUrl(['record' => $this->record]));
                }),

            // ── Lihat Publikasi — jika sudah published ────────────
            Action::make('lihatPublikasi')
                ->label('Lihat Halaman Publikasi')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->visible(
                    // ✅ Gunakan helper terpusat
                    fn() => $this->getPublication()?->status === 'published'
                )
                ->url(fn() => PublicationResource::getUrl('view', [
                    'record' => $this->getPublication(),
                ]))
                ->openUrlInNewTab(false),

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
    // After save — ✅ FIXED: support opini tanpa publicationVersion
    // ─────────────────────────────────────────────────────────────

    protected function afterSave(): void
    {
        if ($this->isAuthor()) return;

        $review = $this->record;

        // ✅ Gunakan helper terpusat — support opini (publication_version_id null)
        $publication = $this->getPublication();

        if (!$publication) return;

        // Sync status publikasi dari decision
        $newStatus = match ($review->decision) {
            'revision_required' => 'revision_required',
            'accepted'          => 'accepted',
            'rejected'          => 'rejected',
            default             => null,
        };

        if ($newStatus) {
            $publication->update(['status' => $newStatus]);
        }

        // Kirim notifikasi ke author
        $recipients = $this->authorRecipients();
        if ($recipients->isEmpty()) return;

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
