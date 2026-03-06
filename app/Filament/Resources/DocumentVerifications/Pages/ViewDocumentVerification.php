<?php
// app/Filament/Resources/DocumentVerifications/Pages/ViewDocumentVerification.php

namespace App\Filament\Resources\DocumentVerifications\Pages;

use App\Filament\Resources\DocumentVerifications\DocumentVerificationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewDocumentVerification extends ViewRecord
{
    protected static string $resource = DocumentVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_verify')
                ->label('Buka Halaman Verifikasi Publik')
                ->icon(Heroicon::ArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn() => route('document.verify', $this->record->code))
                ->openUrlInNewTab(),
        ];
    }
}
