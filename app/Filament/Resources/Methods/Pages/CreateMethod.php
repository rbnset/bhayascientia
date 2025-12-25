<?php

namespace App\Filament\Resources\Methods\Pages;

use App\Filament\Resources\Methods\MethodResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMethod extends CreateRecord
{
    protected static string $resource = MethodResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        $name = $this->record->name ?: "Method #{$this->record->id}";

        return Notification::make()
            ->success()
            ->title('Method berhasil dibuat')
            ->body("Nama: {$name}");
    }
}
