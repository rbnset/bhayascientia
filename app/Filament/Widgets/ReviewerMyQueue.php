<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ReviewerMyQueue extends BaseWidget
{
    protected static ?string $heading = 'Reviewer: Review saya';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('publicationVersion.publication.title')
                    ->label('Judul')
                    ->wrap()
                    ->limit(40),
                Tables\Columns\TextColumn::make('decision')
                    ->label('Decision')
                    ->badge(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since(),
            ])
            ->actions([
                Action::make('open')
                    ->label('Open')
                    ->url(fn(Review $record) => ReviewResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        return Review::query()
            ->when($user?->hasRole('reviewer'), fn(Builder $q) => $q->where('reviewer_id', $user->id))
            ->latest('updated_at');
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('reviewer') ?? false;
    }
}
