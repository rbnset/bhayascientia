<?php
// app/Filament/Widgets/AdminDashboardStats.php

namespace App\Filament\Widgets;

use App\Models\Author;
use App\Models\DocumentVerification;
use App\Models\Publication;
use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminDashboardStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    protected function getStats(): array
    {
        $totalPub      = Publication::count();
        $published     = Publication::where('status', 'published')->count();
        $submitted     = Publication::where('status', 'submitted')->count();
        $inReview      = Publication::where('status', 'in_review')->count();
        $revision      = Publication::where('status', 'revision_required')->count();
        $accepted      = Publication::where('status', 'accepted')->count();
        $rejected      = Publication::where('status', 'rejected')->count();

        $totalReview   = Review::count();
        $reviewAccepted = Review::where('decision', 'accepted')->count();
        $reviewRevision = Review::where('decision', 'revision_required')->count();

        $totalAuthors  = Author::count();
        $linkedAuthors = Author::whereNotNull('user_id')->count();

        $scanToday     = DocumentVerification::whereDate('last_scanned_at', today())->sum('scan_count');
        $scanTotal     = DocumentVerification::sum('scan_count');

        // Trend publikasi: bulan ini vs bulan lalu
        $pubThisMonth  = Publication::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();
        $pubLastMonth  = Publication::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)->count();
        $pubTrend      = collect(
            Publication::selectRaw('COUNT(*) as count, MONTH(created_at) as month')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray()
        )->values()->toArray();

        return [
            Stat::make('Total Publikasi', $totalPub)
                ->description($pubThisMonth . ' baru bulan ini' . ($pubLastMonth > 0 ? ' · ' . ($pubThisMonth >= $pubLastMonth ? '↑' : '↓') . ' vs bulan lalu' : ''))
                ->descriptionIcon($pubThisMonth >= $pubLastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($pubTrend)
                ->color('primary'),

            Stat::make('Diterbitkan', $published)
                ->description($accepted . ' diterima · ' . $submitted . ' menunggu')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Perlu Perhatian', $revision + $submitted + $inReview)
                ->description($submitted . ' submitted · ' . $inReview . ' review · ' . $revision . ' revisi')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($revision > 0 ? 'danger' : 'warning'),

            Stat::make('Total Review', $totalReview)
                ->description($reviewAccepted . ' diterima · ' . $reviewRevision . ' minta revisi · ' . $rejected . ' ditolak')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('info'),

            Stat::make('Author Terdaftar', $totalAuthors)
                ->description($linkedAuthors . ' terhubung akun · ' . ($totalAuthors - $linkedAuthors) . ' eksternal')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('Scan Verifikasi', $scanTotal)
                ->description($scanToday . ' scan hari ini')
                ->descriptionIcon('heroicon-m-qr-code')
                ->color($scanToday > 0 ? 'success' : 'gray'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin']) ?? false;
    }
}
