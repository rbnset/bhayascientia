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
        $user   = null;

        // ✅ Selalu cari di tabel authors dulu
        if (is_numeric($identifier)) {
            $author = Author::with('user')->find($identifier);
        }

        // ✅ Fallback: cari di tabel users (untuk kasus lama / direct link)
        if (!$author) {
            $user = User::find($identifier);
            if ($user) {
                // Cek apakah user ini punya author profile
                $author = $user->authorProfile ?? null;
            }
        }

        if (!$author && !$user) {
            abort(404, 'Author not found');
        }

        // ✅ Jika author ditemukan (kasus utama sekarang)
        if ($author) {
            // Load user jika belum ter-load
            if (!$author->relationLoaded('user')) {
                $author->load('user');
            }

            $user        = $author->user; // bisa null jika external author
            $name        = $author->name; // accessor resolved dari user jika ada
            $email       = $author->email;
            $bio         = $author->bio ?? ($user?->bio ?? null);
            $affiliation = $author->affiliation ?? ($user?->job_title ?? null);
            $photoUrl    = $author->photo_url;

            $publicationsQuery = $author->publications();
        } else {
            // Fallback: user tanpa author profile
            $name        = $user->name;
            $email       = $user->email;
            $bio         = $user->bio ?? null;
            $affiliation = $user->affiliation ?? $user->job_title ?? null;
            $photoUrl    = $user->photo_url;

            return view('author.profile', [
                'user'                  => $user,
                'author'                => null,
                'name'                  => $name,
                'email'                 => $email,
                'bio'                   => $bio,
                'affiliation'           => $affiliation,
                'photoUrl'              => $photoUrl,
                'publications'          => collect()->paginate(9),
                'formattedPublications' => collect(),
                'totalPublications'     => 0,
                'totalViews'            => 0,
                'totalDownloads'        => 0,
                'coAuthors'             => collect(),
                'isUserProfile'         => true,
            ]);
        }

        // ✅ Get publications dengan pagination
        $publications = $publicationsQuery
            ->with(['publicationType', 'categories', 'authors.user', 'downloadLogs', 'viewLogs'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(9)
            ->withQueryString();

        // ✅ Format publications
        $formattedPublications = $publications->getCollection()->transform(function ($publication) {
            $category = $publication->categories->first();

            $pubType  = $publication->publicationType?->name ?? 'Publikasi';
            $coverUrl = $this->getCoverUrl($publication);

            return [
                'id'               => $publication->id,
                'title'            => $publication->title,
                'slug'             => $publication->slug,
                'abstract'         => $publication->abstract
                    ? Str::limit(strip_tags($publication->abstract), 150)
                    : 'No abstract available',
                'cover_url'        => $coverUrl,
                'category'         => $category?->name ?? 'Uncategorized',
                'category_slug'    => $category?->slug ?? null,
                'publication_type' => $pubType,
                'type'             => $pubType,
                'type_slug'        => $publication->publicationType?->slug ?? 'publikasi',
                'formatted_date'   => $publication->published_at
                    ?->locale('id_ID')
                    ->isoFormat('D MMM Y'),
                'year'             => $publication->published_at?->year,
                'detail_url'       => route('publikasi.show', $publication->slug),
                'authors'          => $publication->authors->map(fn($a) => [
                    'name'     => $a->name,
                    'photo'    => $a->photo_url,
                    'initials' => $a->initials,
                ])->toArray(),
                'total_authors'    => $publication->authors->count(),
                'views_count'      => $publication->viewLogs->count(),
                'download_count'   => $publication->downloadLogs->count(),
            ];
        });

        // ✅ Stats
        $publicationIds = $author->publications()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->pluck('publications.id')
            ->toArray();

        $totalPublications = count($publicationIds);

        $totalViews = empty($publicationIds) ? 0
            : DB::table('publication_view_logs')
            ->whereIn('publication_id', $publicationIds)
            ->count();

        $totalDownloads = empty($publicationIds) ? 0
            : DB::table('download_logs')
            ->whereIn('publication_id', $publicationIds)
            ->count();

        // ✅ Co-authors
        $coAuthors = Author::whereHas('publications', function ($query) use ($author) {
            $query->whereIn('publications.id', function ($sub) use ($author) {
                $sub->select('publication_id')
                    ->from('author_publication')
                    ->where('author_id', $author->id);
            })
                ->where('publications.status', 'published')
                ->whereNotNull('publications.published_at')
                ->where('publications.published_at', '<=', now());
        })
            ->where('id', '!=', $author->id)
            ->withCount([
                'publications' => fn($q) =>
                $q->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
            ])
            ->limit(6)
            ->get()
            ->map(fn($coAuthor) => [
                'id'                 => $coAuthor->id,
                'name'               => $coAuthor->name,
                'photo_url'          => $coAuthor->photo_url,
                'initials'           => $coAuthor->initials,
                'publications_count' => $coAuthor->publications_count,
                // ✅ FIXED: selalu gunakan author->id
                'profile_url'        => route('author.profile', $coAuthor->id),
            ]);

        return view('author.profile', [
            'author'                => $author,
            'user'                  => $user,
            'name'                  => $name,
            'email'                 => $email,
            'bio'                   => $bio,
            'affiliation'           => $affiliation,
            'photoUrl'              => $photoUrl,
            'publications'          => $publications,
            'formattedPublications' => $formattedPublications,
            'totalPublications'     => $totalPublications,
            'totalViews'            => $totalViews,
            'totalDownloads'        => $totalDownloads,
            'coAuthors'             => $coAuthors,
            'isUserProfile'         => (bool) $author->user_id,
        ]);
    }
}
