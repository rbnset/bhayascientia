<?php

namespace App\Support;

use App\Models\Review;
use App\Models\User;
use App\Notifications\ReviewDecisionForAuthor;
use Illuminate\Support\Facades\Notification;

class ReviewAuthorNotifier
{
    public static function notifyAuthors(Review $review): void
    {
        $publication = $review->publicationVersion?->publication;

        if (! $publication) {
            return;
        }

        // authors adalah table authors, ada kolom user_id
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

        Notification::send($recipients, new ReviewDecisionForAuthor($review));
    }
}
