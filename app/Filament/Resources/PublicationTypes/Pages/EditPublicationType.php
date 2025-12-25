<?php

namespace App\Filament\Resources\PublicationTypes\Pages;

use App\Filament\Resources\PublicationTypes\PublicationTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPublicationType extends EditRecord
{
    protected static string $resource = PublicationTypeResource::class;

    protected function getSavedNotification(): ?Notification
    {
        $name = $this->record->name ?: "Publication Type #{$this->record->id}";

        return Notification::make()
            ->success()
            ->title('Publication type berhasil diubah')
            ->body("Nama: {$name}");
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Publication type berhasil dihapus')
                        ->body('Publication type berhasil dihapus.')
                ),
        ];
    }
}
