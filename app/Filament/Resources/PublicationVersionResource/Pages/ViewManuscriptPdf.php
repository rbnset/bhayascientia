<?php

namespace App\Filament\Resources\PublicationVersionResource\Pages;

use App\Filament\Resources\PublicationVersionResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class ViewManuscriptPdf extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PublicationVersionResource::class;

    protected string $view = 'filament.publication-versions.view-manuscript-pdf';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getHeading(): string
    {
        return 'Manuscript Viewer';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
