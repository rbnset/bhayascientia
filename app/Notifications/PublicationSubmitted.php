<?php

namespace App\Notifications;

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Publication;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PublicationSubmitted extends Notification
{
    use Queueable;

    public function __construct(public Publication $publication) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    protected function resolveSubmitterName(): string
    {
        // Ambil corresponding author kalau ada
        $corresponding = $this->publication->authors()
            ->wherePivot('is_corresponding', true)
            ->first();

        if ($corresponding?->name) {
            return $corresponding->name;
        }

        // Fallback: author pertama
        $firstAuthor = $this->publication->authors()->first();

        return $firstAuthor?->name ?: 'Seorang author';
    }

    public function toDatabase(object $notifiable): array
    {
        $submitterName = $this->resolveSubmitterName();
        $title = (string) ($this->publication->title ?? 'Tanpa judul');
        $typeName = (string) ($this->publication->publicationType?->name ?? 'Publikasi');

        $editUrl = PublicationResource::getUrl('edit', [
            'record' => $this->publication,
        ]);

        // Copywriting + action interaktif
        return FilamentNotification::make()
            ->title("Submission baru: {$typeName}")
            ->body(
                "Dikirim oleh: {$submitterName}\n" .
                    "Judul: {$title}\n" .
                    "Aksi: Mohon lakukan review & ubah status bila diperlukan."
            )
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->iconColor('success')
            ->actions([
                ActionsAction::make('open')
                    ->label('Buka publikasi')
                    ->button()
                    ->url($editUrl)
                    ->markAsRead(), // klik tombol -> otomatis read [web:274]
            ])
            ->getDatabaseMessage(); // format khusus Filament [web:282]
    }
}
