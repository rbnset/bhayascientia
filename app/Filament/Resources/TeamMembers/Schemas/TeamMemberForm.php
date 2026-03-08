<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ═══════════════════════════════════════════════════════════
                // SECTION 1 — Informasi Utama
                // ═══════════════════════════════════════════════════════════
                Section::make('Informasi Utama')
                    ->description('Data dasar anggota tim yang akan ditampilkan di halaman.')
                    ->icon(Heroicon::OutlinedUser)
                    ->columns(2)
                    ->schema([

                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Contoh: Budi Santoso')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedUser),

                        TextInput::make('title')
                            ->label('Jabatan')
                            ->placeholder('Chief Executive Officer')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedBriefcase),

                        Select::make('level')
                            ->label('Level')
                            ->required()
                            ->live()
                            ->native(false)
                            ->options([
                                'leadership' => 'Leadership (CEO / Pimpinan)',
                                'management' => 'Management (C-Level)',
                                'department' => 'Department (Tim)',
                            ])
                            ->default('department'),

                        TextInput::make('department')
                            ->label('Departemen')
                            ->placeholder('Management / Development / Marketing')
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedBuildingOffice2),

                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Semakin kecil angka, semakin awal ditampilkan.')
                            ->prefixIcon(Heroicon::OutlinedBarsArrowUp),

                        Toggle::make('is_active')
                            ->label('Tampilkan di Halaman')
                            ->helperText('Nonaktifkan untuk menyembunyikan tanpa menghapus data.')
                            ->default(true)
                            ->onIcon(Heroicon::OutlinedEye)
                            ->offIcon(Heroicon::OutlinedEyeSlash)
                            ->onColor('success')
                            ->offColor('gray')
                            ->inline(false),

                    ]),

                // ═══════════════════════════════════════════════════════════
                // SECTION 2 — Foto & Kontak
                // ═══════════════════════════════════════════════════════════
                Section::make('Foto & Kontak')
                    ->description('Foto profil dan informasi kontak anggota tim.')
                    ->icon(Heroicon::OutlinedCamera)
                    ->columns(2)
                    ->schema([

                        FileUpload::make('photo')
                            ->label('Foto Profil')
                            ->image()
                            ->directory('team')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->imagePreviewHeight('200')
                            ->helperText('Format: JPG, PNG, WebP. Maks. 2MB.')
                            ->nullable()
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->placeholder('budi@perusahaan.com')
                            ->maxLength(255)
                            ->nullable()
                            ->prefixIcon(Heroicon::OutlinedEnvelope),

                        TextInput::make('linkedin')
                            ->label('LinkedIn URL')
                            ->url()
                            ->placeholder('https://linkedin.com/in/username')
                            ->maxLength(500)
                            ->nullable()
                            ->prefixIcon(Heroicon::OutlinedLink),

                        Textarea::make('description')
                            ->label('Bio Singkat')
                            ->placeholder('Tuliskan deskripsi singkat mengenai peran dan keahlian...')
                            ->rows(3)
                            ->maxLength(500)
                            ->nullable()
                            ->helperText('Maks. 500 karakter.')
                            ->columnSpanFull(),

                    ]),

                // ═══════════════════════════════════════════════════════════
                // SECTION 3 — Pengaturan Department Card
                // (hanya tampil jika level = department)
                // ═══════════════════════════════════════════════════════════
                Section::make('Pengaturan Department Card')
                    ->description('Konfigurasi ikon dan jumlah anggota untuk kartu departemen.')
                    ->icon(Heroicon::OutlinedSquares2x2)
                    ->columns(2)
                    ->schema([

                        Select::make('icon_type')
                            ->label('Ikon Departemen')
                            ->native(false)
                            ->searchable()
                            ->nullable()
                            ->options([
                                'code'       => 'Pengembangan (Development)',
                                'content'    => 'Konten (Content)',
                                'marketing'  => 'Pemasaran (Marketing)',
                                'operations' => 'Operasional (Operations)',
                                'support'    => 'Dukungan (Support)',
                            ])
                            ->helperText('Ikon yang mewakili departemen ini.'),

                        TextInput::make('member_count')
                            ->label('Jumlah Anggota Tim')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('orang')
                            ->prefixIcon(Heroicon::OutlinedUsers)
                            ->helperText('Total anggota aktif dalam departemen ini.'),

                    ])
                    ->visible(fn(Get $get): bool => $get('level') === 'department'),

            ]);
    }
}
