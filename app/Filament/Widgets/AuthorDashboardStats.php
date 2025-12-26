<?php

namespace App\Filament\Widgets;

use App\Models\Publication;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class AuthorDashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();

        $myPublications = Publication::query()
            ->whereHas('authors', fn(Builder $q) => $q->where('authors.user_id', $userId));

        return [
            Stat::make('Publikasi saya', (clone $myPublications)->count()),
            Stat::make('Draft', (clone $myPublications)->where('status', 'draft')->count()),
            Stat::make('Perlu revisi', (clone $myPublications)->where('status', 'revision_required')->count())
                ->color('warning'),
            Stat::make('Ditolak', (clone $myPublications)->where('status', 'rejected')->count())
                ->color('danger'),
            Stat::make('Published', (clone $myPublications)->where('status', 'published')->count())
                ->color('success'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('author') ?? false;
    }
}
