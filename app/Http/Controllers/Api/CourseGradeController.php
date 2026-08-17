<?php

namespace App\Http\Controllers\Api;

use App\Models\CourseGrade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseGradeController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $grades = CourseGrade::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('semester')
            ->orderBy('course_name')
            ->get();

        $bySemester = $grades->groupBy('semester')->map(function ($group) {
            $credits = $group->sum('credits');
            $points = $group->sum(fn ($g) => $g->credits * $g->grade_point);
            return [
                'courses' => $group->values(),
                'credits' => $credits,
                'gpa' => $credits > 0 ? round($points / $credits, 2) : 0,
            ];
        });

        $totalCredits = $grades->sum('credits');
        $totalPoints = $grades->sum(fn ($g) => $g->credits * $g->grade_point);

        return $this->ok([
            'grades' => $grades,
            'by_semester' => (object) $bySemester->all(),
            'total_credits' => $totalCredits,
            'cumulative_gpa' => $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0,
            'grade_scale' => CourseGrade::GRADE_POINTS,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_name' => ['required', 'string', 'max:255'],
            'credits' => ['required', 'integer', 'between:1,8'],
            'semester' => ['required', 'string', 'max:50'],
            'grade_letter' => ['required', 'in:A,AB,B,BC,C,D,E'],
        ]);

        $grade = $request->user()->courseGrades()->create([
            ...$validated,
            'grade_point' => CourseGrade::GRADE_POINTS[$validated['grade_letter']],
        ]);

        return $this->ok($grade, 'Nilai mata kuliah ditambahkan', 201);
    }

    public function update(Request $request, CourseGrade $courseGrade): JsonResponse
    {
        abort_if($courseGrade->user_id !== $request->user()->id, 404, 'Nilai tidak ditemukan');

        $validated = $request->validate([
            'course_name' => ['sometimes', 'required', 'string', 'max:255'],
            'credits' => ['sometimes', 'required', 'integer', 'between:1,8'],
            'semester' => ['sometimes', 'required', 'string', 'max:50'],
            'grade_letter' => ['sometimes', 'required', 'in:A,AB,B,BC,C,D,E'],
        ]);

        if (isset($validated['grade_letter'])) {
            $validated['grade_point'] = CourseGrade::GRADE_POINTS[$validated['grade_letter']];
        }

        $courseGrade->update($validated);

        return $this->ok($courseGrade, 'Nilai mata kuliah diperbarui');
    }

    public function destroy(Request $request, CourseGrade $courseGrade): JsonResponse
    {
        abort_if($courseGrade->user_id !== $request->user()->id, 404, 'Nilai tidak ditemukan');

        $courseGrade->delete();

        return $this->ok(null, 'Nilai mata kuliah dihapus');
    }
}
