<?php

namespace App\Filament\Resources\Authors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // =====================
                // AVATAR
                // =====================
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(
                        fn($record) =>
                        'https://ui-avatars.com/api/?name=' .
                            urlencode($record->name)
                    )
                    ->toggleable(),

                // =====================
                // NAME (PRIMARY)
                // =====================
                TextColumn::make('name')
                    ->label('Author')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn($record) => $record->email),

                // =====================
                // EMAIL (SECONDARY)
                // =====================
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email disalin')
                    ->toggleable(isToggledHiddenByDefault: true),

                // =====================
                // AFFILIATION
                // =====================
                TextColumn::make('affiliation')
                    ->label('Affiliation')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->placeholder('—'),

                // =====================
                // CREATED AT
                // =====================
                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash'),
                ]),
            ]);
    }
}
