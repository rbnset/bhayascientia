<?php

namespace App\Observers;

use App\Mail\ManuscriptAccepted;
use App\Mail\ManuscriptRejected;
use App\Mail\ManuscriptRevisionRequired;
use App\Models\Review;
use Illuminate\Support\Facades\Mail;

class ReviewObserver
{
    public function updated(Review $review): void
    {
        if (!$review->wasChanged('decision')) return;

        $publication = $review->publication
            ?? $review->publicationVersion?->publication;

        if (!$publication) return;

        // ── Kumpulkan penerima email ─────────────────────────────
        $authorUserIds = $publication->authors()
            ->pluck('authors.user_id')
            ->filter()->unique();

        $recipients = $authorUserIds->isNotEmpty()
            ? \App\Models\User::whereIn('id', $authorUserIds)->get()
            : collect([$publication->creator])->filter();

        // ── Handle tiap decision ─────────────────────────────────
        match ($review->decision) {

            'revision_required' => $this->handleRevisionRequired(
                $review,
                $publication,
                $recipients
            ),

            'accepted' => $this->handleAccepted(
                $review,
                $publication,
                $recipients
            ),

            'rejected' => $this->handleRejected(
                $review,
                $publication,
                $recipients
            ),

            default => null,
        };
    }

    // ─────────────────────────────────────────────────────────────

    private function handleRevisionRequired(
        Review $review,
        $publication,
        $recipients
    ): void {
        $publication->update(['status' => 'revision_required']);

        foreach ($recipients as $user) {
            if (filled($user?->email)) {
                Mail::to($user->email, $user->name)
                    ->queue(new ManuscriptRevisionRequired($publication, $review));
            }
        }
    }

    private function handleAccepted(
        Review $review,
        $publication,
        $recipients
    ): void {
        $publication->update(['status' => 'accepted']);

        // ── In-app notification — sesuai konstruktor yang ada ────────
        if ($recipients->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send(
                $recipients,
                new \App\Notifications\PublicationAccepted($review) // ✅ kirim $review
            );
        }

        // ── Email ─────────────────────────────────────────────────────
        foreach ($recipients as $user) {
            if (filled($user?->email)) {
                Mail::to($user->email, $user->name)
                    ->queue(new ManuscriptAccepted($publication, $review));
            }
        }
    }

    private function handleRejected(
        Review $review,
        $publication,
        $recipients
    ): void {
        $publication->update(['status' => 'rejected']);

        // ── In-app notification — sesuai konstruktor yang ada ────────
        if ($recipients->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send(
                $recipients,
                new \App\Notifications\PublicationRejected($review) // ✅ kirim $review
            );
        }

        // ── Email ─────────────────────────────────────────────────────
        foreach ($recipients as $user) {
            if (filled($user?->email)) {
                Mail::to($user->email, $user->name)
                    ->queue(new ManuscriptRejected($publication, $review));
            }
        }
    }
}
