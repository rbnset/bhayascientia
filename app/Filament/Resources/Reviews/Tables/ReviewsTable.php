<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Actions\EditAction as ActionsEditAction;
use Filament\Actions\ViewAction as ActionsViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // ── Judul Publikasi ──────────────────────────────────────────
                // ✅ Support opini: resolusi via helper, bukan chain langsung
                TextColumn::make('publication_title')
                    ->label('Judul Publikasi')
                    ->getStateUsing(function (Review $record): string {
                        $pub = ReviewResource::resolvePublication($record);
                        return $pub?->title ?? '-';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            // Cari di publikasi via versi (review biasa)
                            $q->whereHas('publicationVersion.publication', function ($q2) use ($search) {
                                $q2->where('title', 'like', "%{$search}%");
                            })
                                // ✅ Atau cari di publikasi langsung (opini)
                                ->orWhereHas('publication', function ($q2) use ($search) {
                                    $q2->where('title', 'like', "%{$search}%");
                                });
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Sort berdasarkan judul publikasi (join kedua jalur)
                        return $query->orderByRaw("
                            COALESCE(
                                (SELECT p.title FROM publication_versions pv
                                 JOIN publications p ON p.id = pv.publication_id
                                 WHERE pv.id = reviews.publication_version_id LIMIT 1),
                                (SELECT p2.title FROM publications p2
                                 WHERE p2.id = reviews.publication_id LIMIT 1),
                                ''
                            ) {$direction}
                        ");
                    })
                    ->limit(50)
                    ->tooltip(fn(Review $record): ?string => ReviewResource::resolvePublication($record)?->title),

                // ── Tipe Publikasi ───────────────────────────────────────────
                TextColumn::make('publication_type')
                    ->label('Tipe')
                    ->getStateUsing(function (Review $record): string {
                        $pub = ReviewResource::resolvePublication($record);
                        return $pub?->publicationType?->name ?? '-';
                    })
                    ->badge()
                    ->color('gray'),

                // ── Versi / Opini ────────────────────────────────────────────
                // ✅ Tampilkan "Opini" jika tanpa manuskrip, atau "v1", "v2" dst
                TextColumn::make('version_label')
                    ->label('Versi')
                    ->getStateUsing(function (Review $record): string {
                        if (is_null($record->publication_version_id)) {
                            return 'Opini';
                        }
                        $v = $record->publicationVersion?->version_number;
                        return $v ? "v{$v}" : '-';
                    })
                    ->badge()
                    ->color(fn(string $state): string => $state === 'Opini' ? 'warning' : 'info'),

                // ── Reviewer ─────────────────────────────────────────────────
                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->searchable()
                    ->sortable(),

                // ── Keputusan ────────────────────────────────────────────────
                TextColumn::make('decision')
                    ->label('Keputusan')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'revision_required' => 'Perlu Revisi',
                        'accepted'          => 'Diterima',
                        'rejected'          => 'Ditolak',
                        default             => 'Belum Diputuskan',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'revision_required' => 'warning',
                        'accepted'          => 'success',
                        'rejected'          => 'danger',
                        default             => 'gray',
                    })
                    ->sortable(),

                // ── Tanggal Dibuat ───────────────────────────────────────────
                TextColumn::make('created_at')
                    ->label('Tanggal Review')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])

            ->filters([
                // ── Filter Keputusan ─────────────────────────────────────────
                SelectFilter::make('decision')
                    ->label('Keputusan')
                    ->options([
                        'revision_required' => 'Perlu Revisi',
                        'accepted'          => 'Diterima',
                        'rejected'          => 'Ditolak',
                    ])
                    ->placeholder('Semua'),

                // ── Filter: Opini vs Biasa ───────────────────────────────────
                SelectFilter::make('jenis')
                    ->label('Jenis Review')
                    ->options([
                        'opini'  => 'Opini (tanpa manuskrip)',
                        'biasa'  => 'Dengan Manuskrip',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'opini' => $query->whereNull('publication_version_id'),
                            'biasa' => $query->whereNotNull('publication_version_id'),
                            default => $query,
                        };
                    })
                    ->placeholder('Semua'),
            ])

            ->actions([
                ActionsViewAction::make()
                    ->visible(fn() => (bool) auth()->user()?->hasRole('author')),

                ActionsEditAction::make()
                    ->visible(fn() => ! (bool) auth()->user()?->hasRole('author')),

                ActionsDeleteAction::make()
                    ->visible(fn() => ! (bool) auth()->user()?->hasRole('author')),
            ])

            ->defaultSort('created_at', 'desc');
    }
}
