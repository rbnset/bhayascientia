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
        'profile_photo',
        'whatsapp_number',
        'job_title',
        'username', // ✅ Add this if not exists
        'bio', // ✅ Add this if not exists
        'affiliation', // ✅ Add this if not exists
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
            'password' => 'hashed',
        ];
    }

    // ✅ Accessor untuk photo URL
    public function getPhotoUrlAttribute(): string
    {
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

    // ✅ Accessor untuk initials
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($this->name, 0, 2));
    }

    // ✅ Accessor untuk short bio
    public function getShortBioAttribute(): ?string
    {
        if (!$this->bio) {
            return $this->job_title ?? null;
        }

        return strlen($this->bio) > 150
            ? substr($this->bio, 0, 147) . '...'
            : $this->bio;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return filled($this->profile_photo)
            ? Storage::disk('public')->url($this->profile_photo)
            : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getSavedPublicationsCountAttribute()
    {
        return $this->savedPublications()->count();
    }

    // ✅ Relasi ke Author (jika user ini juga author)
    public function authorProfile()
    {
        return $this->hasOne(Author::class);
    }

    // ✅ Relasi publications through Author
    public function publications()
    {
        return $this->hasManyThrough(
            Publication::class,
            Author::class,
            'user_id', // Foreign key on authors table
            'id', // Foreign key on publications table
            'id', // Local key on users table
            'id' // Local key on authors table
        )->distinct();
    }

    // ✅ Relasi publications langsung (jika ada)
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

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
}
