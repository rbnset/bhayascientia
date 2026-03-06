<?php
// app/Filament/Resources/DocumentVerifications/DocumentVerificationResource.php

namespace App\Filament\Resources\DocumentVerifications;

use App\Filament\Resources\DocumentVerifications\Pages\CreateDocumentVerification;
use App\Filament\Resources\DocumentVerifications\Pages\EditDocumentVerification;
use App\Filament\Resources\DocumentVerifications\Pages\ListDocumentVerifications;
use App\Filament\Resources\DocumentVerifications\Pages\ViewDocumentVerification;
use App\Filament\Resources\DocumentVerifications\Schemas\DocumentVerificationForm;
use App\Filament\Resources\DocumentVerifications\Schemas\DocumentVerificationInfolist;
use App\Filament\Resources\DocumentVerifications\Tables\DocumentVerificationsTable;
use App\Filament\Resources\DocumentVerifications\Widgets\VerificationStatsWidget;
use App\Models\DocumentVerification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DocumentVerificationResource extends Resource
{
    protected static ?string $model = DocumentVerification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Verifikasi Dokumen';

    protected static ?string $modelLabel = 'Verifikasi';

    protected static ?string $pluralModelLabel = 'Log Verifikasi';

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = DocumentVerification::whereDate('last_scanned_at', today())->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return DocumentVerificationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentVerificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentVerificationsTable::configure($table);
    }

    public static function getWidgets(): array
    {
        return [
            VerificationStatsWidget::class,
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDocumentVerifications::route('/'),
            'create' => CreateDocumentVerification::route('/create'),
            'view'   => ViewDocumentVerification::route('/{record}'),
            'edit'   => EditDocumentVerification::route('/{record}/edit'),
        ];
    }
}
