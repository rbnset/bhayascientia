<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PublicationHelperTrait;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Category;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicationBrowseController extends Controller
{
    use PublicationHelperTrait;

    public function browse(Request $request)
    {
        $selectedType = $request->get('type', 'all');
        $filterCategory = $request->get('category');
        $filterYear = $request->get('year');
        $filterSort = $request->get('sort', 'latest');
        $perPage = $request->get('per_page', 12); // ✅ Fixed typo: perpage -> per_page

        // Get publication types
        $publicationTypes = PublicationType::where('is_active', true)
            ->withCount(['publications' => function ($q) {
                $q->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            }])
            ->orderBy('name')
            ->get();

        // Base query
        $query = Publication::with([
            'publicationType',
            'categories',
            'authors.user', // ✅ Eager load authors with user
            'versions',
            'viewLogs',
            'downloadLogs'
        ])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        // Filter by type
        if ($selectedType !== 'all') {
            $query->whereHas('publicationType', function ($q) use ($selectedType) {
                $q->where('slug', $selectedType);
            });
        }

        // Filter by category
        if ($filterCategory) {
            $query->whereHas('categories', function ($q) use ($filterCategory) {
                $q->where('slug', $filterCategory);
            });
        }

        // Filter by year
        if ($filterYear) {
            $query->whereYear('published_at', $filterYear);
        }

        // Sorting
        switch ($filterSort) {
            case 'popular':
                $query->withCount('downloadLogs')->orderByDesc('download_logs_count'); // ✅ Changed to downloadLogs
                break;
            case 'oldest':
                $query->orderBy('published_at', 'asc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('published_at', 'desc');
                break;
        }

        $publications = $query->paginate($perPage)->withQueryString();

        // Get categories with publication count
        $categories = Category::whereHas('publications', function ($q) {
            $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        })
            ->withCount(['publications' => function ($q) {
                $q->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            }])
            ->having('publications_count', '>', 0)
            ->orderByDesc('publications_count')
            ->get();

        // Get available years
        $years = Publication::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->selectRaw('YEAR(published_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        // ✅ Format publications for view
        $formattedPublications = $publications->map(function ($publication) {
            $category = $publication->categories->first();

            // ✅ Get publication type with fallback
            $pubType = 'Publikasi';
            if ($publication->publicationType) {
                $pubType = $publication->publicationType->name;
            }

            // ✅ Get cover URL
            $coverUrl = $this->getCoverUrl($publication);

            return [
                'id' => $publication->id,
                'title' => $publication->title,
                'slug' => $publication->slug,
                'abstract' => $publication->abstract ? Str::limit($publication->abstract, 150) : 'No abstract available',
                'cover_url' => $coverUrl, // Bisa null
                'category' => $category ? $category->name : 'Uncategorized',
                'category_slug' => $category ? $category->slug : null,
                'publication_type' => $pubType, // ✅ ADDED: Key name yang benar
                'type' => $pubType, // ✅ Backward compatibility
                'type_slug' => $publication->publicationType->slug ?? 'publikasi',
                'formatted_date' => $publication->published_at?->locale('id_ID')->isoFormat('D MMM Y'),
                'year' => $publication->published_at?->year,
                'detail_url' => route('publikasi.show', $publication->slug),
                'authors' => $publication->authors->take(3)->map(function ($author) {
                    return [
                        'name' => $author->name,
                        'photo' => $author->photo_url, // ✅ Use accessor
                        'initials' => $author->initials, // ✅ ADDED
                        'profile_url' => route('author.profile', $author->user_id ?? $author->id),
                    ];
                })->toArray(), // ✅ ADDED: Convert to array
                'total_authors' => $publication->authors->count(),
                'views_count' => $publication->viewLogs->count(),
                'download_count' => $publication->downloadLogs->count(), // ✅ Changed key name
                'downloads_count' => $publication->downloadLogs->count(), // ✅ Keep for backward compatibility
            ];
        });

        // Statistics
        $stats = [
            'total' => Publication::where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->count(),
            'this_year' => Publication::where('status', 'published')
                ->whereNotNull('published_at')
                ->whereYear('published_at', now()->year)
                ->count(),
            'categories' => $categories->count(),
            'authors' => Author::has('publications')->count(),
        ];

        return view('pages.publication.browse', compact(
            'publications',
            'formattedPublications',
            'publicationTypes',
            'selectedType',
            'categories',
            'filterCategory',
            'years',
            'filterYear',
            'filterSort',
            'perPage',
            'stats'
        ));
    }
}
