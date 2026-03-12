<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Filament\Resources\PublicationVersionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Download PDF anotasi reviewer jika ada
            Action::make('downloadAnnotatedPdf')
                ->label('Download PDF Reviewer')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn() => filled($this->record->attachments?->first()?->file_path))
                ->action(function () {
                    $attachment = $this->record->attachments()->latest()->first();
                    abort_unless($attachment && filled($attachment->file_path), 404);
                    return Storage::disk('local')->download($attachment->file_path);
                }),

            // Lihat PDF manuskrip versi yang direview
            Action::make('previewPdf')
                ->label(function () {
                    $version = $this->record->publicationVersion;
                    return $version
                        ? 'Lihat Manuskrip (v' . $version->version_number . ')'
                        : 'Lihat Manuskrip';
                })
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->visible(fn() => filled($this->record->publicationVersion?->pdf_file_path))
                ->url(fn() => PublicationVersionResource::getUrl('pdf', [
                    'record' => $this->record->publicationVersion,
                ]))
                ->openUrlInNewTab(),
        ];
    }
}
