<?php

namespace App\Filament\Resources\Authors\Schemas;

use App\Models\Author;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make()
                    ->columns(['default' => 1, 'lg' => 3])
                    ->schema([

                        // =============================================
                        // KOLOM KIRI — Foto Profil
                        // =============================================
                        Section::make()
                            ->columnSpan(['default' => 1, 'lg' => 1])
                            ->schema([
                                FileUpload::make('photo_path')
                                    ->label('Foto Profil')
                                    ->avatar()
                                    ->disk('public')
                                    ->directory('authors/photos')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->imageEditorMode(2)
                                    ->maxSize(2048)
                                    ->live()
                                    ->helperText('JPG, PNG. Maks 2MB. Kosongkan untuk pakai foto dari akun user.')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                    ->moveFiles()
                                    ->extraAttributes([
                                        'class' => 'flex flex-col items-center justify-center',
                                    ]),
                            ]),

                        // =============================================
                        // KOLOM KANAN — Form Input
                        // =============================================
                        Section::make('Informasi Author')
                            ->description('Masukkan data lengkap penulis')
                            ->icon('heroicon-o-user')
                            ->columnSpan(['default' => 1, 'lg' => 2])
                            ->schema([

                                // ── Info: sudah terhubung ke akun ──
                                Placeholder::make('linked_info')
                                    ->label('')
                                    ->content(function ($record) {
                                        if (!$record?->user_id) return null;
                                        $userName  = $record->user?->name ?? '-';
                                        $userEmail = $record->user?->email ?? '-';
                                        return "✅ Profil ini sudah terhubung ke akun: {$userName} ({$userEmail}). Nama & email otomatis diambil dari akun tersebut.";
                                    })
                                    ->visible(fn($record) => (bool) $record?->user_id),

                                // ── Warning: akan merge jika user sudah punya author profile lain ──
                                Placeholder::make('merge_warning')
                                    ->label('')
                                    ->content(function ($get, $record) {
                                        $selectedUserId = $get('user_id');

                                        if (!$selectedUserId) return null;

                                        // Tidak ada perubahan → tidak perlu warning
                                        if ($record?->user_id == $selectedUserId) return null;

                                        // Cek apakah user yang dipilih sudah punya author profile lain
                                        $existingProfile = Author::where('user_id', $selectedUserId)
                                            ->when($record?->id, fn($q) => $q->where('id', '!=', $record->id))
                                            ->first();

                                        if (!$existingProfile) return null;

                                        $userName   = User::find($selectedUserId)?->name ?? 'Pengguna ini';
                                        $theirCount = $existingProfile->publications()->count();
                                        $myCount    = $record?->publications()->count() ?? 0;
                                        $totalCount = $theirCount + $myCount;

                                        return "⚠️ Perhatian! {$userName} sudah punya profil author dengan {$theirCount} publikasi. "
                                            . "Jika kamu menyimpan perubahan ini, {$myCount} publikasi dari profil saat ini akan "
                                            . "digabungkan ke profil milik {$userName}, sehingga total menjadi {$totalCount} publikasi. "
                                            . "Profil yang sedang dibuka ini akan otomatis dihapus. "
                                            . "Pastikan kamu yakin sebelum melanjutkan — tindakan ini tidak dapat dibatalkan.";
                                    })
                                    ->visible(function ($get, $record) {
                                        $selectedUserId = $get('user_id');
                                        if (!$selectedUserId) return false;
                                        if ($record?->user_id == $selectedUserId) return false;

                                        return Author::where('user_id', $selectedUserId)
                                            ->when($record?->id, fn($q) => $q->where('id', '!=', $record->id))
                                            ->exists();
                                    })
                                    ->live(),

                                Grid::make()
                                    ->columns(['default' => 1, 'md' => 2])
                                    ->schema([

                                        // ✅ Name: required hanya jika TIDAK linked ke user
                                        TextInput::make('name')
                                            ->label('Nama Lengkap')
                                            ->maxLength(255)
                                            ->live(debounce: 500)
                                            ->placeholder(
                                                fn($record) => $record?->user_id
                                                    ? 'Otomatis dari akun: ' . ($record->user?->name ?? '-')
                                                    : 'Contoh: Dr. John Doe, M.T.'
                                            )
                                            ->helperText(
                                                fn($record) => $record?->user_id
                                                    ? 'Kosongkan untuk pakai nama dari akun user.'
                                                    : 'Wajib untuk external author (tanpa akun).'
                                            )
                                            ->required(fn($record) => !$record?->user_id)
                                            ->prefixIcon('heroicon-o-user'),

                                        // ✅ Email: required hanya jika TIDAK linked ke user
                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->maxLength(255)
                                            ->live(debounce: 500)
                                            ->placeholder(
                                                fn($record) => $record?->user_id
                                                    ? 'Otomatis dari akun: ' . ($record->user?->email ?? '-')
                                                    : 'john@example.com'
                                            )
                                            ->helperText(
                                                fn($record) => $record?->user_id
                                                    ? 'Kosongkan untuk pakai email dari akun user.'
                                                    : 'Wajib untuk external author (tanpa akun).'
                                            )
                                            ->required(fn($record) => !$record?->user_id)
                                            ->prefixIcon('heroicon-o-envelope'),
                                    ]),

                                // ✅ Affiliation
                                TextInput::make('affiliation')
                                    ->label('Affiliasi / Institusi')
                                    ->maxLength(255)
                                    ->live(debounce: 500)
                                    ->placeholder(
                                        fn($record) => $record?->user_id
                                            ? 'Kosongkan untuk pakai dari akun: ' .
                                            ($record->user?->affiliation ?? $record->user?->job_title ?? '-')
                                            : 'Universitas / Organisasi'
                                    )
                                    ->helperText('Opsional. Isi jika berbeda dari profil akun user.')
                                    ->prefixIcon('heroicon-o-building-office'),

                                // ✅ Bio
                                Textarea::make('bio')
                                    ->label('Biografi')
                                    ->rows(5)
                                    ->maxLength(1000)
                                    ->live(debounce: 500)
                                    ->placeholder('Opsional. Kosongkan untuk pakai bio dari akun user.')
                                    ->helperText('Opsional. Maks. 1000 karakter. Bisa diisi khusus bio akademik.'),

                                // ✅ Link ke akun User — hanya admin/super_admin
                                Select::make('user_id')
                                    ->label('Hubungkan ke Akun Pengguna')
                                    ->relationship(
                                        name: 'user',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn($query, $record) => $query
                                            ->whereDoesntHave(
                                                'author',
                                                fn($q) => $q->when(
                                                    $record?->id,
                                                    fn($q) => $q->where('id', '!=', $record->id)
                                                )
                                            )
                                            ->orderBy('name')
                                    )
                                    ->getOptionLabelFromRecordUsing(
                                        fn(User $user) => "{$user->name} — {$user->email}"
                                    )
                                    ->searchable(['name', 'email'])
                                    ->preload()
                                    ->nullable()
                                    ->placeholder('— Tidak terhubung (External Author) —')
                                    ->prefixIcon('heroicon-o-link')
                                    ->helperText(
                                        'Opsional. Hubungkan ke akun user yang ada. '
                                            . 'Jika diisi, nama & email author otomatis diambil dari akun tersebut. '
                                            . 'Semua publikasi tetap terjaga.'
                                    )
                                    ->live()
                                    ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                                    ->disabled(fn() => !auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            // Kosongkan name & email karena akan dibaca dari akun user
                                            $set('name', null);
                                            $set('email', null);
                                        }
                                    }),
                            ]),
                    ]),
            ]);
    }

    // =============================================
    // MERGE LOGIC — dipanggil dari EditAuthor::afterSave()
    // =============================================

    /**
     * Dipanggil di EditAuthor::afterSave().
     *
     * Skenario yang ditangani:
     * - User yang dipilih belum punya author profile lain → tidak perlu merge, skip.
     * - User yang dipilih sudah punya author profile lain → pindahkan semua
     *   publikasi dari profil duplikat ke $record, lalu hapus profil duplikat.
     *
     * Yang dipertahankan setelah merge:
     * - $record (author yang sedang diedit) — linked ke user, nama/email dari users
     * - Semua publikasi dari profil duplikat dipindah ke $record
     * - Profil duplikat di-soft delete
     */
    public static function handleAfterSave(Author $record): void
    {
        $userId = $record->user_id;

        // Tidak ada user terhubung → tidak perlu merge
        if (!$userId) return;

        // Cari author profile lain milik user yang sama
        $duplicate = Author::where('user_id', $userId)
            ->where('id', '!=', $record->id)
            ->first();

        // Tidak ada duplikat → tidak perlu merge
        if (!$duplicate) return;

        // ── Ada duplikat → jalankan merge dalam transaction ──
        DB::transaction(function () use ($record, $duplicate) {

            $publicationIds = $duplicate->publications()
                ->pluck('publications.id')
                ->toArray();

            $movedCount = 0;

            foreach ($publicationIds as $pubId) {

                // Cegah duplikat di pivot author_publication
                $alreadyLinked = $record->publications()
                    ->where('publications.id', $pubId)
                    ->exists();

                if (!$alreadyLinked) {
                    // Ambil data pivot asli (order, is_corresponding)
                    $pivotData = $duplicate->authorPublications()
                        ->where('publication_id', $pubId)
                        ->first();

                    $record->publications()->attach($pubId, [
                        'order'            => $pivotData?->order ?? 99,
                        'is_corresponding' => $pivotData?->is_corresponding ?? false,
                    ]);

                    $movedCount++;
                }
            }

            // Lepas semua relasi publikasi dari profil duplikat
            $duplicate->publications()->detach();

            // Soft delete profil duplikat
            $duplicate->delete();

            // ── Notifikasi sukses dengan info detail ──
            $authorName = $record->getRawOriginal('name') ?? $record->user?->name ?? "Author #{$record->id}";

            Notification::make()
                ->title('Profil author berhasil digabungkan! 🎉')
                ->body(
                    $movedCount > 0
                        ? "{$movedCount} publikasi dari profil lama berhasil dipindahkan ke profil {$authorName}. "
                        . "Profil duplikat sudah dihapus secara otomatis."
                        : "Profil duplikat berhasil dihapus. Tidak ada publikasi yang perlu dipindahkan."
                )
                ->success()
                ->duration(8000)
                ->send();
        });
    }
}
