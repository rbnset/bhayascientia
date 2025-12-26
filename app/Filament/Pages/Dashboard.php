<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AuthorMyPublications;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\ReviewerMyQueue;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|array
    {
        return 3; // grid dashboard [web:451]
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\AdminDashboardStats::class,
            \App\Filament\Widgets\AuthorDashboardStats::class,
            \App\Filament\Widgets\ReviewerDashboardStats::class,

            \App\Filament\Widgets\ReviewerMyQueue::class,
            \App\Filament\Widgets\AuthorMyPublications::class,
        ];
    }
}
