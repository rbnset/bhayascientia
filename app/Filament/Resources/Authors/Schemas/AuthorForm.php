<?php

namespace App\Filament\Resources\Authors\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

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

                                // ── Info: jika linked ke user, name & email dari user ──
                                Placeholder::make('linked_info')
                                    ->label('')
                                    ->content(function ($record) {
                                        if (!$record?->user_id) return null;
                                        $userName  = $record->user?->name ?? '-';
                                        $userEmail = $record->user?->email ?? '-';
                                        return "ℹ️ Author ini terhubung ke akun: {$userName} ({$userEmail}). Nama & email otomatis diambil dari akun tersebut.";
                                    })
                                    ->visible(fn($record) => (bool) $record?->user_id),

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

                                // ✅ Affiliation: opsional, fallback ke user jika kosong
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

                                // ✅ Bio: opsional, fallback ke user jika kosong
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
                                        fn(User $user) =>
                                        "{$user->name} — {$user->email}"
                                    )
                                    ->searchable(['name', 'email'])
                                    ->preload()
                                    ->nullable()
                                    ->placeholder('— Tidak terhubung (External Author) —')
                                    ->prefixIcon('heroicon-o-link')
                                    ->helperText(
                                        'Opsional. Hubungkan ke akun user yang ada. ' .
                                            'Jika diisi, nama & email author otomatis diambil dari akun tersebut. ' .
                                            'Data publikasi lama tetap terjaga.'
                                    )
                                    ->live() // ✅ Live agar placeholder name/email update saat user dipilih
                                    ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                                    ->disabled(fn() => !auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                                    // ✅ Saat user dipilih, kosongkan name & email (akan dibaca dari user)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $set('name', null);
                                            $set('email', null);
                                        }
                                    }),
                            ]),
                    ]),
            ]);
    }
}
