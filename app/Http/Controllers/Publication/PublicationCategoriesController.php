<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Keyword;
use App\Models\Publication;
use App\Models\PublicationType;
use Illuminate\Http\Request;

class PublicationCategoriesController extends Controller
{
    public function categories(Request $request, ?string $categorySlug = null)
    {
        $selectedType   = $request->query('type', 'all');
        $filterSort     = $request->query('sort', 'latest');
        $searchQuery    = $request->query('search');
        $filterCategory = $categorySlug ?? $request->query('category');
        $filterYear     = $request->query('year');
        $filterKeyword  = $request->query('keyword');

        // ✅ Categories — path icon disimpan mentah (resolve di blade)
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
            ->having('publications_count', '>', 0)
            ->orderByDesc('publications_count')
            ->get()
            ->map(function ($category) {
                return [
                    'id'                 => $category->id,
                    'name'               => $category->name,
                    'slug'               => $category->slug,
                    'description'        => $category->description,
                    'publications_count' => $category->publications_count,
                    'icon'               => $category->icon ?? null,   // ✅ path mentah
                    'color'              => $category->color ?? '#FF6B18',
                ];
            });

        // ✅ PublicationTypes
        $publicationTypes = PublicationType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name']);

        // ✅ Years
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

        // ✅ Top Keywords
        $topKeywords = Keyword::withCount([
            'publications' => fn($q) => $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
        ])
            ->having('publications_count', '>', 0)
            ->orderByDesc('publications_count')
            ->limit(20)
            ->get();

        // ✅ Current Category object
        $currentCategory = null;
        if ($filterCategory) {
            $currentCategory = Category::where('slug', $filterCategory)->firstOrFail();
        }

        // ✅ Publications — hanya query jika ada filterCategory
        $publications = null;
        if ($filterCategory) {
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

            $publicationsQuery->whereHas(
                'categories',
                fn($q) => $q->where('slug', $filterCategory)
            );

            if ($filterYear) {
                $publicationsQuery->whereYear('published_at', $filterYear);
            }

            if ($filterKeyword) {
                $publicationsQuery->whereHas(
                    'keywords',
                    fn($q) => $q->where('slug', $filterKeyword)
                );
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
        }

        return view('pages.publication.categories', [
            'categories'       => $categories,
            'publications'     => $publications,
            'publicationTypes' => $publicationTypes,
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
}
