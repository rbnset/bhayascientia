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
            'user_id' => $author->user_id,
            'name' => $author->name,
            'avatar' => $author->photo_url,
            'initials' => $author->initials,
            'publication_count' => $author->publications_count ?? 0,
            // ✅ FIXED: Gunakan route 'author.profile' dan gunakan user_id jika ada
            'profile_url' => route('author.profile', $author->user_id ?? $author->id),
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
        $userData = $author->user;

        return [
            'id' => $author->id,
            'user_id' => $author->user_id,
            'name' => $author->name,
            'initials' => $author->initials,
            'photo' => $author->photo_url,
            'photo_url' => $author->photo_url,
            'affiliation' => $author->affiliation ?? ($userData ? ($userData->job_title ?? $userData->organization ?? '-') : '-'),
            'bio' => $author->bio ?? ($userData ? $userData->bio : null),
            'short_bio' => $author->short_bio,
            'email' => $author->email,
            'is_corresponding' => $author->pivot->is_corresponding ?? false,
            // ✅ Add profile routing support
            'profile_type' => $author->user_id ? 'user' : 'author',
            'profile_id' => $author->user_id ?? $author->id,
            'profile_url' => route('author.profile', $author->user_id ?? $author->id),
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

    /**
     * Format author untuk author card display (homepage, search, etc)
     */
    public function formatAuthorCardData(Author $author): array
    {
        return [
            'id' => $author->id,
            'user_id' => $author->user_id,
            'name' => $author->name,
            'avatar' => $author->photo_url,
            'initials' => $author->initials,
            'affiliation' => $author->affiliation ?? $author->short_bio ?? 'Author',
            'publication_count' => $author->publications_count ?? 0,
            'profile_url' => route('author.profile', $author->user_id ?? $author->id),
            'verified' => $author->user_id !== null,
        ];
    }
}
