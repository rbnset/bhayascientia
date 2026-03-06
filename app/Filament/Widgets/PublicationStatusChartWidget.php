<?php

namespace App\Filament\Widgets;

use App\Models\Publication;
use Filament\Widgets\ChartWidget;

class PublicationStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): string
    {
        return 'Distribusi Status Publikasi';
    }

    public function getDescription(): ?string
    {
        return 'Proporsi setiap status saat ini';
    }

    protected function getMaxHeight(): ?string
    {
        return '280px';
    }

    protected function getData(): array
    {
        $statuses = [
            'draft'             => ['label' => 'Draft',        'color' => 'rgba(156, 163, 175, 0.8)'],
            'submitted'         => ['label' => 'Submitted',    'color' => 'rgba(245, 158, 11, 0.8)'],
            'in_review'         => ['label' => 'In Review',    'color' => 'rgba(59, 130, 246, 0.8)'],
            'revision_required' => ['label' => 'Perlu Revisi', 'color' => 'rgba(239, 68, 68, 0.8)'],
            'accepted'          => ['label' => 'Diterima',     'color' => 'rgba(34, 197, 94, 0.8)'],
            'rejected'          => ['label' => 'Ditolak',      'color' => 'rgba(220, 38, 38, 0.8)'],
            'published'         => ['label' => 'Published',    'color' => 'rgba(16, 185, 129, 0.8)'],
        ];

        $counts = Publication::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $labels = [];
        $data   = [];
        $colors = [];

        foreach ($statuses as $key => $info) {
            if (($counts[$key] ?? 0) > 0) {
                $labels[] = $info['label'];
                $data[]   = $counts[$key];
                $colors[] = $info['color'];
            }
        }

        return [
            'datasets' => [[
                'data'            => $data,
                'backgroundColor' => $colors,
                'borderColor'     => array_fill(0, count($colors), 'rgba(255,255,255,0.8)'),
                'borderWidth'     => 2,
                'hoverOffset'     => 6,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin']) ?? false;
    }
}
