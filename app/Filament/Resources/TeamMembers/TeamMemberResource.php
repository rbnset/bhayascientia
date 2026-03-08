<?php

namespace App\Filament\Resources\TeamMembers;

use App\Filament\Resources\TeamMembers\Pages\CreateTeamMember;
use App\Filament\Resources\TeamMembers\Pages\EditTeamMember;
use App\Filament\Resources\TeamMembers\Pages\ListTeamMembers;
use App\Filament\Resources\TeamMembers\Pages\ViewTeamMember;
use App\Filament\Resources\TeamMembers\Schemas\TeamMemberForm;
use App\Filament\Resources\TeamMembers\Tables\TeamMembersTable;
use App\Models\TeamMember;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    // ✅ FIX: Hapus $navigationGroup statis, override method getNavigationGroup() saja
    protected static ?string $navigationLabel = 'Struktur Organisasi';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Anggota Tim';

    protected static ?string $pluralModelLabel = 'Anggota Tim';

    // ✅ Gunakan method override, bukan property — ini cara yang benar di Filament 4
    public static function getNavigationGroup(): string|null
    {
        return 'Konten';
    }

    public static function form(Schema $schema): Schema
    {
        return TeamMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeamMembersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTeamMembers::route('/'),
            'create' => CreateTeamMember::route('/create'),
            'edit'   => EditTeamMember::route('/{record}/edit'),
            'view'   => ViewTeamMember::route('/{record}'),
        ];
    }
}
