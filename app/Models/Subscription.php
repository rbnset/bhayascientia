<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'notify_email',
        'notify_whatsapp',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'notify_email' => 'boolean',
        'notify_whatsapp' => 'boolean',
    ];

    /**
     * Subscription belongs to a User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
