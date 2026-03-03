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

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'profile_photo',
        'whatsapp_number',
        'job_title',
        'username',
        'bio',
        'affiliation',
        'google_id',
        'facebook_id',
        'avatar',
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
            'password'          => 'hashed',
        ];
    }

    // ========================================
    // ACCESSORS
    // ========================================

    public function getPhotoUrlAttribute(): string
    {
        if ($this->avatar && filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        if ($this->profile_photo) {
            $cleanPath = str_starts_with($this->profile_photo, 'public/')
                ? substr($this->profile_photo, 7)
                : $this->profile_photo;

            if (Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) .
            '&background=FF6B18&color=fff&size=128&bold=true&font-size=0.4';
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($this->name, 0, 2));
    }

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
    // FILAMENT METHODS
    // ========================================

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar && filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

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

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    // ========================================
    // SOCIAL LOGIN HELPERS
    // ========================================

    public function isSocialLogin(): bool
    {
        return in_array($this->provider, ['google', 'facebook']);
    }

    public function hasPassword(): bool
    {
        return !is_null($this->password) && !empty($this->password);
    }

    public function getProviderNameAttribute(): string
    {
        return match ($this->provider) {
            'google'   => 'Google',
            'facebook' => 'Facebook',
            'manual'   => 'Email',
            default    => 'Unknown',
        };
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function author()
    {
        return $this->hasOne(Author::class);
    }

    public function authorProfile()
    {
        return $this->hasOne(Author::class);
    }

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

    public function directPublications()
    {
        return $this->belongsToMany(Publication::class, 'author_publication', 'author_id', 'publication_id')
            ->wherePivot('author_id', function ($query) {
                $query->select('id')
                    ->from('authors')
                    ->where('user_id', $this->id);
            });
    }

    public function favoritePublications()
    {
        return $this->belongsToMany(Publication::class, 'user_favorite_publications')
            ->withTimestamps();
    }

    public function readPublications()
    {
        return $this->belongsToMany(Publication::class, 'user_read_publications')
            ->withPivot('last_read_at')
            ->withTimestamps()
            ->using(UserReadPublication::class);
    }

    public function savedPublications()
    {
        return $this->belongsToMany(Publication::class, 'user_saved_publications')
            ->withTimestamps();
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    // ── OTP Relationship ──────────────────────────────────────────────────────

    /**
     * Relasi ke tabel otp_codes (tabel terpisah — lebih baik untuk normalisasi)
     */
    public function otpCodes()
    {
        return $this->hasMany(OtpCode::class);
    }

    // ========================================
    // PUBLICATION INTERACTION METHODS
    // ========================================

    public function getSavedPublicationsCountAttribute()
    {
        return $this->savedPublications()->count();
    }

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

    public function isFavorited($publicationId)
    {
        return $this->favoritePublications()->where('publication_id', $publicationId)->exists();
    }

    public function isSaved($publicationId)
    {
        return $this->savedPublications()->where('publication_id', $publicationId)->exists();
    }

    // ========================================
    // OTP METHODS — pakai tabel otp_codes
    // ========================================

    /**
     * Generate OTP baru — simpan ke tabel otp_codes
     */
    public function generateOtp(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Hapus semua OTP lama milik user ini dulu
        $this->otpCodes()->delete();

        // Buat OTP baru di tabel terpisah
        $this->otpCodes()->create([
            'code'       => bcrypt($code), // hash sebelum simpan
            'expires_at' => now()->addMinutes(10),
            'is_used'    => false,
        ]);

        return $code;
    }

    /**
     * Verifikasi OTP — ambil dari tabel otp_codes
     */
    public function verifyOtp(string $code): bool
    {
        // Ambil OTP terbaru yang belum dipakai langsung dari DB (no cache)
        $otp = $this->otpCodes()
            ->where('is_used', false)
            ->latest()
            ->first();

        // Tidak ada OTP
        if (!$otp) {
            return false;
        }

        // Sudah kadaluarsa
        if ($otp->isExpired()) {
            $otp->delete();
            return false;
        }

        // Timing-safe comparison
        if (!\Illuminate\Support\Facades\Hash::check($code, $otp->code)) {
            return false;
        }

        // One-time use: hapus setelah berhasil
        $otp->delete();

        return true;
    }

    /**
     * Cek apakah email sudah diverifikasi
     */
    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }
}
