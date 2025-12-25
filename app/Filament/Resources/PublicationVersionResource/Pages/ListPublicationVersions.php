<?php

namespace App\Filament\Resources\PublicationVersionResource\Pages;

use App\Filament\Resources\PublicationVersionResource;
use Filament\Resources\Pages\ListRecords;

class ListPublicationVersions extends ListRecords
{
    protected static string $resource = PublicationVersionResource::class;

    // Biar ini jadi "dummy" page, tidak perlu tombol create
    protected function getHeaderActions(): array
    {
        return [];
    }
}
