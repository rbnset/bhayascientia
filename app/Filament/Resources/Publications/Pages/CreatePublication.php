<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePublication extends CreateRecord
{
    protected static string $resource = PublicationResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        $shortTitle = Str::of((string) $this->record->title)
            ->squish()
            ->words(8, '…')
            ->toString();

        return Notification::make()
            ->success()
            ->title('Publikasi berhasil dibuat')
            ->body("Judul: {$shortTitle}");
    }
}
