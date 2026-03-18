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
     * ✅ Dipanggil setelah data tersimpan ke DB.
     * Jika user yang dipilih sudah punya author profile lain,
     * publikasi dari profil lama dipindah ke sini, lalu profil lama dihapus.
     */
    protected function afterSave(): void
    {
        AuthorForm::handleAfterSave($this->record);
    }

    /**
     * ✅ Notifikasi sukses default saat save tanpa merge.
     * Jika terjadi merge, notifikasi ditangani oleh handleAfterSave().
     */
    protected function getSavedNotification(): ?Notification
    {
        // Jika ada merge (user sudah punya author profile lain),
        // notifikasi lebih detail sudah dikirim dari handleAfterSave().
        // Di sini kita cek apakah ada duplikat — jika ya, skip notifikasi default ini.
        $userId = $this->record->user_id;
        if ($userId) {
            $hasDuplicate = \App\Models\Author::where('user_id', $userId)
                ->where('id', '!=', $this->record->id)
                ->exists();

            if ($hasDuplicate) {
                // Akan segera di-merge oleh afterSave, biarkan notifikasi dari sana
                return null;
            }
        }

        $authorLabel = $this->record->name ?: ($this->record->email ?: "Author #{$this->record->id}");
        $userLabel   = $this->record->user?->name
            ?? ($this->record->user_id ? "User ID: {$this->record->user_id}" : 'Tanpa akun');

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
                ->modalDescription('Profil author akan dihapus permanen. Publikasi yang terhubung tidak ikut terhapus, namun nama author tidak akan muncul lagi.')
                ->modalSubmitActionLabel('Ya, hapus sekarang')
                ->modalCancelActionLabel('Batal')
                ->successNotification(
                    fn() => Notification::make()
                        ->success()
                        ->title('Profil author dihapus')
                        ->body('Data author berhasil dihapus dari sistem.')
                ),
        ];
    }
}
