<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'course_name', 'credits', 'semester', 'grade_letter', 'grade_point'])]
class CourseGrade extends Model
{
    // Skala nilai standar (huruf -> bobot), dipakai UNDIP & sebagian besar PTN di Indonesia
    public const GRADE_POINTS = [
        'A' => 4.00,
        'AB' => 3.50,
        'B' => 3.00,
        'BC' => 2.50,
        'C' => 2.00,
        'D' => 1.00,
        'E' => 0.00,
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'grade_point' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
