<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Publication;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicationPolicy
{
    use HandlesAuthorization;

    private function isAuthorRole(AuthUser $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('author');
    }

    private function isReviewerRole(AuthUser $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('reviewer');
    }

    /**
     * Ownership versi opsi A:
     * user login dianggap "pemilik" publication kalau dia punya Author record (authors.user_id = user_id login)
     * yang terhubung ke publication lewat pivot author_publication.
     */
    private function isOwnerViaAuthors(AuthUser $user, Publication $publication): bool
    {
        return $publication->authors()
            ->where('authors.user_id', $user->getAuthIdentifier())
            ->exists();
    }

    public function viewAny(AuthUser $authUser): bool
    {
        // Author boleh masuk halaman index, data difilter lewat ListPublications.
        if ($this->isAuthorRole($authUser)) {
            return true;
        }

        // Reviewer biasanya boleh lihat list (tergantung permission Anda).
        if ($this->isReviewerRole($authUser)) {
            return $authUser->can('ViewAny:Publication');
        }

        return $authUser->can('ViewAny:Publication');
    }

    public function view(AuthUser $authUser, Publication $publication): bool
    {
        if ($this->isAuthorRole($authUser)) {
            return $this->isOwnerViaAuthors($authUser, $publication);
        }

        return $authUser->can('View:Publication');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Publication');
    }

    public function update(AuthUser $authUser, Publication $publication): bool
    {
        // Author boleh edit publication miliknya sendiri (versi opsi A)
        if ($this->isAuthorRole($authUser)) {
            return $this->isOwnerViaAuthors($authUser, $publication)
                && $authUser->can('Update:Publication');
        }

        // Reviewer logic update Anda sudah dibatasi di EditPublication (mutateFormDataBeforeSave),
        // tapi tetap perlu permission Update:Publication agar bisa akses halaman edit.
        return $authUser->can('Update:Publication');
    }

    public function delete(AuthUser $authUser, Publication $publication): bool
    {
        // Jika ingin author boleh delete publication miliknya sendiri:
        if ($this->isAuthorRole($authUser)) {
            return $this->isOwnerViaAuthors($authUser, $publication)
                && $authUser->can('Delete:Publication');
        }

        return $authUser->can('Delete:Publication');
    }

    public function restore(AuthUser $authUser, Publication $publication): bool
    {
        if ($this->isAuthorRole($authUser)) {
            return false;
        }

        return $authUser->can('Restore:Publication');
    }

    public function forceDelete(AuthUser $authUser, Publication $publication): bool
    {
        if ($this->isAuthorRole($authUser)) {
            return false;
        }

        return $authUser->can('ForceDelete:Publication');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        if ($this->isAuthorRole($authUser)) {
            return false;
        }

        return $authUser->can('ForceDeleteAny:Publication');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        if ($this->isAuthorRole($authUser)) {
            return false;
        }

        return $authUser->can('RestoreAny:Publication');
    }

    public function replicate(AuthUser $authUser, Publication $publication): bool
    {
        if ($this->isAuthorRole($authUser)) {
            return false;
        }

        return $authUser->can('Replicate:Publication');
    }

    public function reorder(AuthUser $authUser): bool
    {
        if ($this->isAuthorRole($authUser)) {
            return false;
        }

        return $authUser->can('Reorder:Publication');
    }
}
