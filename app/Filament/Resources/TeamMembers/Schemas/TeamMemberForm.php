<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Informasi Utama')
                    ->description('Data dasar anggota tim')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nama Lengkap')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('title')
                                ->label('Jabatan')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Chief Executive Officer'),

                            Select::make('level')
                                ->label('Level')
                                ->required()
                                ->live()
                                ->options([
                                    'leadership'  => '👑 Leadership (CEO / Pimpinan)',
                                    'management'  => '🏢 Management (C-Level)',
                                    'department'  => '👥 Department (Tim)',
                                ])
                                ->default('department'),

                            TextInput::make('department')
                                ->label('Departemen')
                                ->maxLength(255)
                                ->placeholder('Management / Development / dll'),

                            TextInput::make('order')
                                ->label('Urutan Tampil')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->minValue(0),

                            Toggle::make('is_active')
                                ->label('Aktif / Tampil di halaman')
                                ->default(true)
                                ->inline(false),
                        ]),
                    ]),

                Section::make('Foto & Kontak')
                    ->description('Foto profil dan informasi kontak')
                    ->icon('heroicon-o-camera')
                    ->schema([

                        // ✅ FIX: Hapus semua imageResize* — penyebab loading terus
                        FileUpload::make('photo')
                            ->label('Foto Profil')
                            ->image()
                            ->directory('team')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(2048)              // max 2MB
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->imagePreviewHeight('200')  // ✅ Preview height saja, tidak resize
                            ->nullable()
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('linkedin')
                            ->label('LinkedIn URL')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://linkedin.com/in/...')
                            ->nullable(),

                        Textarea::make('description')
                            ->label('Deskripsi / Bio Singkat')
                            ->rows(3)
                            ->maxLength(500)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Pengaturan Department Card')
                    ->description('Hanya untuk level Department — ikon dan jumlah anggota tim')
                    ->icon('heroicon-o-squares-2x2')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('icon_type')
                                ->label('Ikon Departemen')
                                ->options([
                                    'code'       => '💻 Pengembangan (Code)',
                                    'content'    => '✏️ Konten (Content)',
                                    'marketing'  => '📢 Pemasaran (Marketing)',
                                    'operations' => '📋 Operasional (Operations)',
                                    'support'    => '🛠 Dukungan (Support)',
                                ])
                                ->nullable()
                                ->searchable(),

                            TextInput::make('member_count')
                                ->label('Jumlah Anggota Tim')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ]),
                    ])
                    ->visible(fn(Get $get): bool => $get('level') === 'department'),

            ]);
    }
}
