<?php

namespace App\Filament\Resources\PublicationTypes\Pages;

use App\Filament\Resources\PublicationTypes\PublicationTypeResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePublicationType extends CreateRecord
{
    protected static string $resource = PublicationTypeResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        $name = $this->record->name ?: "Publication Type #{$this->record->id}";

        return Notification::make()
            ->success()
            ->title('Publication type berhasil dibuat')
            ->body("Nama: {$name}");
    }
}
