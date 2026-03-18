<?php

namespace App\Filament\Resources\Authors\Pages;

use App\Filament\Resources\Authors\AuthorResource;
use App\Filament\Resources\Authors\Schemas\AuthorForm;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAuthor extends EditRecord
{
    protected static string $resource = AuthorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * ✅ Dipanggil otomatis setelah data tersimpan ke DB.
     * Menangani merge jika user yang dipilih sudah punya author profile lain.
     */
    protected function afterSave(): void
    {
        AuthorForm::handleAfterSave($this->record);
    }

    /**
     * ✅ Notifikasi sukses default — hanya tampil jika tidak terjadi merge.
     * Jika merge terjadi, notifikasi lebih detail sudah dikirim dari handleAfterSave().
     */
    protected function getSavedNotification(): ?Notification
    {
        $userId = $this->record->user_id;

        // Jika akan ada merge, skip notifikasi default ini
        // karena handleAfterSave() sudah kirim notifikasi yang lebih informatif
        if ($userId) {
            $willMerge = \App\Models\Author::where('user_id', $userId)
                ->where('id', '!=', $this->record->id)
                ->exists();

            if ($willMerge) return null;
        }

        $authorLabel = $this->record->name
            ?: ($this->record->email
                ?: "Author #{$this->record->id}");

        $userLabel = $this->record->user?->name
            ?? ($this->record->user_id
                ? "User ID: {$this->record->user_id}"
                : 'Tanpa akun');

        return Notification::make()
            ->success()
            ->title('Perubahan tersimpan!')
            ->body("Profil {$authorLabel} berhasil diperbarui. Terhubung ke: {$userLabel}.");
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Lihat')
                ->icon('heroicon-o-eye'),

            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Hapus profil author ini?')
                ->modalDescription(
                    'Profil author akan dihapus. Publikasi yang terhubung tidak ikut terhapus, '
                        . 'namun nama author tidak akan muncul lagi di publikasi tersebut.'
                )
                ->modalSubmitActionLabel('Ya, hapus sekarang')
                ->modalCancelActionLabel('Batal, saya tidak jadi')
                ->successNotification(
                    fn() => Notification::make()
                        ->success()
                        ->title('Profil author dihapus')
                        ->body('Data author berhasil dihapus dari sistem.')
                ),
        ];
    }
}
