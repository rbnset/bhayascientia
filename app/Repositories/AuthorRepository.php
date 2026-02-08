<?php

namespace App\Repositories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AuthorRepository
{
    /**
     * Base query untuk author dengan user
     */
    private function baseQuery(): Builder
    {
        return Author::query()->with('user');
    }

    /**
     * Get authors dengan jumlah publikasi untuk tipe tertentu
     */
    public function getWithPublicationCount(?string $typeSlug = null): Builder
    {
        return $this->baseQuery()
            ->withCount(['publications' => function ($query) use ($typeSlug) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());

                if ($typeSlug) {
                    $query->whereHas('publicationType', function ($q) use ($typeSlug) {
                        $q->where('slug', $typeSlug)
                            ->where('is_active', true);
                    });
                }
            }])
            ->having('publications_count', '>', 0);
    }

    /**
     * Get top authors berdasarkan jumlah publikasi
     */
    public function getTopAuthors(?string $typeSlug = null, int $limit = 6): Collection
    {
        return $this->getWithPublicationCount($typeSlug)
            ->orderByDesc('publications_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Find author dengan publikasinya
     */
    public function findWithPublications(int $id): ?Author
    {
        return Author::with([
            'user',
            'publications' => function ($query) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->with(['publicationType', 'categories'])
                    ->orderBy('published_at', 'desc');
            }
        ])->find($id);
    }

    /**
     * Get all authors dengan filter
     */
    public function getAll(array $filters = []): Collection
    {
        $query = $this->baseQuery();

        // Filter by name
        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        // Filter by has publications
        if (!empty($filters['has_publications'])) {
            $query->has('publications');
        }

        return $query->orderBy('name')->get();
    }
}
