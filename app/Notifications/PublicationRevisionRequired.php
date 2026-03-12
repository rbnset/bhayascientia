<?php

namespace App\Notifications;

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Review;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PublicationRevisionRequired extends Notification
{
    use Queueable;

    public function __construct(public Review $review) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $publication  = $this->review->publicationVersion?->publication;
        $title        = (string) ($publication?->title ?? 'Tanpa judul');
        $type         = (string) ($publication?->publicationType?->name ?? 'Publikasi');
        $reviewerName = (string) ($this->review->reviewer?->name ?? 'Reviewer');

        $url = $publication
            ? PublicationResource::getUrl('view', ['record' => $publication])
            : null;

        return FilamentNotification::make()
            ->title('Revisi diperlukan')
            ->body(
                "{$type}\n" .
                    "Judul: {$title}\n" .
                    "Dari: {$reviewerName}\n" .
                    "Aksi: Buka publikasi, pelajari catatan reviewer, lalu upload revisi."
            )
            ->warning()
            ->icon('heroicon-o-arrow-path')
            ->actions(array_filter([
                $url
                    ? ActionsAction::make('open')
                    ->label('Lihat catatan & upload revisi')
                    ->button()
                    ->url($url)
                    ->markAsRead()
                    : null,
            ]))
            ->getDatabaseMessage();
    }
}
