<?php
// app/Filament/Resources/DocumentVerifications/Pages/ListDocumentVerifications.php

namespace App\Filament\Resources\DocumentVerifications\Pages;

use App\Filament\Resources\DocumentVerifications\DocumentVerificationResource;
use App\Filament\Resources\DocumentVerifications\Widgets\VerificationStatsWidget;
use Filament\Resources\Pages\ListRecords;

class ListDocumentVerifications extends ListRecords
{
    protected static string $resource = DocumentVerificationResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            VerificationStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return []; // No create button
    }
}
