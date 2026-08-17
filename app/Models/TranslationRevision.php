<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['translation_exercise_id', 'content'])]
class TranslationRevision extends Model
{
    use HasFactory;

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(TranslationExercise::class, 'translation_exercise_id');
    }
}
