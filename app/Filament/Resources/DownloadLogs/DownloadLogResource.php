<?php

namespace App\Filament\Resources\DownloadLogs;

use App\Filament\Resources\DownloadLogs\Pages\CreateDownloadLog;
use App\Filament\Resources\DownloadLogs\Pages\EditDownloadLog;
use App\Filament\Resources\DownloadLogs\Pages\ListDownloadLogs;
use App\Filament\Resources\DownloadLogs\Schemas\DownloadLogForm;
use App\Filament\Resources\DownloadLogs\Tables\DownloadLogsTable;
use App\Models\DownloadLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;


class DownloadLogResource extends Resource
{
    protected static ?string $model = DownloadLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'User Management';

    public static function form(Schema $schema): Schema
    {
        return DownloadLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DownloadLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDownloadLogs::route('/'),
            'create' => CreateDownloadLog::route('/create'),
            'edit' => EditDownloadLog::route('/{record}/edit'),
        ];
    }
}
