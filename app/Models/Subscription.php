<?php
// app/Models/Subscription.php (Updated)

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
        'is_active',
        'last_sent_at',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'types' => 'array',
        'categories' => 'array',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categoryModels()
    {
        return $this->belongsToMany(Category::class, 'subscription_categories');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByNotificationType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    public function hasType($type)
    {
        return in_array($type, $this->types ?? []);
    }

    public function hasCategory($categoryId)
    {
        return in_array($categoryId, $this->categories ?? []);
    }
}
