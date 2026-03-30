<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Author;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
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
                                FileUpload::make('profile_photo')
                                    ->label('Foto Profil')
                                    ->avatar()
                                    ->disk('public')
                                    ->directory('users/profile-photos')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->imageEditorMode(2)
                                    ->maxSize(2048)
                                    ->live()
                                    ->helperText('JPG, PNG. Maks 2MB. Foto ini juga dipakai di profil author jika tidak ada foto khusus.')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                    ->moveFiles()
                                    ->extraAttributes([
                                        'class' => 'flex flex-col items-center justify-center',
                                    ]),
                            ]),

                        // =============================================
                        // KOLOM KANAN — Form Input
                        // =============================================
                        Section::make('Informasi Pengguna')
                            ->description('Informasi akun pengguna')
                            ->icon('heroicon-o-user')
                            ->columnSpan(['default' => 1, 'lg' => 2])
                            ->schema([

                                // ── Info: status author profile ───────────────
                                Placeholder::make('author_profile_info')
                                    ->label('')
                                    ->content(function ($record) {
                                        if (!$record) return null;

                                        if ($record->authorProfile()->exists()) {
                                            return '✅ User ini sudah memiliki profil author. ' .
                                                'Perubahan nama, email, dan foto akan otomatis tersinkron ke profil author.';
                                        }

                                        if ($record->hasRole('author')) {
                                            return '⚠️ User memiliki role author tapi belum ada profil author. ' .
                                                'Profil author akan dibuat otomatis saat disimpan.';
                                        }

                                        return null;
                                    })
                                    ->visible(fn($record) => (bool) $record?->id),

                                Grid::make()
                                    ->columns(['default' => 1, 'md' => 2])
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Lengkap')
                                            ->placeholder('Contoh: Robin Setiyawan')
                                            ->required()
                                            ->live(debounce: 500)
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-user')
                                            ->helperText('Nama ini otomatis dipakai di profil author jika terhubung.'),

                                        TextInput::make('email')
                                            ->label('Alamat Email')
                                            ->placeholder('contoh@email.com')
                                            ->email()
                                            ->required()
                                            ->live(debounce: 500)
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-envelope')
                                            ->helperText('Email ini otomatis dipakai di profil author jika terhubung.'),

                                        TextInput::make('whatsapp_number')
                                            ->label('Nomor WhatsApp')
                                            ->placeholder('08xxxxxxxxxx')
                                            ->tel()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if (filled($state) && str_starts_with($state, '08')) {
                                                    $set('whatsapp_number', '628' . substr($state, 2));
                                                }
                                            })
                                            ->maxLength(20)
                                            ->prefixIcon('heroicon-o-phone'),

                                        TextInput::make('job_title')
                                            ->label('Pekerjaan / Jabatan')
                                            ->placeholder('Contoh: Mahasiswa, Dosen, Admin')
                                            ->live(debounce: 500)
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-briefcase')
                                            ->helperText('Dipakai sebagai fallback affiliasi di profil author.'),
                                    ]),

                                // ── Affiliasi & Bio — tersinkron ke Author ────
                                Grid::make()
                                    ->columns(['default' => 1, 'md' => 2])
                                    ->schema([
                                        TextInput::make('affiliation')
                                            ->label('Affiliasi / Institusi')
                                            ->placeholder('Universitas / Organisasi')
                                            ->live(debounce: 500)
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-building-office')
                                            ->helperText('Dipakai sebagai affiliasi di profil author jika tidak diisi khusus.'),

                                        TextInput::make('orcid_id')
                                            ->label('ORCID iD')
                                            ->placeholder('0000-0000-0000-0000')
                                            ->helperText('Format: 0000-0000-0000-0000')
                                            ->maxLength(19)
                                            ->regex('/^\d{4}-\d{4}-\d{4}-\d{3}[\dXx]$/')
                                            ->validationMessages([
                                                'regex' => 'Format ORCID tidak valid. Gunakan format: 0000-0000-0000-0000',
                                            ])
                                            ->suffixAction(
                                                Action::make('open_orcid')
                                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                                    ->url(
                                                        fn($get) => $get('orcid_id')
                                                            ? 'https://orcid.org/' . $get('orcid_id')
                                                            : null
                                                    )
                                                    ->openUrlInNewTab()
                                                    ->visible(fn($get) => filled($get('orcid_id')))
                                            ),
                                    ]),

                                Textarea::make('bio')
                                    ->label('Bio')
                                    ->placeholder('Tulis bio singkat...')
                                    ->live(debounce: 500)
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->helperText('Dipakai sebagai bio di profil author jika tidak diisi khusus.'),

                                // ── Password ──────────────────────────────────
                                Grid::make()
                                    ->columns(['default' => 1, 'md' => 2])
                                    ->schema([
                                        TextInput::make('password')
                                            ->label('Password')
                                            ->placeholder('Masukkan password')
                                            ->password()
                                            ->revealable()
                                            ->required(fn($operation) => $operation === 'create')
                                            ->confirmed()
                                            ->dehydrated(fn($state) => filled($state))
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-lock-closed'),

                                        TextInput::make('password_confirmation')
                                            ->label('Konfirmasi Password')
                                            ->placeholder('Ulangi password')
                                            ->password()
                                            ->revealable()
                                            ->required(fn($operation) => $operation === 'create')
                                            ->dehydrated(false)
                                            ->prefixIcon('heroicon-o-lock-closed'),
                                    ]),

                                // ── Role & Verifikasi ─────────────────────────
                                Grid::make()
                                    ->columns(['default' => 1, 'md' => 2])
                                    ->schema([
                                        Select::make('roles')
                                            ->label('Peran')
                                            ->multiple()
                                            ->relationship(
                                                'roles',
                                                'name',
                                                fn($query) => auth()->user()->hasRole('super_admin')
                                                    ? $query
                                                    : $query->whereIn('name', ['author', 'reviewer'])
                                            )
                                            ->preload()
                                            ->searchable()
                                            ->prefixIcon('heroicon-o-shield-check')
                                            ->helperText('Memberikan role "author" akan otomatis membuat profil author.'),

                                        DateTimePicker::make('email_verified_at')
                                            ->label('Email Terverifikasi Pada')
                                            ->placeholder('Pilih tanggal & waktu')
                                            ->prefixIcon('heroicon-o-check-badge'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
