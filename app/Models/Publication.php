<?php

namespace App\Models;

use App\Models\Pivots\AuthorPublication;
use App\Models\Pivots\PublicationKeyword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Publication extends Model
{
    use SoftDeletes, HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_REVISION_REQUIRED = 'revision_required';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'publication_type_id',
        'method_id',
        'title',
        'slug',
        'abstract',
        'status',
        'published_at',
        'cover_image_path',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // ✅ Appends accessor
    protected $appends = [
        'cover_url',
        'formatted_date',
        'category_name',
        'views_count',
        'downloads_count',
    ];

    // ✅ Cache untuk menghindari multiple file exists check
    protected $coverUrlCache = null;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function publicationType(): BelongsTo
    {
        return $this->belongsTo(PublicationType::class);
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(
            Keyword::class,
            'publication_keyword'
        )->using(PublicationKeyword::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(
            Author::class,
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
        return $this->hasMany(AuthorPublication::class)
            ->orderBy('order');
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            Review::class,
            PublicationVersion::class,
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

    public function viewLogs(): HasMany
    {
        return $this->hasMany(PublicationViewLog::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope query untuk publikasi yang sudah dipublish
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope query untuk publikasi terbaru
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Scope query berdasarkan tipe publikasi
     */
    public function scopeOfType($query, $typeSlug)
    {
        return $query->whereHas('publicationType', function ($q) use ($typeSlug) {
            $q->where('slug', $typeSlug);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Accessor untuk mendapatkan kategori pertama
     */
    public function getCategoryNameAttribute()
    {
        // ✅ Gunakan relationLoaded untuk avoid N+1
        if ($this->relationLoaded('categories')) {
            return $this->categories->first()?->name ?? 'Umum';
        }

        return $this->categories()->first()?->name ?? 'Umum';
    }

    /**
     * Accessor untuk format tanggal Indonesia
     */
    public function getFormattedDateAttribute()
    {
        if (!$this->published_at) {
            return '-';
        }

        return $this->published_at->locale('id')->isoFormat('D MMMM YYYY');
    }

    /**
     * ✅ Accessor untuk URL cover image - Return NULL untuk support custom placeholder
     */
    public function getCoverUrlAttribute()
    {
        // Return cached value jika ada
        if ($this->coverUrlCache !== null) {
            return $this->coverUrlCache;
        }

        // ✅ Jika tidak ada cover image path, return NULL
        if (!$this->cover_image_path) {
            return $this->coverUrlCache = null;
        }

        // Clean path: remove 'public/' prefix jika ada
        $cleanPath = $this->cover_image_path;
        if (Str::startsWith($cleanPath, 'public/')) {
            $cleanPath = Str::after($cleanPath, 'public/');
        }

        // Cek apakah file ada di storage/app/public
        if (Storage::disk('public')->exists($cleanPath)) {
            return $this->coverUrlCache = asset('storage/' . $cleanPath);
        }

        // Log warning jika file tidak ditemukan (hanya di development)
        if (config('app.debug')) {
            \Log::warning("Cover image not found for publication #{$this->id}", [
                'title' => $this->title,
                'cover_path' => $this->cover_image_path,
                'clean_path' => $cleanPath,
                'expected_location' => storage_path('app/public/' . $cleanPath),
            ]);
        }

        // ✅ Return NULL jika file tidak ada (custom placeholder akan handle di blade)
        return $this->coverUrlCache = null;
    }

    /**
     * ✅ Get cover URL dengan fallback placehold.co (untuk backward compatibility)
     */
    public function getCoverUrlWithFallback()
    {
        return $this->cover_url ?? $this->generatePlaceholder();
    }

    /**
     * ✅ Accessor: Total views (with caching)
     */
    public function getViewsCountAttribute(): int
    {
        return \Cache::remember(
            "publication.{$this->id}.views_count",
            now()->addMinutes(5), // Cache 5 menit
            fn() => $this->viewLogs()->count()
        );
    }

    /**
     * ✅ Accessor: Total downloads (with caching)
     */
    public function getDownloadsCountAttribute(): int
    {
        return \Cache::remember(
            "publication.{$this->id}.downloads_count",
            now()->addMinutes(5), // Cache 5 menit
            fn() => $this->downloadLogs()->count()
        );
    }

    /**
     * ✅ Accessor: Unique visitors (based on IP)
     */
    public function getUniqueViewsCountAttribute(): int
    {
        return \Cache::remember(
            "publication.{$this->id}.unique_views",
            now()->addMinutes(10),
            fn() => $this->viewLogs()->distinct('ip_address')->count('ip_address')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * ✅ Set cover image path (normalize path)
     */
    public function setCoverImagePathAttribute($value)
    {
        // Remove 'public/' prefix jika ada saat save
        if ($value && Str::startsWith($value, 'public/')) {
            $value = Str::after($value, 'public/');
        }

        $this->attributes['cover_image_path'] = $value;
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

        // Auto-generate slug saat create
        static::creating(function ($publication) {
            if (empty($publication->slug)) {
                $publication->slug = Str::slug($publication->title);
            }
        });

        // Clear cover cache saat update
        static::updating(function ($publication) {
            $publication->coverUrlCache = null;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * ✅ Generate placeholder cover URL (untuk backward compatibility)
     */
    private function generatePlaceholder()
    {
        // Get category name (avoid N+1)
        $categoryName = 'Publikasi';
        if ($this->relationLoaded('categories') && $this->categories->isNotEmpty()) {
            $categoryName = $this->categories->first()->name;
        }

        // Truncate title
        $titleShort = Str::limit($this->title, 20, '');

        return 'https://placehold.co/400x600/FF6B18/white?text=' . urlencode($titleShort);
    }

    /**
     * ✅ Check if publication has valid cover image
     */
    public function hasCover(): bool
    {
        if (!$this->cover_image_path) {
            return false;
        }

        $cleanPath = Str::startsWith($this->cover_image_path, 'public/')
            ? Str::after($this->cover_image_path, 'public/')
            : $this->cover_image_path;

        return Storage::disk('public')->exists($cleanPath);
    }

    /**
     * ✅ Get file size in human readable format
     */
    public function getCoverFileSize(): ?string
    {
        if (!$this->hasCover()) {
            return null;
        }

        $cleanPath = Str::startsWith($this->cover_image_path, 'public/')
            ? Str::after($this->cover_image_path, 'public/')
            : $this->cover_image_path;

        $bytes = Storage::disk('public')->size($cleanPath);

        return $this->formatBytes($bytes);
    }

    /**
     * Format bytes ke KB/MB
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }

    /**
     * ✅ Clear cache saat ada view/download baru
     */
    public function clearStatsCache(): void
    {
        \Cache::forget("publication.{$this->id}.views_count");
        \Cache::forget("publication.{$this->id}.downloads_count");
        \Cache::forget("publication.{$this->id}.unique_views");
    }
}
