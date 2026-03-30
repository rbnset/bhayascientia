<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\PublicationHelperTrait;
use App\Models\Author;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthorController extends Controller
{
    use PublicationHelperTrait;

    /**
     * Display the author's profile with their publications.
     *
     * Identifier priority (untuk backward-compatibility):
     *   1. slug  (robin-setiyawan)       → utama, SEO-friendly
     *   2. id    (7)                     → redirect permanen ke slug
     */
    public function show(Request $request, string $identifier)
    {
        // ── 1. Cari by slug dulu (kasus normal) ──────────────────────────
        $author = Author::with('user')->where('slug', $identifier)->first();

        // ── 2. Fallback: jika identifier adalah angka, cari by ID ────────
        //       lalu redirect 301 ke URL slug agar link lama tetap bekerja
        if (!$author && is_numeric($identifier)) {
            $author = Author::with('user')->find((int) $identifier);

            if ($author && !empty($author->slug)) {
                return redirect()
                    ->route('author.profile', $author->slug)
                    ->setStatusCode(301); // ✅ 301 = permanen, bagus untuk SEO
            }
        }

        // ── 3. Fallback: cari di tabel users (kasus legacy / direct link) ──
        $user = null;
        if (!$author) {
            $user = is_numeric($identifier) ? User::find($identifier) : null;

            if ($user) {
                $author = $user->authorProfile ?? null;

                // Jika user punya author profile, redirect ke slug author
                if ($author && !empty($author->slug)) {
                    return redirect()
                        ->route('author.profile', $author->slug)
                        ->setStatusCode(301);
                }
            }
        }

        if (!$author && !$user) {
            abort(404, 'Author not found');
        }

        // ── 4. Kasus: user tanpa author profile (sangat jarang) ──────────
        if (!$author) {
            return view('author.profile', [
                'user'                  => $user,
                'author'                => null,
                'name'                  => $user->name,
                'email'                 => $user->email,
                'bio'                   => $user->bio ?? null,
                'affiliation'           => $user->affiliation ?? $user->job_title ?? null,
                'photoUrl'              => $user->photo_url,
                'publications'          => collect()->paginate(9),
                'formattedPublications' => collect(),
                'totalPublications'     => 0,
                'totalViews'            => 0,
                'totalDownloads'        => 0,
                'coAuthors'             => collect(),
                'isUserProfile'         => true,
                // SEO
                'seoTitle'              => $user->name . ' — Profil Author | DABRAKA',
                'seoDescription'        => 'Profil dan publikasi ilmiah dari ' . $user->name . ' di DABRAKA.',
                'seoUrl'                => request()->url(),
                'seoImage'              => $user->photo_url ?? null,
            ]);
        }

        // ── 5. Author ditemukan — load user jika perlu ───────────────────
        if (!$author->relationLoaded('user')) {
            $author->load('user');
        }

        $user        = $author->user;
        $name        = $author->name;
        $email       = $author->email;
        $bio         = $author->bio ?? ($user?->bio ?? null);
        $affiliation = $author->affiliation ?? ($user?->job_title ?? null);
        $photoUrl    = $author->photo_url;

        // ── 6. Publications ──────────────────────────────────────────────
        $publications = $author->publications()
            ->with(['publicationType', 'categories', 'authors.user', 'downloadLogs', 'viewLogs'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(9)
            ->withQueryString();

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

        // ── 7. Stats ─────────────────────────────────────────────────────
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

        // ── 8. Co-authors ─────────────────────────────────────────────────
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
                // ✅ Gunakan slug untuk URL co-author
                'profile_url'        => route('author.profile', $coAuthor->slug),
            ]);

        // ── 9. SEO Meta ───────────────────────────────────────────────────
        $latestPubYear = $publications->first()?->published_at?->year;
        $pubTypeList   = $publications->getCollection()
            ->pluck('publication_type')->unique()->implode(', ');

        $seoTitle = $name . ' — Profil Peneliti & Publikasi Ilmiah | DABRAKA';

        $seoDescription = trim(
            $name
                . ($affiliation ? ', ' . $affiliation : '')
                . '. Penulis ' . $totalPublications . ' publikasi ilmiah'
                . ($latestPubYear ? ' (terakhir ' . $latestPubYear . ')' : '')
                . ($pubTypeList ? ': ' . $pubTypeList : '')
                . '. Temukan karya ilmiah lengkapnya di DABRAKA.'
        );

        // Canonical URL selalu pakai slug
        $canonicalUrl = route('author.profile', $author->slug);

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
            // ✅ SEO variables
            'seoTitle'              => $seoTitle,
            'seoDescription'        => $seoDescription,
            'seoUrl'                => $canonicalUrl,
            'seoImage'              => $photoUrl,
            'seoAuthorName'         => $name,
            'seoAffiliation'        => $affiliation,
        ]);
    }
}
