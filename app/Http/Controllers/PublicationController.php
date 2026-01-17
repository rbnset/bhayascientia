<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Author;
use App\Models\PublicationViewLog;
use App\Models\DownloadLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class PublicationController extends Controller
{
    public function index(Request $request)
    {
        // ✅ Load publication types WITH content (hasOne relationship)
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
            ]);
        }

        $selectedType = $request->query('type', $publicationTypes->first()->slug);

        $typeExists = $publicationTypes->contains('slug', $selectedType);
        if (!$typeExists) {
            $selectedType = $publicationTypes->first()->slug;
        }

        // ✅ Get current PublicationType object untuk ambil content
        $currentType = $publicationTypes->firstWhere('slug', $selectedType);

        // ✅ Format Featured Type Content dari PublicationTypeContent
        $featuredTypeContent = null;
        if ($currentType && $currentType->content) {
            $featuredTypeContent = [
                'title' => $currentType->content->title ?? $currentType->name,
                'cover_url' => $this->getTypeContentCover($currentType->content),
                'category' => $currentType->name,
                'type' => $currentType->name,
                'abstract' => $currentType->content->description,
                'download_count' => 0,
                'detail_url' => '#',
            ];
        }

        // ✅ Get Latest Publications
        $publications = Publication::with([
            'authors.user',
            'publicationType',
            'categories'
        ])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('publicationType', function ($query) use ($selectedType) {
                $query->where('slug', $selectedType)
                    ->where('is_active', true);
            })
            ->orderBy('published_at', 'desc')
            ->take(6)
            ->get();

        $latestPublications = $publications->map(function ($pub) {
            $coverUrl = $this->getCoverUrl($pub);

            if (config('app.debug')) {
                Log::debug("Publication Cover Debug", [
                    'id' => $pub->id,
                    'title' => $pub->title,
                    'cover_path' => $pub->cover_image_path,
                    'cover_url' => $coverUrl,
                    'file_exists' => $pub->cover_image_path
                        ? Storage::disk('public')->exists($this->cleanPath($pub->cover_image_path))
                        : false,
                ]);
            }

            return [
                'id' => $pub->id,
                'title' => $pub->title,
                'slug' => $pub->slug,
                'cover_url' => $coverUrl,
                'category' => $pub->category_name,
                'formatted_date' => $pub->formatted_date,
                'status' => $pub->publicationType->requires_review ? 'Peer-reviewed' : 'Terverifikasi',
                'type' => $pub->publicationType->name ?? 'Publikasi',
                'detail_url' => route('publikasi.show', $pub->slug),
                'authors' => $pub->authors->map(function ($author) {
                    return [
                        'id' => $author->id,
                        'name' => $author->name,
                        'photo' => $this->getAuthorPhoto($author),
                        'initials' => $this->getInitials($author->name),
                    ];
                })->toArray(),
                'total_authors' => $pub->authors->count(),
            ];
        })->toArray();

        // ✅ Get Best Authors BERDASARKAN FILTER yang dipilih
        $bestAuthors = Author::query()
            ->with('user')
            ->withCount(['publications' => function ($query) use ($selectedType) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->whereHas('publicationType', function ($q) use ($selectedType) {
                        $q->where('slug', $selectedType)
                            ->where('is_active', true);
                    });
            }])
            ->having('publications_count', '>', 0)
            ->orderByDesc('publications_count')
            ->limit(6)
            ->get()
            ->map(function ($author) {
                return [
                    'name' => $author->name,
                    'avatar' => $this->getAuthorPhoto($author),
                    'initials' => $this->getInitials($author->name),
                    'publication_count' => $author->publications_count,
                    'profile_url' => route('author.show', $author->id),
                    'verified' => $author->user_id !== null,
                    'specialty' => $author->affiliation ?? null,
                ];
            });

        // ✅ Get Popular Publications (berdasarkan jumlah download)
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
                $query->where('slug', $selectedType)
                    ->where('is_active', true);
            })
            ->orderByDesc('download_logs_count')
            ->take(7)
            ->get();

        // ✅ Featured Publication (yang paling banyak diunduh)
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

        // ✅ Popular Publications List (6 sisanya)
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
                'authors' => $pub->authors->map(function ($author) {
                    return [
                        'id' => $author->id,
                        'name' => $author->name,
                        'photo' => $this->getAuthorPhoto($author),
                        'initials' => $this->getInitials($author->name),
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
            'featuredTypeContent'
        ));
    }

    /**
     * ✅ Show publication detail - FIXED COVER IMAGE
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

        // ✅ Get latest version
        $latestVersion = $publication->versions->first();
        $fileSize = null;
        $fileSizeFormatted = null;

        // ✅ Get file size from storage
        if ($latestVersion && $latestVersion->pdf_file_path) {
            $filePath = $this->cleanPath($latestVersion->pdf_file_path);

            if (Storage::disk('public')->exists($filePath)) {
                $fileSizeBytes = Storage::disk('public')->size($filePath);
                $fileSize = $fileSizeBytes;
                $fileSizeFormatted = $this->formatFileSize($fileSizeBytes);
            }
        }

        // ✅ Get download count
        $downloadCount = $publication->downloadLogs()
            ->where('publication_id', $publication->id)
            ->count();

        // ✅ Get view count
        $viewsCount = $publication->viewLogs()
            ->where('publication_id', $publication->id)
            ->count();

        // ✅ Log view (throttle per IP per 5 menit)
        $this->logPublicationView($publication);

        return view('pages.publication.show', [
            'publication' => $publication,
            'formatted_date' => $publication->published_at->locale('id_ID')->isoFormat('D MMMM YYYY'),
            'category' => $publication->categories->first()?->name ?? 'Umum',
            'keywords' => $publication->keywords->pluck('name')->toArray(),
            'cover_url' => $this->getCoverUrl($publication),
            'authors' => $publication->authors->map(function ($author) {
                return [
                    'id' => $author->id,
                    'name' => $author->name,
                    'initials' => $this->getInitials($author->name),
                    'photo' => $this->getAuthorPhoto($author),
                    'affiliation' => $author->affiliation ?? $author->user?->organization ?? '-',
                    'is_corresponding' => $author->is_corresponding,
                ];
            }),
            'latestVersion' => $latestVersion,
            'fileSize' => $fileSize,
            'fileSizeFormatted' => $fileSizeFormatted,
            'downloadCount' => $downloadCount,
            'viewsCount' => $viewsCount,
        ]);
    }

    /**
     * ✅ Show categories page
     */
    public function categories()
    {
        $categories = \App\Models\Category::withCount([
            'publications' => function ($query) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            }
        ])
            ->having('publications_count', '>', 0)
            ->orderByDesc('publications_count')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'publications_count' => $category->publications_count,
                    'icon' => $category->icon ?? 'assets/images/icons/category-default.svg',
                    'color' => $category->color ?? '#FF6B18',
                ];
            });

        return view('pages.publication.categories', compact('categories'));
    }

    /**
     * ✅ Show trending publications dengan filter periode & type
     */
    public function trending(Request $request)
    {
        // Get filter parameters
        $period = $request->query('period', '7'); // 7 atau 30 hari
        $typeSlug = $request->query('type', 'all'); // all atau slug publication type

        // Validate period
        if (!in_array($period, ['7', '30'])) {
            $period = '7';
        }

        $daysAgo = (int) $period;

        // Get all active publication types for filter tabs
        $publicationTypes = PublicationType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);

        // Base query
        $query = Publication::with([
            'authors.user',
            'publicationType',
            'categories',
        ])
            ->withCount([
                'viewLogs as recent_views' => function ($query) use ($daysAgo) {
                    $query->where('created_at', '>=', now()->subDays($daysAgo));
                },
                'downloadLogs as recent_downloads' => function ($query) use ($daysAgo) {
                    $query->where('created_at', '>=', now()->subDays($daysAgo));
                },
            ])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        // Filter by publication type if not 'all'
        if ($typeSlug !== 'all') {
            $query->whereHas('publicationType', function ($q) use ($typeSlug) {
                $q->where('slug', $typeSlug)->where('is_active', true);
            });
        }

        // Get trending publications
        $trendingPublications = $query
            ->orderByRaw('(recent_views * 1) + (recent_downloads * 2) DESC')
            ->take(50) // Increase limit untuk lebih banyak hasil
            ->get()
            ->filter(function ($pub) {
                // Filter yang minimal ada aktivitas
                return $pub->recent_views > 0 || $pub->recent_downloads > 0;
            })
            ->values()
            ->map(function ($pub) {
                return [
                    'id' => $pub->id,
                    'title' => $pub->title,
                    'slug' => $pub->slug,
                    'cover_url' => $this->getCoverUrl($pub),
                    'category' => $pub->category_name,
                    'formatted_date' => $pub->formatted_date,
                    'type' => $pub->publicationType->name ?? 'Publikasi',
                    'type_slug' => $pub->publicationType->slug ?? 'publikasi',
                    'detail_url' => route('publikasi.show', $pub->slug),
                    'trending_score' => $pub->recent_views + ($pub->recent_downloads * 2),
                    'recent_views' => $pub->recent_views,
                    'recent_downloads' => $pub->recent_downloads,
                    'authors' => $pub->authors->map(function ($author) {
                        return [
                            'id' => $author->id,
                            'name' => $author->name,
                            'photo' => $this->getAuthorPhoto($author),
                            'initials' => $this->getInitials($author->name),
                        ];
                    })->toArray(),
                    'total_authors' => $pub->authors->count(),
                ];
            });

        // Get stats per type untuk summary
        $typeStats = [];
        foreach ($publicationTypes as $type) {
            $count = $trendingPublications->where('type_slug', $type->slug)->count();
            if ($count > 0) {
                $typeStats[] = [
                    'slug' => $type->slug,
                    'name' => $type->name,
                    'count' => $count,
                ];
            }
        }

        return view('pages.publication.trending', compact(
            'trendingPublications',
            'publicationTypes',
            'period',
            'typeSlug',
            'typeStats'
        ));
    }



    /**
     * ✅ Show user's library with LOGIN GATE (tidak redirect)
     */
    public function library(Request $request)
    {
        $activeTab = $request->query('tab', 'favorites');

        // ✅ Validate tab
        if (!in_array($activeTab, ['favorites', 'history', 'saved'])) {
            $activeTab = 'favorites';
        }

        // ✅ JIKA BELUM LOGIN: Show empty state dengan login prompt
        if (!auth()->check()) {
            return view('pages.publication.library', [
                'publications' => collect([]),
                'stats' => [
                    'favorites' => 0,
                    'history' => 0,
                    'saved' => 0,
                ],
                'activeTab' => $activeTab,
                'requiresLogin' => true, // ✅ Flag untuk show login gate
            ]);
        }

        // ✅ JIKA SUDAH LOGIN: Show actual data
        $user = auth()->user();

        $stats = [
            'favorites' => $user->favoritePublications()->count(),
            'history' => $user->readPublications()->count(),
            'saved' => $user->savedPublications()->count(),
        ];

        $publications = collect([]);

        switch ($activeTab) {
            case 'favorites':
                $publications = $user->favoritePublications()
                    ->with(['authors.user', 'publicationType', 'categories'])
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->orderBy('user_favorite_publications.created_at', 'desc')
                    ->get();
                break;

            case 'history':
                $publications = $user->readPublications()
                    ->with(['authors.user', 'publicationType', 'categories'])
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->orderBy('user_read_publications.last_read_at', 'desc')
                    ->get();
                break;

            case 'saved':
                $publications = $user->savedPublications()
                    ->with(['authors.user', 'publicationType', 'categories'])
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->orderBy('user_saved_publications.created_at', 'desc')
                    ->get();
                break;
        }

        $publications = $publications->map(function ($pub) use ($activeTab) {
            $authorsText = $pub->authors->take(2)->pluck('name')->implode(', ');
            if ($pub->authors->count() > 2) {
                $authorsText .= ' +' . ($pub->authors->count() - 2) . ' lainnya';
            }

            // ✅ FIX: Handle different pivot columns per tab dengan safety check
            $actionTime = '';
            switch ($activeTab) {
                case 'favorites':
                    if ($pub->pivot && $pub->pivot->created_at) {
                        $createdAt = is_string($pub->pivot->created_at)
                            ? \Carbon\Carbon::parse($pub->pivot->created_at)
                            : $pub->pivot->created_at;
                        $actionTime = 'Ditambahkan ' . $createdAt->diffForHumans();
                    } else {
                        $actionTime = 'Ditambahkan baru-baru ini';
                    }
                    break;

                case 'history':
                    if ($pub->pivot && $pub->pivot->last_read_at) {
                        $lastReadAt = is_string($pub->pivot->last_read_at)
                            ? \Carbon\Carbon::parse($pub->pivot->last_read_at)
                            : $pub->pivot->last_read_at;
                        $actionTime = 'Dibaca ' . $lastReadAt->diffForHumans();
                    } else {
                        $actionTime = 'Dibaca baru-baru ini';
                    }
                    break;

                case 'saved':
                    if ($pub->pivot && $pub->pivot->created_at) {
                        $createdAt = is_string($pub->pivot->created_at)
                            ? \Carbon\Carbon::parse($pub->pivot->created_at)
                            : $pub->pivot->created_at;
                        $actionTime = 'Disimpan ' . $createdAt->diffForHumans();
                    } else {
                        $actionTime = 'Disimpan baru-baru ini';
                    }
                    break;
            }

            return [
                'id' => $pub->id,
                'title' => $pub->title,
                'slug' => $pub->slug,
                'cover_url' => $this->getCoverUrl($pub),
                'category' => $pub->category_name,
                'formatted_date' => $pub->formatted_date,
                'type' => $pub->publicationType->name ?? 'Publikasi',
                'detail_url' => route('publikasi.show', $pub->slug),
                'action_time' => $actionTime,
                'authors_text' => $authorsText ?: 'Unknown',
                'authors' => $pub->authors->map(function ($author) {
                    return [
                        'id' => $author->id,
                        'name' => $author->name,
                        'photo' => $this->getAuthorPhoto($author),
                        'initials' => $this->getInitials($author->name),
                    ];
                })->toArray(),
                'total_authors' => $pub->authors->count(),
            ];
        });

        return view('pages.publication.library', compact('publications', 'stats', 'activeTab'));
    }


        /*
    |--------------------------------------------------------------------------
    | Library Action Methods
    |--------------------------------------------------------------------------
    */

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

        // ✅ Log ke history jika user login
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
            'authors' => $publication->authors->map(function ($author) {
                return [
                    'id' => $author->id,
                    'name' => $author->name,
                    'initials' => $this->getInitials($author->name),
                    'photo' => $this->getAuthorPhoto($author),
                ];
            }),
        ]);
    }

    /**
     * ✅ Show author profile
     */
    public function showAuthor($id)
    {
        $author = Author::with([
            'user',
            'publications' => function ($query) {
                $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->orderBy('published_at', 'desc');
            }
        ])->findOrFail($id);

        return view('pages.author.show', compact('author'));
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helper Methods
    |--------------------------------------------------------------------------
    */

    private function formatFileSize($bytes)
    {
        if ($bytes == 0) return '0 B';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, $k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    private function logPublicationView(Publication $publication): void
    {
        $cacheKey = "publication_view_throttle.{$publication->id}." . request()->ip();

        if (!Cache::has($cacheKey)) {
            PublicationViewLog::logView($publication);
            $publication->clearStatsCache();
            Cache::put($cacheKey, true, now()->addMinutes(5));
        }
    }

    private function cleanPath($path)
    {
        if (!$path) return null;
        if (str_starts_with($path, 'public/')) {
            return substr($path, 7);
        }
        return $path;
    }

    private function getTypeContentCover($content)
    {
        if (!$content || !$content->image_path) {
            return 'https://placehold.co/800x600/FF6B18/white?text=' . urlencode('Publication Type');
        }

        $cleanPath = $this->cleanPath($content->image_path);

        if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }

        return 'https://placehold.co/800x600/FF6B18/white?text=' . urlencode($content->title ?? 'Content');
    }

    private function getCoverUrl($publication)
    {
        if (!$publication->cover_image_path) {
            return $this->getPlaceholderCover($publication);
        }

        $cleanPath = $this->cleanPath($publication->cover_image_path);

        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }

        if (config('app.debug')) {
            Log::warning("Cover image not found", [
                'publication_id' => $publication->id,
                'title' => $publication->title,
                'cover_path' => $publication->cover_image_path,
                'clean_path' => $cleanPath,
            ]);
        }

        return $this->getPlaceholderCover($publication);
    }

    private function getPlaceholderCover($publication)
    {
        $categoryName = 'Publikasi';

        if ($publication->relationLoaded('categories') && $publication->categories->isNotEmpty()) {
            $categoryName = $publication->categories->first()->name;
        }

        $titleShort = \Illuminate\Support\Str::limit($publication->title, 25, '');

        return 'https://placehold.co/400x600/FF6B18/white?text=' . urlencode($titleShort);
    }

    private function getAuthorPhoto($author)
    {
        if ($author->photo_path) {
            $cleanPath = $this->cleanPath($author->photo_path);

            if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
        }

        if ($author->user_id && $author->relationLoaded('user') && $author->user) {
            if ($author->user->profile_photo) {
                $cleanPath = $this->cleanPath($author->user->profile_photo);

                if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
                    return asset('storage/' . $cleanPath);
                }
            }
        }

        $initials = $this->getInitials($author->name);

        return 'https://ui-avatars.com/api/?' . http_build_query([
            'name' => $initials,
            'background' => 'FF6B18',
            'color' => 'ffffff',
            'size' => '128',
            'bold' => 'true',
            'font-size' => '0.5',
            'length' => '2'
        ]);
    }

    private function getInitials($name)
    {
        $name = trim($name);
        $words = preg_split('/\s+/', $name);

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }
}
