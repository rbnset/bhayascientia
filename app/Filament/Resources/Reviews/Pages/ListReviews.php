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
            CreateAction::make()
                ->visible(fn() => ! (auth()->user()?->hasRole('author'))),
        ];
    }

    /**
     * Reviewer : hanya review miliknya.
     * Author   : review via publicationVersion (biasa) ATAU via publication langsung (opini).
     */
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        $user  = auth()->user();

        if ($user?->hasRole('reviewer')) {
            return $query->where('reviewer_id', $user->id);
        }

        if ($user?->hasRole('author')) {
            return $query->where(function (Builder $q) use ($user) {
                // ── Review biasa: via publicationVersion → publication → authors ──
                $q->whereHas(
                    'publicationVersion.publication.authors',
                    fn(Builder $q2) => $q2->where('authors.user_id', $user->id)
                )
                    // ── Review opini: via publication langsung (publication_version_id null) ──
                    ->orWhere(function (Builder $q2) use ($user) {
                        $q2->whereNull('publication_version_id')
                            ->whereHas(
                                'publication.authors',
                                fn(Builder $q3) => $q3->where('authors.user_id', $user->id)
                            );
                    });
            });
        }

        return $query;
    }
}
