<?php

namespace App\Observers;

use App\Mail\ManuscriptRevisionRequired;
use App\Models\Review;
use Illuminate\Support\Facades\Mail;

class ReviewObserver
{
    public function updated(Review $review): void
    {
        // Hanya kirim email saat decision baru saja berubah ke revision_required
        if (
            $review->wasChanged('decision') &&
            $review->decision === 'revision_required'
        ) {
            $publication = $review->publication
                ?? $review->publicationVersion?->publication;

            if (!$publication) return;

            // Kumpulkan semua author dari relasi
            $authorUserIds = $publication->authors()
                ->pluck('authors.user_id')
                ->filter()->unique();

            $recipients = $authorUserIds->isNotEmpty()
                ? \App\Models\User::whereIn('id', $authorUserIds)->get()
                : collect([$publication->creator])->filter();

            foreach ($recipients as $user) {
                if (filled($user?->email)) {
                    Mail::to($user->email, $user->name)
                        ->queue(new ManuscriptRevisionRequired($publication, $review));
                }
            }

            // Update status publikasi ke revision_required
            $publication->update(['status' => 'revision_required']);
        }

        // Auto-reject jika deadline terlewat (guard tambahan selain scheduler)
        if (
            $review->decision === 'revision_required' &&
            $review->revision_deadline &&
            $review->revision_deadline->isPast()
        ) {
            $review->updateQuietly(['decision' => 'rejected']);
        }
    }
}
