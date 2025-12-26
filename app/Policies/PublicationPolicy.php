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

    private function isOwner(AuthUser $user, Publication $publication): bool
    {
        // Sesuai model Anda: creator() pakai FK created_by
        return (int) ($publication->created_by ?? 0) === (int) $user->getAuthIdentifier();
    }

    public function viewAny(AuthUser $authUser): bool
    {
        // Role author boleh masuk halaman index,
        // tapi record yang tampil dibatasi oleh query di ListPublications.
        if ($this->isAuthorRole($authUser)) {
            return true;
        }

        return $authUser->can('ViewAny:Publication');
    }

    public function view(AuthUser $authUser, Publication $publication): bool
    {
        // Author hanya boleh lihat publication milik user sendiri
        if ($this->isAuthorRole($authUser)) {
            return $this->isOwner($authUser, $publication);
        }

        return $authUser->can('View:Publication');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Publication');
    }

    public function update(AuthUser $authUser, Publication $publication): bool
    {
        // Author hanya boleh update publication milik sendiri
        if ($this->isAuthorRole($authUser)) {
            return $this->isOwner($authUser, $publication)
                && $authUser->can('Update:Publication');
        }

        return $authUser->can('Update:Publication');
    }

    public function delete(AuthUser $authUser, Publication $publication): bool
    {
        // Kalau Anda ingin author juga dibatasi hanya bisa delete miliknya:
        if ($this->isAuthorRole($authUser)) {
            return $this->isOwner($authUser, $publication)
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
