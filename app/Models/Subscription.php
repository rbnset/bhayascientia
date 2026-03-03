<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'types',
        'categories',
        'notification_type',
        'max_emails_per_day',
        'emails_sent_today',
        'last_email_date',
        'is_active',
        'last_sent_at',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'types'           => 'array',
        'categories'      => 'array',
        'is_active'       => 'boolean',
        'last_sent_at'    => 'datetime',
        'subscribed_at'   => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_email_date' => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByNotificationType($query, string $type)
    {
        return $query->where('notification_type', $type);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function hasType(string $type): bool
    {
        return in_array($type, $this->types ?? []);
    }

    public function hasCategory(int $categoryId): bool
    {
        return in_array($categoryId, $this->categories ?? []);
    }

    public function canSendEmail(): bool
    {
        if ($this->notification_type !== 'instant') return true;

        $today = now()->toDateString();

        if ($this->last_email_date?->toDateString() !== $today) {
            return true;
        }

        return $this->emails_sent_today < $this->max_emails_per_day;
    }

    public function incrementEmailCount(): void
    {
        $today = now()->toDateString();

        if ($this->last_email_date?->toDateString() !== $today) {
            $this->update([
                'emails_sent_today' => 1,
                'last_email_date'   => $today,
            ]);
        } else {
            $this->increment('emails_sent_today');
        }
    }
}
