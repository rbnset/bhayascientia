<?php

namespace App\Filament\Resources\DownloadLogs\Pages;

use App\Filament\Resources\DownloadLogs\DownloadLogResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDownloadLog extends EditRecord
{
    protected static string $resource = DownloadLogResource::class;

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Download log berhasil diubah')
            ->body('Perubahan download log berhasil disimpan.');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Download log berhasil dihapus')
                        ->body('Data download log berhasil dihapus.')
                ),
        ];
    }
}
