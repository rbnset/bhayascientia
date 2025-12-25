<?php

namespace App\Filament\Resources\Keywords\Pages;

use App\Filament\Resources\Keywords\KeywordResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKeyword extends EditRecord
{
    protected static string $resource = KeywordResource::class;

    protected function getSavedNotification(): ?Notification
    {
        $name = $this->record->name ?: "Keyword #{$this->record->id}";

        return Notification::make()
            ->success()
            ->title('Keyword berhasil diubah')
            ->body("Nama: {$name}");
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Keyword berhasil dihapus')
                        ->body('Keyword berhasil dihapus.')
                ),
        ];
    }
}
