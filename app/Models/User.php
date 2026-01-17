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

    public function favoritePublications()
    {
        return $this->belongsToMany(Publication::class, 'user_favorite_publications')
            ->withTimestamps();
    }

    public function readPublications()
    {
        return $this->belongsToMany(Publication::class, 'user_read_publications')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function savedPublications()
    {
        return $this->belongsToMany(Publication::class, 'user_saved_publications')
            ->withTimestamps();
    }

    /**
     * Toggle favorite publication
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
     * Toggle saved publication
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
}
