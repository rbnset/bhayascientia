<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\PublicationVersionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewPublication extends ViewRecord
{
    protected static string $resource = PublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol lihat PDF jika ada versi yang diupload
            Action::make('previewPdf')
                ->label(function () {
                    $version = $this->record->versions()->latest('version_number')->first();
                    return $version
                        ? 'Lihat PDF (v' . $version->version_number . ')'
                        : 'Lihat PDF';
                })
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->visible(fn() => $this->record->versions()->exists())
                ->url(fn() => PublicationVersionResource::getUrl('pdf', [
                    'record' => $this->record->versions()->latest('version_number')->first(),
                ]))
                ->openUrlInNewTab(),
        ];
    }
}
