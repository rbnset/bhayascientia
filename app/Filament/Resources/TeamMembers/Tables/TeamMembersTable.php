<?php

namespace App\Filament\Resources\TeamMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class TeamMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // ✅ FIX: Pakai getStateUsing untuk full control URL foto
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->size(48)
                    ->getStateUsing(function ($record): string {
                        if (!empty($record->photo)) {
                            // ✅ Kalau URL eksternal langsung return
                            if (filter_var($record->photo, FILTER_VALIDATE_URL)) {
                                return $record->photo;
                            }

                            // ✅ Path dari DB: "team/xxx.jpg"
                            // Gunakan Storage::url() — tidak ada double slash
                            $path = ltrim($record->photo, '/');
                            if (Storage::disk('public')->exists($path)) {
                                return Storage::disk('public')->url($path);
                            }
                        }

                        // ✅ Fallback UI Avatars
                        return 'https://ui-avatars.com/api/?name='
                            . urlencode($record->name ?? 'NN')
                            . '&size=80&background=FFF7F2&color=FF6B18&bold=true';
                    }),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('title')
                    ->label('Jabatan')
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'leadership' => 'danger',
                        'management' => 'warning',
                        'department' => 'success',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'leadership' => '👑 Leadership',
                        'management' => '🏢 Management',
                        'department' => '👥 Department',
                        default      => $state,
                    }),

                TextColumn::make('department')
                    ->label('Departemen')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('member_count')
                    ->label('Anggota')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order', 'asc')
            ->filters([
                SelectFilter::make('level')
                    ->label('Filter Level')
                    ->options([
                        'leadership' => '👑 Leadership',
                        'management' => '🏢 Management',
                        'department' => '👥 Department',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order');
    }
}
