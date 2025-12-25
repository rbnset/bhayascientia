<?php

namespace App\Filament\Resources\Methods\Pages;

use App\Filament\Resources\Methods\MethodResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMethod extends EditRecord
{
    protected static string $resource = MethodResource::class;

    protected function getSavedNotification(): ?Notification
    {
        $name = $this->record->name ?: "Method #{$this->record->id}";

        return Notification::make()
            ->success()
            ->title('Method berhasil diubah')
            ->body("Nama: {$name}");
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Method berhasil dihapus')
                        ->body('Method berhasil dihapus.')
                ),
        ];
    }
}
