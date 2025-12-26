<?php

namespace App\Filament\Widgets;

use App\Models\Publication;
use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminDashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total publikasi', Publication::query()->count()),
            Stat::make('Submitted', Publication::query()->where('status', 'submitted')->count()),
            Stat::make('Revision required', Publication::query()->where('status', 'revision_required')->count()),
            Stat::make('Published', Publication::query()->where('status', 'published')->count())
                ->color('success'),
            Stat::make('Total review', Review::query()->count()),
        ];
    }

    public static function canView(): bool
    {
        // Sesuaikan: pakai role 'super_admin' / 'admin' sesuai yang ada di sistem Anda
        return auth()->user()?->hasRole('super_admin') ?? false;
    }
}
