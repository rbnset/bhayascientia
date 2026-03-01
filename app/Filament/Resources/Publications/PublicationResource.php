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
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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

    public static function form(Schema $schema): Schema
    {
        return PublicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PublicationVersionsRelationManager::class,
            ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPublications::route('/'),
            'create' => CreatePublication::route('/create'),
            'edit'   => EditPublication::route('/{record}/edit'),
        ];
    }

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
                SoftDeletingScope::class, // ← tambahan: agar TrashedFilter berfungsi
            ])
            ->with([
                'publicationType', // ← description di TextColumn title
                'categories',      // ← TextColumn categories.name
                'authors',         // ← TextColumn authors.name
                'method',          // ← tambahan: TextColumn method.name
            ]);
    }
}
