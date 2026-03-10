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
        'has_seen_onboarding',
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
            'has_seen_onboarding'  => 'boolean',
        ];
    }

    // ========================================
    // BOOT — Auto-sync Author saat User update
    // ========================================

    protected static function boot(): void
    {
        parent::boot();

        /**
         * ✅ Saat user update nama/bio/foto/affiliasi,
         * otomatis sync ke Author profile jika ada
         */
        static::updated(function (User $user) {
            if (!$user->authorProfile()->exists()) {
                return;
            }

            $changes    = $user->getChanges();
            $syncFields = [];

            if (isset($changes['name'])) {
                $syncFields['name'] = null; // ✅ Null karena sekarang dibaca dari user
            }

            if (isset($changes['email'])) {
                $syncFields['email'] = null; // ✅ Null karena dibaca dari user
            }

            if (isset($changes['bio'])) {
                // Hanya sync jika author belum punya bio sendiri
                $author = $user->authorProfile;
                if (empty($author->getRawOriginal('bio'))) {
                    $syncFields['bio'] = null; // tetap null, accessor akan baca dari user
                }
            }

            if (isset($changes['affiliation']) || isset($changes['job_title'])) {
                $author = $user->authorProfile;
                if (empty($author->getRawOriginal('affiliation'))) {
                    $syncFields['affiliation'] = null;
                }
            }

            if (isset($changes['profile_photo'])) {
                $author = $user->authorProfile;
                // Hanya sync jika author tidak punya foto sendiri
                if (empty($author->getRawOriginal('photo_path'))) {
                    $syncFields['photo_path'] = null;
                }
            }

            if (!empty($syncFields)) {
                $user->authorProfile()->update($syncFields);
            }
        });
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * ✅ FIXED: Upload manual lebih prioritas dari Google avatar
     */
    public function getPhotoUrlAttribute(): string
    {
        // 1. Foto upload manual — paling prioritas
        if ($this->profile_photo) {
            $cleanPath = str_starts_with($this->profile_photo, 'public/')
                ? substr($this->profile_photo, 7)
                : $this->profile_photo;

            if (Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
        }

        // 2. Avatar Google/OAuth
        if ($this->avatar && filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        // 3. Fallback UI Avatars
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

    /**
     * ✅ FIXED: Prioritas sama dengan getPhotoUrlAttribute
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->profile_photo) {
            $cleanPath = str_starts_with($this->profile_photo, 'public/')
                ? substr($this->profile_photo, 7)
                : $this->profile_photo;

            if (Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
        }

        if ($this->avatar && filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
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

    /**
     * ✅ FIXED: Ganti hasManyThrough yang salah dengan JOIN via pivot
     */
    public function publications()
    {
        return Publication::select('publications.*')
            ->join('author_publication', 'publications.id', '=', 'author_publication.publication_id')
            ->join('authors', 'authors.id', '=', 'author_publication.author_id')
            ->where('authors.user_id', $this->id)
            ->distinct();
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
    // OTP METHODS
    // ========================================

    public function generateOtp(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->otpCodes()->delete();

        $this->otpCodes()->create([
            'code'       => bcrypt($code),
            'expires_at' => now()->addMinutes(10),
            'is_used'    => false,
        ]);

        return $code;
    }

    public function verifyOtp(string $code): bool
    {
        $otp = $this->otpCodes()
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otp) return false;

        if ($otp->isExpired()) {
            $otp->delete();
            return false;
        }

        if (!\Illuminate\Support\Facades\Hash::check($code, $otp->code)) {
            return false;
        }

        $otp->delete();
        return true;
    }

    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    // ❌ HAPUS ini dari User.php:
// public function assignRole(...$roles): static { ... }
// public function syncRoles(...$roles): static { ... }

// ✅ GANTI dengan method baru yang tidak konflik dengan Spatie:

    /**
     * ✅ Assign role + auto-create Author profile
     * Panggil method ini sebagai pengganti assignRole() jika butuh auto-create
     */
    public function assignRoleWithProfile(string|array $roles): static
    {
        $this->assignRole($roles);
        $this->createAuthorProfileIfNeeded(
            is_array($roles) ? $roles : [$roles]
        );
        return $this;
    }

    /**
     * ✅ Sync role + auto-create Author profile
     * Panggil method ini sebagai pengganti syncRoles() jika butuh auto-create
     */
    public function syncRolesWithProfile(string|array $roles): static
    {
        $hadAuthorRole = $this->hasRole('author');
        $this->syncRoles($roles);

        if (!$hadAuthorRole) {
            $this->createAuthorProfileIfNeeded(
                is_array($roles) ? $roles : [$roles]
            );
        }

        return $this;
    }

    private function createAuthorProfileIfNeeded(array $roleNames): void
    {
        $roleNames = collect($roleNames)
            ->flatten()
            ->map(fn($r) => is_string($r) ? $r : (is_object($r) ? $r->name : null))
            ->filter()
            ->toArray();

        if (!in_array('author', $roleNames)) return;

        $this->refresh();

        if ($this->authorProfile()->exists()) return;

        \App\Models\Author::create([
            'user_id'     => $this->id,
            'name'        => null,
            'email'       => null,
            'affiliation' => null,
            'bio'         => null,
            'photo_path'  => null,
        ]);
    }
}
