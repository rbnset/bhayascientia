<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Keyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi ke publications (many-to-many)
     */
    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(
            Publication::class,
            'publication_keyword'
        )->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope untuk mencari keyword berdasarkan slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope untuk mencari keyword yang popular (banyak digunakan)
     */
    public function scopePopular($query, int $minPublications = 5)
    {
        return $query->has('publications', '>=', $minPublications)
            ->withCount('publications')
            ->orderBy('publications_count', 'desc');
    }

    /**
     * Scope untuk search keyword by name
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('name', 'like', '%' . $term . '%');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Accessor untuk mendapatkan jumlah publikasi
     */
    public function getPublicationsCountAttribute(): int
    {
        return $this->publications()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($keyword) {
            if (empty($keyword->slug)) {
                $keyword->slug = Str::slug($keyword->name);
            }
        });

        static::updating(function ($keyword) {
            if ($keyword->isDirty('name') && empty($keyword->slug)) {
                $keyword->slug = Str::slug($keyword->name);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get route key untuk route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
