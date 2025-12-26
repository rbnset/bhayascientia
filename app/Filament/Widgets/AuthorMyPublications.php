<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Publication;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class AuthorMyPublications extends BaseWidget
{
    protected static ?string $heading = 'Author: Publikasi saya';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->wrap()
                    ->limit(45),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publish at')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since(),
            ])
            ->actions([
                Action::make('open')
                    ->label('Open')
                    ->url(fn(Publication $record) => PublicationResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        return Publication::query()
            ->when($user?->hasRole('author'), function (Builder $q) use ($user) {
                $q->whereHas('authors', fn(Builder $aq) => $aq->where('authors.user_id', $user->id));
            })
            ->latest('updated_at');
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('author') ?? false;
    }
}
