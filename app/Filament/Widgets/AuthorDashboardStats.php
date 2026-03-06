<?php
// app/Filament/Widgets/AuthorDashboardStats.php

namespace App\Filament\Widgets;

use App\Models\Publication;
use App\Models\Review;
use App\Models\DocumentVerification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class AuthorDashboardStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    protected function getStats(): array
    {
        $userId = auth()->id();

        $myPub = Publication::query()
            ->whereHas('authors', fn(Builder $q) => $q->where('authors.user_id', $userId));

        $total      = (clone $myPub)->count();
        $draft      = (clone $myPub)->where('status', 'draft')->count();
        $submitted  = (clone $myPub)->where('status', 'submitted')->count();
        $inReview   = (clone $myPub)->where('status', 'in_review')->count();
        $revision   = (clone $myPub)->where('status', 'revision_required')->count();
        $accepted   = (clone $myPub)->where('status', 'accepted')->count();
        $rejected   = (clone $myPub)->where('status', 'rejected')->count();
        $published  = (clone $myPub)->where('status', 'published')->count();

        // Versi terbaru dari publikasi saya
        $myVersionIds = (clone $myPub)
            ->with('versions')
            ->get()
            ->pluck('versions')
            ->flatten()
            ->pluck('id');

        // Total review yang diterima
        $totalReviews = Review::whereIn('publication_version_id', $myVersionIds)->count();
        $revisionReviews = Review::whereIn('publication_version_id', $myVersionIds)
            ->where('decision', 'revision_required')->count();

        // Scan verifikasi dokumen saya
        $myCodes = DocumentVerification::whereHas(
            'publicationVersion.publication',
            fn($q) => $q->whereHas('authors', fn($q2) => $q2->where('authors.user_id', $userId))
        );
        $totalScans = (clone $myCodes)->sum('scan_count');
        $scanToday  = (clone $myCodes)->whereDate('last_scanned_at', today())->sum('scan_count');

        // Trend publikasi saya (12 bulan)
        $trend = collect(range(1, 12))->map(function ($m) use ($userId) {
            return Publication::whereHas('authors', fn($q) => $q->where('authors.user_id', $userId))
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', $m)
                ->count();
        })->toArray();

        return [
            Stat::make('Total Publikasi Saya', $total)
                ->description($published . ' diterbitkan · ' . $accepted . ' diterima')
                ->descriptionIcon('heroicon-m-document-text')
                ->chart($trend)
                ->color('primary'),

            Stat::make('Perlu Tindakan', $revision + $draft)
                ->description($revision . ' perlu revisi · ' . $draft . ' masih draft')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($revision > 0 ? 'danger' : 'warning'),

            Stat::make('Sedang Diproses', $submitted + $inReview)
                ->description($submitted . ' menunggu review · ' . $inReview . ' sedang direview')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make('Review Diterima', $totalReviews)
                ->description($revisionReviews . ' minta revisi')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($revisionReviews > 0 ? 'warning' : 'success'),

            Stat::make('Ditolak', $rejected)
                ->description($rejected > 0 ? 'Hubungi editor untuk info lebih lanjut' : 'Tidak ada yang ditolak')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($rejected > 0 ? 'danger' : 'gray'),

            Stat::make('Scan Verifikasi', $totalScans)
                ->description($scanToday . ' scan hari ini')
                ->descriptionIcon('heroicon-m-qr-code')
                ->color($scanToday > 0 ? 'success' : 'gray'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('author') ?? false;
    }
}
