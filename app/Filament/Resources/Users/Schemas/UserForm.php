<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                // =========================
                // PREVIEW (ATAS)
                // =========================
                Section::make('User Preview')
                    ->description('Pratinjau data user')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        View::make('filament.users.preview-card'),
                    ]),

                // =========================
                // FORM INPUT (BAWAH)
                // =========================
                Section::make('User Details')
                    ->description('Informasi akun pengguna')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->live(),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->live(),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At'),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn($operation) => $operation === 'create')
                            ->dehydrated(fn($state) => filled($state))
                            ->maxLength(255),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 1,
                    ]),
            ]);
    }
}
