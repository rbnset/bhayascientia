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
                'bestAuthors' => collect([])
            ]);
        }

        $selectedType = $request->query('type', $publicationTypes->first()->slug);

        $typeExists = $publicationTypes->contains('slug', $selectedType);
        if (!$typeExists) {
            $selectedType = $publicationTypes->first()->slug;
        }

        // ✅ Get Latest Publications
        $publications = Publication::with([
            'authors',
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
                        ? Storage::disk('public')->exists($pub->cover_image_path)
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
                    ];
                })->toArray(),
                'total_authors' => $pub->authors->count(),
            ];
        })->toArray();

        // ✅ Get Best Authors BERDASARKAN FILTER yang dipilih
        $bestAuthors = Author::query()
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
            ->limit(6) // ✅ UBAH: Maksimal 6 author
            ->get()
            ->map(function ($author) {
                return [
                    'name' => $author->name,
                    'avatar' => $this->getAuthorPhoto($author),
                    'publication_count' => $author->publications_count,
                    'profile_url' => route('author.show', $author->id),
                    'verified' => $author->user_id !== null,
                    'specialty' => $author->affiliation ?? null,
                ];
            });

        return view('pages.publication.index', compact(
            'latestPublications',
            'publicationTypes',
            'selectedType',
            'bestAuthors'
        ));
    }

    public function show($slug)
    {
        $publication = Publication::with([
            'authors',
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

    private function getCoverUrl($publication)
    {
        if (!$publication->cover_image_path) {
            return $this->getPlaceholderCover($publication);
        }

        $cleanPath = $publication->cover_image_path;
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }

        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }

        Log::warning("Cover image not found", [
            'publication_id' => $publication->id,
            'cover_path' => $publication->cover_image_path,
            'clean_path' => $cleanPath,
            'full_path' => storage_path('app/public/' . $cleanPath),
        ]);

        return $this->getPlaceholderCover($publication);
    }

    private function getPlaceholderCover($publication)
    {
        $category = $publication->categories->first()?->name ?? 'Publikasi';
        $titleShort = substr($publication->title, 0, 20);

        return 'https://placehold.co/400x600/FF6B18/white?text=' . urlencode($titleShort);
    }

    private function getAuthorPhoto($author)
    {
        if (!$author->photo_path) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($author->name) . '&background=FF6B18&color=fff&size=128&bold=true&font-size=0.4';
        }

        $cleanPath = $author->photo_path;
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }

        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($author->name) . '&background=FF6B18&color=fff&size=128&bold=true&font-size=0.4';
    }

    private function getInitials($name)
    {
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }

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

        $filePath = $latestVersion->file_path;
        if (str_starts_with($filePath, 'public/')) {
            $filePath = substr($filePath, 7);
        }

        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File tidak ditemukan di storage');
        }

        return Storage::disk('public')->download(
            $filePath,
            $publication->title . '.pdf'
        );
    }

    public function showAuthor($id)
    {
        $author = Author::with(['publications' => function ($query) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc');
        }])->findOrFail($id);

        return view('pages.author.show', compact('author'));
    }
}
