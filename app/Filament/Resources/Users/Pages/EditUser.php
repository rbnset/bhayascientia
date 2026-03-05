<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Author;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * ✅ Setelah user di-update, cek apakah perlu buat Author profile
     * Kasus: user yang sebelumnya bukan author lalu diberi role author
     */
    protected function afterSave(): void
    {
        $user  = $this->record;
        $roles = $this->data['roles'] ?? [];

        // Normalize role names
        $roleNames = collect($roles)
            ->map(
                fn($r) => is_numeric($r)
                    ? \Spatie\Permission\Models\Role::find($r)?->name
                    : $r
            )
            ->filter()
            ->toArray();

        // ✅ Buat Author profile jika sekarang punya role author tapi belum ada profil
        if (
            (in_array('author', $roleNames) || $user->hasRole('author'))
            && !$user->authorProfile()->exists()
        ) {
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
