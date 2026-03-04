<?php

namespace App\Http\Controllers;

use App\Actions\Author\GetBestAuthorsAction;
use App\Http\Controllers\Traits\PublicationHelperTrait as TraitsPublicationHelperTrait;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\DownloadLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublicationController extends Controller
{
    use TraitsPublicationHelperTrait;

    public function __construct(
        private GetBestAuthorsAction $getBestAuthorsAction
    ) {}

    /**
     * Helper — normalize keyword input jadi array bersih
     */
    private function normalizeKeywords(mixed $input): array
    {
        if (empty($input)) return [];
        $arr = is_array($input) ? $input : [$input];
        return array_values(array_filter(array_map('strval', $arr)));
    }

    /**
     * Display publication index/homepage
     */
    public function index(Request $request)
    {
        // ✅ Cek tour — letakkan paling atas agar tersedia di semua return path
        $showTour = ! session()->has('has_seen_index_tour');

        $publicationTypes = PublicationType::with('content')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);

        if ($publicationTypes->isEmpty()) {
            return view('pages.publication.index', [
                'latestPublications'  => [],
                'publicationTypes'    => $publicationTypes,
                'selectedType'        => null,
                'bestAuthors'         => collect([]),
                'popularPublications' => collect([]),
                'featuredPublication' => null,
                'featuredTypeContent' => null,
                'categories'          => collect([]),
                'years'               => collect([]),
                'topKeywords'         => collect([]),
                'filterSort'          => 'latest',
                'filterKeyword'       => [],
                'searchQuery'         => null,
                'showTour'            => $showTour, // ✅ tambahkan di empty state juga
            ]);
        }

        $selectedType = $request->query('type', $publicationTypes->first()->slug);
        $filterSort   = $request->query('sort', 'latest');
        $searchQuery  = null;

        if (!$publicationTypes->contains('slug', $selectedType)) {
            $selectedType = $publicationTypes->first()->slug;
        }

        $currentType         = $publicationTypes->firstWhere('slug', $selectedType);
        $featuredTypeContent = $currentType?->content;

        // Filter options
        $categories = \App\Models\Category::whereHas('publications', function ($q) use ($selectedType) {
            $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereHas('publicationType', fn($q2) => $q2->where('slug', $selectedType)->where('is_active', true));
        })
            ->withCount(['publications' => function ($q) use ($selectedType) {
                $q->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->whereHas('publicationType', fn($q2) => $q2->where('slug', $selectedType)->where('is_active', true));
            }])
            ->orderBy('name')
            ->get();

        $years = Publication::selectRaw('YEAR(published_at) as year')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('publicationType', fn($q) => $q->where('slug', $selectedType)->where('is_active', true))
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year');

        $topKeywords = \App\Models\Keyword::whereHas('publications', function ($q) use ($selectedType) {
            $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereHas('publicationType', fn($q2) => $q2->where('slug', $selectedType)->where('is_active', true));
        })
            ->withCount(['publications' => function ($q) use ($selectedType) {
                $q->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->whereHas('publicationType', fn($q2) => $q2->where('slug', $selectedType)->where('is_active', true));
            }])
            ->orderByDesc('publications_count')
            ->limit(20)
            ->get();

        $publicationsQuery = Publication::with(['authors.user', 'publicationType', 'categories'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('publicationType', fn($q) => $q->where('slug', $selectedType)->where('is_active', true));

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
            default:
                $publicationsQuery->orderBy('published_at', 'desc');
                break;
        }

        $publications = $publicationsQuery->take(6)->get();

        $latestPublications = $publications->map(function ($pub) {
            $publicationType = 'Publikasi';
            if ($pub->relationLoaded('publicationType') && $pub->publicationType) {
                $publicationType = $pub->publicationType->name;
            } elseif ($pub->publication_type_id) {
                $type = \App\Models\PublicationType::find($pub->publication_type_id);
                $publicationType = $type ? $type->name : 'Publikasi';
            }

            $firstAuthor = $pub->authors->first();

            return [
                'id'                => $pub->id,
                'title'             => $pub->title,
                'slug'              => $pub->slug,
                'cover_url'         => $this->getCoverUrl($pub),
                'category'          => $pub->category_name,
                'publication_type'  => $publicationType,
                'formatted_date'    => $pub->formatted_date,
                'status'            => $pub->publicationType?->requires_review ? 'Peer-reviewed' : 'Terverifikasi',
                'type'              => $publicationType,
                'detail_url'        => route('publikasi.show', $pub->slug),
                'authors'           => $pub->authors->take(6)->map(fn($a) => [
                    'id'       => $a->id,
                    'name'     => $a->name,
                    'photo'    => $a->photo_url,
                    'initials' => $a->initials,
                ])->toArray(),
                'total_authors'     => $pub->authors->count(),
                'first_author_name' => $firstAuthor?->name ?? 'Anonymous',
            ];
        })->toArray();

        $bestAuthors = $this->getBestAuthorsAction->execute($selectedType, 6);

        $popularPubs = Publication::with(['authors.user', 'publicationType', 'categories'])
            ->withCount([
                'viewLogs as total_views',
                'downloadLogs as total_downloads',
            ])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('publicationType', function ($q) use ($selectedType) {
                $q->where('slug', $selectedType)->where('is_active', true);
            })
            ->get()
            ->map(function ($pub) {
                $pub->trending_score = (int) $pub->total_views + ((int) $pub->total_downloads * 2);
                return $pub;
            })
            ->sortByDesc('trending_score')
            ->take(6)
            ->values();

        $featuredPublication = null;

        $popularPublications = $popularPubs->map(function ($pub) {
            $pubType = $pub->publicationType?->name ?? 'Publikasi';

            return [
                'id'               => $pub->id,
                'title'            => $pub->title,
                'slug'             => $pub->slug,
                'cover_url'        => $this->getCoverUrl($pub),
                'category'         => $pub->category_name ?? ($pub->categories->first()?->name ?? 'Umum'),
                'publication_type' => $pubType,
                'formatted_date'   => $pub->formatted_date ?? ($pub->published_at?->locale('id')->isoFormat('D MMMM YYYY') ?? ''),
                'download_count'   => (int) $pub->total_downloads,
                'views_count'      => (int) $pub->total_views,
                'trending_score'   => (int) $pub->trending_score,
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
            'searchQuery',
            'showTour', // ✅
        ));
    }

    /**
     * Display publications by category
     */
    public function category(Request $request, ?string $categorySlug = null)
    {
        $selectedType   = $request->query('type', 'all');
        $filterSort     = $request->query('sort', 'latest');
        $searchQuery    = $request->query('search');
        $filterCategory = $categorySlug ?? $request->query('category');
        $filterYear     = $request->query('year');

        $filterKeyword = $this->normalizeKeywords($request->input('keyword'));

        $publicationTypes = PublicationType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);

        $categories = \App\Models\Category::withCount([
            'publications' => fn($q) => $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
        ])
            ->having('publications_count', '>', 0)
            ->orderBy('name')
            ->get();

        $yearsQuery = Publication::selectRaw('YEAR(published_at) as year')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($selectedType !== 'all') {
            $yearsQuery->whereHas(
                'publicationType',
                fn($q) => $q->where('slug', $selectedType)->where('is_active', true)
            );
        }

        $years = $yearsQuery->groupBy('year')->orderByDesc('year')->pluck('year');

        $topKeywords = \App\Models\Keyword::withCount([
            'publications' => fn($q) => $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
        ])
            ->having('publications_count', '>', 0)
            ->orderByDesc('publications_count')
            ->limit(20)
            ->get();

        $currentCategory = null;
        if ($filterCategory) {
            $currentCategory = \App\Models\Category::where('slug', $filterCategory)->first();
        }

        $publicationsQuery = Publication::with(['authors.user', 'publicationType', 'categories'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($selectedType !== 'all') {
            $publicationsQuery->whereHas(
                'publicationType',
                fn($q) => $q->where('slug', $selectedType)->where('is_active', true)
            );
        }

        if ($filterCategory) {
            $publicationsQuery->whereHas(
                'categories',
                fn($q) => $q->where('slug', $filterCategory)
            );
        }

        if ($filterYear) {
            $publicationsQuery->whereYear('published_at', $filterYear);
        }

        if (!empty($filterKeyword)) {
            $publicationsQuery->whereHas('keywords', function ($q) use ($filterKeyword) {
                $q->whereIn('slug', $filterKeyword);
            });
        }

        if ($searchQuery) {
            $publicationsQuery->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%")
                    ->orWhere('abstract', 'like', "%{$searchQuery}%");
            });
        }

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
            default:
                $publicationsQuery->orderBy('published_at', 'desc');
                break;
        }

        $publications = $publicationsQuery->paginate(12)->withQueryString();

        return view('pages.publication.categories', [
            'publications'     => $publications,
            'publicationTypes' => $publicationTypes,
            'categories'       => $categories,
            'years'            => $years,
            'topKeywords'      => $topKeywords,
            'currentCategory'  => $currentCategory,
            'selectedType'     => $selectedType,
            'filterSort'       => $filterSort,
            'searchQuery'      => $searchQuery,
            'filterCategory'   => $filterCategory,
            'filterYear'       => $filterYear,
            'filterKeyword'    => $filterKeyword,
        ]);
    }

    /**
     * Search publications
     */
    public function search(Request $request)
    {
        $selectedType   = $request->query('type', 'all');
        $filterSort     = $request->query('sort', 'latest');
        $searchQuery    = $request->query('search');
        $filterCategory = $request->query('category');
        $filterYear     = $request->query('year');

        $filterKeyword = $this->normalizeKeywords($request->input('keyword'));

        $publicationTypes = PublicationType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);

        $categories = \App\Models\Category::withCount([
            'publications' => fn($q) => $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
        ])
            ->having('publications_count', '>', 0)
            ->orderBy('name')
            ->get();

        $yearsQuery = Publication::selectRaw('YEAR(published_at) as year')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($selectedType !== 'all') {
            $yearsQuery->whereHas(
                'publicationType',
                fn($q) => $q->where('slug', $selectedType)->where('is_active', true)
            );
        }

        $years = $yearsQuery->groupBy('year')->orderByDesc('year')->pluck('year');

        $topKeywords = \App\Models\Keyword::withCount([
            'publications' => fn($q) => $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
        ])
            ->having('publications_count', '>', 0)
            ->orderByDesc('publications_count')
            ->limit(20)
            ->get();

        $publicationsQuery = Publication::with(['authors.user', 'publicationType', 'categories'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($selectedType !== 'all') {
            $publicationsQuery->whereHas(
                'publicationType',
                fn($q) => $q->where('slug', $selectedType)->where('is_active', true)
            );
        }

        if ($filterCategory) {
            $publicationsQuery->whereHas(
                'categories',
                fn($q) => $q->where('slug', $filterCategory)
            );
        }

        if ($filterYear) {
            $publicationsQuery->whereYear('published_at', $filterYear);
        }

        if (!empty($filterKeyword)) {
            $publicationsQuery->whereHas('keywords', function ($q) use ($filterKeyword) {
                $q->whereIn('slug', $filterKeyword);
            });
        }

        if ($searchQuery) {
            $publicationsQuery->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%")
                    ->orWhere('abstract', 'like', "%{$searchQuery}%")
                    ->orWhereHas('authors', fn($a) => $a->where('name', 'like', "%{$searchQuery}%"));
            });
        }

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
            default:
                $publicationsQuery->orderBy('published_at', 'desc');
                break;
        }

        $publications = $publicationsQuery->paginate(12)->withQueryString();

        return view('pages.publication.search', [
            'publications'     => $publications,
            'publicationTypes' => $publicationTypes,
            'categories'       => $categories,
            'years'            => $years,
            'topKeywords'      => $topKeywords,
            'selectedType'     => $selectedType,
            'filterSort'       => $filterSort,
            'searchQuery'      => $searchQuery,
            'filterCategory'   => $filterCategory,
            'filterYear'       => $filterYear,
            'filterKeyword'    => $filterKeyword,
        ]);
    }

    /**
     * Show publication detail
     */
    public function show($slug)
    {
        $publication = Publication::with([
            'authors.user',
            'publicationType',
            'categories',
            'keywords',
            'versions' => function ($query) {
                $query->orderBy('version_number', 'desc');
            },
        ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $latestVersion     = $publication->versions->first();
        $fileSize          = null;
        $fileSizeFormatted = null;

        if ($latestVersion && $latestVersion->pdf_file_path) {
            $filePath = $this->cleanPath($latestVersion->pdf_file_path);
            if (Storage::disk('public')->exists($filePath)) {
                $fileSizeBytes     = Storage::disk('public')->size($filePath);
                $fileSize          = $fileSizeBytes;
                $fileSizeFormatted = $this->formatFileSize($fileSizeBytes);
            }
        }

        $downloadCount = $publication->downloadLogs()
            ->where('publication_id', $publication->id)
            ->count();

        $viewsCount = $publication->viewLogs()
            ->where('publication_id', $publication->id)
            ->count();

        $this->logPublicationView($publication);

        $authors = $publication->authors->map(function ($author) {
            $userData = $author->user;
            return [
                'id'               => $author->id,
                'user_id'          => $author->user_id,
                'name'             => $author->name,
                'initials'         => $author->initials,
                'photo'            => $author->photo_url,
                'photo_url'        => $author->photo_url,
                'affiliation'      => $author->affiliation ?? ($userData ? ($userData->job_title ?? $userData->organization ?? '-') : '-'),
                'bio'              => $author->bio ?? ($userData ? $userData->bio : null),
                'short_bio'        => $author->short_bio,
                'email'            => $author->email,
                'is_corresponding' => $author->pivot->is_corresponding ?? false,
                'profile_type'     => $author->user_id ? 'user' : 'author',
                'profile_id'       => $author->user_id ?? $author->id,
            ];
        });

        return view('pages.publication.show', [
            'publication'       => $publication,
            'formatted_date'    => $publication->published_at->locale('id_ID')->isoFormat('D MMMM YYYY'),
            'category'          => $publication->categories->first()?->name ?? 'Umum',
            'publication_type'  => $publication->publicationType->name ?? 'Publikasi',
            'keywords'          => $publication->keywords->pluck('name')->toArray(),
            'cover_url'         => $this->getCoverUrl($publication),
            'authors'           => $authors,
            'latestVersion'     => $latestVersion,
            'fileSize'          => $fileSize,
            'fileSizeFormatted' => $fileSizeFormatted,
            'downloadCount'     => $downloadCount,
            'viewsCount'        => $viewsCount,
        ]);
    }

    /**
     * Toggle favorite publication
     */
    public function toggleFavorite($slug)
    {
        if (!auth()->check()) {
            return response()->json([
                'success'  => false,
                'message'  => 'Silakan login terlebih dahulu',
                'redirect' => route('login')
            ], 401);
        }

        $publication = Publication::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $result = auth()->user()->toggleFavorite($publication->id);

        return response()->json([
            'success'     => true,
            'status'      => $result['status'],
            'message'     => $result['message'],
            'isFavorited' => $result['status'] === 'added'
        ]);
    }

    /**
     * Toggle saved publication
     */
    public function toggleSaved($slug)
    {
        if (!auth()->check()) {
            return response()->json([
                'success'  => false,
                'message'  => 'Silakan login terlebih dahulu',
                'redirect' => route('login')
            ], 401);
        }

        $publication = Publication::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $result = auth()->user()->toggleSaved($publication->id);

        return response()->json([
            'success' => true,
            'status'  => $result['status'],
            'message' => $result['message'],
            'isSaved' => $result['status'] === 'added'
        ]);
    }

    /**
     * Download publikasi
     */
    public function download($slug)
    {
        $publication = Publication::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $latestVersion = $publication->versions()
            ->whereNotNull('pdf_file_path')
            ->orderBy('version_number', 'desc')
            ->first();

        if (!$latestVersion || !$latestVersion->pdf_file_path) {
            abort(404, 'File publikasi tidak ditemukan');
        }

        try {
            DownloadLog::logDownload($publication);
            $publication->clearStatsCache();
        } catch (\Exception $e) {
            Log::warning("Download log failed: " . $e->getMessage());
        }

        $filePath = $this->cleanPath($latestVersion->pdf_file_path);

        if (!Storage::disk('public')->exists($filePath)) {
            Log::error("File not found in storage", [
                'publication_id' => $publication->id,
                'file_path'      => $latestVersion->pdf_file_path,
                'clean_path'     => $filePath,
            ]);
            abort(404, 'File tidak ditemukan di storage');
        }

        $fileName = \Illuminate\Support\Str::slug($publication->title) . '.pdf';

        return Storage::disk('public')->download($filePath, $fileName);
    }

    /**
     * READ/VIEW publikasi PDF di browser
     */
    public function read($slug)
    {
        $publication = Publication::with([
            'authors.user',
            'publicationType',
            'categories',
            'keywords',
            'versions' => function ($query) {
                $query->orderBy('version_number', 'desc');
            },
        ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $latestVersion = $publication->versions()
            ->whereNotNull('pdf_file_path')
            ->orderBy('version_number', 'desc')
            ->first();

        if (!$latestVersion || !$latestVersion->pdf_file_path) {
            abort(404, 'File publikasi tidak ditemukan');
        }

        $filePath = $this->cleanPath($latestVersion->pdf_file_path);

        if (!Storage::disk('public')->exists($filePath)) {
            Log::error("File not found in storage", [
                'publication_id' => $publication->id,
                'file_path'      => $latestVersion->pdf_file_path,
                'clean_path'     => $filePath,
            ]);
            abort(404, 'File tidak ditemukan di storage');
        }

        $this->logPublicationView($publication);

        if (auth()->check()) {
            auth()->user()->readPublications()->syncWithoutDetaching([
                $publication->id => ['last_read_at' => now()]
            ]);
        }

        $pdfUrl = asset('storage/' . $filePath);

        return view('pages.publication.read', [
            'publication'      => $publication,
            'pdfUrl'           => $pdfUrl,
            'category'         => $publication->categories->first()?->name ?? 'Umum',
            'publication_type' => $publication->publicationType->name ?? 'Publikasi',
            'authors'          => $publication->authors->take(6)->map(function ($author) {
                return [
                    'id'       => $author->id,
                    'name'     => $author->name,
                    'initials' => $author->initials,
                    'photo'    => $author->photo_url,
                ];
            }),
        ]);
    }
}
