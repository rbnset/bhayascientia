<?php
// app/Filament/Widgets/AuthorPublicationChartWidget.php

namespace App\Filament\Widgets;

use App\Models\Publication;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AuthorPublicationChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Publikasi Saya';
    }

    public function getDescription(): ?string
    {
        return 'Status publikasi saya per bulan tahun ini';
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

        $getMonthly = function (string $status) use ($userId): array {
            $raw = Publication::whereHas('authors', fn($q) => $q->where('authors.user_id', $userId))
                ->where('status', $status)
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
                    'label'           => 'Submitted',
                    'data'            => $getMonthly('submitted'),
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'borderColor'     => 'rgb(245, 158, 11)',
                    'borderWidth'     => 2,
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Published',
                    'data'            => $getMonthly('published'),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'borderColor'     => 'rgb(16, 185, 129)',
                    'borderWidth'     => 2,
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Revision',
                    'data'            => $getMonthly('revision_required'),
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
        return 'line';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('author') ?? false;
    }
}
