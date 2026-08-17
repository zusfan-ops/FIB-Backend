<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['book_id', 'title', 'sort_order', 'page_start', 'page_end', 'is_completed'])]
class Chapter extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'page_start' => 'integer',
            'page_end' => 'integer',
            'is_completed' => 'boolean',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ChapterNote::class);
    }
}
