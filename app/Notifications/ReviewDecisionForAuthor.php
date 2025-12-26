<?php

namespace App\Notifications;

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Review;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReviewDecisionForAuthor extends Notification
{
    use Queueable;

    public function __construct(public Review $review) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    private function decisionLabel(?string $decision): string
    {
        return match ($decision) {
            'revision_required' => 'Perlu revisi',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            default => 'Diperbarui',
        };
    }

    private function decisionIcon(?string $decision): string
    {
        return match ($decision) {
            'accepted' => 'heroicon-o-check-circle',
            'rejected' => 'heroicon-o-x-circle',
            'revision_required' => 'heroicon-o-exclamation-triangle',
            default => 'heroicon-o-bell',
        };
    }


    private function decisionColor(?string $decision): string
    {
        return match ($decision) {
            'accepted' => 'success',
            'rejected' => 'danger',
            'revision_required' => 'warning',
            default => 'gray',
        };
    }

    public function toDatabase(object $notifiable): array
    {
        $publication = $this->review->publicationVersion?->publication;

        $publicationTitle = (string) ($publication?->title ?? 'Tanpa judul');
        $publicationType  = (string) ($publication?->publicationType?->name ?? 'Publikasi');

        $reviewerName = (string) ($this->review->reviewer?->name ?? 'Reviewer');
        $decisionLabel = $this->decisionLabel($this->review->decision);

        $url = $publication
            ? PublicationResource::getUrl('edit', ['record' => $publication])
            : null;

        return FilamentNotification::make()
            ->title("Review baru: {$decisionLabel}")
            ->body(
                "{$publicationType}\n" .
                    "Judul: {$publicationTitle}\n" .
                    "Dari: {$reviewerName}\n" .
                    "Status: {$decisionLabel}"
            )
            ->icon($this->decisionIcon($this->review->decision))
            ->iconColor($this->decisionColor($this->review->decision))
            ->actions(array_filter([
                $url
                    ? ActionsAction::make('open')
                    ->label('Buka publikasi')
                    ->button()
                    ->url($url)
                    ->markAsRead()
                    : null,
            ]))
            ->getDatabaseMessage(); // wajib untuk UI Filament [web:274]
    }
}
