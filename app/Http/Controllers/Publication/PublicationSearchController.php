<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PublicationHelperTrait;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Category;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicationSearchController extends Controller
{
    use PublicationHelperTrait;

    public function search(Request $request)
    {
        $searchQuery    = $request->query('search');
        $selectedType   = $request->query('type', 'all');
        $filterCategory = $request->query('category');
        $filterYear     = $request->query('year');
        $filterSort     = $request->query('sort', 'latest');

        // ✅ FIX: normalize keyword jadi array
        $filterKeyword = $request->input('keyword', []);
        $filterKeyword = is_array($filterKeyword)
            ? array_values(array_filter($filterKeyword))
            : array_filter([$filterKeyword]);

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

        if ($selectedType !== 'all') {
            $query->whereHas('publicationType', function ($q) use ($selectedType) {
                $q->where('slug', $selectedType)->where('is_active', true);
            });
        }

        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('title', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('abstract', 'LIKE', "%{$searchQuery}%")
                    ->orWhereHas('authors', function ($authorQuery) use ($searchQuery) {
                        $authorQuery->where('name', 'LIKE', "%{$searchQuery}%");
                    });
            });
        }

        if ($filterCategory) {
            $query->whereHas('categories', function ($q) use ($filterCategory) {
                $q->where('slug', $filterCategory);
            });
        }

        if ($filterYear) {
            $query->whereYear('published_at', $filterYear);
        }

        // ✅ FIX: support multi keyword (array)
        if (!empty($filterKeyword)) {
            $query->whereHas('keywords', function ($q) use ($filterKeyword) {
                $q->whereIn('slug', $filterKeyword);
            });
        }

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

        // ✅ 9 per halaman
        $publications = $query->paginate(9)->withQueryString();

        $searchResults = $publications->map(function ($pub) {
            $pubType      = $pub->publicationType?->name ?? 'Publikasi';
            $coverUrl     = $this->getCoverUrl($pub);
            $categoryName = $pub->category_name ?? 'Umum';
            $formattedDate = $pub->formatted_date
                ?? ($pub->published_at
                    ? $pub->published_at->locale('id_ID')->isoFormat('D MMM Y')
                    : 'Tanggal tidak tersedia');
            $abstract = Str::limit($pub->abstract ?? 'Tidak ada abstrak tersedia', 150);

            return [
                'id'               => $pub->id,
                'title'            => $pub->title,
                'slug'             => $pub->slug,
                'cover_url'        => $coverUrl,
                'category'         => $categoryName,
                'formatted_date'   => $formattedDate,
                'publication_type' => $pubType,
                'status'           => $pub->publicationType?->requires_review
                    ? 'Peer-reviewed'
                    : 'Terverifikasi',
                'type'             => $pubType,
                'abstract'         => $abstract,
                'detail_url'       => route('publikasi.show', $pub->slug),
                'authors'          => $pub->authors->take(6)->map(fn($a) => [
                    'id'       => $a->id,
                    'name'     => $a->name,
                    'photo'    => $a->photo_url,
                    'initials' => $a->initials,
                ])->toArray(),
                'total_authors'    => $pub->authors->count(),
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
