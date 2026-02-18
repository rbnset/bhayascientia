<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class TeamMember extends Model
{
    protected $fillable = [
        'name',
        'title',
        'department',
        'level',
        'photo',
        'email',
        'linkedin',
        'description',
        'icon_type',
        'member_count',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'member_count' => 'integer',
        'order'        => 'integer',
    ];

    // ✅ Scope active
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ✅ Scope per level
    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    // ✅ Get photo URL — fix double slash & fallback benar
    public function getPhotoUrlAttribute(): string
    {
        if (!empty($this->photo)) {

            // ✅ Kalau sudah URL eksternal (http/https), langsung return
            if (filter_var($this->photo, FILTER_VALIDATE_URL)) {
                return $this->photo;
            }

            // ✅ Normalize path — strip "public/" prefix & leading slash
            $path = ltrim(str_replace('public/', '', $this->photo), '/');

            // ✅ Cek file exist di disk public
            if (Storage::disk('public')->exists($path)) {
                // ✅ Pakai Storage::url() — tidak ada double slash
                return Storage::disk('public')->url($path);
            }
        }

        // ✅ Fallback UI Avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?? 'NN')
            . '&size=200&background=FFF7F2&color=FF6B18&bold=true';
    }

    // ✅ Helper: cek apakah foto ada (untuk dipakai di Blade jika perlu)
    public function hasPhoto(): bool
    {
        if (empty($this->photo)) {
            return false;
        }

        if (filter_var($this->photo, FILTER_VALIDATE_URL)) {
            return true;
        }

        $path = ltrim(str_replace('public/', '', $this->photo), '/');
        return Storage::disk('public')->exists($path);
    }

    // ✅ Auto clear cache saat data berubah
    protected static function boot(): void
    {
        parent::boot();

        $clearCache = function () {
            Cache::forget('about_page_team');
            Cache::forget('about_page_stats');
        };

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
    }
}
