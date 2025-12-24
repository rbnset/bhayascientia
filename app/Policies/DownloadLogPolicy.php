<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DownloadLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class DownloadLogPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DownloadLog');
    }

    public function view(AuthUser $authUser, DownloadLog $downloadLog): bool
    {
        return $authUser->can('View:DownloadLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DownloadLog');
    }

    public function update(AuthUser $authUser, DownloadLog $downloadLog): bool
    {
        return $authUser->can('Update:DownloadLog');
    }

    public function delete(AuthUser $authUser, DownloadLog $downloadLog): bool
    {
        return $authUser->can('Delete:DownloadLog');
    }

    public function restore(AuthUser $authUser, DownloadLog $downloadLog): bool
    {
        return $authUser->can('Restore:DownloadLog');
    }

    public function forceDelete(AuthUser $authUser, DownloadLog $downloadLog): bool
    {
        return $authUser->can('ForceDelete:DownloadLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DownloadLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DownloadLog');
    }

    public function replicate(AuthUser $authUser, DownloadLog $downloadLog): bool
    {
        return $authUser->can('Replicate:DownloadLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DownloadLog');
    }

}