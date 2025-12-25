<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPublications extends ListRecords
{
    protected static string $resource = PublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'draft' => Tab::make('Draft')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'draft')),

            'submitted' => Tab::make('Submitted')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'submitted')),

            'in_review' => Tab::make('In Review')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'in_review')),

            'revision_required' => Tab::make('Revision Required')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'revision_required')),

            'accepted' => Tab::make('Accepted')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'accepted')),

            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'rejected')),

            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'published')),
        ];
    }
}
