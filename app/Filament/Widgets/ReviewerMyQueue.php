<?php
// app/Filament/Widgets/ReviewerMyQueue.php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ReviewerMyQueue extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        $pending = Review::where('reviewer_id', auth()->id())
            ->whereNull('decision')
            ->count();

        return 'Antrian Review Saya' . ($pending > 0 ? " ({$pending} menunggu)" : '');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('publicationVersion.publication.title')
                    ->label('Judul Publikasi')
                    ->weight(FontWeight::Medium)
                    ->wrap()
                    ->limit(60)
                    ->searchable()
                    ->description(
                        fn(Review $record) =>
                        'Versi ' . ($record->publicationVersion?->version_number ?? '-') .
                            ' · Submitted ' . ($record->publicationVersion?->submitted_at?->diffForHumans() ?? '-')
                    ),

                Tables\Columns\TextColumn::make('decision')
                    ->label('Keputusan')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'accepted'          => 'Diterima',
                        'revision_required' => 'Perlu Revisi',
                        'rejected'          => 'Ditolak',
                        null                => 'Belum Diputuskan',
                        default             => ucfirst($state),
                    })
                    ->color(fn($state) => match ($state) {
                        'accepted'          => 'success',
                        'revision_required' => 'warning',
                        'rejected'          => 'danger',
                        null                => 'gray',
                        default             => 'gray',
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('notes_count')
                    ->label('Catatan')
                    ->counts('notes')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ditugaskan')
                    ->since()
                    ->sortable()
                    ->color('gray')
                    ->tooltip(fn($record) => $record->created_at?->format('d M Y, H:i')),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->since()
                    ->sortable()
                    ->color(fn($record) => $record->updated_at?->isToday() ? 'warning' : 'gray')
                    ->tooltip(fn($record) => $record->updated_at?->format('d M Y, H:i')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('decision')
                    ->label('Keputusan')
                    ->options([
                        'accepted'          => 'Diterima',
                        'revision_required' => 'Perlu Revisi',
                        'rejected'          => 'Ditolak',
                    ])
                    ->placeholder('Semua'),

                Tables\Filters\Filter::make('pending')
                    ->label('Belum Diputuskan')
                    ->query(fn(Builder $q) => $q->whereNull('decision'))
                    ->toggle(),

                Tables\Filters\Filter::make('today')
                    ->label('Diperbarui Hari Ini')
                    ->query(fn(Builder $q) => $q->whereDate('updated_at', today()))
                    ->toggle(),
            ])
            ->actions([
                Action::make('open')
                    ->label('Buka Review')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn(Review $record) => ReviewResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->emptyStateHeading('Tidak ada review')
            ->emptyStateDescription('Anda belum memiliki tugas review saat ini.')
            ->striped()
            ->paginated([5, 10, 25]);
    }

    protected function getTableQuery(): Builder
    {
        return Review::query()
            ->where('reviewer_id', auth()->id())
            ->withCount('notes')
            ->latest('updated_at');
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('reviewer') ?? false;
    }
}
