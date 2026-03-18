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
        $name  = $this->name;
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
        $bio = $this->bio;

        if (!$bio) {
            return $this->affiliation;
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
     * ✅ Claim author oleh user — support merge jika user sudah punya author profile lain
     *
     * Skenario 1: User belum punya author profile
     *   → Langsung link authors.user_id = user.id
     *
     * Skenario 2: User sudah punya author profile (misal dari assign role)
     *   → Pindahkan semua publikasi dari author external (ini) ke author profile user
     *   → Hapus author external (ini) karena sudah tidak diperlukan
     *   → Yang dipakai adalah author profile user yang sudah ada (data dari tabel users)
     */
    public function claimBy(User $user): array
    {
        // Pastikan author external ini belum diklaim siapapun
        if ($this->isClaimed()) {
            return [
                'success' => false,
                'message' => 'Profil author ini sudah terhubung ke akun lain.',
            ];
        }

        // Cek apakah user sudah punya author profile lain
        $existingAuthorProfile = $user->authorProfile()->first();

        if ($existingAuthorProfile) {
            // ══ Skenario 2: Merge ══
            // User sudah punya author profile → pindahkan publikasi lalu hapus author external ini
            return $this->mergeInto($existingAuthorProfile);
        }

        // ══ Skenario 1: Claim biasa ══
        // User belum punya author profile → link langsung
        $this->update([
            'user_id' => $user->id,
            'name'    => null, // tidak perlu duplikasi, dibaca dari users.name
            'email'   => null, // tidak perlu duplikasi, dibaca dari users.email
        ]);

        return [
            'success' => true,
            'message' => 'Berhasil! Profil author telah terhubung ke akun Anda.',
        ];
    }

    /**
     * ✅ Merge: pindahkan semua publikasi dari author external (ini) ke $targetAuthor
     * lalu hapus author external (ini)
     *
     * Yang dipakai setelah merge adalah $targetAuthor (author profile milik user)
     * Data nama, email, foto → dari tabel users via accessor $targetAuthor
     */
    private function mergeInto(Author $targetAuthor): array
    {
        DB::transaction(function () use ($targetAuthor) {

            // Ambil semua publikasi milik author external ini
            $myPublicationIds = $this->publications()->pluck('publications.id')->toArray();

            foreach ($myPublicationIds as $publicationId) {

                // Cek apakah target author sudah terhubung ke publikasi ini
                // (hindari duplikat di pivot author_publication)
                $alreadyLinked = $targetAuthor->publications()
                    ->where('publications.id', $publicationId)
                    ->exists();

                if (!$alreadyLinked) {
                    // Ambil data pivot dari author external (order, is_corresponding)
                    $pivotData = $this->authorPublications()
                        ->where('publication_id', $publicationId)
                        ->first();

                    // Pindahkan ke target author dengan data pivot yang sama
                    $targetAuthor->publications()->attach($publicationId, [
                        'order'              => $pivotData?->order ?? 99,
                        'is_corresponding'   => $pivotData?->is_corresponding ?? false,
                    ]);
                }
            }

            // Hapus semua relasi pivot author external ini
            // (baris di tabel author_publication)
            $this->publications()->detach();

            // Hapus author external (soft delete agar bisa di-restore jika perlu)
            $this->delete();
        });

        return [
            'success'  => true,
            'message'  => 'Berhasil! Semua publikasi dari profil author lama telah digabungkan ke akun Anda.',
            'merged'   => true,
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
