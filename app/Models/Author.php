<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Pivots\AuthorPublication;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
     * ✅ Accessor untuk mendapatkan URL foto author (UPDATED)
     */
    public function getPhotoUrlAttribute(): string
    {
        // Prioritas 1: photo_path dari author
        if ($this->photo_path) {
            $cleanPath = $this->photo_path;
            if (str_starts_with($cleanPath, 'public/')) {
                $cleanPath = substr($cleanPath, 7);
            }

            if (Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
        }

        // Prioritas 2: profile_photo dari user (jika ada relasi)
        if ($this->user_id && $this->relationLoaded('user') && $this->user && $this->user->profile_photo) {
            $cleanPath = $this->user->profile_photo;
            if (str_starts_with($cleanPath, 'public/')) {
                $cleanPath = substr($cleanPath, 7);
            }

            if (Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
        }

        // Prioritas 3: Fallback UI Avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=FF6B18&color=fff&size=128&bold=true&font-size=0.4';
    }

    /**
     * Accessor untuk mendapatkan inisial nama (untuk fallback avatar)
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($this->name, 0, 2));
    }

    /**
     * Accessor untuk bio pendek (150 karakter)
     */
    public function getShortBioAttribute(): ?string
    {
        if (!$this->bio) {
            return null;
        }

        return strlen($this->bio) > 150
            ? substr($this->bio, 0, 147) . '...'
            : $this->bio;
    }

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
}
