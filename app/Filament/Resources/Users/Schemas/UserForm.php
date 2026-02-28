<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make()
                    ->columns([
                        'default' => 1,
                        'lg'      => 3,
                    ])
                    ->schema([

                        // =============================================
                        // KOLOM KIRI — Foto + Preview Card (1/3 lebar)
                        // =============================================
                        Section::make()
                            ->columnSpan([
                                'default' => 1,
                                'lg'      => 1,
                            ])
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
                                    ->helperText('JPG, PNG. Maks 2MB')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                    ->moveFiles()
                                    ->extraAttributes([
                                        'class' => 'flex flex-col items-center justify-center',
                                    ]),

                                // Preview card di bawah foto
                                // View::make('filament.users.preview-card'),
                            ]),

                        // =============================================
                        // KOLOM KANAN — Form Input (2/3 lebar)
                        // =============================================
                        Section::make('Informasi Pengguna')
                            ->description('Informasi akun pengguna')
                            ->icon('heroicon-o-user')
                            ->columnSpan([
                                'default' => 1,
                                'lg'      => 2,
                            ])
                            ->schema([

                                Grid::make()
                                    ->columns([
                                        'default' => 1,
                                        'md'      => 2,
                                    ])
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Lengkap')
                                            ->placeholder('Contoh: Robin Setiyawan')
                                            ->required()
                                            ->live(debounce: 500)
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-user'),

                                        TextInput::make('email')
                                            ->label('Alamat Email')
                                            ->placeholder('contoh@email.com')
                                            ->email()
                                            ->required()
                                            ->live(debounce: 500)
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-envelope'),

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
                                            ->placeholder('Contoh: Mahasiswa, Admin')
                                            ->live(debounce: 500)
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-briefcase'),
                                    ]),

                                Grid::make()
                                    ->columns([
                                        'default' => 1,
                                        'md'      => 2,
                                    ])
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

                                Grid::make()
                                    ->columns([
                                        'default' => 1,
                                        'md'      => 2,
                                    ])
                                    ->schema([
                                        Select::make('roles')
                                            ->label('Peran')
                                            ->multiple()
                                            ->relationship('roles', 'name')
                                            ->preload()
                                            ->searchable()
                                            ->prefixIcon('heroicon-o-shield-check'),

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
