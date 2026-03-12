<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\Publications\Widgets\PublicationStatusBanner;
use App\Filament\Resources\PublicationVersionResource;
use App\Models\Author;
use App\Models\Publication;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

class EditPublication extends EditRecord
{
    protected static string $resource = PublicationResource::class;

    // ─────────────────────────────────────────────────────────────
    // Mount — blokir akses URL langsung untuk author jika terkunci
    // Notifikasi hanya muncul saat halaman ini benar-benar diakses
    // ─────────────────────────────────────────────────────────────

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (
            auth()->user()?->hasRole('author') &&
            in_array($this->record->status, PublicationResource::AUTHOR_LOCKED_STATUSES, true)
        ) {
            Notification::make()
                ->title('Tidak dapat mengedit publikasi')
                ->body(
                    'Publikasi berstatus "' . str($this->record->status)->headline() . '" ' .
                        'tidak dapat diubah. Hubungi editor jika ada koreksi yang diperlukan.'
                )
                ->warning()
                ->persistent()
                ->send();

            $this->redirect(PublicationResource::getUrl('index'));
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    protected function isReviewer(): bool
    {
        return (bool) auth()->user()?->hasRole('reviewer');
    }

    protected function shortTitle(): string
    {
        return Str::of((string) $this->record->title)
            ->squish()
            ->words(8, '…')
            ->toString();
    }

    // ─────────────────────────────────────────────────────────────
    // Mutate before save
    // ─────────────────────────────────────────────────────────────

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->isReviewer()) {
            $filtered = [
                'status' => $data['status'] ?? $this->record->status,
            ];

            if (($filtered['status'] ?? null) === 'published') {
                $filtered['published_at'] = $data['published_at'] ?? $this->record->published_at;
            }

            return $filtered;
        }

        // Non-reviewer: cek apakah judul berubah dan sudah digunakan publikasi lain
        $title = trim($data['title'] ?? '');
        if (filled($title) && $title !== $this->record->title) {
            $exists = Publication::where('title', $title)
                ->where('id', '!=', $this->record->id)
                ->exists();

            if ($exists) {
                Notification::make()
                    ->title('Judul sudah digunakan')
                    ->body(
                        'Judul karya ilmiah ini sudah pernah digunakan oleh publikasi lain. ' .
                            'Silakan gunakan judul yang berbeda atau tambahkan penjelasan spesifik ' .
                            '(metode, lokasi, atau konteks).'
                    )
                    ->danger()
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }

        return $data;
    }

    // ─────────────────────────────────────────────────────────────
    // Handle record update
    // ─────────────────────────────────────────────────────────────

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (UniqueConstraintViolationException $e) {
            Notification::make()
                ->title('Gagal memperbarui publikasi')
                ->body(
                    'Perubahan yang Anda lakukan bertabrakan dengan data yang sudah ada. ' .
                        'Silakan cek kembali judul atau data yang diubah.'
                )
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // After save
    // ─────────────────────────────────────────────────────────────

    protected function afterSave(): void
    {
        if ($this->isReviewer()) {
            if (
                $this->record->status === 'published' &&
                ($this->record->wasChanged('status') || $this->record->wasChanged('published_at'))
            ) {
                $authorUserIds = $this->record->authors()
                    ->pluck('authors.user_id')
                    ->filter()
                    ->unique()
                    ->values();

                if ($authorUserIds->isNotEmpty()) {
                    $authors = \App\Models\User::query()
                        ->whereIn('id', $authorUserIds)
                        ->get();

                    \Illuminate\Support\Facades\Notification::send(
                        $authors,
                        new \App\Notifications\PublicationScheduledToPublish($this->record)
                    );
                }
            }

            return;
        }

        $creator = $this->record->creator ?? null;
        if (! $creator) {
            return;
        }

        $author = Author::query()->firstOrCreate(
            ['user_id' => $creator->id],
            [
                'name'        => $creator->name,
                'email'       => $creator->email,
                'affiliation' => null,
            ]
        );

        $this->record->authors()->syncWithoutDetaching([
            $author->id => [
                'order'            => 1,
                'is_corresponding' => true,
            ],
        ]);

        \App\Models\Pivots\AuthorPublication::query()
            ->where('publication_id', $this->record->id)
            ->where('author_id', '!=', $author->id)
            ->update(['is_corresponding' => false]);
    }

    // ─────────────────────────────────────────────────────────────
    // Saved notification
    // ─────────────────────────────────────────────────────────────

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Publikasi berhasil diubah')
            ->body('Judul: ' . $this->shortTitle());
    }

    // ─────────────────────────────────────────────────────────────
    // Header actions
    // ─────────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submitManuscript')
                ->label('Submit Manuscript')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn() => $this->record->status === 'draft' && ! $this->isReviewer())
                ->modalHeading('Submit Manuscript')
                ->modalDescription(
                    'Pastikan manuskrip yang Anda unggah sudah benar dan final. ' .
                        'Setelah dikirim, berkas tidak dapat diubah kecuali editor meminta revisi.'
                )
                ->modalSubmitActionLabel('Kirim Manuskrip')
                ->form([
                    FileUpload::make('pdf_file_path')
                        ->label('Manuscript (PDF)')
                        ->disk('public')
                        ->directory('publications/versions')
                        ->acceptedFileTypes(['application/pdf'])
                        ->required()
                        ->maxSize(10240)
                        ->helperText('Pastikan isi berkas sudah benar sebelum mengirim. Maksimal upload 10Mb'),

                    Checkbox::make('confirm_reviewed')
                        ->label('Saya telah meninjau berkas PDF dan memastikan isinya sudah benar')
                        ->required()
                        ->accepted(),
                ])
                ->action(function (array $data) {
                    $this->record->versions()->create([
                        'pdf_file_path'  => $data['pdf_file_path'],
                        'version_number' => 1,
                        'submitted_at'   => now(),
                    ]);

                    $this->record->update([
                        'status' => 'submitted',
                    ]);

                    $reviewers = \App\Models\User::role('reviewer')->get();

                    \Illuminate\Support\Facades\Notification::send(
                        $reviewers,
                        new \App\Notifications\PublicationSubmitted($this->record)
                    );

                    Notification::make()
                        ->success()
                        ->title('Manuskrip berhasil dikirim')
                        ->body('Judul: ' . $this->shortTitle())
                        ->send();
                }),

            Action::make('previewPdf')
                ->label('Lihat Manuskrip (PDF)')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->visible(fn() => $this->record->versions()->exists())
                ->url(fn() => PublicationVersionResource::getUrl('pdf', [
                    'record' => $this->record->versions()->latest('version_number')->first(),
                ]))
                ->openUrlInNewTab(),

            // ── Mulai Review (khusus Reviewer, status submitted) ──
            Action::make('reviewManuscript')
                ->label('Review Naskah')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary')
                ->visible(fn() => $this->isReviewer() && $this->record->status === 'submitted')
                ->requiresConfirmation()
                ->modalHeading('Mulai Review Naskah?')
                ->modalDescription(new \Illuminate\Support\HtmlString(
                    '📄 <strong>' . e($this->record->title) . '</strong><br><br>' .
                        'Status publikasi akan berubah menjadi <strong>In Review</strong> dan ' .
                        'Anda akan diarahkan ke halaman review. Pastikan Anda siap untuk meninjau naskah ini.'
                ))
                ->modalSubmitActionLabel('Ya, Mulai Review')
                ->modalCancelActionLabel('Batal')
                ->action(function () {
                    // 1. Ubah status publikasi menjadi in_review
                    $this->record->update(['status' => 'in_review']);

                    // 2. Ambil versi terbaru
                    $latestVersion = $this->record->versions()
                        ->latest('version_number')
                        ->first();

                    if (!$latestVersion) {
                        Notification::make()
                            ->title('Versi manuskrip tidak ditemukan')
                            ->body('Tidak ada berkas PDF yang bisa direview.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // 3. Cek apakah reviewer sudah punya review untuk versi ini
                    $existingReview = \App\Models\Review::query()
                        ->where('publication_version_id', $latestVersion->id)
                        ->where('reviewer_id', auth()->id())
                        ->first();

                    if ($existingReview) {
                        // Sudah ada — langsung ke halaman edit review
                        $this->redirect(
                            \App\Filament\Resources\Reviews\ReviewResource::getUrl('edit', [
                                'record' => $existingReview->id,
                            ])
                        );
                        return;
                    }

                    // 4. Buat review baru dengan version & reviewer terisi otomatis
                    $review = \App\Models\Review::create([
                        'publication_version_id' => $latestVersion->id,
                        'reviewer_id'            => auth()->id(),
                    ]);

                    // 5. Notifikasi ke author bahwa naskah sedang direview
                    $authorUserIds = $this->record->authors()
                        ->pluck('authors.user_id')
                        ->filter()
                        ->unique()
                        ->values();

                    if ($authorUserIds->isNotEmpty()) {
                        \Illuminate\Support\Facades\Notification::send(
                            \App\Models\User::whereIn('id', $authorUserIds)->get(),
                            new \App\Notifications\PublicationInReview($this->record)
                        );
                    }

                    Notification::make()
                        ->success()
                        ->title('Review dimulai')
                        ->body('Status naskah diubah ke "In Review". Silakan isi formulir review.')
                        ->send();

                    // 6. Redirect ke halaman edit review yang baru dibuat
                    $this->redirect(
                        \App\Filament\Resources\Reviews\ReviewResource::getUrl('edit', [
                            'record' => $review->id,
                        ])
                    );
                }),

            Action::make('uploadNewVersion')
                ->label('Upload Revisi')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->visible(fn() => $this->record->status === 'revision_required' && ! $this->isReviewer())
                ->modalHeading('Upload Revisi Manuskrip')
                ->modalSubmitActionLabel('Kirim Revisi')
                ->form([
                    FileUpload::make('pdf_file_path')
                        ->label('Revised Manuscript (PDF)')
                        ->disk('public')
                        ->directory('publications/versions')
                        ->acceptedFileTypes(['application/pdf'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $nextVersion = ($this->record->versions()->max('version_number') ?? 0) + 1;

                    $this->record->versions()->create([
                        'pdf_file_path'  => $data['pdf_file_path'],
                        'version_number' => $nextVersion,
                        'submitted_at'   => now(),
                    ]);

                    $this->record->update([
                        'status' => 'submitted',
                    ]);

                    $publication = $this->record;

                    $reviewerIds = $publication->reviews()
                        ->pluck('reviews.reviewer_id')
                        ->filter()
                        ->unique()
                        ->values();

                    if ($reviewerIds->isNotEmpty()) {
                        $reviewers = \App\Models\User::query()
                            ->whereIn('id', $reviewerIds)
                            ->get();

                        foreach ($reviewers as $reviewer) {
                            $reviewIdToOpen = $publication->reviews()
                                ->where('reviews.reviewer_id', $reviewer->id)
                                ->orderByDesc('reviews.id')
                                ->value('reviews.id');

                            $reviewer->notify(
                                new \App\Notifications\AuthorSubmittedRevision(
                                    publication: $publication,
                                    newVersionNumber: $nextVersion,
                                    reviewIdToOpen: $reviewIdToOpen,
                                )
                            );
                        }
                    }

                    Notification::make()
                        ->success()
                        ->title('Revisi berhasil diunggah')
                        ->body('Judul: ' . $this->shortTitle() . " (v{$nextVersion})")
                        ->send();
                }),
        ];
    }
}
