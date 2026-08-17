<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'deck_id', 'front', 'readings', 'meaning', 'example_sentence',
    'example_translation', 'tags', 'source', 'clip_id', 'repetition', 'interval',
    'ease_factor', 'lapses', 'due_date', 'state',
])]
class Card extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'readings' => 'array',
            'tags' => 'array',
            'repetition' => 'integer',
            'interval' => 'integer',
            'ease_factor' => 'float',
            'lapses' => 'integer',
            'due_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function clip(): BelongsTo
    {
        return $this->belongsTo(Clip::class);
    }

    public function reviewLogs(): HasMany
    {
        return $this->hasMany(ReviewLog::class);
    }

    public function isDue(): bool
    {
        return $this->due_date->isPast() || $this->due_date->isToday();
    }
}
