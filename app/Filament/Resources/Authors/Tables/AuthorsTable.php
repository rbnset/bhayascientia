<?php

namespace App\Filament\Resources\Authors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_url')      // ← sesuaikan dengan nama accessor di model
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode(
                        $record->name ?: ($record->user?->name ?? 'Author')
                    ))
                    ->toggleable(),

                // Nama utama: kalau authors.name kosong, pakai user.name [web:861]
                TextColumn::make('display_name')
                    ->label('Author')
                    ->state(fn($record) => $record->name ?: ($record->user?->name ?? '—'))
                    ->searchable(query: function ($query, string $search) {
                        // cari di authors.name dan users.name (relasi) [web:861]
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->sortable(query: function ($query, string $direction) {
                        // sorting tetap pakai authors.name sebagai default
                        $query->orderBy('name', $direction);
                    })
                    ->weight('medium')
                    ->description(fn($record) => $record->email ?: ($record->user?->email ?? null)),

                TextColumn::make('email')
                    ->label('Email')
                    ->state(fn($record) => $record->email ?: ($record->user?->email ?? '—'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email disalin')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('affiliation')
                    ->label('Affiliation')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->placeholder('—'),

                // Optional: indikator apakah terhubung ke User
                TextColumn::make('user_id')
                    ->label('Account')
                    ->state(fn($record) => $record->user_id ? 'Linked' : 'External')
                    ->badge()
                    ->color(fn($record) => $record->user_id ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square'),

                ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-o-eye'),

                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->successNotification(
                        fn() => Notification::make()
                            ->danger()
                            ->title('Author berhasil dihapus')
                            ->body('Data author telah dihapus secara permanen.')
                    ),
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
