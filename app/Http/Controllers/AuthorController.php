<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\PublicationHelperTrait; // ✅ Import trait
use App\Models\Author;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthorController extends Controller
{
    use PublicationHelperTrait; // ✅ Use trait untuk getCoverUrl() dan helper methods

    /**
     * Display the author's profile with their publications
     * Support both Author model and User model
     */
    public function show($identifier)
    {
        $author = null;
        $user = null;
        $isUserProfile = false;

        // Try to find in Author table first (by ID)
        if (is_numeric($identifier)) {
            $author = Author::with('user')->find($identifier);
        }

        // If not found, try to find in User table
        if (!$author) {
            $user = User::find($identifier);
            if ($user) {
                $isUserProfile = true;
                // Check if user has author profile
                $author = $user->authorProfile ?? null;
            }
        }

        // If still not found, return 404
        if (!$author && !$user) {
            abort(404, 'Author not found');
        }

        // Prepare data based on author type
        if ($isUserProfile && $user) {
            $name = $user->name;
            $email = $user->email;
            $bio = $user->bio ?? null;
            $affiliation = $user->affiliation ?? $user->job_title ?? null;
            $photoUrl = $user->photo_url;

            // Get publications through author profile
            if ($author) {
                $publicationsQuery = $author->publications();
            } else {
                // If user doesn't have author profile, return empty data
                return view('author.profile', [
                    'user' => $user,
                    'author' => null,
                    'name' => $name,
                    'email' => $email,
                    'bio' => $bio,
                    'affiliation' => $affiliation,
                    'photoUrl' => $photoUrl,
                    'publications' => collect()->paginate(9),
                    'formattedPublications' => collect(), // ✅ ADDED
                    'totalPublications' => 0,
                    'totalViews' => 0,
                    'totalDownloads' => 0,
                    'coAuthors' => collect(),
                    'isUserProfile' => $isUserProfile,
                ]);
            }
        } else {
            $name = $author->name;
            $email = $author->email;
            $bio = $author->bio;
            $affiliation = $author->affiliation;
            $photoUrl = $author->photo_url;
            $user = $author->user; // Get related user if exists
            $publicationsQuery = $author->publications();
        }

        // ✅ Get publications with pagination (9 per page)
        $publications = $publicationsQuery
            ->with(['publicationType', 'categories', 'authors.user', 'downloadLogs', 'viewLogs']) // ✅ Added logs
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(9)
            ->withQueryString(); // ✅ Preserve query string

        // ✅ Format publications untuk view (gunakan getCollection()->transform untuk preserve pagination)
        $formattedPublications = $publications->getCollection()->transform(function ($publication) {
            $category = $publication->categories->first();

            // ✅ Get publication type with fallback
            $pubType = 'Publikasi';
            if ($publication->publicationType) {
                $pubType = $publication->publicationType->name;
            }

            // ✅ Get cover URL using trait method
            $coverUrl = $this->getCoverUrl($publication);

            return [
                'id' => $publication->id,
                'title' => $publication->title,
                'slug' => $publication->slug,
                'abstract' => $publication->abstract ? Str::limit($publication->abstract, 150) : 'No abstract available',
                'cover_url' => $coverUrl, // Bisa null
                'category' => $category ? $category->name : 'Uncategorized',
                'category_slug' => $category ? $category->slug : null,
                'publication_type' => $pubType, // ✅ ADDED: Correct key
                'type' => $pubType, // ✅ Backward compatibility
                'type_slug' => $publication->publicationType->slug ?? 'publikasi',
                'formatted_date' => $publication->published_at?->locale('id_ID')->isoFormat('D MMM Y'),
                'year' => $publication->published_at?->year,
                'detail_url' => route('publikasi.show', $publication->slug),
                'authors' => $publication->authors->map(function ($author) {
                    return [
                        'name' => $author->name,
                        'photo' => $author->photo_url, // ✅ Use accessor
                        'initials' => $author->initials, // ✅ ADDED
                    ];
                })->toArray(),
                'total_authors' => $publication->authors->count(),
                'views_count' => $publication->viewLogs->count(),
                'download_count' => $publication->downloadLogs->count(),
            ];
        });

        // ✅ Get publication IDs for stats
        $publicationIds = $author->publications()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->pluck('publications.id')
            ->toArray();

        // ✅ Count statistics
        $totalPublications = count($publicationIds);

        // ✅ Total Views
        $totalViews = 0;
        if (!empty($publicationIds)) {
            $totalViews = DB::table('publication_view_logs')
                ->whereIn('publication_id', $publicationIds)
                ->count();
        }

        // ✅ Total Downloads
        $totalDownloads = 0;
        if (!empty($publicationIds)) {
            $totalDownloads = DB::table('download_logs')
                ->whereIn('publication_id', $publicationIds)
                ->count();
        }

        // ✅ Get co-authors
        $coAuthors = collect();
        if ($author) {
            $coAuthors = Author::whereHas('publications', function ($query) use ($author) {
                $query->whereIn('publications.id', function ($subQuery) use ($author) {
                    $subQuery->select('publication_id')
                        ->from('author_publication')
                        ->where('author_id', $author->id);
                })
                    ->where('publications.status', 'published')
                    ->whereNotNull('publications.published_at')
                    ->where('publications.published_at', '<=', now());
            })
                ->where('id', '!=', $author->id)
                ->withCount(['publications' => function ($query) {
                    $query->where('status', 'published')
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now());
                }])
                ->limit(6)
                ->get()
                ->map(function ($coAuthor) {
                    return [
                        'id' => $coAuthor->id,
                        'name' => $coAuthor->name,
                        'photo_url' => $coAuthor->photo_url,
                        'initials' => $coAuthor->initials,
                        'publications_count' => $coAuthor->publications_count,
                        'profile_url' => route('author.profile', $coAuthor->id),
                    ];
                });
        }

        return view('author.profile', [
            'author' => $author,
            'user' => $user,
            'name' => $name,
            'email' => $email,
            'bio' => $bio,
            'affiliation' => $affiliation,
            'photoUrl' => $photoUrl,
            'publications' => $publications, // ✅ Paginator object
            'formattedPublications' => $formattedPublications, // ✅ Formatted data
            'totalPublications' => $totalPublications,
            'totalViews' => $totalViews,
            'totalDownloads' => $totalDownloads,
            'coAuthors' => $coAuthors,
            'isUserProfile' => $isUserProfile,
        ]);
    }
}
