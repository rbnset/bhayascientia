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
        $data['created_by'] = auth()->id();

        // ✅ Pre-check: cek apakah judul sudah ada
        $title = trim($data['title'] ?? '');
        if (filled($title)) {
            $exists = Publication::where('title', $title)->exists();

            if ($exists) {
                Notification::make()
                    ->title('Judul sudah digunakan')
                    ->body('Judul karya ilmiah ini sudah pernah dibuat sebelumnya. Silakan gunakan judul yang berbeda atau tambahkan penjelasan spesifik (metode, lokasi, atau konteks).')
                    ->danger()
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }

        // Generate slug unik
        if (blank($data['slug'] ?? null) && filled($data['title'] ?? null)) {
            $data['slug'] = Publication::generateUniqueSlug($data['title']);
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (UniqueConstraintViolationException $e) {
            Notification::make()
                ->title('Gagal menyimpan publikasi')
                ->body('Data publikasi bertabrakan dengan data yang sudah ada. Silakan cek kembali judul atau data yang diisi.')
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

        $author = Author::query()->firstOrCreate(
            ['user_id' => $creator->id],
            [
                'name'        => $creator->name,
                'email'       => $creator->email,
                'affiliation' => null,
            ]
        );

        $this->record->authors()->syncWithoutDetaching([
            $author->id => [
                'order'            => 1,
                'is_corresponding' => true,
            ],
        ]);

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

    protected function getRedirectUrl(): string
    {
        return PublicationResource::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreateFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
