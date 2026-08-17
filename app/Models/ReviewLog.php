<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'card_id', 'rating', 'interval', 'ease_factor', 'reviewed_at'])]
class ReviewLog extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'interval' => 'integer',
            'ease_factor' => 'float',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
