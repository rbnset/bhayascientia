<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // =====================
                // FOTO PROFIL
                // =====================
                ImageColumn::make('profile_photo')
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
                // NAMA
                // =====================
                TextColumn::make('name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn($record) => $record->email),

                // =====================
                // EMAIL
                // =====================
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email disalin')
                    ->toggleable(isToggledHiddenByDefault: true),

                // =====================
                // PEKERJAAN
                // =====================
                TextColumn::make('job_title')
                    ->label('Pekerjaan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // =====================
                // WHATSAPP
                // =====================
                TextColumn::make('whatsapp_number')
                    ->label('WhatsApp')
                    ->copyable()
                    ->copyMessage('Nomor WhatsApp disalin')
                    ->toggleable(isToggledHiddenByDefault: true),

                // =====================
                // STATUS VERIFIKASI
                // =====================
                TextColumn::make('email_verified_at')
                    ->label('Status Verifikasi')
                    ->badge()
                    ->sortable()
                    ->color(fn($state) => $state ? 'success' : 'gray')
                    ->formatStateUsing(
                        fn($state) => $state ? 'Terverifikasi' : 'Belum Terverifikasi'
                    ),

                // =====================
                // DIBUAT PADA
                // =====================
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->icon('heroicon-o-trash'),
                ]),
            ]);
    }
}
