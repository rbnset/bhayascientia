<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\PublicationVersionResource;
use App\Filament\Resources\Reviews\ReviewResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewPublication extends ViewRecord
{
    protected static string $resource = PublicationResource::class;

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
    // Mount — reviewer & admin redirect ke edit
    // ─────────────────────────────────────────────────────────────

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->isReviewer() || $this->isAdmin()) {
            $this->redirect(
                PublicationResource::getUrl('edit', ['record' => $record])
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    protected function latestVersion(): ?\App\Models\PublicationVersion
    {
        return $this->record->versions()->latest('version_number')->first();
    }

    protected function latestReview(): ?\App\Models\Review
    {
        $latestVersion = $this->latestVersion();
        if (!$latestVersion) return null;

        return $this->record->reviews()
            ->where('reviews.publication_version_id', $latestVersion->id)
            ->orderByDesc('reviews.id')
            ->first();
    }

    // ─────────────────────────────────────────────────────────────
    // Header actions
    // ─────────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            // ── Lihat PDF Manuskrip ───────────────────────────────
            Action::make('previewPdf')
                ->label(function () {
                    $v = $this->latestVersion()?->version_number;
                    return $v ? "Lihat Manuskrip (v{$v})" : 'Lihat Manuskrip';
                })
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->visible(fn() => $this->record->versions()->exists())
                ->url(fn() => PublicationVersionResource::getUrl('pdf', [
                    'record' => $this->latestVersion(),
                ]))
                ->openUrlInNewTab(),

            // ── Lihat Detail Review — author, jika ada review ─────
            Action::make('lihatReview')
                ->label(function () {
                    $count = $this->record->reviews()->count();
                    return $count > 1 ? "Lihat Review ({$count})" : 'Lihat Detail Review';
                })
                ->icon('heroicon-o-clipboard-document-list')
                ->color('info')
                ->visible(fn() => $this->isAuthor() && $this->record->reviews()->exists())
                ->url(function () {
                    $review = $this->latestReview();
                    return $review
                        ? ReviewResource::getUrl('view', ['record' => $review->id])
                        : null;
                }),

            // ── Upload Revisi — author, revision_required ─────────
            Action::make('uploadRevisi')
                ->label('Upload Revisi')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->visible(
                    fn() =>
                    $this->isAuthor() &&
                        $this->record->status === 'revision_required'
                )
                ->url(fn() => PublicationResource::getUrl('edit', [
                    'record' => $this->record,
                ]))
                ->tooltip('Klik untuk membuka halaman edit dan upload revisi'),
        ];
    }
}
