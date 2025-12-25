<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\PublicationVersionResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class EditPublication extends EditRecord
{
    protected static string $resource = PublicationResource::class;

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

            /*
            |--------------------------------------------------------------------------
            | SUBMIT FIRST MANUSCRIPT
            |--------------------------------------------------------------------------
            */
            Action::make('submitManuscript')
                ->label('Submit Manuscript')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn() => $this->record->status === 'draft')
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

            /*
            |--------------------------------------------------------------------------
            | PREVIEW PDF (SETELAH SUBMIT)
            |--------------------------------------------------------------------------
            */
            Action::make('previewPdf')
                ->label('Lihat Manuskrip (PDF)')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->visible(fn() => $this->record->versions()->exists())
                ->url(fn() => PublicationVersionResource::getUrl('pdf', [
                    'record' => $this->record->versions()->latest('version_number')->first(),
                ]))
                ->openUrlInNewTab(),


            /*
            |--------------------------------------------------------------------------
            | UPLOAD REVISION (SETELAH REVIEW)
            |--------------------------------------------------------------------------
            */
            Action::make('uploadNewVersion')
                ->label('Upload Revisi')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->visible(fn() => $this->record->status === 'revision_required')
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
