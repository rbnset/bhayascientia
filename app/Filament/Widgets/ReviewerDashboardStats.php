<?php

namespace App\Filament\Widgets;

use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReviewerDashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();

        $myReviews = Review::query()->where('reviewer_id', $userId);

        return [
            Stat::make('Review saya', (clone $myReviews)->count()),
            Stat::make('Accepted', (clone $myReviews)->where('decision', 'accepted')->count())
                ->color('success'),
            Stat::make('Revision required', (clone $myReviews)->where('decision', 'revision_required')->count())
                ->color('warning'),
            Stat::make('Rejected', (clone $myReviews)->where('decision', 'rejected')->count())
                ->color('danger'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('reviewer') ?? false;
    }
}
