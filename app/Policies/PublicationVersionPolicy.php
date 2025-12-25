<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PublicationVersion;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicationVersionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PublicationVersion');
    }

    public function view(AuthUser $authUser, PublicationVersion $publicationVersion): bool
    {
        return $authUser->can('View:PublicationVersion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PublicationVersion');
    }

    public function update(AuthUser $authUser, PublicationVersion $publicationVersion): bool
    {
        return $authUser->can('Update:PublicationVersion');
    }

    public function delete(AuthUser $authUser, PublicationVersion $publicationVersion): bool
    {
        return $authUser->can('Delete:PublicationVersion');
    }

    public function restore(AuthUser $authUser, PublicationVersion $publicationVersion): bool
    {
        return $authUser->can('Restore:PublicationVersion');
    }

    public function forceDelete(AuthUser $authUser, PublicationVersion $publicationVersion): bool
    {
        return $authUser->can('ForceDelete:PublicationVersion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PublicationVersion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PublicationVersion');
    }

    public function replicate(AuthUser $authUser, PublicationVersion $publicationVersion): bool
    {
        return $authUser->can('Replicate:PublicationVersion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PublicationVersion');
    }

}