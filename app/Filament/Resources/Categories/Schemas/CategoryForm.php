<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                // =========================
                // CATEGORY DETAILS
                // =========================
                Section::make('Category Details')
                    ->description('Masukkan informasi kategori')
                    ->icon('heroicon-o-tag')
                    ->collapsed(false)
                    ->schema([

                        TextInput::make('name')
                            ->label('Category Name')
                            ->placeholder('Contoh: Crime')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->helperText('Nama kategori yang ditampilkan ke user.')
                            ->afterStateUpdated(function ($state, callable $set, $record) {
                                if (! $record) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->placeholder('crime')
                            ->required()
                            ->maxLength(120)
                            ->helperText('Digunakan untuk URL, dibuat otomatis.')
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
