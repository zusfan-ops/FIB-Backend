<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['jlpt_target_id', 'name', 'is_done'])]
class JlptChecklistItem extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
        ];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(JlptTarget::class, 'jlpt_target_id');
    }
}
