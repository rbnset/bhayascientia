<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
        'is_used',
        'resend_count',
        'last_resend_at',
    ];

    protected $casts = [
        'expires_at'     => 'datetime',
        'last_resend_at' => 'datetime',
        'is_used'        => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }

    public function canResend(): bool
    {
        // Maksimal 3x resend, interval minimal 60 detik
        if ($this->resend_count >= 3) return false;

        if ($this->last_resend_at && now()->diffInSeconds($this->last_resend_at) < 60) {
            return false;
        }

        return true;
    }
}
