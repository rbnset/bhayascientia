<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                // =========================
                // PREVIEW USER (REACTIVE)
                // =========================
                Section::make('Pratinjau Pengguna')
                    ->description('Pratinjau profil pengguna secara langsung')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        View::make('filament.users.preview-card')
                            ->live(),
                    ]),

                // =========================
                // DETAIL USER
                // =========================
                Section::make('Detail Pengguna')
                    ->description('Informasi akun pengguna')
                    ->icon('heroicon-o-user')
                    ->schema([
                        FileUpload::make('profile_photo')
                            ->label('Foto Profil')
                            ->placeholder('Unggah foto profil')
                            ->image()
                            ->disk('public')
                            ->directory('users/profile-photos')
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->maxSize(2048)
                            ->live()
                            ->reactive(),

                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Contoh: Robin Setiyawan')
                            ->required()
                            ->live()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->placeholder('contoh@email.com')
                            ->email()
                            ->required()
                            ->live()
                            ->maxLength(255),

                        TextInput::make('whatsapp_number')
                            ->label('Nomor WhatsApp')
                            ->placeholder('08xxxxxxxxxx atau 628xxxxxxxxxx')
                            ->tel()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (filled($state) && str_starts_with($state, '08')) {
                                    $set('whatsapp_number', '628' . substr($state, 2));
                                }
                            })
                            ->maxLength(20),

                        TextInput::make('job_title')
                            ->label('Pekerjaan / Jabatan')
                            ->placeholder('Contoh: Mahasiswa, Admin')
                            ->live()
                            ->maxLength(255),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Terverifikasi Pada')
                            ->placeholder('Pilih tanggal & waktu'),

                        // =========================
                        // PASSWORD
                        // =========================
                        TextInput::make('password')
                            ->label('Password')
                            ->placeholder('Masukkan password')
                            ->password()
                            ->revealable()
                            ->required(fn($operation) => $operation === 'create')
                            ->confirmed()
                            ->dehydrated(fn($state) => filled($state))
                            ->maxLength(255),

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->placeholder('Ulangi password')
                            ->password()
                            ->revealable()
                            ->required(fn($operation) => $operation === 'create')
                            ->dehydrated(false),

                        // =========================
                        // PERAN (roles = BelongsToMany, wajib multiple)
                        // =========================
                        Select::make('roles')
                            ->label('Peran')
                            ->multiple() // penting untuk BelongsToMany supaya state array valid
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
            ]);
    }
}
