<?php

namespace App\Filament\Resources\Authors\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;;

use Filament\Schemas\Components\View;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // =========================
                // PREVIEW (ATAS)
                // =========================

                Section::make('Author Preview')
                    ->description('Pratinjau tampilan kartu')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        View::make('filament.authors.preview-card'),
                    ]),

                // =========================
                // FORM INPUT (BAWAH)
                // =========================
                Section::make('Author Details')
                    ->description('Masukkan data lengkap penulis')
                    ->icon('heroicon-o-user')
                    ->collapsed(false)
                    ->schema([
                        Hidden::make('user_id')
                            ->default(fn() => auth()->id())
                            ->dehydrated(),

                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->placeholder('Contoh: John Doe'),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->placeholder('john@example.com'),

                        TextInput::make('affiliation')
                            ->label('Affiliasi')
                            ->maxLength(255)
                            ->live()
                            ->placeholder('Universitas / Organisasi'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 1,
                    ]),
            ]);
    }
}
