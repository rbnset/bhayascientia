<?php
// app/Filament/Resources/DocumentVerifications/Widgets/VerificationStatsWidget.php

namespace App\Filament\Resources\DocumentVerifications\Widgets;

use App\Models\DocumentVerification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class VerificationStatsWidget extends BaseWidget
{
    // Hapus $pollingInterval, gunakan method ini sebagai gantinya
    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    protected function getStats(): array
    {
        $totalScans  = DocumentVerification::sum('scan_count');
        $today       = DocumentVerification::whereDate('last_scanned_at', today())->count();
        $yesterday   = DocumentVerification::whereDate('last_scanned_at', today()->subDay())->count();
        $thisWeek    = DocumentVerification::whereBetween('last_scanned_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])->count();
        $uniqueCodes = DocumentVerification::count();
        $highScan    = DocumentVerification::where('scan_count', '>=', 10)->count();

        $trendDesc = $yesterday > 0
            ? ($today >= $yesterday ? '↑ ' : '↓ ') . 'vs kemarin (' . $yesterday . ')'
            : 'Belum ada data kemarin';

        return [
            Stat::make('Total Scan Keseluruhan', number_format($totalScans))
                ->description('Sepanjang waktu')
                ->descriptionIcon('heroicon-m-eye')
                ->color('primary'),

            Stat::make('Scan Hari Ini', $today)
                ->description($trendDesc)
                ->descriptionIcon($today >= $yesterday
                    ? 'heroicon-m-arrow-trending-up'
                    : 'heroicon-m-arrow-trending-down')
                ->color($today >= $yesterday ? 'success' : 'warning'),

            Stat::make('Scan Minggu Ini', $thisWeek)
                ->description('7 hari terakhir')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Kode Terdaftar', number_format($uniqueCodes))
                ->description($highScan . ' kode dengan scan tinggi (≥10)')
                ->descriptionIcon('heroicon-m-qr-code')
                ->color('warning'),
        ];
    }
}
