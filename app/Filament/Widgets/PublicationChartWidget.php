<?php

namespace App\Filament\Widgets;

use App\Models\Publication;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PublicationChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Tren Publikasi';
    }

    public function getDescription(): ?string
    {
        return 'Jumlah publikasi masuk per bulan (tahun ini)';
    }

    protected function getPollingInterval(): ?string
    {
        return '60s';
    }

    protected function getMaxHeight(): ?string
    {
        return '280px';
    }

    protected function getData(): array
    {
        $submitted = $this->getMonthlyCount('submitted');
        $published = $this->getMonthlyCount('published');
        $revision  = $this->getMonthlyCount('revision_required');

        $labels = collect(range(1, 12))
            ->map(fn($m) => Carbon::create(now()->year, $m, 1)->translatedFormat('M'))
            ->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Submitted',
                    'data'            => $submitted,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'borderColor'     => 'rgb(245, 158, 11)',
                    'borderWidth'     => 2,
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Published',
                    'data'            => $published,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'borderColor'     => 'rgb(16, 185, 129)',
                    'borderWidth'     => 2,
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Revision Required',
                    'data'            => $revision,
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

    private function getMonthlyCount(string $status): array
    {
        $raw = Publication::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->where('status', $status)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return collect(range(1, 12))->map(fn($m) => $raw[$m] ?? 0)->toArray();
    }

    protected function getType(): string
    {
        return 'line';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin']) ?? false;
    }
}
