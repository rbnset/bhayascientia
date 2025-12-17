<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // =====================
                // USER (PRIMARY)
                // =====================
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn($record) => $record->user?->email),

                // =====================
                // EMAIL NOTIFICATION
                // =====================
                IconColumn::make('notify_email')
                    ->label('Email')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope')
                    ->falseIcon('heroicon-o-envelope-open')
                    ->tooltip(
                        fn($state) =>
                        $state
                            ? 'Email notification aktif'
                            : 'Email notification nonaktif'
                    ),

                // =====================
                // WHATSAPP NOTIFICATION
                // =====================
                IconColumn::make('notify_whatsapp')
                    ->label('WhatsApp')
                    ->boolean()
                    ->trueIcon('heroicon-o-chat-bubble-left-right')
                    ->falseIcon('heroicon-o-chat-bubble-oval-left')
                    ->tooltip(
                        fn($state) =>
                        $state
                            ? 'WhatsApp notification aktif'
                            : 'WhatsApp notification nonaktif'
                    ),

                // =====================
                // CREATED AT
                // =====================
                TextColumn::make('created_at')
                    ->label('Subscribed At')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // future: channel filters
            ])
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
