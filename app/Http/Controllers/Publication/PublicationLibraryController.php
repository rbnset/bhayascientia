<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PublicationHelperTrait;
use App\Models\PublicationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicationLibraryController extends Controller
{
    use PublicationHelperTrait;

    public function library(Request $request)
    {
        $activeTab = $request->query('tab', 'favorites');
        $search = $request->query('search');
        $typeFilter = $request->query('type');

        if (!in_array($activeTab, ['favorites', 'history', 'saved'])) {
            $activeTab = 'favorites';
        }

        // ✅ Get only ACTIVE publication types for filter
        $publicationTypes = PublicationType::where('is_active', true)
            ->orderBy('name')
            ->get();

        if (!Auth::check()) {
            return view('pages.publication.library', [
                'publications' => collect(),
                'stats' => ['favorites' => 0, 'history' => 0, 'saved' => 0],
                'activeTab' => $activeTab,
                'requiresLogin' => true,
                'user' => null,
                'publicationTypes' => $publicationTypes,
                'search' => $search,
                'typeFilter' => $typeFilter,
            ]);
        }

        $user = Auth::user();

        // ✅ Stats (filtered by type if selected, only active types)
        $stats = [
            'favorites' => $user->favoritePublications()
                ->whereHas('publicationType', fn($q) => $q->where('is_active', true))
                ->when($typeFilter, fn($q) => $q->where('publication_type_id', $typeFilter))
                ->count(),
            'history' => $user->readPublications()
                ->whereHas('publicationType', fn($q) => $q->where('is_active', true))
                ->when($typeFilter, fn($q) => $q->where('publication_type_id', $typeFilter))
                ->count(),
            'saved' => $user->savedPublications()
                ->whereHas('publicationType', fn($q) => $q->where('is_active', true))
                ->when($typeFilter, fn($q) => $q->where('publication_type_id', $typeFilter))
                ->count(),
        ];

        $publications = collect();

        switch ($activeTab) {
            case 'favorites':
                $query = $user->favoritePublications()
                    ->with(['authors.user', 'publicationType', 'categories'])
                    ->whereHas('publicationType', fn($q) => $q->where('is_active', true)) // ✅ Only active types
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());

                // Apply search filter
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', '%' . $search . '%')
                            ->orWhere('abstract', 'like', '%' . $search . '%')
                            ->orWhereHas('authors', function ($authorQuery) use ($search) {
                                $authorQuery->where('name', 'like', '%' . $search . '%');
                            });
                    });
                }

                // Apply type filter
                if ($typeFilter) {
                    $query->where('publication_type_id', $typeFilter);
                }

                $publications = $query->orderBy('user_favorite_publications.created_at', 'desc')->get();
                break;

            case 'history':
                $query = $user->readPublications()
                    ->with(['authors.user', 'publicationType', 'categories'])
                    ->whereHas('publicationType', fn($q) => $q->where('is_active', true)) // ✅ Only active types
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());

                // Apply search filter
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', '%' . $search . '%')
                            ->orWhere('abstract', 'like', '%' . $search . '%')
                            ->orWhereHas('authors', function ($authorQuery) use ($search) {
                                $authorQuery->where('name', 'like', '%' . $search . '%');
                            });
                    });
                }

                // Apply type filter
                if ($typeFilter) {
                    $query->where('publication_type_id', $typeFilter);
                }

                $publications = $query->orderBy('user_read_publications.last_read_at', 'desc')->get();
                break;

            case 'saved':
                $query = $user->savedPublications()
                    ->with(['authors.user', 'publicationType', 'categories'])
                    ->whereHas('publicationType', fn($q) => $q->where('is_active', true)) // ✅ Only active types
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());

                // Apply search filter
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', '%' . $search . '%')
                            ->orWhere('abstract', 'like', '%' . $search . '%')
                            ->orWhereHas('authors', function ($authorQuery) use ($search) {
                                $authorQuery->where('name', 'like', '%' . $search . '%');
                            });
                    });
                }

                // Apply type filter
                if ($typeFilter) {
                    $query->where('publication_type_id', $typeFilter);
                }

                $publications = $query->orderBy('user_saved_publications.created_at', 'desc')->get();
                break;
        }

        // ✅ Map publications (filter out any with inactive types as extra safety)
        $publications = $publications->filter(function ($pub) {
            return $pub->publicationType && $pub->publicationType->is_active;
        })->map(function ($pub) use ($activeTab) {
            $authorsText = $pub->authors->take(2)->pluck('name')->implode(', ');
            if ($pub->authors->count() > 2) {
                $authorsText .= ' +' . ($pub->authors->count() - 2) . ' lainnya';
            }

            $actionTime = match ($activeTab) {
                'favorites' => $pub->pivot?->created_at
                    ? (is_string($pub->pivot->created_at)
                        ? \Carbon\Carbon::parse($pub->pivot->created_at)->diffForHumans()
                        : $pub->pivot->created_at->diffForHumans()
                    )
                    : 'Ditambahkan baru-baru ini',
                'history' => $pub->pivot?->last_read_at
                    ? (is_string($pub->pivot->last_read_at)
                        ? \Carbon\Carbon::parse($pub->pivot->last_read_at)->diffForHumans()
                        : $pub->pivot->last_read_at->diffForHumans()
                    )
                    : 'Dibaca baru-baru ini',
                'saved' => $pub->pivot?->created_at
                    ? (is_string($pub->pivot->created_at)
                        ? \Carbon\Carbon::parse($pub->pivot->created_at)->diffForHumans()
                        : $pub->pivot->created_at->diffForHumans()
                    )
                    : 'Disimpan baru-baru ini',
                default => 'Unknown'
            };

            return [
                'id' => $pub->id,
                'title' => $pub->title,
                'slug' => $pub->slug,
                'cover_url' => $this->getCoverUrl($pub),
                'category' => $pub->category_name,
                'formatted_date' => $pub->formatted_date,
                'type' => $pub->publicationType->name ?? 'Publikasi',
                'type_id' => $pub->publication_type_id,
                'type_active' => $pub->publicationType->is_active ?? false,
                'detail_url' => route('publikasi.show', $pub->slug),
                'action_time' => $actionTime,
                'authors_text' => $authorsText ?: 'Unknown',
                'authors' => $pub->authors->take(6)->map(fn($author) => [
                    'id' => $author->id,
                    'name' => $author->name,
                    'photo' => $author->photo_url,
                    'initials' => $author->initials,
                ])->toArray(),
                'total_authors' => $pub->authors->count(),
            ];
        });

        return view('pages.publication.library', compact(
            'publications',
            'stats',
            'activeTab',
            'publicationTypes',
            'search',
            'typeFilter'
        ));
    }
}
