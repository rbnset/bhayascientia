<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Pivots\AuthorPublication;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Author extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'orcid_id',
        'slug',        // ✅ SEO-friendly URL identifier
        'name',        // NULL jika linked ke user — dibaca dari users.name
        'email',       // NULL jika linked ke user — dibaca dari users.email
        'affiliation', // NULL = fallback ke users.affiliation / users.job_title
        'bio',         // NULL = fallback ke users.bio
        'photo_path',  // NULL = fallback ke users.profile_photo / users.avatar
    ];

    // ========================================
    // BOOT — Auto-generate slug
    // ========================================

    protected static function booted(): void
    {
        static::creating(function (Author $author) {
            if (empty($author->slug)) {
                $author->slug = $author->generateUniqueSlug();
            }
        });

        // ✅ Saat nama author berubah (misal edit profil external author),
        //    slug TIDAK diubah otomatis agar URL tetap stabil.
        //    Jika ingin update slug, lakukan secara eksplisit.
    }

    // ========================================
    // SLUG HELPERS
    // ========================================

    /**
     * Generate slug unik dari nama author.
     * Untuk author linked ke user, nama diambil dari users.name.
     */
    public function generateUniqueSlug(?string $fromName = null): string
    {
        // Prioritas: parameter → user.name → author.name
        $name = $fromName
            ?? ($this->user_id ? User::find($this->user_id)?->name : null)
            ?? $this->getRawOriginal('name')
            ?? 'author';

        $base    = Str::slug($name);
        if (empty($base)) $base = 'author';

        $slug    = $base;
        $counter = 1;

        while (
            static::withTrashed()
            ->where('slug', $slug)
            ->where('id', '!=', $this->id ?? 0)
            ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    /**
     * Route model binding pakai slug.
     * Aktifkan ini agar Laravel bisa resolve Author::findOrFail($slug) via route.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ========================================
    // ACCESSORS — Baca dari User jika linked
    // ========================================

    public function getNameAttribute($value): string
    {
        if ($this->user_id) {
            if (!$this->relationLoaded('user')) {
                $this->load('user');
            }

            $resolved = $this->getRelation('user')?->name;
            if (!empty($resolved)) return $resolved;
        }

        return $value ?? 'Unknown Author';
    }

    public function getEmailAttribute($value): ?string
    {
        if ($this->user_id) {
            if (!$this->relationLoaded('user')) {
                $this->load('user');
            }

            $resolved = $this->getRelation('user')?->email;
            if (!empty($resolved)) return $resolved;
        }

        return $value;
    }

    public function getAffiliationAttribute($value): ?string
    {
        if (!empty($value)) return $value;

        if ($this->user_id) {
            if (!$this->relationLoaded('user')) {
                $this->load('user');
            }

            $user = $this->getRelation('user');
            if ($user) return $user->affiliation ?? $user->job_title;
        }

        return null;
    }

    public function getBioAttribute($value): ?string
    {
        if (!empty($value)) return $value;

        if ($this->user_id) {
            $user = $this->relationLoaded('user')
                ? $this->user
                : User::find($this->user_id);

            if ($user) return $user->bio;
        }

        return null;
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->getRawOriginal('photo_path')) {
            $cleanPath = $this->cleanPath($this->getRawOriginal('photo_path'));

            if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
        }

        if ($this->user_id) {
            $user = $this->relationLoaded('user')
                ? $this->user
                : User::find($this->user_id);

            if ($user) return $user->photo_url;
        }

        $name = $this->getRawOriginal('name') ?? 'Author';
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) .
            '&background=FF6B18&color=fff&size=160&bold=true&font-size=0.4&length=2';
    }

    public function getInitialsAttribute(): string
    {
        $name  = $this->name;
        $words = explode(' ', trim($name));

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    public function getShortBioAttribute(): ?string
    {
        $bio = $this->bio;

        if (!$bio) return $this->affiliation;

        return strlen($bio) > 150 ? substr($bio, 0, 147) . '...' : $bio;
    }

    // ========================================
    // CLAIM AUTHORSHIP
    // ========================================

    public function isClaimed(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * ✅ Saat user claim author, slug di-regenerate dari users.name
     *    supaya URL mencerminkan nama asli user (bukan nama lama external).
     */
    public function claimBy(User $user): array
    {
        if ($this->isClaimed()) {
            return [
                'success' => false,
                'message' => 'Profil author ini sudah terhubung ke akun lain.',
            ];
        }

        $existingAuthorProfile = $user->authorProfile()->first();

        if ($existingAuthorProfile) {
            return $this->mergeInto($existingAuthorProfile);
        }

        // Claim biasa: update user_id dan regenerate slug dari nama user
        $newSlug = $this->generateUniqueSlug($user->name);

        $this->update([
            'user_id' => $user->id,
            'slug'    => $newSlug, // ✅ Slug di-refresh dengan nama user
            'name'    => null,
            'email'   => null,
        ]);

        if ($this->getRawOriginal('orcid_id') && !$user->orcid_id) {
            $user->update(['orcid_id' => $this->getRawOriginal('orcid_id')]);
        }

        return [
            'success' => true,
            'message' => 'Berhasil! Profil author telah terhubung ke akun Anda.',
        ];
    }

    private function mergeInto(Author $targetAuthor): array
    {
        DB::transaction(function () use ($targetAuthor) {
            $myPublicationIds = $this->publications()->pluck('publications.id')->toArray();

            foreach ($myPublicationIds as $publicationId) {
                $alreadyLinked = $targetAuthor->publications()
                    ->where('publications.id', $publicationId)
                    ->exists();

                if (!$alreadyLinked) {
                    $pivotData = $this->authorPublications()
                        ->where('publication_id', $publicationId)
                        ->first();

                    $targetAuthor->publications()->attach($publicationId, [
                        'order'            => $pivotData?->order ?? 99,
                        'is_corresponding' => $pivotData?->is_corresponding ?? false,
                    ]);
                }
            }

            $this->publications()->detach();
            $this->delete();
        });

        return [
            'success' => true,
            'message' => 'Berhasil! Semua publikasi dari profil author lama telah digabungkan ke akun Anda.',
            'merged'  => true,
        ];
    }

    public function unclaim(): void
    {
        $user = $this->user;

        $this->update([
            'user_id'     => null,
            'name'        => $user?->name ?? $this->getRawOriginal('name'),
            'email'       => $user?->email ?? $this->getRawOriginal('email'),
            'bio'         => $this->getRawOriginal('bio') ?? $user?->bio,
            'affiliation' => $this->getRawOriginal('affiliation')
                ?? $user?->affiliation
                ?? $user?->job_title,
            // ✅ Pertahankan slug yang sudah ada saat unclaim
        ]);
    }

    // ========================================
    // PRIVATE HELPERS
    // ========================================

    private function cleanPath(?string $path): ?string
    {
        if (!$path) return null;

        return str_starts_with($path, 'public/')
            ? substr($path, 7)
            : $path;
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(
            Publication::class,
            'author_publication',
            'author_id',
            'publication_id'
        )
            ->withPivot(['order', 'is_corresponding'])
            ->withTimestamps();
    }

    public function authorPublications(): HasMany
    {
        return $this->hasMany(AuthorPublication::class, 'author_id');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeHasPublications($query)
    {
        return $query->has('publications');
    }

    public function scopeHasPublishedPublications($query)
    {
        return $query->whereHas('publications', function ($q) {
            $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        });
    }

    public function scopeExternal($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeLinked($query)
    {
        return $query->whereNotNull('user_id');
    }

    // ========================================
    // ORCID ACCESSORS & HELPERS
    // ========================================

    public function getOrcidIdAttribute($value): ?string
    {
        if (!empty($value)) return $value;

        if ($this->user_id) {
            $user = $this->relationLoaded('user')
                ? $this->user
                : User::find($this->user_id);

            return $user?->orcid_id;
        }

        return null;
    }

    public function getOrcidUrlAttribute(): ?string
    {
        $id = $this->orcid_id;
        return $id ? "https://orcid.org/{$id}" : null;
    }

    public static function isValidOrcid(string $orcid): bool
    {
        return (bool) preg_match(
            '/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/',
            strtoupper($orcid)
        );
    }

    public static function normalizeOrcid(string $raw): ?string
    {
        $clean = preg_replace('/[^0-9X]/i', '', strtoupper($raw));

        if (strlen($clean) !== 16) return null;

        return implode('-', str_split($clean, 4));
    }
}
