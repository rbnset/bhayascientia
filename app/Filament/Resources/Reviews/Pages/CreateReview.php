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

        if ($review->decision === 'revision_required') {
            $review->publicationVersion?->publication?->update([
                'status' => 'revision_required',
            ]);
        }
    }
}
