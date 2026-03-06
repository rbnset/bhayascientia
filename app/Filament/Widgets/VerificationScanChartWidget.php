<?php

namespace App\Filament\Widgets;

use App\Models\DocumentVerification;
use Filament\Widgets\ChartWidget;

class VerificationScanChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): string
    {
        return 'Aktivitas Scan Verifikasi';
    }

    public function getDescription(): ?string
    {
        return 'Total scan dokumen 30 hari terakhir';
    }

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    protected function getMaxHeight(): ?string
    {
        return '280px';
    }

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn($d) => now()->subDays($d)->startOfDay());

        $raw = DocumentVerification::selectRaw('DATE(last_scanned_at) as date, SUM(scan_count) as total')
            ->where('last_scanned_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        return [
            'datasets' => [[
                'label'               => 'Scan',
                'data'                => $days->map(fn($d) => $raw[$d->toDateString()] ?? 0)->toArray(),
                'backgroundColor'     => 'rgba(99, 102, 241, 0.15)',
                'borderColor'         => 'rgb(99, 102, 241)',
                'borderWidth'         => 2,
                'tension'             => 0.4,
                'fill'                => true,
                'pointRadius'         => 3,
                'pointBackgroundColor' => 'rgb(99, 102, 241)',
            ]],
            'labels' => $days->map(fn($d) => $d->format('d M'))->toArray(),
        ];
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
