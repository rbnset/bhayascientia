<?php

namespace App\Observers;

use App\Models\Author;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRoleObserver
{
    /**
     * ✅ Dipanggil setelah role di-assign/sync via Spatie
     * Spatie menyimpan ke pivot model_has_roles
     * Kita listen via event di pivot tersebut
     */
    public function saved(User $user): void
    {
        $this->syncAuthorProfile($user);
    }

    private function syncAuthorProfile(User $user): void
    {
        // Refresh agar role terbaru ter-load
        $user->refresh();

        if (!$user->hasRole('author')) {
            return;
        }

        if ($user->authorProfile()->exists()) {
            return;
        }

        Author::create([
            'user_id'     => $user->id,
            'name'        => null,
            'email'       => null,
            'affiliation' => null,
            'bio'         => null,
            'photo_path'  => null,
        ]);
    }
}
