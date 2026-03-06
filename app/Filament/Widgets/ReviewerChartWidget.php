<?php
// app/Filament/Widgets/ReviewerChartWidget.php

namespace App\Filament\Widgets;

use App\Models\Review;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ReviewerChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Aktivitas Review Saya';
    }

    public function getDescription(): ?string
    {
        return 'Keputusan review per bulan tahun ini';
    }

    protected function getMaxHeight(): ?string
    {
        return '260px';
    }

    protected function getData(): array
    {
        $userId = auth()->id();

        $labels = collect(range(1, 12))
            ->map(fn($m) => Carbon::create(now()->year, $m)->translatedFormat('M'))
            ->toArray();

        $getMonthly = function (?string $decision) use ($userId): array {
            $raw = Review::where('reviewer_id', $userId)
                ->when($decision, fn($q) => $q->where('decision', $decision))
                ->when(! $decision, fn($q) => $q->whereNull('decision'))
                ->whereYear('created_at', now()->year)
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray();

            return collect(range(1, 12))->map(fn($m) => $raw[$m] ?? 0)->toArray();
        };

        return [
            'datasets' => [
                [
                    'label'           => 'Diterima',
                    'data'            => $getMonthly('accepted'),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'borderColor'     => 'rgb(16, 185, 129)',
                    'borderWidth'     => 2,
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Perlu Revisi',
                    'data'            => $getMonthly('revision_required'),
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'borderColor'     => 'rgb(245, 158, 11)',
                    'borderWidth'     => 2,
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Ditolak',
                    'data'            => $getMonthly('rejected'),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.15)',
                    'borderColor'     => 'rgb(239, 68, 68)',
                    'borderWidth'     => 2,
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('reviewer') ?? false;
    }
}
