<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublicationController extends Controller
{
    public function index(Request $request)
    {
        $publicationTypes = PublicationType::where('is_active', true)
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
            ]);
        }

        $selectedType = $request->query('type', $publicationTypes->first()->slug);

        $typeExists = $publicationTypes->contains('slug', $selectedType);
        if (!$typeExists) {
            $selectedType = $publicationTypes->first()->slug;
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
                        'initials' => $this->getInitials($author->name), // ✅ Tambahkan initials
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
                    'initials' => $this->getInitials($author->name), // ✅ Tambahkan initials
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
        $featuredPublication = $popularPubs->first() ? [
            'id' => $popularPubs->first()->id,
            'title' => $popularPubs->first()->title,
            'slug' => $popularPubs->first()->slug,
            'cover_url' => $this->getCoverUrl($popularPubs->first()),
            'category' => $popularPubs->first()->category_name,
            'type' => $popularPubs->first()->publicationType->name ?? 'Publikasi',
            'abstract' => \Illuminate\Support\Str::limit($popularPubs->first()->abstract, 120),
            'download_count' => $popularPubs->first()->download_logs_count,
            'detail_url' => route('publikasi.show', $popularPubs->first()->slug),
        ] : null;

        // ✅ Popular Publications List (6 sisanya)
        $popularPublications = $popularPubs->skip(1)->take(6)->map(function ($pub) {
            return [
                'id' => $pub->id,
                'title' => $pub->title,
                'slug' => $pub->slug,
                'cover_url' => $this->getCoverUrl($pub),
                'category' => $pub->category_name,
                'formatted_date' => $pub->formatted_date,
                'download_count' => $pub->download_logs_count,
                'detail_url' => route('publikasi.show', $pub->slug),
                'authors' => $pub->authors->map(function ($author) {
                    return [
                        'id' => $author->id,
                        'name' => $author->name,
                        'photo' => $this->getAuthorPhoto($author),
                        'initials' => $this->getInitials($author->name), // ✅ Tambahkan initials
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
            'featuredPublication'
        ));
    }

    public function show($slug)
    {
        $publication = Publication::with([
            'authors.user',
            'publicationType',
            'categories',
            'keywords',
            'versions' => function ($query) {
                $query->latest();
            },
            'method',
            'downloadLogs'
        ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $data = [
            'publication' => $publication,
            'category' => $publication->categories->first()?->name ?? 'Umum',
            'authors' => $publication->authors->map(function ($author) {
                return [
                    'id' => $author->id,
                    'name' => $author->name,
                    'affiliation' => $author->affiliation ?? 'N/A',
                    'initials' => $this->getInitials($author->name),
                    'photo' => $this->getAuthorPhoto($author),
                    'is_corresponding' => $author->pivot->is_corresponding ?? false,
                ];
            }),
            'keywords' => $publication->keywords->pluck('name'),
            'formatted_date' => $publication->published_at->format('F j, Y'),
            'cover_url' => $this->getCoverUrl($publication),
        ];

        return view('pages.publication.show', $data);
    }

    /**
     * ✅ Helper: Clean storage path (remove 'public/' prefix)
     */
    private function cleanPath($path)
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'public/')) {
            return substr($path, 7);
        }

        return $path;
    }

    /**
     * ✅ Helper: Generate cover URL dengan fallback
     */
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
                'storage_path' => storage_path('app/public/' . $cleanPath),
                'file_exists' => file_exists(storage_path('app/public/' . $cleanPath)),
            ]);
        }

        return $this->getPlaceholderCover($publication);
    }

    /**
     * ✅ Helper: Generate placeholder cover
     */
    private function getPlaceholderCover($publication)
    {
        $categoryName = 'Publikasi';

        if ($publication->relationLoaded('categories') && $publication->categories->isNotEmpty()) {
            $categoryName = $publication->categories->first()->name;
        }

        $titleShort = \Illuminate\Support\Str::limit($publication->title, 25, '');

        return 'https://placehold.co/400x600/FF6B18/white?text=' . urlencode($titleShort);
    }

    /**
     * ✅ Helper: Get author photo dengan fallback ke UI Avatars dengan INISIAL
     */
    private function getAuthorPhoto($author)
    {
        // ✅ Prioritas 1: Cek photo_path dari Author model
        if ($author->photo_path) {
            $cleanPath = $this->cleanPath($author->photo_path);

            if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
        }

        // ✅ Prioritas 2: Cek profile_photo dari User (jika author punya user_id)
        if ($author->user_id && $author->relationLoaded('user') && $author->user) {
            if ($author->user->profile_photo) {
                $cleanPath = $this->cleanPath($author->user->profile_photo);

                if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
                    return asset('storage/' . $cleanPath);
                }
            }
        }

        // ✅ Prioritas 3: Fallback ke UI Avatars dengan INISIAL dari nama
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

    /**
     * ✅ Helper: Generate initials dari nama (2 huruf pertama)
     */
    private function getInitials($name)
    {
        $name = trim($name);
        $words = preg_split('/\s+/', $name);

        if (count($words) >= 2) {
            // Ambil huruf pertama dari 2 kata pertama
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        // Jika hanya 1 kata, ambil 2 huruf pertama
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * ✅ Download publikasi
     */
    public function download($slug)
    {
        $publication = Publication::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $latestVersion = $publication->versions()->latest()->first();

        if (!$latestVersion || !$latestVersion->file_path) {
            abort(404, 'File publikasi tidak ditemukan');
        }

        try {
            \App\Models\DownloadLog::create([
                'publication_id' => $publication->id,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::warning("Download log failed: " . $e->getMessage());
        }

        $filePath = $this->cleanPath($latestVersion->file_path);

        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File tidak ditemukan di storage');
        }

        return Storage::disk('public')->download(
            $filePath,
            $publication->title . '.pdf'
        );
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
}
