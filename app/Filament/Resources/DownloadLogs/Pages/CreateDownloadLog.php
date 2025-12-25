<?php

namespace App\Filament\Resources\DownloadLogs\Pages;

use App\Filament\Resources\DownloadLogs\DownloadLogResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDownloadLog extends CreateRecord
{
    protected static string $resource = DownloadLogResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Download log berhasil dibuat')
            ->body('Data download log berhasil ditambahkan.');
    }
}
