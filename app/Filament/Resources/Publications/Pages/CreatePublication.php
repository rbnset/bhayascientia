<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Author;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePublication extends CreateRecord
{
    protected static string $resource = PublicationResource::class;

    protected function afterCreate(): void
    {
        $user = auth()->user();

        // hanya berlaku untuk user role author (kalau mau untuk semua user, hapus if ini)
        if (! $user?->hasRole('author')) {
            return;
        }

        // pastikan ada profil Author untuk user login
        $author = Author::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
                'affiliation' => null,
            ]
        );

        // pastikan pembuat publikasi ter-attach di pivot author_publication
        // syncWithoutDetaching tidak menghapus author lain yang sudah ada [web:891]
        $this->record->authors()->syncWithoutDetaching([
            $author->id => [
                'order' => 1,
                'is_corresponding' => true,
            ],
        ]);
    }

    protected function getCreatedNotification(): ?Notification
    {
        $shortTitle = Str::of((string) $this->record->title)
            ->squish()
            ->words(8, '…')
            ->toString();

        return Notification::make()
            ->success()
            ->title('Publikasi berhasil dibuat')
            ->body("Judul: {$shortTitle}");
    }
}
