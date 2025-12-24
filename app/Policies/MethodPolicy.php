<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Method;
use Illuminate\Auth\Access\HandlesAuthorization;

class MethodPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Method');
    }

    public function view(AuthUser $authUser, Method $method): bool
    {
        return $authUser->can('View:Method');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Method');
    }

    public function update(AuthUser $authUser, Method $method): bool
    {
        return $authUser->can('Update:Method');
    }

    public function delete(AuthUser $authUser, Method $method): bool
    {
        return $authUser->can('Delete:Method');
    }

    public function restore(AuthUser $authUser, Method $method): bool
    {
        return $authUser->can('Restore:Method');
    }

    public function forceDelete(AuthUser $authUser, Method $method): bool
    {
        return $authUser->can('ForceDelete:Method');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Method');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Method');
    }

    public function replicate(AuthUser $authUser, Method $method): bool
    {
        return $authUser->can('Replicate:Method');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Method');
    }

}