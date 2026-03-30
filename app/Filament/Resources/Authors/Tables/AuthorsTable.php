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

                // ── Foto: pakai accessor photo_url di Author model ──────────
                ImageColumn::make('photo_url')
                    ->label('')
                    ->circular()
                    ->size(40)
                    // ✅ Accessor sudah handle fallback ke user/UI Avatars
                    ->defaultImageUrl(
                        fn($record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->name) .
                            '&background=FF6B18&color=fff&size=80&bold=true'
                    )
                    ->toggleable(),

                // ── Nama: accessor name di Author sudah resolved dari user ──
                TextColumn::make('name')
                    ->label('Author')
                    ->searchable(query: function (Builder $query, string $search) {
                        // ✅ Cari di authors.name DAN users.name
                        $query->where(function ($q) use ($search) {
                            $q->where('authors.name', 'like', "%{$search}%")
                                ->orWhereHas(
                                    'user',
                                    fn($u) =>
                                    $u->where('name', 'like', "%{$search}%")
                                );
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction) {
                        // ✅ Sort gabungan: COALESCE(authors.name, users.name)
                        $query->leftJoin('users', 'authors.user_id', '=', 'users.id')
                            ->orderByRaw("COALESCE(authors.name, users.name) {$direction}");
                    })
                    ->weight('medium')
                    // ✅ Deskripsi: email resolved dari accessor
                    ->description(fn($record) => $record->email ?? '—'),

                // ── Affiliasi: resolved dari accessor ──────────────────────
                TextColumn::make('affiliation')
                    ->label('Affiliasi')
                    ->state(fn($record) => $record->affiliation ?? '—')
                    ->badge()
                    ->color('primary')
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('authors.affiliation', 'like', "%{$search}%")
                                ->orWhereHas(
                                    'user',
                                    fn($u) =>
                                    $u->where('affiliation', 'like', "%{$search}%")
                                        ->orWhere('job_title', 'like', "%{$search}%")
                                );
                        });
                    })
                    ->placeholder('—'),

                TextColumn::make('orcid_id')
                    ->label('ORCID')
                    ->formatStateUsing(fn($state) => $state ?? '—')
                    ->url(fn($record) => $record->orcid_url)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-identification')
                    ->copyable()
                    ->searchable()
                    ->placeholder('—'),

                // ── Status claim ────────────────────────────────────────────
                TextColumn::make('claim_status')
                    ->label('Status')
                    ->state(fn($record) => $record->isClaimed() ? 'Linked' : 'External')
                    ->badge()
                    ->color(fn($record) => $record->isClaimed() ? 'success' : 'gray')
                    ->icon(
                        fn($record) => $record->isClaimed()
                            ? 'heroicon-o-link'
                            : 'heroicon-o-user-minus'
                    )
                    ->tooltip(
                        fn($record) => $record->isClaimed()
                            ? 'Terhubung ke akun: ' . ($record->user?->email ?? '-')
                            : 'External author — belum terhubung ke akun manapun'
                    ),

                // ── Akun user yang terhubung ────────────────────────────────
                TextColumn::make('user.name')
                    ->label('Akun User')
                    ->placeholder('—')
                    ->description(fn($record) => $record->user?->email)
                    ->toggleable(),

                // ── Jumlah publikasi ────────────────────────────────────────
                TextColumn::make('publications_count')
                    ->label('Publikasi')
                    ->counts('publications')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                // Filter: linked vs external
                TernaryFilter::make('account_status')
                    ->label('Status Claim')
                    ->placeholder('Semua Author')
                    ->trueLabel('Linked (punya akun)')
                    ->falseLabel('External (tanpa akun)')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('user_id'),
                        false: fn(Builder $query) => $query->whereNull('user_id'),
                        blank: fn(Builder $query) => $query,
                    ),

                // Filter: affiliasi
                SelectFilter::make('affiliation')
                    ->label('Affiliasi')
                    ->options(function () {
                        // ✅ Gabungkan affiliasi dari authors + users
                        $fromAuthors = \App\Models\Author::query()
                            ->whereNotNull('affiliation')
                            ->distinct()
                            ->pluck('affiliation');

                        $fromUsers = \App\Models\User::query()
                            ->whereHas('author')
                            ->whereNotNull('affiliation')
                            ->distinct()
                            ->pluck('affiliation');

                        return $fromAuthors->merge($fromUsers)
                            ->unique()
                            ->sort()
                            ->mapWithKeys(fn($v) => [$v => $v])
                            ->toArray();
                    })
                    ->searchable()
                    ->placeholder('Semua Affiliasi'),

                // Filter: punya email
                Filter::make('has_email')
                    ->label('Punya Email')
                    ->query(fn(Builder $query) => $query->where(function ($q) {
                        $q->whereNotNull('email')
                            ->orWhereHas('user', fn($u) => $u->whereNotNull('email'));
                    }))
                    ->toggle(),

                // Filter: tanggal dibuat
                Filter::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('created_until')->label('Sampai'),
                    ])
                    ->query(
                        fn(Builder $query, array $data) => $query
                            ->when(
                                $data['created_from'],
                                fn($q, $date) => $q->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn($q, $date) => $q->whereDate('created_at', '<=', $date)
                            )
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Dari: ' .
                                \Carbon\Carbon::parse($data['created_from'])->format('d M Y');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Sampai: ' .
                                \Carbon\Carbon::parse($data['created_until'])->format('d M Y');
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
                        Notification::make()
                            ->danger()
                            ->title('Author berhasil dihapus')
                            ->body('Data author telah dihapus.')
                    ),

                ForceDeleteAction::make()
                    ->label('Hapus Permanen')
                    ->icon('heroicon-o-x-circle'),
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
