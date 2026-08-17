<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'pattern', 'meaning', 'structure', 'usage', 'examples', 'notes',
    'is_bungo', 'tags',
])]
class GrammarPattern extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'examples' => 'array',
            'is_bungo' => 'boolean',
            'tags' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
