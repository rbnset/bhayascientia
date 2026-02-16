<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PublicationHelperTrait;
use App\Models\Publication;
use App\Models\Author;
use App\Models\PublicationType;
use App\Models\Category;
use Illuminate\Http\Request;

class PublicationBrowseController extends Controller
{
    use PublicationHelperTrait;

    public function index(Request $request)
    {
        $selectedType = $request->get('type', 'semua');
        $searchQuery = $request->get('search', '');
        $filterSort = $request->get('sort', 'latest');

        // Get publication types
        $publicationTypes = $this->getPublicationTypes();

        // Get categories & years
        $categories = Category::orderBy('name')->get();
        $years = Publication::selectRaw('YEAR(published_at) as year')
            ->whereNotNull('published_at')
            ->where('status', 'published')
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year');

        // Latest Publications
        $latestPublications = $this->getLatestPublications($selectedType);

        // Best Authors
        $bestAuthors = $this->getBestAuthors($selectedType);

        // Popular Publications
        $popularPublications = $this->getPopularPublications($selectedType);

        // Featured Publication
        $featuredPublication = $this->getFeaturedPublication($selectedType);

        return view('publications.browse', compact(
            'selectedType',
            'searchQuery',
            'filterSort',
            'publicationTypes',
            'categories',
            'years',
            'latestPublications',
            'bestAuthors',
            'popularPublications',
            'featuredPublication'
        ));
    }

    /**
     * ✅ Get latest publications dengan mapping yang benar
     */
    private function getLatestPublications($type)
    {
        $query = Publication::with(['authors.user', 'categories', 'publicationType']) // ✅ Eager load publicationType
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($type !== 'semua') {
            $query->whereHas('publicationType', function ($q) use ($type) {
                $q->where('slug', $type);
            });
        }

        return $query->latest('published_at')
            ->take(10)
            ->get()
            ->map(function ($publication) {
                // ✅ Get publication type
                $pubType = 'Publikasi';
                if ($publication->publicationType) {
                    $pubType = $publication->publicationType->name;
                }

                return [
                    'title' => $publication->title,
                    'slug' => $publication->slug,
                    'cover_url' => $this->getCoverUrl($publication),
                    'category' => $publication->categories->first()?->name ?? 'Umum',
                    'publication_type' => $pubType, // ✅ ADDED
                    'formatted_date' => $publication->published_at?->locale('id_ID')->isoFormat('D MMMM YYYY'),
                    'status' => $publication->status,
                    'authors' => $publication->authors->map(function ($author) {
                        return [
                            'name' => $author->name,
                            'photo' => $author->photo_url,
                            'initials' => $author->initials,
                        ];
                    })->toArray(),
                    'total_authors' => $publication->authors->count(),
                    'detail_url' => route('publikasi.show', $publication->slug),
                ];
            });
    }

    /**
     * ✅ Get best authors dengan mapping yang benar
     */
    private function getBestAuthors($type)
    {
        $query = Author::withCount(['publications' => function ($q) use ($type) {
            $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());

            if ($type !== 'semua') {
                $q->whereHas('publicationType', function ($query) use ($type) {
                    $query->where('slug', $type);
                });
            }
        }])
            ->with('user')
            ->having('publications_count', '>', 0)
            ->orderByDesc('publications_count');

        return $query->take(8)
            ->get()
            ->map(function ($author) {
                return [
                    'id' => $author->id,
                    'name' => $author->name,
                    'photo_url' => $author->photo_url,
                    'initials' => $author->initials,
                    'affiliation' => $author->affiliation ?? ($author->user ? $author->user->job_title : '-'),
                    'short_bio' => $author->short_bio,
                    'publications_count' => $author->publications_count,
                ];
            });
    }

    /**
     * ✅ Get popular publications
     */
    private function getPopularPublications($type)
    {
        $query = Publication::with(['authors.user', 'categories', 'downloadLogs', 'publicationType']) // ✅ Eager load publicationType
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($type !== 'semua') {
            $query->whereHas('publicationType', function ($q) use ($type) {
                $q->where('slug', $type);
            });
        }

        return $query->withCount('downloadLogs')
            ->orderByDesc('download_logs_count')
            ->take(6)
            ->get()
            ->map(function ($publication) {
                // ✅ Get publication type
                $pubType = 'Publikasi';
                if ($publication->publicationType) {
                    $pubType = $publication->publicationType->name;
                }

                return [
                    'title' => $publication->title,
                    'slug' => $publication->slug,
                    'cover_url' => $this->getCoverUrl($publication),
                    'category' => $publication->categories->first()?->name ?? 'Umum',
                    'publication_type' => $pubType, // ✅ ADDED
                    'formatted_date' => $publication->published_at?->locale('id_ID')->isoFormat('D MMMM YYYY'),
                    'authors' => $publication->authors->map(function ($author) {
                        return [
                            'name' => $author->name,
                            'photo' => $author->photo_url,
                            'initials' => $author->initials,
                        ];
                    })->toArray(),
                    'total_authors' => $publication->authors->count(),
                    'detail_url' => route('publikasi.show', $publication->slug),
                    'download_count' => $publication->download_logs_count,
                    'views_count' => $publication->views_count ?? 0, // ✅ ADDED
                ];
            });
    }

    /**
     * ✅ Get featured publication
     */
    private function getFeaturedPublication($type)
    {
        $query = Publication::with(['authors.user', 'categories', 'publicationType']) // ✅ Eager load publicationType
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('is_featured', true); // Assuming you have this column

        if ($type !== 'semua') {
            $query->whereHas('publicationType', function ($q) use ($type) {
                $q->where('slug', $type);
            });
        }

        $publication = $query->first();

        if (!$publication) {
            return null;
        }

        // ✅ Get publication type
        $pubType = 'Publikasi';
        if ($publication->publicationType) {
            $pubType = $publication->publicationType->name;
        }

        return [
            'title' => $publication->title,
            'slug' => $publication->slug,
            'cover_url' => $this->getCoverUrl($publication),
            'category' => $publication->categories->first()?->name ?? 'Umum',
            'publication_type' => $pubType, // ✅ ADDED
            'type' => $pubType, // ✅ Backward compatibility
            'abstract' => \Illuminate\Support\Str::limit($publication->abstract, 120), // ✅ ADDED
            'formatted_date' => $publication->published_at?->locale('id_ID')->isoFormat('D MMMM YYYY'),
            'authors' => $publication->authors->map(function ($author) {
                return [
                    'name' => $author->name,
                    'photo' => $author->photo_url,
                    'initials' => $author->initials,
                ];
            })->toArray(),
            'total_authors' => $publication->authors->count(),
            'detail_url' => route('publikasi.show', $publication->slug),
            'download_count' => $publication->download_logs_count ?? 0, // ✅ ADDED
        ];
    }

    /**
     * Get publication types with counts
     */
    private function getPublicationTypes()
    {
        return PublicationType::withCount(['publications' => function ($q) {
            $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        }])
            ->where('is_active', true) // ✅ ADDED: Only active types
            ->orderBy('name')
            ->get();
    }
}
