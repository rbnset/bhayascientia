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

    public function viewAny(AuthUser $authUser): bool
    {
        // Reviewer boleh buka halaman list, tapi nanti datanya difilter cuma miliknya.
        if ($this->isReviewer($authUser)) {
            return true;
        }

        return $authUser->can('ViewAny:Review');
    }

    public function view(AuthUser $authUser, Review $review): bool
    {
        // Reviewer hanya boleh lihat review miliknya
        if ($this->isReviewer($authUser)) {
            return (int) $review->reviewer_id === (int) $authUser->getAuthIdentifier();
        }

        return $authUser->can('View:Review');
    }

    public function create(AuthUser $authUser): bool
    {
        // Reviewer boleh create jika punya permission, atau Anda bisa return true khusus reviewer
        if ($this->isReviewer($authUser)) {
            return $authUser->can('Create:Review');
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

        return $authUser->can('Update:Review');
    }

    public function delete(AuthUser $authUser, Review $review): bool
    {
        // Reviewer hanya boleh delete review miliknya (kalau Anda ingin reviewer tidak boleh delete sama sekali, return false)
        if ($this->isReviewer($authUser)) {
            return ((int) $review->reviewer_id === (int) $authUser->getAuthIdentifier())
                && $authUser->can('Delete:Review');
        }

        return $authUser->can('Delete:Review');
    }

    public function restore(AuthUser $authUser, Review $review): bool
    {
        if ($this->isReviewer($authUser)) {
            return false;
        }

        return $authUser->can('Restore:Review');
    }

    public function forceDelete(AuthUser $authUser, Review $review): bool
    {
        if ($this->isReviewer($authUser)) {
            return false;
        }

        return $authUser->can('ForceDelete:Review');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        if ($this->isReviewer($authUser)) {
            return false;
        }

        return $authUser->can('ForceDeleteAny:Review');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        if ($this->isReviewer($authUser)) {
            return false;
        }

        return $authUser->can('RestoreAny:Review');
    }

    public function replicate(AuthUser $authUser, Review $review): bool
    {
        if ($this->isReviewer($authUser)) {
            return false;
        }

        return $authUser->can('Replicate:Review');
    }

    public function reorder(AuthUser $authUser): bool
    {
        if ($this->isReviewer($authUser)) {
            return false;
        }

        return $authUser->can('Reorder:Review');
    }
}
