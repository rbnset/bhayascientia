<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Author;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * ✅ Setelah user baru dibuat, cek apakah perlu buat Author profile
     */
    protected function afterCreate(): void
    {
        $user  = $this->record;
        $roles = $this->data['roles'] ?? [];

        // Normalize: bisa array of ID atau array of name
        $roleNames = collect($roles)
            ->map(
                fn($r) => is_numeric($r)
                    ? \Spatie\Permission\Models\Role::find($r)?->name
                    : $r
            )
            ->filter()
            ->toArray();

        if (in_array('author', $roleNames) && !$user->authorProfile()->exists()) {
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
}
