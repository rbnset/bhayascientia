<?php

namespace App\Notifications;

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Review;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PublicationRejected extends Notification
{
    use Queueable;

    public function __construct(public Review $review) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        // ✅ fallback untuk opini tanpa publicationVersion
        $publication  = $this->review->publicationVersion?->publication
            ?? $this->review->publication;

        $title        = (string) ($publication?->title ?? 'Tanpa judul');
        $type         = (string) ($publication?->publicationType?->name ?? 'Publikasi');
        $reviewerName = (string) ($this->review->reviewer?->name ?? 'Reviewer');

        $url = $publication
            ? PublicationResource::getUrl('view', ['record' => $publication])
            : null;

        return FilamentNotification::make()
            ->title('Naskah tidak dapat diterima')
            ->body(
                "{$type}\n" .
                    "Judul: {$title}\n" .
                    "Dari: {$reviewerName}\n" .
                    "Aksi: Buka publikasi untuk membaca alasan penolakan dari reviewer."
            )
            ->danger()
            ->icon('heroicon-o-x-circle')
            ->actions(array_filter([
                $url
                    ? ActionsAction::make('open')
                    ->label('Baca catatan reviewer')
                    ->button()
                    ->url($url)
                    ->markAsRead()
                    : null,
            ]))
            ->getDatabaseMessage();
    }
}
