<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Keyword;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeywordPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Keyword');
    }

    public function view(AuthUser $authUser, Keyword $keyword): bool
    {
        return $authUser->can('View:Keyword');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Keyword');
    }

    public function update(AuthUser $authUser, Keyword $keyword): bool
    {
        return $authUser->can('Update:Keyword');
    }

    public function delete(AuthUser $authUser, Keyword $keyword): bool
    {
        return $authUser->can('Delete:Keyword');
    }

    public function restore(AuthUser $authUser, Keyword $keyword): bool
    {
        return $authUser->can('Restore:Keyword');
    }

    public function forceDelete(AuthUser $authUser, Keyword $keyword): bool
    {
        return $authUser->can('ForceDelete:Keyword');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Keyword');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Keyword');
    }

    public function replicate(AuthUser $authUser, Keyword $keyword): bool
    {
        return $authUser->can('Replicate:Keyword');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Keyword');
    }

}