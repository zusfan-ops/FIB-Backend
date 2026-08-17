<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'code',
        'lecturer',
        'room',
        'day_of_week',
        'start_time',
        'end_time',
        'credits',
        'reminder_minutes',
        'color',
        'notes',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'credits' => 'integer',
        'reminder_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
