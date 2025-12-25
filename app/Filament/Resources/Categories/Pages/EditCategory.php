<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getSavedNotification(): ?Notification
    {
        $name = $this->record->name ?: "Category #{$this->record->id}";

        return Notification::make()
            ->success()
            ->title('Kategori berhasil diubah')
            ->body("Nama: {$name}");
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Kategori berhasil dihapus')
                        ->body('Kategori berhasil dihapus.')
                ),
        ];
    }
}
