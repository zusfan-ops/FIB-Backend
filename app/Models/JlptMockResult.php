<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'level', 'total_questions', 'correct_count', 'score', 'duration_seconds'])]
class JlptMockResult extends Model
{
    protected function casts(): array
    {
        return [
            'total_questions' => 'integer',
            'correct_count' => 'integer',
            'score' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
