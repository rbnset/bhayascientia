<?php

namespace App\Models;

use App\Models\Pivots\AuthorPublication;
use App\Models\Pivots\PublicationKeyword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'publication_type_id',
        'method_id',
        'title',
        'abstract',
        'status',
        'published_at',
        'cover_image_path',
        // penting: kalau Anda memang punya kolom created_by, biasanya ini juga perlu fillable
        // 'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // =====================
    // PUBLICATION TYPE
    // =====================
    public function publicationType(): BelongsTo
    {
        return $this->belongsTo(PublicationType::class);
    }

    // =====================
    // KEYWORDS (MANY TO MANY)
    // =====================
    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(
            Keyword::class,
            'publication_keyword'
        )->using(PublicationKeyword::class);
    }

    // =====================
    // AUTHORS
    // =====================
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Author::class,
            'author_publication',
            'publication_id',
            'author_id'
        )
            ->using(AuthorPublication::class)
            ->withPivot(['order', 'is_corresponding'])
            ->withTimestamps()
            ->orderBy('author_publication.order');
    }

    public function authorPublications(): HasMany
    {
        return $this->hasMany(\App\Models\Pivots\AuthorPublication::class)
            ->orderBy('order');
    }

    // =====================
    // REVIEWS
    // =====================
    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Review::class,
            \App\Models\PublicationVersion::class,
            'publication_id',
            'publication_version_id',
            'id',
            'id'
        );
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withTimestamps();
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(Method::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PublicationVersion::class);
    }

    public function downloadLogs(): HasMany
    {
        return $this->hasMany(DownloadLog::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
