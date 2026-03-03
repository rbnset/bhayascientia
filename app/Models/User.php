<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'profile_photo',    // ✅ Field utama untuk photo
        'whatsapp_number',
        'job_title',
        'username',
        'bio',
        'affiliation',
        'google_id',
        'facebook_id',
        'avatar',           // ✅ Hanya untuk URL dari social login (Google/Facebook)
        'provider',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ========================================
    // ✅ ACCESSORS
    // ========================================

    /**
     * ✅ PERBAIKAN: Accessor untuk photo URL dengan prioritas yang benar
     */
    public function getPhotoUrlAttribute(): string
    {
        // Prioritas 1: Avatar dari social login (Google/Facebook) - ini URL langsung
        if ($this->avatar && filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        // Prioritas 2: Profile photo yang diupload manual - ini path storage
        if ($this->profile_photo) {
            $cleanPath = str_starts_with($this->profile_photo, 'public/')
                ? substr($this->profile_photo, 7)
                : $this->profile_photo;

            if (Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
        }

        // Prioritas 3: Default UI Avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) .
            '&background=FF6B18&color=fff&size=128&bold=true&font-size=0.4';
    }

    /**
     * ✅ Accessor untuk initials
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($this->name, 0, 2));
    }

    /**
     * ✅ Accessor untuk short bio
     */
    public function getShortBioAttribute(): ?string
    {
        if (!$this->bio) {
            return $this->job_title ?? null;
        }

        return strlen($this->bio) > 150
            ? substr($this->bio, 0, 147) . '...'
            : $this->bio;
    }

    // ========================================
    // ✅ FILAMENT METHODS
    // ========================================

    /**
     * ✅ Filament avatar URL (prioritaskan avatar dari social media)
     */
    public function getFilamentAvatarUrl(): ?string
    {
        // Prioritas 1: Avatar dari social login (URL langsung)
        if ($this->avatar && filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        // Prioritas 2: Profile photo upload (path storage)
        if ($this->profile_photo) {
            $cleanPath = str_starts_with($this->profile_photo, 'public/')
                ? substr($this->profile_photo, 7)
                : $this->profile_photo;

            if (Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
        }

        return null;
    }

    /**
     * ✅ Determine if user can access Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    // ========================================
    // ✅ SOCIAL LOGIN HELPERS
    // ========================================

    /**
     * Check if user logged in via social provider
     */
    public function isSocialLogin(): bool
    {
        return in_array($this->provider, ['google', 'facebook']);
    }

    /**
     * Check if user has password (manual registration)
     */
    public function hasPassword(): bool
    {
        return !is_null($this->password) && !empty($this->password);
    }

    /**
     * Get provider display name
     */
    public function getProviderNameAttribute(): string
    {
        return match ($this->provider) {
            'google' => 'Google',
            'facebook' => 'Facebook',
            'manual' => 'Email',
            default => 'Unknown'
        };
    }

    // ========================================
    // ✅ RELATIONSHIPS
    // ========================================

    public function author()
    {
        return $this->hasOne(Author::class);
    }

    /**
     * Relasi ke Author profile
     */
    public function authorProfile()
    {
        return $this->hasOne(Author::class);
    }

    /**
     * Publications through Author
     */
    public function publications()
    {
        return $this->hasManyThrough(
            Publication::class,
            Author::class,
            'user_id',
            'id',
            'id',
            'id'
        )->distinct();
    }

    /**
     * Direct publications relationship
     */
    public function directPublications()
    {
        return $this->belongsToMany(Publication::class, 'author_publication', 'author_id', 'publication_id')
            ->wherePivot('author_id', function ($query) {
                $query->select('id')
                    ->from('authors')
                    ->where('user_id', $this->id);
            });
    }

    /**
     * Favorite publications
     */
    public function favoritePublications()
    {
        return $this->belongsToMany(Publication::class, 'user_favorite_publications')
            ->withTimestamps();
    }

    /**
     * Read publications
     */
    public function readPublications()
    {
        return $this->belongsToMany(Publication::class, 'user_read_publications')
            ->withPivot('last_read_at')
            ->withTimestamps()
            ->using(UserReadPublication::class);
    }

    /**
     * Saved publications
     */
    public function savedPublications()
    {
        return $this->belongsToMany(Publication::class, 'user_saved_publications')
            ->withTimestamps();
    }

    /**
     * Subscription relationship
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    // ========================================
    // ✅ PUBLICATION INTERACTION METHODS
    // ========================================

    /**
     * Get saved publications count
     */
    public function getSavedPublicationsCountAttribute()
    {
        return $this->savedPublications()->count();
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite($publicationId)
    {
        $exists = $this->favoritePublications()->where('publication_id', $publicationId)->exists();

        if ($exists) {
            $this->favoritePublications()->detach($publicationId);
            return ['status' => 'removed', 'message' => 'Dihapus dari favorit'];
        }

        $this->favoritePublications()->attach($publicationId);
        return ['status' => 'added', 'message' => 'Ditambahkan ke favorit'];
    }

    /**
     * Toggle saved status
     */
    public function toggleSaved($publicationId)
    {
        $exists = $this->savedPublications()->where('publication_id', $publicationId)->exists();

        if ($exists) {
            $this->savedPublications()->detach($publicationId);
            return ['status' => 'removed', 'message' => 'Dihapus dari simpanan'];
        }

        $this->savedPublications()->attach($publicationId);
        return ['status' => 'added', 'message' => 'Disimpan untuk nanti'];
    }

    /**
     * Check if publication is favorited
     */
    public function isFavorited($publicationId)
    {
        return $this->favoritePublications()->where('publication_id', $publicationId)->exists();
    }

    /**
     * Check if publication is saved
     */
    public function isSaved($publicationId)
    {
        return $this->savedPublications()->where('publication_id', $publicationId)->exists();
    }

    // Tambahkan di dalam class User

    public function otpCodes()
    {
        return $this->hasMany(OtpCode::class);
    }

    // =========================================================================
    // OTP: Generate kode baru
    // =========================================================================

    public function generateOtp(): string
    {
        // Buat kode 6 digit acak
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'otp_code'       => Hash::make($code),  // simpan sebagai hash
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        return $code; // return plain code untuk dikirim via email
    }

    // =========================================================================
    // OTP: Verifikasi kode
    // =========================================================================

    public function verifyOtp(string $code): bool
    {
        // Tidak ada OTP tersimpan
        if (!$this->otp_code || !$this->otp_expires_at) {
            return false;
        }

        // Kode sudah kadaluarsa
        if (now()->isAfter($this->otp_expires_at)) {
            // Hapus OTP kadaluarsa
            $this->update([
                'otp_code'       => null,
                'otp_expires_at' => null,
            ]);
            return false;
        }

        // Timing-safe comparison (cegah timing attack)
        if (!Hash::check($code, $this->otp_code)) {
            return false;
        }

        // One-time use: hapus OTP setelah berhasil dipakai
        $this->update([
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        return true;
    }

    // =========================================================================
    // Cek apakah email sudah diverifikasi
    // =========================================================================

    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }
}
