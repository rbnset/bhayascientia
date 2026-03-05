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

    protected function isReviewer(): bool
    {
        return (bool) auth()->user()?->hasRole('reviewer');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PublicationStatusBanner::class,
        ];
    }

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

        // ✅ Non-reviewer: cek apakah judul berubah dan sudah digunakan publikasi lain
        $title = trim($data['title'] ?? '');
        if (filled($title) && $title !== $this->record->title) {
            $exists = Publication::where('title', $title)
                ->where('id', '!=', $this->record->id)
                ->exists();

            if ($exists) {
                Notification::make()
                    ->title('Judul sudah digunakan')
                    ->body('Judul karya ilmiah ini sudah pernah digunakan oleh publikasi lain. Silakan gunakan judul yang berbeda atau tambahkan penjelasan spesifik (metode, lokasi, atau konteks).')
                    ->danger()
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (UniqueConstraintViolationException $e) {
            Notification::make()
                ->title('Gagal memperbarui publikasi')
                ->body('Perubahan yang Anda lakukan bertabrakan dengan data yang sudah ada. Silakan cek kembali judul atau data yang diubah.')
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }
    }

    protected function afterSave(): void
    {
        if ($this->isReviewer()) {
            if (
                $this->record->status === 'published'
                && ($this->record->wasChanged('status') || $this->record->wasChanged('published_at'))
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

    protected function shortTitle(): string
    {
        return Str::of((string) $this->record->title)
            ->squish()
            ->words(8, '…')
            ->toString();
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Publikasi berhasil diubah')
            ->body('Judul: ' . $this->shortTitle());
    }

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
                    'Pastikan manuskrip yang Anda unggah sudah benar dan final.
                    Setelah dikirim, berkas tidak dapat diubah kecuali editor meminta revisi.'
                )
                ->modalSubmitActionLabel('Kirim Manuskrip')
                ->form([
                    FileUpload::make('pdf_file_path')
                        ->label('Manuscript (PDF)')
                        ->disk('public')
                        ->directory('publications/versions')
                        ->acceptedFileTypes(['application/pdf'])
                        ->required()
                        ->helperText('Pastikan nama dan isi berkas sudah benar sebelum mengirim.'),

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
