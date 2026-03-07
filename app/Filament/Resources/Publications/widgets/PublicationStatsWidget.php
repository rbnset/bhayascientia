<?php

namespace App\Filament\Resources\Publications\Widgets;

use App\Models\Publication;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PublicationStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return true;
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        // Base query — author hanya lihat miliknya
        $baseQuery = Publication::query();
        if ($user->hasRole('author')) {
            $baseQuery->whereHas('authors', fn($q) => $q->where('authors.user_id', $user->id));
        }

        // Hitung per status sekaligus (1 query)
        $counts = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $total     = $counts->sum();
        $draft     = $counts->get('draft', 0);
        $submitted = $counts->get('submitted', 0);
        $inReview  = $counts->get('in_review', 0);
        $revision  = $counts->get('revision_required', 0);
        $accepted  = $counts->get('accepted', 0);
        $published = $counts->get('published', 0);
        $rejected  = $counts->get('rejected', 0);

        // Published bulan ini
        $publishedThisMonth = (clone $baseQuery)
            ->where('status', 'published')
            ->whereMonth('published_at', now()->month)
            ->whereYear('published_at', now()->year)
            ->count();

        // Trend 7 hari terakhir untuk sparkline
        $weeklyTrend = (clone $baseQuery)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $trendData = collect(range(6, 0))
            ->map(fn($d) => $weeklyTrend->get(now()->subDays($d)->toDateString(), 0))
            ->values()
            ->toArray();

        // ── SUPER ADMIN & ADMIN ────────────────────────────────────────────
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return [
                Stat::make('Total Publications', number_format($total))
                    ->description('All publications in system')
                    ->descriptionIcon('heroicon-o-document-text')
                    ->chart($trendData)
                    ->color('primary'),

                Stat::make('Needs Attention', number_format($submitted + $revision))
                    ->description($submitted . ' submitted · ' . $revision . ' need revision')
                    ->descriptionIcon('heroicon-o-exclamation-triangle')
                    ->color($submitted + $revision > 0 ? 'warning' : 'success'),

                Stat::make('In Review', number_format($inReview))
                    ->description('Currently being reviewed')
                    ->descriptionIcon('heroicon-o-magnifying-glass')
                    ->color('info'),

                Stat::make('Accepted', number_format($accepted))
                    ->description('Ready to be published')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success'),

                Stat::make('Published', number_format($published))
                    ->description($publishedThisMonth . ' new this month')
                    ->descriptionIcon('heroicon-o-globe-alt')
                    ->chart($trendData)
                    ->color('success'),

                Stat::make('Rejected', number_format($rejected))
                    ->description('Total rejected')
                    ->descriptionIcon('heroicon-o-x-circle')
                    ->color($rejected > 0 ? 'danger' : 'gray'),
            ];
        }

        // ── EDITOR ────────────────────────────────────────────────────────
        if ($user->hasRole('editor')) {
            return [
                Stat::make('Total Publications', number_format($total))
                    ->description('All publications')
                    ->descriptionIcon('heroicon-o-document-text')
                    ->chart($trendData)
                    ->color('primary'),

                Stat::make('Submitted', number_format($submitted))
                    ->description('Waiting for review')
                    ->descriptionIcon('heroicon-o-paper-airplane')
                    ->color($submitted > 0 ? 'warning' : 'gray'),

                Stat::make('In Review', number_format($inReview))
                    ->description('Currently in review')
                    ->descriptionIcon('heroicon-o-magnifying-glass')
                    ->color('info'),

                Stat::make('Revision Required', number_format($revision))
                    ->description('Authors need to revise')
                    ->descriptionIcon('heroicon-o-pencil')
                    ->color($revision > 0 ? 'danger' : 'gray'),

                Stat::make('Accepted', number_format($accepted))
                    ->description('Ready to publish')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success'),

                Stat::make('Published This Month', number_format($publishedThisMonth))
                    ->description('Of ' . $published . ' total published')
                    ->descriptionIcon('heroicon-o-globe-alt')
                    ->color('success'),
            ];
        }

        // ── REVIEWER ──────────────────────────────────────────────────────
        if ($user->hasRole('reviewer')) {
            $pendingReview = Publication::whereIn('status', ['submitted', 'in_review'])->count();

            return [
                Stat::make('Pending Review', number_format($pendingReview))
                    ->description($submitted . ' submitted · ' . $inReview . ' in review')
                    ->descriptionIcon('heroicon-o-clock')
                    ->color($pendingReview > 0 ? 'warning' : 'success'),

                Stat::make('In Review', number_format($inReview))
                    ->description('Currently being reviewed')
                    ->descriptionIcon('heroicon-o-magnifying-glass')
                    ->color('info'),

                Stat::make('Revision Required', number_format($revision))
                    ->description('Waiting for author revision')
                    ->descriptionIcon('heroicon-o-pencil-square')
                    ->color($revision > 0 ? 'danger' : 'gray'),

                Stat::make('Accepted', number_format($accepted))
                    ->description('Reviewed & accepted')
                    ->descriptionIcon('heroicon-o-check-badge')
                    ->color('success'),
            ];
        }

        // ── AUTHOR ────────────────────────────────────────────────────────
        if ($user->hasRole('author')) {
            return [
                Stat::make('My Publications', number_format($total))
                    ->description('Total you have submitted')
                    ->descriptionIcon('heroicon-o-document-text')
                    ->chart($trendData)
                    ->color('primary'),

                Stat::make('Draft', number_format($draft))
                    ->description('Not yet submitted')
                    ->descriptionIcon('heroicon-o-pencil')
                    ->color('gray'),

                Stat::make('In Progress', number_format($submitted + $inReview))
                    ->description($submitted . ' submitted · ' . $inReview . ' in review')
                    ->descriptionIcon('heroicon-o-arrow-path')
                    ->color('info'),

                Stat::make('Needs Revision', number_format($revision))
                    ->description('Please revise and resubmit')
                    ->descriptionIcon('heroicon-o-exclamation-circle')
                    ->color($revision > 0 ? 'danger' : 'gray'),

                Stat::make('Published', number_format($published))
                    ->description($publishedThisMonth . ' published this month')
                    ->descriptionIcon('heroicon-o-globe-alt')
                    ->color('success'),

                Stat::make('Rejected', number_format($rejected))
                    ->description('Can be revised & resubmitted')
                    ->descriptionIcon('heroicon-o-x-circle')
                    ->color($rejected > 0 ? 'danger' : 'gray'),
            ];
        }

        // Fallback
        return [
            Stat::make('Total Publications', number_format($total))
                ->description('All publications')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),
        ];
    }
}
