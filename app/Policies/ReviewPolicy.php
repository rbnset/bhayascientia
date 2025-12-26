<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Review;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
{
    use HandlesAuthorization;

    private function isReviewer(AuthUser $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('reviewer');
    }

    private function isAuthor(AuthUser $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('author');
    }

    /**
     * Author boleh melihat review jika review itu milik publikasi di mana
     * user login tercantum sebagai author (authors.user_id = user_id login).
     */
    private function isReviewForThisAuthor(AuthUser $user, Review $review): bool
    {
        $publication = $review->publicationVersion?->publication;

        if (! $publication) {
            return false;
        }

        return $publication->authors()
            ->where('authors.user_id', $user->getAuthIdentifier())
            ->exists();
    }

    public function viewAny(AuthUser $authUser): bool
    {
        // Reviewer boleh buka halaman list, nanti difilter only mine
        if ($this->isReviewer($authUser)) {
            return true;
        }

        // Author boleh buka halaman list, nanti difilter only assigned-to-me
        if ($this->isAuthor($authUser)) {
            return true;
        }

        return $authUser->can('ViewAny:Review');
    }

    public function view(AuthUser $authUser, Review $review): bool
    {
        if ($this->isReviewer($authUser)) {
            return (int) $review->reviewer_id === (int) $authUser->getAuthIdentifier();
        }

        if ($this->isAuthor($authUser)) {
            return $this->isReviewForThisAuthor($authUser, $review);
        }

        return $authUser->can('View:Review');
    }

    public function create(AuthUser $authUser): bool
    {
        // Umumnya author TIDAK create review, hanya reviewer/editor.
        if ($this->isAuthor($authUser)) {
            return false;
        }

        return $authUser->can('Create:Review');
    }

    public function update(AuthUser $authUser, Review $review): bool
    {
        // Reviewer hanya boleh edit review miliknya
        if ($this->isReviewer($authUser)) {
            return ((int) $review->reviewer_id === (int) $authUser->getAuthIdentifier())
                && $authUser->can('Update:Review');
        }

        // Author tidak boleh edit review (hanya baca)
        if ($this->isAuthor($authUser)) {
            return false;
        }

        return $authUser->can('Update:Review');
    }

    public function delete(AuthUser $authUser, Review $review): bool
    {
        // Reviewer hanya boleh delete review miliknya (opsional)
        if ($this->isReviewer($authUser)) {
            return ((int) $review->reviewer_id === (int) $authUser->getAuthIdentifier())
                && $authUser->can('Delete:Review');
        }

        // Author tidak boleh delete review
        if ($this->isAuthor($authUser)) {
            return false;
        }

        return $authUser->can('Delete:Review');
    }

    public function restore(AuthUser $authUser, Review $review): bool
    {
        if ($this->isReviewer($authUser) || $this->isAuthor($authUser)) {
            return false;
        }

        return $authUser->can('Restore:Review');
    }

    public function forceDelete(AuthUser $authUser, Review $review): bool
    {
        if ($this->isReviewer($authUser) || $this->isAuthor($authUser)) {
            return false;
        }

        return $authUser->can('ForceDelete:Review');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        if ($this->isReviewer($authUser) || $this->isAuthor($authUser)) {
            return false;
        }

        return $authUser->can('ForceDeleteAny:Review');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        if ($this->isReviewer($authUser) || $this->isAuthor($authUser)) {
            return false;
        }

        return $authUser->can('RestoreAny:Review');
    }

    public function replicate(AuthUser $authUser, Review $review): bool
    {
        if ($this->isReviewer($authUser) || $this->isAuthor($authUser)) {
            return false;
        }

        return $authUser->can('Replicate:Review');
    }

    public function reorder(AuthUser $authUser): bool
    {
        if ($this->isReviewer($authUser) || $this->isAuthor($authUser)) {
            return false;
        }

        return $authUser->can('Reorder:Review');
    }
}
