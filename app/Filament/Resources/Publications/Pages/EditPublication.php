<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\Publications\Widgets\PublicationStatusBanner;
use App\Filament\Resources\PublicationVersionResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
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

    /**
     * Reviewer hanya boleh update field status (dan published_at jika diperlukan).
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! $this->isReviewer()) {
            return $data;
        }

        $filtered = [
            'status' => $data['status'] ?? $this->record->status,
        ];

        // Kalau status = published, izinkan published_at ikut tersimpan.
        if (($filtered['status'] ?? null) === 'published') {
            $filtered['published_at'] = $data['published_at'] ?? $this->record->published_at;
        }

        return $filtered;
    }

    protected function afterSave(): void
    {
        // Reviewer tidak perlu menjalankan logika relasi authors setelah save
        if ($this->isReviewer()) {
            return;
        }

        $creator = $this->record->creator;

        if (! $creator) {
            return;
        }

        $author = \App\Models\Author::query()->firstOrCreate(
            ['user_id' => $creator->id],
            [
                'name' => $creator->name,
                'email' => $creator->email,
                'affiliation' => null,
            ]
        );

        $this->record->authors()->syncWithoutDetaching([
            $author->id => [
                'order' => 1,
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
                        'pdf_file_path' => $data['pdf_file_path'],
                        'version_number' => 1,
                        'submitted_at' => now(),
                    ]);

                    $this->record->update([
                        'status' => 'submitted',
                    ]);

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
                        'pdf_file_path' => $data['pdf_file_path'],
                        'version_number' => $nextVersion,
                        'submitted_at' => now(),
                    ]);

                    $this->record->update([
                        'status' => 'submitted',
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Revisi berhasil diunggah')
                        ->body('Judul: ' . $this->shortTitle() . " (v{$nextVersion})")
                        ->send();
                }),
        ];
    }
}
