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
}
