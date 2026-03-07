<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\Publications\Widgets\PublicationStatsWidget;
use App\Models\Publication;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListPublications extends ListRecords
{
    protected static string $resource = PublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $user = auth()->user();

        if ($user?->hasRole('author')) {
            $query->whereHas('authors', function (Builder $q) use ($user) {
                $q->where('authors.user_id', $user->id);
            });
        }

        return $query;
    }

    public function getTabs(): array
    {
        $user = auth()->user();

        // Hitung badge per status, scope ke author jika perlu
        $baseQuery = Publication::query();
        if ($user?->hasRole('author')) {
            $baseQuery->whereHas('authors', fn($q) => $q->where('authors.user_id', $user->id));
        }

        $counts = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'all' => Tab::make('All')
                ->icon('heroicon-o-document-text')
                ->badge($counts->sum() ?: null),

            'draft' => Tab::make('Draft')
                ->icon('heroicon-o-pencil')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'draft'))
                ->badge($counts->get('draft') ?: null),

            'submitted' => Tab::make('Submitted')
                ->icon('heroicon-o-paper-airplane')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'submitted'))
                ->badge($counts->get('submitted') ?: null)
                ->badgeColor('warning'),

            'in_review' => Tab::make('In Review')
                ->icon('heroicon-o-magnifying-glass')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'in_review'))
                ->badge($counts->get('in_review') ?: null)
                ->badgeColor('info'),

            'revision_required' => Tab::make('Revision Required')
                ->icon('heroicon-o-exclamation-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'revision_required'))
                ->badge($counts->get('revision_required') ?: null)
                ->badgeColor('danger'),

            'accepted' => Tab::make('Accepted')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'accepted'))
                ->badge($counts->get('accepted') ?: null)
                ->badgeColor('success'),

            'rejected' => Tab::make('Rejected')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'rejected'))
                ->badge($counts->get('rejected') ?: null)
                ->badgeColor('danger'),

            'published' => Tab::make('Published')
                ->icon('heroicon-o-globe-alt')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'published'))
                ->badge($counts->get('published') ?: null)
                ->badgeColor('success'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PublicationStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'lg'      => 3,
        ];
    }
}
