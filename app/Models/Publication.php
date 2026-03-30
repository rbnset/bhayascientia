<?php

namespace App\Models;

use App\Models\Pivots\AuthorPublication;
use App\Models\Pivots\PublicationKeyword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Publication extends Model
{
    use SoftDeletes, HasFactory;

    public const STATUS_DRAFT             = 'draft';
    public const STATUS_SUBMITTED         = 'submitted';
    public const STATUS_IN_REVIEW         = 'in_review';
    public const STATUS_REVISION_REQUIRED = 'revision_required';
    public const STATUS_ACCEPTED          = 'accepted';
    public const STATUS_REJECTED          = 'rejected';
    public const STATUS_PUBLISHED         = 'published';

    public const PRIOR_IDENTIFIER_DOI        = 'doi';
    public const PRIOR_IDENTIFIER_ISBN       = 'isbn';
    public const PRIOR_IDENTIFIER_MEDIA_NAME = 'media_name';

    protected $fillable = [
        'publication_type_id',
        'method_id',
        'title',
        'slug',
        'abstract',
        'status',
        'published_at',
        'cover_image_path',

        'is_previously_published',
        'prior_publisher_name',
        'prior_publisher_url',
        'prior_identifier_type',
        'prior_identifier_value',
        'is_open_access_origin',
        'origin_license',
        'prior_published_date',

        'loa_is_original_work',
        'loa_grants_display_rights',
        'loa_platform_not_liable',
        'loa_agrees_takedown_policy',
        'loa_agreed',
        'loa_agreed_at',
        'loa_agreed_ip',
        'loa_agreed_user_agent',
    ];

    protected $casts = [
        'published_at'              => 'datetime',
        'prior_published_date'      => 'date',
        'loa_agreed_at'             => 'datetime',

        // ⚠️  is_previously_published SENGAJA TIDAK di-cast ke boolean.
        // Filament Radio component menyimpan/membaca nilai sebagai string '0'/'1'.
        // Jika di-cast boolean, Filament akan menerima false/true dari model
        // dan perbandingan dengan string '1' di ->hidden() / ->required() selalu gagal.
        // Konversi ke boolean tetap tersedia via helper isPreviouslyPublished() di form
        // dan via accessor isTrulyPreviouslyPublished() di model ini.
        //
        // 'is_previously_published' => 'boolean',  // ← DIHAPUS, ini penyebab bug

        'is_open_access_origin'     => 'boolean',
        'loa_is_original_work'      => 'boolean',
        'loa_grants_display_rights' => 'boolean',
        'loa_platform_not_liable'   => 'boolean',
        'loa_agrees_takedown_policy' => 'boolean',
        'loa_agreed'                => 'boolean',
    ];

    protected $appends = [
        'cover_url',
        'formatted_date',
        'category_name',
        'views_count',
        'downloads_count',
    ];

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

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
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

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

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
     * Accessor boolean yang aman untuk digunakan di luar form Filament
     * (misalnya di controller, blade, atau policy).
     * Gunakan ini — bukan is_previously_published langsung — untuk logic backend.
     */
    public function getIsTrulyPreviouslyPublishedAttribute(): bool
    {
        return filter_var($this->attributes['is_previously_published'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function getCategoryNameAttribute()
    {
        if ($this->relationLoaded('categories')) {
            return $this->categories->first()?->name ?? 'Umum';
        }

        return $this->categories()->first()?->name ?? 'Umum';
    }

    public function getFormattedDateAttribute()
    {
        if (!$this->published_at) return '-';

        return $this->published_at->locale('id')->isoFormat('D MMMM YYYY');
    }

    public function getCoverUrlAttribute(): string
    {
        if ($this->coverUrlCache !== null) {
            return $this->coverUrlCache;
        }

        if ($this->cover_image_path) {
            $cleanPath = Str::startsWith($this->cover_image_path, 'public/')
                ? Str::after($this->cover_image_path, 'public/')
                : $this->cover_image_path;

            if (Storage::disk('public')->exists($cleanPath)) {
                return $this->coverUrlCache = asset('storage/' . $cleanPath);
            }
        }

        return $this->coverUrlCache = $this->generatePlaceholderUrl();
    }

    private function generatePlaceholderUrl(): string
    {
        $authorName = 'Anonymous';
        if ($this->relationLoaded('authors') && $this->authors->isNotEmpty()) {
            $authorName = $this->authors->pluck('name')->join(', ');
        }

        $categoryName = 'Umum';
        if ($this->relationLoaded('categories') && $this->categories->isNotEmpty()) {
            $categoryName = $this->categories->first()->name;
        }

        $typeName = 'Publikasi';
        if ($this->relationLoaded('publicationType') && $this->publicationType) {
            $typeName = $this->publicationType->name;
        }

        $words    = explode(' ', trim($this->title));
        $initials = count($words) >= 2
            ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
            : strtoupper(substr($this->title, 0, 2));

        return route('placeholder.cover', [
            'initials' => $initials,
            'type'     => $typeName,
            'title'    => Str::limit($this->title, 60),
            'category' => $categoryName,
            'author'   => $authorName,
        ]);
    }

    public function getViewsCountAttribute(): int
    {
        return \Cache::remember(
            "publication.{$this->id}.views_count",
            now()->addMinutes(5),
            fn() => $this->viewLogs()->count()
        );
    }

    public function getDownloadsCountAttribute(): int
    {
        return \Cache::remember(
            "publication.{$this->id}.downloads_count",
            now()->addMinutes(5),
            fn() => $this->downloadLogs()->count()
        );
    }

    public function getUniqueViewsCountAttribute(): int
    {
        return \Cache::remember(
            "publication.{$this->id}.unique_views",
            now()->addMinutes(10),
            fn() => $this->viewLogs()->distinct('ip_address')->count('ip_address')
        );
    }

    /**
     * Gunakan is_truly_previously_published (accessor) untuk logic backend.
     */
    public function isLoaComplete(): bool
    {
        return $this->loa_is_original_work
            && $this->loa_grants_display_rights
            && $this->loa_platform_not_liable
            && $this->loa_agrees_takedown_policy
            && $this->loa_agreed
            && $this->loa_agreed_at !== null;
    }

    public function isPriorPublicationComplete(): bool
    {
        // Gunakan accessor agar aman dari ambiguitas tipe
        if (!$this->is_truly_previously_published) {
            return true;
        }

        return filled($this->prior_publisher_name)
            && filled($this->prior_identifier_value)
            && filled($this->prior_identifier_type);
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    public function setCoverImagePathAttribute($value)
    {
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($publication) {
            if (empty($publication->slug)) {
                $publication->slug = Str::slug($publication->title);
            }
        });

        static::updating(function ($publication) {
            $publication->coverUrlCache = null;
        });
    }

    protected static function booted(): void
    {
        static::saving(function (Publication $publication) {
            if (empty($publication->slug) || $publication->isDirty('title')) {
                $publication->slug = static::generateUniqueSlug(
                    $publication->title,
                    $publication->id
                );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug     = $baseSlug;
        $counter  = 2;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function hasCover(): bool
    {
        if (!$this->cover_image_path) return false;

        $cleanPath = Str::startsWith($this->cover_image_path, 'public/')
            ? Str::after($this->cover_image_path, 'public/')
            : $this->cover_image_path;

        return Storage::disk('public')->exists($cleanPath);
    }

    public function getCoverFileSize(): ?string
    {
        if (!$this->hasCover()) return null;

        $cleanPath = Str::startsWith($this->cover_image_path, 'public/')
            ? Str::after($this->cover_image_path, 'public/')
            : $this->cover_image_path;

        $bytes = Storage::disk('public')->size($cleanPath);

        return $this->formatBytes($bytes);
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);

        return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }

    public function clearStatsCache(): void
    {
        \Cache::forget("publication.{$this->id}.views_count");
        \Cache::forget("publication.{$this->id}.downloads_count");
        \Cache::forget("publication.{$this->id}.unique_views");
    }
}