<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DocumentVerification;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentVerificationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DocumentVerification');
    }

    public function view(AuthUser $authUser, DocumentVerification $documentVerification): bool
    {
        return $authUser->can('View:DocumentVerification');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DocumentVerification');
    }

    public function update(AuthUser $authUser, DocumentVerification $documentVerification): bool
    {
        return $authUser->can('Update:DocumentVerification');
    }

    public function delete(AuthUser $authUser, DocumentVerification $documentVerification): bool
    {
        return $authUser->can('Delete:DocumentVerification');
    }

    public function restore(AuthUser $authUser, DocumentVerification $documentVerification): bool
    {
        return $authUser->can('Restore:DocumentVerification');
    }

    public function forceDelete(AuthUser $authUser, DocumentVerification $documentVerification): bool
    {
        return $authUser->can('ForceDelete:DocumentVerification');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DocumentVerification');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DocumentVerification');
    }

    public function replicate(AuthUser $authUser, DocumentVerification $documentVerification): bool
    {
        return $authUser->can('Replicate:DocumentVerification');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DocumentVerification');
    }

}