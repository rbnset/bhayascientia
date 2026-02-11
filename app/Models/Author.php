<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Pivots\AuthorPublication;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'affiliation',
        'bio',
        'photo_path',
    ];

    /**
     * ✅ Accessor untuk mendapatkan URL foto author
     */
    public function getPhotoUrlAttribute(): string
    {
        // 1. Cek photo_path dari author
        if ($this->photo_path) {
            $cleanPath = $this->cleanPath($this->photo_path);

            if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            } else {
                // Log jika file tidak ditemukan (hanya di development)
                if (config('app.debug')) {
                    Log::debug("Author photo not found", [
                        'author_id' => $this->id,
                        'photo_path' => $this->photo_path,
                        'clean_path' => $cleanPath,
                    ]);
                }
            }
        }

        // 2. Cek profile_photo dari user (jika ada relasi)
        if ($this->user_id && $this->user) {
            if ($this->user->profile_photo) {
                $cleanPath = $this->cleanPath($this->user->profile_photo);

                if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
                    return asset('storage/' . $cleanPath);
                }
            }

            // 3. Cek avatar_url dari user (untuk OAuth/Socialite)
            if (!empty($this->user->avatar_url)) {
                return $this->user->avatar_url;
            }
        }

        // 4. Fallback UI Avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) .
            '&background=FF6B18&color=fff&size=160&bold=true&font-size=0.4&length=2';
    }

    /**
     * ✅ Accessor untuk mendapatkan inisial nama
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($this->name, 0, 2));
    }

    /**
     * ✅ Accessor untuk bio pendek (150 karakter)
     */
    public function getShortBioAttribute(): ?string
    {
        if (!$this->bio) {
            return $this->affiliation ?? null;
        }

        return strlen($this->bio) > 150
            ? substr($this->bio, 0, 147) . '...'
            : $this->bio;
    }

    /**
     * ✅ Helper untuk clean path (hapus prefix 'public/')
     */
    private function cleanPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Hapus prefix 'public/' jika ada
        if (str_starts_with($path, 'public/')) {
            return substr($path, 7);
        }

        return $path;
    }

    /**
     * Relationships
     */
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
            ->withPivot([
                'order',
                'is_corresponding',
            ])
            ->withTimestamps();
    }

    public function authorPublications(): HasMany
    {
        return $this->hasMany(AuthorPublication::class, 'author_id');
    }

    /**
     * ✅ Scope untuk author dengan publikasi
     */
    public function scopeHasPublications($query)
    {
        return $query->has('publications');
    }

    /**
     * ✅ Scope untuk author dengan publikasi published
     */
    public function scopeHasPublishedPublications($query)
    {
        return $query->whereHas('publications', function ($q) {
            $q->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        });
    }
}
