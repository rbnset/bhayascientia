<?php

namespace App\Services;

use App\Models\Author;
use Illuminate\Support\Collection;

class AuthorService
{
    /**
     * Format author data untuk view card
     */
    public function formatAuthorForCard(Author $author): array
    {
        return [
            'id' => $author->id,
            'name' => $author->name,
            'avatar' => $author->photo_url,
            'initials' => $author->initials,
            'publication_count' => $author->publications_count ?? 0,
            'profile_url' => route('author.show', $author->id),
            'verified' => $author->user_id !== null,
            'specialty' => $author->affiliation ?? $author->short_bio ?? null,
        ];
    }

    /**
     * Format collection authors untuk view dengan limit maksimal
     */
    public function formatAuthorsForCards(Collection $authors, int $limit = 6): Collection
    {
        return $authors->take($limit)
            ->map(fn($author) => $this->formatAuthorForCard($author));
    }

    /**
     * Format author untuk publication detail
     */
    public function formatAuthorForPublication(Author $author): array
    {
        return [
            'id' => $author->id,
            'name' => $author->name,
            'initials' => $author->initials,
            'photo' => $author->photo_url,
            'affiliation' => $author->affiliation ?? $author->user?->organization ?? '-',
            'is_corresponding' => $author->pivot->is_corresponding ?? false,
        ];
    }

    /**
     * Format authors untuk publication dengan limit
     */
    public function formatAuthorsForPublication(Collection $authors, int $limit = 6): Collection
    {
        return $authors->take($limit)
            ->map(fn($author) => $this->formatAuthorForPublication($author));
    }
}
