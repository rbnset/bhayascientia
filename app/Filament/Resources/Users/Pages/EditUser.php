<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Akun User berhasil diubah')
            ->body("Perubahan untuk akun user \"{$this->record->name}\" sudah disimpan.");
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Akun User berhasil dihapus')
                        ->body("Akun User \"{$this->record->name}\" sudah dihapus.")
                ),
        ];
    }
}
