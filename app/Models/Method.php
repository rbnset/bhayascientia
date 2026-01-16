<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Method extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Relasi ke publications
     */
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    /**
     * Scope untuk mencari berdasarkan slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Auto-generate slug dari name
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($method) {
            if (empty($method->slug)) {
                $method->slug = Str::slug($method->name);
            }
        });

        static::updating(function ($method) {
            if ($method->isDirty('name') && empty($method->slug)) {
                $method->slug = Str::slug($method->name);
            }
        });
    }

    /**
     * Get route key untuk route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
