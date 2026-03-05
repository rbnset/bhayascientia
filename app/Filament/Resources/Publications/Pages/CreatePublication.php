<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Author;
use App\Models\Publication;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

class CreatePublication extends CreateRecord
{
    protected static string $resource = PublicationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Simpan siapa pembuat publication (bukan siapa yang mengedit)
        $data['created_by'] = auth()->id();

        // Pastikan slug di-generate / diset, sisanya akan diamankan di model
        if (blank($data['slug'] ?? null) && filled($data['title'] ?? null)) {
            $data['slug'] = Publication::generateUniqueSlug($data['title']);
        }

        return $data;
    }

    /**
     * Tangani error DB (termasuk unique constraint) dengan pesan yang ramah.
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (UniqueConstraintViolationException $e) {
            Notification::make()
                ->title('Gagal menyimpan publikasi')
                ->body('Data publikasi bertabrakan dengan data yang sudah ada (misalnya slug atau field unik lain). Silakan cek kembali judul atau data yang diisi.')
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }
    }

    protected function afterCreate(): void
    {
        $creator = $this->record->creator;
        if (! $creator) {
            return;
        }

        // Buat/ambil author profile milik creator
        $author = Author::query()->firstOrCreate(
            ['user_id' => $creator->id],
            [
                'name'        => $creator->name,
                'email'       => $creator->email,
                'affiliation' => null,
            ]
        );

        // Jadikan creator sebagai corresponding author
        $this->record->authors()->syncWithoutDetaching([
            $author->id => [
                'order'           => 1,
                'is_corresponding' => true,
            ],
        ]);

        // Matikan corresponding lain (kalau ada)
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
