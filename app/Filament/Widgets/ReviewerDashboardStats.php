<?php
// app/Filament/Widgets/ReviewerDashboardStats.php

namespace App\Filament\Widgets;

use App\Models\Review;
use App\Models\Publication;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReviewerDashboardStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    protected function getStats(): array
    {
        $userId = auth()->id();

        $myReviews = Review::query()->where('reviewer_id', $userId);

        $total        = (clone $myReviews)->count();
        $accepted     = (clone $myReviews)->where('decision', 'accepted')->count();
        $revision     = (clone $myReviews)->where('decision', 'revision_required')->count();
        $rejected     = (clone $myReviews)->where('decision', 'rejected')->count();
        $pending      = (clone $myReviews)->whereNull('decision')->count();

        // Review bulan ini vs bulan lalu
        $thisMonth    = (clone $myReviews)->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();
        $lastMonth    = (clone $myReviews)->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)->count();

        // Trend review saya 12 bulan
        $trend = collect(range(1, 12))->map(
            fn($m) =>
            Review::where('reviewer_id', $userId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', $m)
                ->count()
        )->toArray();

        // Publikasi unik yang pernah saya review
        $uniquePubs = Review::where('reviewer_id', $userId)
            ->with('publicationVersion.publication')
            ->get()
            ->pluck('publicationVersion.publication.id')
            ->unique()
            ->filter()
            ->count();

        // Rata-rata waktu review (hari) — dari created_at ke updated_at
        $avgDays = Review::where('reviewer_id', $userId)
            ->whereNotNull('decision')
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
            ->value('avg_days');

        $trendDesc = $lastMonth > 0
            ? ($thisMonth >= $lastMonth ? '↑ ' : '↓ ') . 'vs bulan lalu (' . $lastMonth . ')'
            : $thisMonth . ' review bulan ini';

        return [
            Stat::make('Total Review Saya', $total)
                ->description($trendDesc)
                ->descriptionIcon($thisMonth >= $lastMonth
                    ? 'heroicon-m-arrow-trending-up'
                    : 'heroicon-m-arrow-trending-down')
                ->chart($trend)
                ->color('primary'),

            Stat::make('Belum Diputuskan', $pending)
                ->description($pending > 0 ? 'Menunggu keputusan Anda' : 'Semua sudah diputuskan')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pending > 0 ? 'warning' : 'gray'),

            Stat::make('Diterima', $accepted)
                ->description(
                    $total > 0
                        ? round(($accepted / $total) * 100) . '% dari total review'
                        : 'Belum ada review'
                )
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Perlu Revisi', $revision)
                ->description(
                    $total > 0
                        ? round(($revision / $total) * 100) . '% dari total review'
                        : 'Belum ada review'
                )
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            Stat::make('Ditolak', $rejected)
                ->description(
                    $total > 0
                        ? round(($rejected / $total) * 100) . '% dari total review'
                        : 'Belum ada review'
                )
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($rejected > 0 ? 'danger' : 'gray'),

            Stat::make('Publikasi Direviu', $uniquePubs)
                ->description('Rata-rata ' . round($avgDays ?? 0) . ' hari per review')
                ->descriptionIcon('heroicon-m-document-magnifying-glass')
                ->color('info'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('reviewer') ?? false;
    }
}
