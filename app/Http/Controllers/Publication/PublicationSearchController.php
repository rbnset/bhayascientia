<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PublicationHelperTrait;  // ✅ TAMBAH
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Category;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Str;  // ✅ TAMBAH untuk Str::limit

class PublicationSearchController extends Controller
{
    use PublicationHelperTrait;  // ✅ TAMBAH INI

    public function search(Request $request)
    {
        $searchQuery = $request->query('search');
        $selectedType = $request->query('type', 'all');
        $filterCategory = $request->query('category');
        $filterYear = $request->query('year');
        $filterKeyword = $request->query('keyword');
        $filterSort = $request->query('sort', 'latest');

        // Publication types
        $publicationTypes = PublicationType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);

        // Categories
        $categories = Category::whereHas('publications', function ($query) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        })
            ->withCount(['publications' => function ($query) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            }])
            ->orderBy('name')
            ->get();

        // Years
        $years = Publication::selectRaw('YEAR(published_at) as year')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year');

        // Top keywords
        $topKeywords = Keyword::whereHas('publications', function ($query) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        })
            ->withCount(['publications' => function ($query) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            }])
            ->orderByDesc('publications_count')
            ->limit(20)
            ->get();

        // Main query
        $query = Publication::with(['authors.user', 'publicationType', 'categories', 'keywords'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        // Filter by type
        if ($selectedType !== 'all') {
            $query->whereHas('publicationType', function ($q) use ($selectedType) {
                $q->where('slug', $selectedType)->where('is_active', true);
            });
        }

        // Search query
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('title', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('abstract', 'LIKE', "%{$searchQuery}%")
                    ->orWhereHas('authors', function ($authorQuery) use ($searchQuery) {
                        $authorQuery->where('name', 'LIKE', "%{$searchQuery}%");
                    });
            });
        }

        // Filter category
        if ($filterCategory) {
            $query->whereHas('categories', function ($q) use ($filterCategory) {
                $q->where('slug', $filterCategory);
            });
        }

        // Filter year
        if ($filterYear) {
            $query->whereYear('published_at', $filterYear);
        }

        // Filter keyword
        if ($filterKeyword) {
            $query->whereHas('keywords', function ($q) use ($filterKeyword) {
                $q->where('slug', $filterKeyword);
            });
        }

        // Sorting
        switch ($filterSort) {
            case 'popular':
                $query->withCount('downloadLogs')->orderByDesc('download_logs_count');
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

        $publications = $query->paginate(18)->withQueryString();
        $searchResults = $publications->map(function ($pub) {
            return [
                'id' => $pub->id,
                'title' => $pub->title,
                'slug' => $pub->slug,
                'cover_url' => $this->getCoverUrl($pub),  // ✅ FIX: use $this->getCoverUrl()
                'category' => $pub->category_name ?? 'Umum',
                'formatted_date' => $pub->formatted_date ?? $pub->published_at?->locale('id_ID')->isoFormat('D MMM Y'),
                'status' => $pub->publicationType->requires_review ? 'Peer-reviewed Terverifikasi' : '',
                'type' => $pub->publicationType->name ?? 'Publikasi',
                'abstract' => Str::limit($pub->abstract ?? '', 150),
                'detail_url' => route('publikasi.show', $pub->slug),
                'authors' => $pub->authors->take(6)->map(function ($author) {
                    return [
                        'id' => $author->id,
                        'name' => $author->name,
                        'photo' => $author->photo_url,
                        'initials' => $author->initials,
                    ];
                })->toArray(),
                'total_authors' => $pub->authors->count(),
            ];
        });

        return view('pages.publication.search', compact(
            'searchResults',
            'publications',
            'searchQuery',
            'selectedType',
            'filterCategory',
            'filterYear',
            'filterKeyword',
            'filterSort',
            'publicationTypes',
            'categories',
            'years',
            'topKeywords'
        ));
    }
}
