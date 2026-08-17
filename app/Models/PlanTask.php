<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'title', 'description', 'column', 'order', 'due_date', 'schedule_item_id',
])]
class PlanTask extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'due_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scheduleItem(): BelongsTo
    {
        return $this->belongsTo(ScheduleItem::class);
    }
}
