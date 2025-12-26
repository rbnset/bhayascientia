<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\User;
use App\Notifications\ReviewDecisionForAuthor;
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

        // 1) Sync status publication dari decision
        $newStatus = match ($review->decision) {
            'revision_required' => 'revision_required',
            'accepted' => 'accepted',
            'rejected' => 'rejected',
            default => null,
        };

        if ($newStatus) {
            $publication->update([
                'status' => $newStatus,
            ]);
        }

        // 2) Notify author user terkait publication
        $authorUserIds = $publication->authors()
            ->pluck('authors.user_id')
            ->filter()
            ->unique()
            ->values();

        if ($authorUserIds->isEmpty()) {
            return;
        }

        $recipients = User::query()
            ->whereIn('id', $authorUserIds)
            ->get();

        \Illuminate\Support\Facades\Notification::send(
            $recipients,
            new ReviewDecisionForAuthor($review)
        );
    }
}
