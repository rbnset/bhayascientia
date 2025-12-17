<?php

namespace App\Filament\Resources\Methods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                // =========================
                // METHOD DETAILS
                // =========================
                Section::make('Method Details')
                    ->description('Masukkan metodologi penelitian yang digunakan')
                    ->icon('heroicon-o-beaker')
                    ->collapsed(false)
                    ->schema([

                        TextInput::make('name')
                            ->label('Method Name')
                            ->placeholder('Contoh: Penelitian Eksperimental')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->helperText('Nama metodologi penelitian.')
                            ->afterStateUpdated(function ($state, callable $set, $record) {
                                if (! $record) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->placeholder('penelitian-eksperimental')
                            ->required()
                            ->maxLength(120)
                            ->helperText('Digunakan untuk URL, dibuat otomatis.')
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 1,
                    ]),
            ]);
    }
}
