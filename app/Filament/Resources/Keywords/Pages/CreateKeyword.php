<?php

namespace App\Filament\Resources\Keywords\Pages;

use App\Filament\Resources\Keywords\KeywordResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateKeyword extends CreateRecord
{
    protected static string $resource = KeywordResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        $name = $this->record->name ?: "Keyword #{$this->record->id}";

        return Notification::make()
            ->success()
            ->title('Keyword berhasil dibuat')
            ->body("Nama: {$name}");
    }
}
