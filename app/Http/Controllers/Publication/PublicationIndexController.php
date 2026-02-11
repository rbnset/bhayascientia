<?php

namespace App\Http\Controllers\Publication;

use App\Actions\Author\GetBestAuthorsAction as AuthorGetBestAuthorsAction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PublicationHelperTrait;
use App\Actions\GetBestAuthorsAction;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Category;
use App\Models\Keyword;
use Illuminate\Http\Request;

class PublicationIndexController extends Controller
{
    use PublicationHelperTrait;

    public function __construct(private AuthorGetBestAuthorsAction $getBestAuthorsAction) {}

    public function index(Request $request)
    {
        // Load publication types WITH content (hasOne relationship)
        $publicationTypes = PublicationType::with('content')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);

        if ($publicationTypes->isEmpty()) {
            return view('pages.publication.index', [
                'latestPublications' => collect(),
                'publicationTypes' => $publicationTypes,
                'selectedType' => null,
                'bestAuthors' => collect(),
                'popularPublications' => collect(),
                'featuredPublication' => null,
                'featuredTypeContent' => null,
                'categories' => collect(),
                'years' => collect(),
                'topKeywords' => collect(),
                'filterSort' => 'latest',
                'searchQuery' => null,
            ]);
        }

        // Simple parameters: type & sort only
        $selectedType = $request->query('type', $publicationTypes->first()->slug);
        $filterSort = $request->query('sort', 'latest');
        $searchQuery = null;

        $typeExists = $publicationTypes->contains('slug', $selectedType);
        if (!$typeExists) {
            $selectedType = $publicationTypes->first()->slug;
        }

        // Get current PublicationType object untuk ambil content
        $currentType = $publicationTypes->firstWhere('slug', $selectedType);

        // Format Featured Type Content dari PublicationTypeContent
        $featuredTypeContent = null;
        if ($currentType && $currentType->content) {
            $featuredTypeContent = [
                'title' => $currentType->content->title ?? $currentType->name,
                'cover_url' => $this->getTypeContentCover($currentType->content),
                'category' => $currentType->name,
                'type' => $currentType->name,
                'abstract' => $currentType->content->description,
                'download_count' => 0,
                'detail_url' => '',
            ];
        }

        // GET FILTER OPTIONS DATA for search modal
        $categories = Category::whereHas('publications', function ($query) use ($selectedType) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereHas('publicationType', function ($q) use ($selectedType) {
                    $q->where('slug', $selectedType)->where('is_active', true);
                });
        })
            ->withCount(['publications' => function ($query) use ($selectedType) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->whereHas('publicationType', function ($q) use ($selectedType) {
                        $q->where('slug', $selectedType)->where('is_active', true);
                    });
            }])
            ->orderBy('name')
            ->get();

        // Publication years untuk current type
        $years = Publication::selectRaw('YEAR(published_at) as year')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('publicationType', function ($query) use ($selectedType) {
                $query->where('slug', $selectedType)->where('is_active', true);
            })
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year');

        // Top keywords untuk current type
        $topKeywords = Keyword::whereHas('publications', function ($query) use ($selectedType) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereHas('publicationType', function ($q) use ($selectedType) {
                    $q->where('slug', $selectedType)->where('is_active', true);
                });
        })
            ->withCount(['publications' => function ($query) use ($selectedType) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->whereHas('publicationType', function ($q) use ($selectedType) {
                        $q->where('slug', $selectedType)->where('is_active', true);
                    });
            }])
            ->orderByDesc('publications_count')
            ->limit(20)
            ->get();

        // SIMPLIFIED QUERY - Only type & sort filter
        $publicationsQuery = Publication::with('authors.user', 'publicationType', 'categories')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('publicationType', function ($query) use ($selectedType) {
                $query->where('slug', $selectedType)->where('is_active', true);
            });

        // Apply sorting only
        switch ($filterSort) {
            case 'popular':
                $publicationsQuery->withCount('downloadLogs')->orderByDesc('download_logs_count');
                break;
            case 'oldest':
                $publicationsQuery->orderBy('published_at', 'asc');
                break;
            case 'title':
                $publicationsQuery->orderBy('title', 'asc');
                break;
            case 'latest':
            default:
                $publicationsQuery->orderBy('published_at', 'desc');
                break;
        }

        // Get Latest Publications
        $publications = $publicationsQuery->take(6)->get();
        $latestPublications = $publications->map(function ($pub) {
            return [
                'id' => $pub->id,
                'title' => $pub->title,
                'slug' => $pub->slug,
                'cover_url' => $this->getCoverUrl($pub),
                'category' => $pub->category_name,
                'formatted_date' => $pub->formatted_date,
                'status' => $pub->publicationType->requires_review ? 'Peer-reviewed' : 'Terverifikasi',
                'type' => $pub->publicationType->name ?? 'Publikasi',
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
        })->toArray();

        // GET BEST AUTHORS - LIMIT 6 BUKAN 12
        $bestAuthors = $this->getBestAuthorsAction->execute($selectedType, 6);

        // Get Popular Publications
        $popularPubs = Publication::with('authors.user', 'publicationType', 'categories')
            ->withCount('downloadLogs')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('publicationType', function ($query) use ($selectedType) {
                $query->where('slug', $selectedType)->where('is_active', true);
            })
            ->orderByDesc('download_logs_count')
            ->take(7)
            ->get();

        // Featured Publication
        $featuredPublication = null;
        if (!$featuredTypeContent && $popularPubs->first()) {
            $featuredPublication = [
                'id' => $popularPubs->first()->id,
                'title' => $popularPubs->first()->title,
                'slug' => $popularPubs->first()->slug,
                'cover_url' => $this->getCoverUrl($popularPubs->first()),
                'category' => $popularPubs->first()->category_name,
                'type' => $popularPubs->first()->publicationType->name ?? 'Publikasi',
                'abstract' => \Illuminate\Support\Str::limit($popularPubs->first()->abstract, 120),
                'download_count' => $popularPubs->first()->download_logs_count,
                'detail_url' => route('publikasi.show', $popularPubs->first()->slug),
            ];
        }

        // Popular Publications List
        $skipCount = $featuredTypeContent ? 0 : 1;
        $popularPublications = $popularPubs->skip($skipCount)->take(6)->map(function ($pub) {
            return [
                'id' => $pub->id,
                'title' => $pub->title,
                'slug' => $pub->slug,
                'cover_url' => $this->getCoverUrl($pub),
                'category' => $pub->category_name,
                'formatted_date' => $pub->formatted_date,
                'download_count' => $pub->download_logs_count,
                'views_count' => $pub->views_count,
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

        return view('pages.publication.index', compact(
            'latestPublications',
            'publicationTypes',
            'selectedType',
            'bestAuthors',
            'popularPublications',
            'featuredPublication',
            'featuredTypeContent',
            'categories',
            'years',
            'topKeywords',
            'filterSort',
            'searchQuery'
        ));
    }
}
