<?php

namespace App\Filament\Resources\Authors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\View;

class AuthorForm
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
                                    ->helperText('JPG, PNG. Maks 2MB')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                    ->moveFiles()
                                    ->extraAttributes([
                                        'class' => 'flex flex-col items-center justify-center',
                                    ]),

                            ]),

                        // =============================================
                        // KOLOM KANAN — Form Input (2/3 lebar)
                        // =============================================
                        Section::make('Informasi Author')
                            ->description('Masukkan data lengkap penulis')
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
                                            ->required()
                                            ->maxLength(255)
                                            ->live(debounce: 500)
                                            ->placeholder('Contoh: John Doe')
                                            ->prefixIcon('heroicon-o-user'),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255)
                                            ->live(debounce: 500)
                                            ->placeholder('john@example.com')
                                            ->prefixIcon('heroicon-o-envelope'),
                                    ]),

                                TextInput::make('affiliation')
                                    ->label('Affiliasi / Institusi')
                                    ->maxLength(255)
                                    ->live(debounce: 500)
                                    ->placeholder('Universitas / Organisasi')
                                    ->prefixIcon('heroicon-o-building-office'),

                                Textarea::make('bio')
                                    ->label('Biografi')
                                    ->rows(6)
                                    ->maxLength(1000)
                                    ->live(debounce: 500)
                                    ->placeholder('Tulis biografi singkat penulis...')
                                    ->helperText('Maksimal 1000 karakter'),

                                // ← Hidden dihapus, diganti Select opsional
                                Select::make('user_id')
                                    ->label('Akun Pengguna')
                                    ->relationship(
                                        name: 'user',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn($query, $record) => $query->whereDoesntHave(
                                            'author',
                                            fn($q) => $q->when(
                                                $record?->id,
                                                fn($q) => $q->where('id', '!=', $record->id)
                                            )
                                        )
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->placeholder('Tidak terhubung ke akun')
                                    ->prefixIcon('heroicon-o-link')
                                    ->helperText('Opsional: hubungkan author ke akun pengguna yang ada')
                                    ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                                    ->disabled(fn() => ! auth()->user()?->hasAnyRole(['admin', 'super_admin'])),
                            ]),
                    ]),
            ]);
    }
}
