<?php

namespace App\Filament\Resources\Keywords\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class KeywordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                // =========================
                // KEYWORD DETAILS
                // =========================
                Section::make('Keyword Details')
                    ->description('Masukkan kata kunci yang akan digunakan')
                    ->icon('heroicon-o-hashtag')
                    ->collapsed(false)
                    ->schema([

                        TextInput::make('name')
                            ->label('Keyword Name')
                            ->placeholder('Contoh: Laravel')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->helperText('Nama keyword yang ditampilkan ke user.')
                            ->afterStateUpdated(function ($state, callable $set, $record) {
                                if (! $record) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->placeholder('laravel')
                            ->required()
                            ->maxLength(120)
                            ->helperText('Digunakan untuk URL, dibuat otomatis dari nama.')
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
            ]);
    }
}
