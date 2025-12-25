<?php

namespace App\Filament\Resources\Authors\Pages;

use App\Filament\Resources\Authors\AuthorResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAuthor extends CreateRecord
{
    protected static string $resource = AuthorResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        $authorLabel = $this->record->name ?: ($this->record->email ?: "Author #{$this->record->id}");
        $userLabel = $this->record->user?->name
            ?? ($this->record->user_id ? "User ID: {$this->record->user_id}" : 'Tanpa user');

        return Notification::make()
            ->success()
            ->title('Author berhasil dibuat')
            ->body("Author: {$authorLabel}. Terkait: {$userLabel}.");
    }
}
