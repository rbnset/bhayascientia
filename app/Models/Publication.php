<?php

namespace App\Models;

use App\Models\Pivots\PublicationKeyword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // =====================
    // PUBLICATION TYPE
    // =====================
    public function publicationType()
    {
        return $this->belongsTo(PublicationType::class);
    }

    // =====================
    // KEYWORDS (MANY TO MANY)
    // =====================
    public function keywords()
    {
        return $this->belongsToMany(
            Keyword::class,
            'publication_keyword'
        )
            ->using(PublicationKeyword::class);
    }

    // =====================
    // AUTHORS
    // =====================
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(
            Author::class,
            'author_publication',
            'publication_id',
            'author_id'
        )
            ->withPivot([
                'order',
                'is_corresponding',
            ])
            ->withTimestamps()
            ->orderBy('pivot_order');
    }

    // =====================
    // CATEGORIES (MANY TO MANY)
    // =====================
    public function categories()
    {
        return $this->belongsToMany(Category::class)
            ->withTimestamps();
    }

    // =====================
    // RESEARCH METHOD
    // =====================
    public function method()
    {
        return $this->belongsTo(Method::class);
    }

    // =====================
    // VERSIONS
    // =====================
    public function versions()
    {
        return $this->hasMany(PublicationVersion::class);
    }

    // =====================
    // DOWNLOAD LOGS
    // =====================
    public function downloadLogs()
    {
        return $this->hasMany(DownloadLog::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
