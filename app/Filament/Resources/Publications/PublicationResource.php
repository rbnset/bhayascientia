<?php

namespace App\Filament\Resources\Publications;

use App\Filament\Resources\Publications\Pages\CreatePublication;
use App\Filament\Resources\Publications\Pages\EditPublication;
use App\Filament\Resources\Publications\Pages\ListPublications;
use App\Filament\Resources\Publications\RelationManagers\PublicationVersionsRelationManager;
use App\Filament\Resources\Publications\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\Publications\Schemas\PublicationForm;
use App\Filament\Resources\Publications\Tables\PublicationsTable;
use App\Models\Publication;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PublicationResource extends Resource
{
    protected static ?string $model = Publication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Daftar Publikasi';

    protected static ?string $modelLabel = 'Publikasi';

    protected static ?string $pluralModelLabel = 'Publikasi';

    protected static ?string $recordTitleAttribute = 'title';

    // ─────────────────────────────────────────────────────────────
    // Status yang mengunci aksi edit & delete untuk role author
    // ─────────────────────────────────────────────────────────────

    private const AUTHOR_LOCKED_STATUSES = [
        'in_review',
        'accepted',
        'rejected',
        'published',
    ];

    // ─────────────────────────────────────────────────────────────
    // Authorization — blokir akses URL langsung untuk author
    // ─────────────────────────────────────────────────────────────

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();

        if (
            $user?->hasRole('author') &&
            in_array($record->status, self::AUTHOR_LOCKED_STATUSES, true)
        ) {
            Notification::make()
                ->title('Tidak dapat mengedit publikasi')
                ->body(
                    'Publikasi berstatus "' . str($record->status)->headline() . '" ' .
                        'tidak dapat diubah. Hubungi editor jika ada koreksi yang diperlukan.'
                )
                ->warning()
                ->persistent()
                ->send();

            return false;
        }

        return true;
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();

        if (
            $user?->hasRole('author') &&
            in_array($record->status, self::AUTHOR_LOCKED_STATUSES, true)
        ) {
            Notification::make()
                ->title('Tidak dapat menghapus publikasi')
                ->body(
                    'Publikasi berstatus "' . str($record->status)->headline() . '" ' .
                        'tidak dapat dihapus. Hubungi editor untuk informasi lebih lanjut.'
                )
                ->danger()
                ->persistent()
                ->send();

            return false;
        }

        return true;
    }

    // ─────────────────────────────────────────────────────────────
    // Form & Table
    // ─────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return PublicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicationsTable::configure($table);
    }

    // ─────────────────────────────────────────────────────────────
    // Relations
    // ─────────────────────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [
            PublicationVersionsRelationManager::class,
            ReviewsRelationManager::class,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Pages
    // ─────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => ListPublications::route('/'),
            'create' => CreatePublication::route('/create'),
            'edit'   => EditPublication::route('/{record}/edit'),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Eloquent Query
    // ─────────────────────────────────────────────────────────────

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with([
                'publicationType',
                'categories',
                'authors',
                'method',
            ]);
    }
}
