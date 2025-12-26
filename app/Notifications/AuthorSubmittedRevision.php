<?php

namespace App\Notifications;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Publication;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AuthorSubmittedRevision extends Notification
{
    use Queueable;

    public function __construct(
        public Publication $publication,
        public int $newVersionNumber,
        public ?int $reviewIdToOpen = null, // review milik reviewer penerima
    ) {}

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
        $submitterName = $this->resolveSubmitterName();

        $title = (string) ($this->publication->title ?? 'Tanpa judul');
        $type  = (string) ($this->publication->publicationType?->name ?? 'Publikasi');

        $url = $this->reviewIdToOpen
            ? ReviewResource::getUrl('edit', ['record' => $this->reviewIdToOpen])
            : null;

        return FilamentNotification::make()
            ->title('Revisi baru diterima')
            ->body(
                "{$type}\n" .
                    "Judul: {$title}\n" .
                    "Dari: {$submitterName}\n" .
                    "Versi terbaru: v{$this->newVersionNumber}\n" .
                    "Aksi: Silakan buka review Anda untuk cek revisi."
            )
            ->info()
            ->icon('heroicon-o-arrow-up-tray')
            ->actions(array_filter([
                $url
                    ? ActionsAction::make('open')
                    ->label('Buka review saya')
                    ->button()
                    ->url($url)
                    ->markAsRead()
                    : null,
            ]))
            ->getDatabaseMessage(); // wajib agar tampil rapi di Filament [web:274]
    }
}
