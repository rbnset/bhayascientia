<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateReview extends CreateRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Review berhasil dibuat')
            ->body('Data review berhasil ditambahkan.');
    }

    protected function afterCreate(): void
    {
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
