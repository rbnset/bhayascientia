<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditReview extends EditRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Review berhasil diubah')
            ->body('Perubahan review berhasil disimpan.');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn() => ! (auth()->user()?->hasRole('author'))) // author tidak boleh delete
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Review berhasil dihapus')
                        ->body('Data review berhasil dihapus.')
                ),
        ];
    }

    protected function afterSave(): void
    {
        // Jika author somehow bisa masuk, jangan jalankan logic ini
        if (auth()->user()?->hasRole('author')) {
            return;
        }

        $review = $this->record;

        $publication = $review->publicationVersion?->publication;

        if (! $publication) {
            return;
        }

        $newStatus = match ($review->decision) {
            'revision_required' => 'revision_required',
            'accepted' => 'accepted',
            'rejected' => 'rejected',
            default => null,
        };

        if (! $newStatus) {
            return;
        }

        $publication->update([
            'status' => $newStatus,
        ]);
    }
}
