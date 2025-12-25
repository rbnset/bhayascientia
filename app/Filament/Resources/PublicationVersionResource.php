<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublicationVersionResource\Pages;
use App\Models\PublicationVersion;
use Filament\Resources\Resource;

class PublicationVersionResource extends Resource
{
    protected static ?string $model = PublicationVersion::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublicationVersions::route('/'),
            'pdf' => Pages\ViewManuscriptPdf::route('/{record}/pdf'),
        ];
    }
}
