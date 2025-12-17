<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;

class UsersTable
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
                    ),

                // =====================
                // NAME
                // =====================
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                // =====================
                // EMAIL
                // =====================
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                // =====================
                // VERIFIED
                // =====================
                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'gray'),

                // =====================
                // CREATED AT (OPTIONAL)
                // =====================
                TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
