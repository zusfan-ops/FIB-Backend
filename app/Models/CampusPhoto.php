<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampusPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'photo_url',
        'event_date',
        'location',
        'category',
        'likes_count',
        'comments_count',
        'is_public',
    ];

    protected $casts = [
        'event_date' => 'date:Y-m-d',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'is_public' => 'boolean',
    ];

    protected $appends = [
        'share_url',
    ];

    public function getShareUrlAttribute(): string
    {
        return url('/p/' . $this->id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CampusPhotoLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CampusPhotoComment::class)->orderBy('created_at', 'asc');
    }
}
