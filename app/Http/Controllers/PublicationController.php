<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\PublicationType;
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
                'selectedType' => null
            ]);
        }

        $selectedType = $request->query('type', $publicationTypes->first()->slug);

        $typeExists = $publicationTypes->contains('slug', $selectedType);
        if (!$typeExists) {
            $selectedType = $publicationTypes->first()->slug;
        }

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
            ->take(10)
            ->get();

        $latestPublications = $publications->map(function ($pub) {
            // ✅ Generate cover URL dengan validasi
            $coverUrl = $this->getCoverUrl($pub);

            // ✅ Debug logging (hanya di development)
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

        return view('pages.publication.index', compact('latestPublications', 'publicationTypes', 'selectedType'));
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

        // Format data untuk view
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
     * ✅ Helper: Generate cover URL dengan fallback
     */
    private function getCoverUrl($publication)
    {
        // Jika tidak ada cover path
        if (!$publication->cover_image_path) {
            return $this->getPlaceholderCover($publication);
        }

        // Clean path (remove 'public/' prefix jika ada)
        $cleanPath = $publication->cover_image_path;
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7); // Remove 'public/'
        }

        // Cek apakah file ada di storage
        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }

        // Log jika file tidak ditemukan
        Log::warning("Cover image not found", [
            'publication_id' => $publication->id,
            'cover_path' => $publication->cover_image_path,
            'clean_path' => $cleanPath,
            'full_path' => storage_path('app/public/' . $cleanPath),
        ]);

        // Return placeholder jika file tidak ada
        return $this->getPlaceholderCover($publication);
    }

    /**
     * ✅ Helper: Generate placeholder cover
     */
    private function getPlaceholderCover($publication)
    {
        $category = $publication->categories->first()?->name ?? 'Publikasi';
        $titleShort = substr($publication->title, 0, 20);

        return 'https://placehold.co/400x600/FF6B18/white?text=' . urlencode($titleShort);
    }

    /**
     * ✅ Helper: Get author photo dengan fallback
     */
    private function getAuthorPhoto($author)
    {
        if (!$author->photo_path) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($author->name) . '&background=FF6B18&color=fff&size=128&bold=true&font-size=0.4';
        }

        // Clean path
        $cleanPath = $author->photo_path;
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }

        // Cek file ada
        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }

        // Fallback ke UI Avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($author->name) . '&background=FF6B18&color=fff&size=128&bold=true&font-size=0.4';
    }

    /**
     * ✅ Helper: Generate initials dari nama
     */
    private function getInitials($name)
    {
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * ✅ Download publikasi (untuk nanti)
     */
    public function download($slug)
    {
        $publication = Publication::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Get latest version file
        $latestVersion = $publication->versions()->latest()->first();

        if (!$latestVersion || !$latestVersion->file_path) {
            abort(404, 'File publikasi tidak ditemukan');
        }

        // Log download (jika ada model DownloadLog)
        try {
            \App\Models\DownloadLog::create([
                'publication_id' => $publication->id,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silent fail jika model tidak ada
            Log::warning("Download log failed: " . $e->getMessage());
        }

        // Download file
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
}
