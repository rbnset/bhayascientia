<?php

namespace App\Filament\Resources\Authors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuthorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_url')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode(
                        $record->name ?: ($record->user?->name ?? 'Author')
                    ))
                    ->toggleable(),

                TextColumn::make('display_name')
                    ->label('Author')
                    ->state(fn($record) => $record->name ?: ($record->user?->name ?? '—'))
                    ->searchable(query: function ($query, string $search) {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->sortable(query: function ($query, string $direction) {
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
            ->filters([
                TrashedFilter::make(),

                // 1. Filter: apakah author terhubung ke akun User atau tidak
                TernaryFilter::make('account_status')
                    ->label('Account Status')
                    ->placeholder('Semua Author')
                    ->trueLabel('Linked (punya akun)')
                    ->falseLabel('External (tanpa akun)')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('user_id'),
                        false: fn(Builder $query) => $query->whereNull('user_id'),
                        blank: fn(Builder $query) => $query,
                    ),

                // 2. Filter: berdasarkan affiliation (distinct dari DB)
                SelectFilter::make('affiliation')
                    ->label('Affiliation')
                    ->options(
                        fn() => \App\Models\Author::query()
                            ->whereNotNull('affiliation')
                            ->distinct()
                            ->orderBy('affiliation')
                            ->pluck('affiliation', 'affiliation')
                            ->toArray()
                    )
                    ->searchable()
                    ->placeholder('Semua Affiliasi'),

                // 3. Filter: author yang punya email (baik di authors maupun via relasi user)
                Filter::make('has_email')
                    ->label('Punya Email')
                    ->query(fn(Builder $query) => $query->where(function ($q) {
                        $q->whereNotNull('email')
                            ->orWhereHas('user', fn($u) => $u->whereNotNull('email'));
                    }))
                    ->toggle(),

                // 4. Filter: rentang tanggal dibuat
                Filter::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn($q, $date) => $q->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn($q, $date) => $q->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Dari: ' . \Carbon\Carbon::parse($data['created_from'])->format('d M Y');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Sampai: ' . \Carbon\Carbon::parse($data['created_until'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square'),

                ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-o-eye'),

                RestoreAction::make()
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Author berhasil di-restore')
                            ->body('Data author telah dipulihkan.')
                    ),

                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->successNotification(
                        fn() => Notification::make()
                            ->danger()
                            ->title('Author berhasil dihapus')
                            ->body('Data author telah dihapus secara permanen.')
                    ),

                ForceDeleteAction::make()
                    ->label('Hapus Permanen')
                    ->icon('heroicon-o-x-circle'),

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
