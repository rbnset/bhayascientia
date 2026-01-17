<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class PublicationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'requires_review',
        'is_active'
    ];

    /**
     * Cast attributes ke tipe data yang benar
     */
    protected function casts(): array
    {
        return [
            'requires_review' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke publications
     */
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    /**
     * Relasi One-to-One ke content
     */
    public function content(): HasOne
    {
        return $this->hasOne(PublicationTypeContent::class);
    }

    /**
     * Scope untuk tipe aktif saja
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk tipe yang perlu review
     */
    public function scopeRequiresReview($query)
    {
        return $query->where('requires_review', true);
    }

    /**
     * Auto-generate slug dari name
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($type) {
            if (empty($type->slug)) {
                $type->slug = Str::slug($type->name);
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
