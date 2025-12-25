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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Simpan siapa pembuat publication (bukan siapa yang mengedit) [web:532]
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $creator = $this->record->creator; // pembuat asli publication
        if (! $creator) {
            return;
        }

        // Buat/ambil author profile milik creator
        $author = Author::query()->firstOrCreate(
            ['user_id' => $creator->id],
            [
                'name' => $creator->name,
                'email' => $creator->email,
                'affiliation' => null,
            ]
        );

        // Jadikan creator sebagai corresponding author, dan pastikan hanya dia yang corresponding
        $this->record->authors()->syncWithoutDetaching([
            $author->id => [
                'order' => 1,
                'is_corresponding' => true,
            ],
        ]);

        // Matikan corresponding lain (kalau ada) supaya konsisten
        \App\Models\Pivots\AuthorPublication::query()
            ->where('publication_id', $this->record->id)
            ->where('author_id', '!=', $author->id)
            ->update(['is_corresponding' => false]);
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
