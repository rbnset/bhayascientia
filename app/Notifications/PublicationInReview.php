<?php

namespace App\Notifications;

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Publication;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PublicationInReview extends Notification
{
    use Queueable;

    public function __construct(public Publication $publication) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $title = (string) ($this->publication->title ?? 'Tanpa judul');
        $type  = (string) ($this->publication->publicationType?->name ?? 'Publikasi');

        $url = PublicationResource::getUrl('view', [
            'record' => $this->publication,
        ]);

        return FilamentNotification::make()
            ->title('Naskah sedang direview')
            ->body(
                "{$type}\n" .
                    "Judul: {$title}\n" .
                    "Status: Reviewer sedang meninjau naskah Anda."
            )
            ->info()
            ->icon('heroicon-o-magnifying-glass')
            ->actions([
                ActionsAction::make('open')
                    ->label('Lihat publikasi')
                    ->button()
                    ->url($url)
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
