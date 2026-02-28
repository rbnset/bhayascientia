<?php

namespace App\Filament\Resources\Authors\Pages;

use App\Filament\Resources\Authors\AuthorResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAuthor extends EditRecord
{
    protected static string $resource = AuthorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        $authorLabel = $this->record->name ?: ($this->record->email ?: "Author #{$this->record->id}");
        $userLabel   = $this->record->user?->name
            ?? ($this->record->user_id ? "User ID: {$this->record->user_id}" : 'Tanpa user');

        return Notification::make()
            ->success()
            ->title('Author berhasil diubah')
            ->body("Author: {$authorLabel}. Terkait: {$userLabel}.");
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Lihat')
                ->icon('heroicon-o-eye'),

            DeleteAction::make()
                ->successNotification(
                    fn() => Notification::make()
                        ->success()
                        ->title('Author berhasil dihapus')
                        ->body('Data author berhasil dihapus.')
                ),
        ];
    }
}
