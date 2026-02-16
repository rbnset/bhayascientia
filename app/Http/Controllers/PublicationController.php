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
     * ✅ Display publication index/homepage
     */
    public function index(Request $request)
    {
        // Load publication types WITH content (hasOne relationship)
        $publicationTypes = PublicationType::with('content')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);

        if ($publicationTypes->isEmpty()) {
            return view('pages.publication.index', [
                'latestPublications' => [],
                'publicationTypes' => $publicationTypes,
                'selectedType' => null,
                'bestAuthors' => collect([]),
                'popularPublications' => collect([]),
                'featuredPublication' => null,
                'featuredTypeContent' => null,
                'categories' => collect([]),
                'years' => collect([]),
                'topKeywords' => collect([]),
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
                'publication_type' => $currentType->name,
                'abstract' => $currentType->content->description,
                'download_count' => 0,
                'detail_url' => '#',
                'slug' => $currentType->slug,
            ];
        }

        // GET FILTER OPTIONS DATA (for search modal)
        $categories = \App\Models\Category::whereHas('publications', function ($query) use ($selectedType) {
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
        $topKeywords = \App\Models\Keyword::whereHas('publications', function ($query) use ($selectedType) {
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
        $publicationsQuery = Publication::with([
            'authors.user',
            'publicationType',
            'categories'
        ])
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

        // ✅ DEBUG: Log raw publications data
        if ($publications->isNotEmpty()) {
            \Log::info('Raw Publications Query Result', [
                'count' => $publications->count(),
                'first_pub_id' => $publications->first()->id,
                'first_pub_title' => $publications->first()->title,
                'first_pub_type_id' => $publications->first()->publication_type_id,
                'first_pub_has_relation' => $publications->first()->relationLoaded('publicationType'),
                'first_pub_type_name' => $publications->first()->publicationType?->name,
            ]);
        }

        // ✅ Map Latest Publications dengan data BENAR
        $latestPublications = $publications->map(function ($pub) {
            // Get first author name
            $firstAuthor = $pub->authors->first();
            $firstAuthorName = $firstAuthor ? $firstAuthor->name : 'Anonymous';

            // ✅ PENTING: Pastikan publicationType ter-load dengan benar
            $publicationType = 'Publikasi'; // Default fallback

            if ($pub->relationLoaded('publicationType') && $pub->publicationType) {
                $publicationType = $pub->publicationType->name;
            } elseif ($pub->publication_type_id) {
                // Emergency fallback jika relation tidak ter-load
                $type = \App\Models\PublicationType::find($pub->publication_type_id);
                $publicationType = $type ? $type->name : 'Publikasi';
            }

            // ✅ DEBUG: Log setiap mapping
            \Log::info('Mapping Publication', [
                'id' => $pub->id,
                'title' => \Illuminate\Support\Str::limit($pub->title, 50),
                'publication_type' => $publicationType,
                'is_default' => $publicationType === 'Publikasi',
            ]);

            return [
                'id' => $pub->id,
                'title' => $pub->title,
                'slug' => $pub->slug,
                'cover_url' => $this->getCoverUrl($pub),
                'category' => $pub->category_name,

                // ✅ PENTING: Kirim nama type yang BENAR (Buku, Jurnal, dll)
                'publication_type' => $publicationType,

                'formatted_date' => $pub->formatted_date,
                'status' => $pub->publicationType && $pub->publicationType->requires_review
                    ? 'Peer-reviewed'
                    : 'Terverifikasi',
                'type' => $publicationType,
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
                'first_author_name' => $firstAuthorName,
            ];
        })->toArray();

        // ✅ DEBUG: Log final mapped data
        if (!empty($latestPublications)) {
            \Log::info('Final Latest Publications', [
                'count' => count($latestPublications),
                'first_item' => [
                    'title' => \Illuminate\Support\Str::limit($latestPublications[0]['title'], 50),
                    'publication_type' => $latestPublications[0]['publication_type'],
                    'is_default' => $latestPublications[0]['publication_type'] === 'Publikasi',
                ],
            ]);
        }

        // GET BEST AUTHORS - LIMIT 6
        $bestAuthors = $this->getBestAuthorsAction->execute($selectedType, 6);

        // Get Popular Publications
        $popularPubs = Publication::with([
            'authors.user',
            'publicationType',
            'categories'
        ])
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
            $featuredPub = $popularPubs->first();

            // Get publication type untuk featured
            $featuredPubType = 'Publikasi';
            if ($featuredPub->publicationType) {
                $featuredPubType = $featuredPub->publicationType->name;
            }

            $featuredPublication = [
                'id' => $featuredPub->id,
                'title' => $featuredPub->title,
                'slug' => $featuredPub->slug,
                'cover_url' => $this->getCoverUrl($featuredPub),
                'category' => $featuredPub->category_name,
                'publication_type' => $featuredPubType,
                'type' => $featuredPubType,
                'abstract' => \Illuminate\Support\Str::limit($featuredPub->abstract, 120),
                'download_count' => $featuredPub->download_logs_count,
                'detail_url' => route('publikasi.show', $featuredPub->slug),
            ];
        }

        // Popular Publications List
        $skipCount = $featuredTypeContent ? 0 : 1;
        $popularPublications = $popularPubs->skip($skipCount)->take(6)->map(function ($pub) {
            // Get publication type
            $pubType = 'Publikasi';
            if ($pub->publicationType) {
                $pubType = $pub->publicationType->name;
            }

            return [
                'id' => $pub->id,
                'title' => $pub->title,
                'slug' => $pub->slug,
                'cover_url' => $this->getCoverUrl($pub),
                'category' => $pub->category_name,
                'publication_type' => $pubType,
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


    /**
     * ✅ Show publication detail
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

        $latestVersion = $publication->versions->first();
        $fileSize = null;
        $fileSizeFormatted = null;

        if ($latestVersion && $latestVersion->pdf_file_path) {
            $filePath = $this->cleanPath($latestVersion->pdf_file_path);

            if (Storage::disk('public')->exists($filePath)) {
                $fileSizeBytes = Storage::disk('public')->size($filePath);
                $fileSize = $fileSizeBytes;
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

        // Map authors dengan support User dan Author profile
        $authors = $publication->authors->map(function ($author) {
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
                'profile_type' => $author->user_id ? 'user' : 'author',
                'profile_id' => $author->user_id ?? $author->id,
            ];
        });

        return view('pages.publication.show', [
            'publication' => $publication,
            'formatted_date' => $publication->published_at->locale('id_ID')->isoFormat('D MMMM YYYY'),
            'category' => $publication->categories->first()?->name ?? 'Umum',
            'publication_type' => $publication->publicationType->name ?? 'Publikasi',
            'keywords' => $publication->keywords->pluck('name')->toArray(),
            'cover_url' => $this->getCoverUrl($publication),
            'authors' => $authors,
            'latestVersion' => $latestVersion,
            'fileSize' => $fileSize,
            'fileSizeFormatted' => $fileSizeFormatted,
            'downloadCount' => $downloadCount,
            'viewsCount' => $viewsCount,
        ]);
    }

    /**
     * ✅ Toggle favorite publication
     */
    public function toggleFavorite($slug)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu',
                'redirect' => route('login')
            ], 401);
        }

        $publication = Publication::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $result = auth()->user()->toggleFavorite($publication->id);

        return response()->json([
            'success' => true,
            'status' => $result['status'],
            'message' => $result['message'],
            'isFavorited' => $result['status'] === 'added'
        ]);
    }

    /**
     * ✅ Toggle saved publication
     */
    public function toggleSaved($slug)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu',
                'redirect' => route('login')
            ], 401);
        }

        $publication = Publication::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $result = auth()->user()->toggleSaved($publication->id);

        return response()->json([
            'success' => true,
            'status' => $result['status'],
            'message' => $result['message'],
            'isSaved' => $result['status'] === 'added'
        ]);
    }

    /**
     * ✅ Download publikasi
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
                'file_path' => $latestVersion->pdf_file_path,
                'clean_path' => $filePath,
            ]);
            abort(404, 'File tidak ditemukan di storage');
        }

        $fileName = \Illuminate\Support\Str::slug($publication->title) . '.pdf';

        return Storage::disk('public')->download($filePath, $fileName);
    }

    /**
     * ✅ READ/VIEW publikasi PDF di browser
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
                'file_path' => $latestVersion->pdf_file_path,
                'clean_path' => $filePath,
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
            'publication' => $publication,
            'pdfUrl' => $pdfUrl,
            'category' => $publication->categories->first()?->name ?? 'Umum',
            'publication_type' => $publication->publicationType->name ?? 'Publikasi',
            'authors' => $publication->authors->take(6)->map(function ($author) {
                return [
                    'id' => $author->id,
                    'name' => $author->name,
                    'initials' => $author->initials,
                    'photo' => $author->photo_url,
                ];
            }),
        ]);
    }
}
