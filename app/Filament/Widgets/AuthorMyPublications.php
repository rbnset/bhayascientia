<?php
// app/Filament/Widgets/AuthorMyPublications.php

namespace App\Filament\Widgets;

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Publication;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class AuthorMyPublications extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        $userId  = auth()->id();
        $total   = Publication::whereHas('authors', fn($q) => $q->where('authors.user_id', $userId))->count();
        $pending = Publication::whereHas('authors', fn($q) => $q->where('authors.user_id', $userId))
            ->whereIn('status', ['submitted', 'in_review', 'revision_required'])
            ->count();

        return 'Publikasi Saya' . ($pending > 0 ? " · {$pending} perlu perhatian" : " · {$total} total");
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Publikasi')
                    ->weight(FontWeight::Medium)
                    ->wrap()
                    ->limit(60)
                    ->searchable()
                    ->description(
                        fn(Publication $record) =>
                        $record->authors->pluck('name')->join(', ') ?: '-'
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'draft'             => 'Draft',
                        'submitted'         => 'Terkirim',
                        'in_review'         => 'Dalam Review',
                        'revision_required' => 'Perlu Revisi',
                        'accepted'          => 'Diterima',
                        'rejected'          => 'Ditolak',
                        'published'         => 'Diterbitkan',
                        default             => ucfirst($state),
                    })
                    ->color(fn($state) => match ($state) {
                        'draft'             => 'gray',
                        'submitted'         => 'info',
                        'in_review'         => 'info',
                        'revision_required' => 'danger',
                        'accepted'          => 'success',
                        'rejected'          => 'danger',
                        'published'         => 'success',
                        default             => 'gray',
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('versions_count')
                    ->label('Versi')
                    ->counts('versions')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Diterbitkan')
                    ->dateTime('d M Y')
                    ->placeholder('—')
                    ->sortable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->since()
                    ->sortable()
                    ->color(fn($record) => $record->updated_at?->isToday() ? 'warning' : 'gray')
                    ->tooltip(fn($record) => $record->updated_at?->format('d M Y, H:i')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'             => 'Draft',
                        'submitted'         => 'Terkirim',
                        'in_review'         => 'Dalam Review',
                        'revision_required' => 'Perlu Revisi',
                        'accepted'          => 'Diterima',
                        'rejected'          => 'Ditolak',
                        'published'         => 'Diterbitkan',
                    ])
                    ->placeholder('Semua Status'),

                Tables\Filters\Filter::make('needs_action')
                    ->label('Perlu Perhatian')
                    ->query(fn(Builder $q) => $q->whereIn('status', ['revision_required', 'rejected']))
                    ->toggle(),

                Tables\Filters\Filter::make('in_progress')
                    ->label('Sedang Diproses')
                    ->query(fn(Builder $q) => $q->whereIn('status', ['submitted', 'in_review']))
                    ->toggle(),

                Tables\Filters\Filter::make('published')
                    ->label('Sudah Terbit')
                    ->query(fn(Builder $q) => $q->where('status', 'published'))
                    ->toggle(),
            ])
            ->actions([
                Action::make('open')
                    ->label('Buka')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn(Publication $record) => PublicationResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('Belum ada publikasi')
            ->emptyStateDescription('Anda belum memiliki publikasi yang terdaftar.')
            ->striped()
            ->paginated([5, 10, 25]);
    }

    protected function getTableQuery(): Builder
    {
        $userId = auth()->id();

        return Publication::query()
            ->whereHas('authors', fn(Builder $q) => $q->where('authors.user_id', $userId))
            ->withCount('versions')
            ->with('authors')
            ->latest('updated_at');
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('author') ?? false;
    }
}
