<?php

namespace App\Filament\Resources\TeamMembers\Pages;

use App\Filament\Resources\TeamMembers\TeamMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewTeamMember extends ViewRecord
{
    protected static string $resource = TeamMemberResource::class;

    // ✅ Arahkan ke custom blade view
    protected string $view = 'filament.resources.team-members.pages.view-team-member';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon(Heroicon::OutlinedPencilSquare)
                ->label('Edit Anggota'),

            DeleteAction::make()
                ->icon(Heroicon::OutlinedTrash)
                ->label('Hapus')
                ->requiresConfirmation()
                ->modalHeading('Hapus Anggota Tim')
                ->modalDescription('Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus'),
        ];
    }
}
