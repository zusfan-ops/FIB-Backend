<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'title', 'author', 'author_jp', 'genre', 'original_language',
    'total_pages', 'current_page', 'status', 'cover_color', 'notes',
])]
class Book extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'total_pages' => 'integer',
            'current_page' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }
}
