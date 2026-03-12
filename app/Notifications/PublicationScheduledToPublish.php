<?php

namespace App\Notifications;

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Publication;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PublicationScheduledToPublish extends Notification
{
    use Queueable;

    public function __construct(public Publication $publication) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    private function resolveSubmitterName(): string
    {
        $corresponding = $this->publication->authors()
            ->wherePivot('is_corresponding', true)
            ->first();

        return $corresponding?->name
            ?: ($this->publication->authors()->first()?->name ?: 'Author');
    }

    public function toDatabase(object $notifiable): array
    {
        $title = (string) ($this->publication->title ?? 'Tanpa judul');
        $type  = (string) ($this->publication->publicationType?->name ?? 'Publikasi');

        $publishAt = $this->publication->published_at
            ? $this->publication->published_at->translatedFormat('d M Y H:i')
            : '—';

        $url = PublicationResource::getUrl('view', [
            'record' => $this->publication,
        ]);

        return FilamentNotification::make()
            ->title('Publikasi dijadwalkan terbit')
            ->body(
                "{$type}\n" .
                    "Judul: {$title}\n" .
                    "Terbit pada: {$publishAt}\n" .
                    "Status: Published"
            )
            ->success()
            ->icon('heroicon-o-megaphone')
            ->actions([
                ActionsAction::make('open')
                    ->label('Buka publikasi')
                    ->button()
                    ->url($url)
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
