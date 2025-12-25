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

    // Wajib untuk avatar di user menu Filament [web:146]
    public function getFilamentAvatarUrl(): ?string
    {
        return filled($this->profile_photo)
            ? Storage::disk('public')->url($this->profile_photo)
            : null;
    }

    // Wajib kalau kamu implement FilamentUser (boleh kamu batasi akses panel) [web:146]
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
