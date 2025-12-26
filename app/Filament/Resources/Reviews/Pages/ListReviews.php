<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Author tidak boleh membuat review
            CreateAction::make()
                ->visible(fn() => ! (auth()->user()?->hasRole('author'))),
        ];
    }

    /**
     * Reviewer: hanya review miliknya (reviewer_id).
     * Author: hanya review untuk publication yang memiliki authors.user_id = auth user.
     */
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $user = auth()->user();

        if ($user?->hasRole('reviewer')) {
            return $query->where('reviewer_id', $user->id);
        }

        if ($user?->hasRole('author')) {
            return $query->whereHas('publicationVersion.publication.authors', function (Builder $q) use ($user) {
                $q->where('authors.user_id', $user->id);
            });
        }

        return $query;
    }
}
