<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Pivots\AuthorPublication;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Accessor untuk mendapatkan URL foto author
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }

        return asset('assets/images/default-avatar.png');
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
