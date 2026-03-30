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
        $selectedType   = $request->get('type', 'all');
        $filterCategory = $request->get('category');
        $filterYear     = $request->get('year');
        $filterSort     = $request->get('sort', 'latest');
        $perPage        = $request->get('per_page', 12);

        $publicationTypes = PublicationType::where('is_active', true)
            ->withCount(['publications' => function ($q) {
                $q->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            }])
            ->orderBy('name')
            ->get();

        // Base query
        // ✅ FIX: Ganti eager load viewLogs & downloadLogs (load semua baris = lambat)
        // dengan withCount — hanya hitung jumlahnya di DB, jauh lebih efisien
        $query = Publication::with([
            'publicationType',
            'categories',
            'authors.user',
            'versions',
        ])
            ->withCount([
                // ✅ FIX: viewLogs pakai tabel publication_view_logs, kolom viewed_at
                // withCount tidak butuh nama kolom timestamp — hanya count() saja
                'viewLogs as views_count',
                'downloadLogs as downloads_count',
            ])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($selectedType !== 'all') {
            $query->whereHas('publicationType', function ($q) use ($selectedType) {
                $q->where('slug', $selectedType);
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

        switch ($filterSort) {
            case 'popular':
                // ✅ FIX: sort by trending score (views + downloads * 2)
                // konsisten dengan TrendingController dan IndexController
                $query->orderByRaw('(views_count + downloads_count * 2) DESC');
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

        $years = Publication::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->selectRaw('YEAR(published_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $formattedPublications = $publications->map(function ($publication) {
            $category = $publication->categories->first();
            $pubType  = $publication->publicationType?->name ?? 'Publikasi';
            $coverUrl = $this->getCoverUrl($publication);

            $abstract = $publication->abstract
                ? Str::limit(strip_tags($publication->abstract), 150)
                : null;

            return [
                'id'               => $publication->id,
                'title'            => $publication->title,
                'slug'             => $publication->slug,
                'abstract'         => $abstract,
                'cover_url'        => $coverUrl,
                'category'         => $category?->name ?? 'Uncategorized',
                'category_slug'    => $category?->slug,
                'publication_type' => $pubType,
                'type'             => $pubType,
                'type_slug'        => $publication->publicationType?->slug ?? 'publikasi',
                'formatted_date'   => $publication->published_at?->locale('id_ID')->isoFormat('D MMM Y'),
                'year'             => $publication->published_at?->year,
                'detail_url'       => route('publikasi.show', $publication->slug),
                'authors'          => $publication->authors->take(3)->map(function ($author) {
                    return [
                        'name'        => $author->name,
                        'photo'       => $author->photo_url,
                        'initials'    => $author->initials,
                        'profile_url' => route('author.profile', $author->user_id ?? $author->id),
                    ];
                })->toArray(),
                'total_authors'   => $publication->authors->count(),
                // ✅ Ambil dari withCount — bukan dari relasi eager load
                'views_count'     => (int) $publication->views_count,
                'download_count'  => (int) $publication->downloads_count,
                'downloads_count' => (int) $publication->downloads_count,
            ];
        });

        $stats = [
            'total'      => Publication::where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->count(),
            'this_year'  => Publication::where('status', 'published')
                ->whereNotNull('published_at')
                ->whereYear('published_at', now()->year)
                ->count(),
            'categories' => $categories->count(),
            'authors'    => Author::has('publications')->count(),
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
            'stats',
        ))->with([
            'seoTitle'       => 'Jelajahi Semua Publikasi — DABRAKA',
            'seoDescription' => 'Temukan jurnal, buku, dan opini ilmiah dari insan Bhayangkara. Filter berdasarkan kategori, tahun, dan kata kunci.',
        ]);
    }
}
