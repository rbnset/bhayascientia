<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Pivots\AuthorPublication;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Author extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',        // NULL jika linked ke user — dibaca dari users.name
        'email',       // NULL jika linked ke user — dibaca dari users.email
        'affiliation', // NULL = fallback ke users.affiliation / users.job_title
        'bio',         // NULL = fallback ke users.bio
        'photo_path',  // NULL = fallback ke users.profile_photo / users.avatar
    ];

    // ========================================
    // ACCESSORS — Baca dari User jika linked
    // ========================================

    /**
     * ✅ Override accessor 'name':
     * - Jika linked ke user → ambil dari users.name
     * - Jika external → ambil dari authors.name
     */
    public function getNameAttribute($value): string
    {
        if ($this->user_id) {
            // Hindari infinite loop: load hanya jika belum
            if (!$this->relationLoaded('user')) {
                $this->load('user');
            }

            $resolved = $this->getRelation('user')?->name;
            if (!empty($resolved)) return $resolved;
        }

        return $value ?? 'Unknown Author';
    }

    /**
     * ✅ Override accessor 'email'
     */
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

    /**
     * ✅ Affiliation: authors.affiliation override, fallback ke user
     */
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

    /**
     * ✅ Bio: authors.bio override, fallback ke user
     */
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

    /**
     * ✅ Foto: photo_path author override, fallback ke user photo
     */
    public function getPhotoUrlAttribute(): string
    {
        // 1. Foto khusus author (foto formal/akademik)
        if ($this->getRawOriginal('photo_path')) {
            $cleanPath = $this->cleanPath($this->getRawOriginal('photo_path'));

            if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
        }

        // 2. Foto dari user yang terhubung
        if ($this->user_id) {
            $user = $this->relationLoaded('user')
                ? $this->user
                : User::find($this->user_id);

            if ($user) return $user->photo_url;
        }

        // 3. Fallback UI Avatars
        $name = $this->getRawOriginal('name') ?? 'Author';
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) .
            '&background=FF6B18&color=fff&size=160&bold=true&font-size=0.4&length=2';
    }

    /**
     * ✅ Initials — gunakan nama yang sudah di-resolve
     */
    public function getInitialsAttribute(): string
    {
        $name  = $this->name; // pakai accessor name yang sudah resolved
        $words = explode(' ', trim($name));

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    /**
     * ✅ Short bio — gunakan bio yang sudah di-resolve
     */
    public function getShortBioAttribute(): ?string
    {
        $bio = $this->bio; // pakai accessor bio yang sudah resolved

        if (!$bio) {
            return $this->affiliation; // pakai accessor affiliation yang sudah resolved
        }

        return strlen($bio) > 150
            ? substr($bio, 0, 147) . '...'
            : $bio;
    }

    // ========================================
    // CLAIM AUTHORSHIP
    // ========================================

    /**
     * ✅ Cek apakah author sudah terhubung ke akun user
     */
    public function isClaimed(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * ✅ Claim author oleh user
     * Data name/email di authors menjadi NULL karena sudah dibaca dari user
     */
    public function claimBy(User $user): array
    {
        if ($user->authorProfile()->exists()) {
            return [
                'success' => false,
                'message' => 'Akun Anda sudah terhubung ke profil author lain.',
            ];
        }

        if ($this->isClaimed()) {
            return [
                'success' => false,
                'message' => 'Profil author ini sudah terhubung ke akun lain.',
            ];
        }

        $this->update([
            'user_id' => $user->id,
            'name'    => null, // tidak perlu duplikasi, baca dari user
            'email'   => null, // tidak perlu duplikasi, baca dari user
        ]);

        return [
            'success' => true,
            'message' => 'Berhasil! Profil author telah terhubung ke akun Anda.',
        ];
    }

    /**
     * ✅ Lepas claim — kembalikan data dari user ke authors agar tidak hilang
     */
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
}
