<?php

namespace App\Http\Controllers\Api;

use App\Models\JlptMockResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JlptMockResultController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $results = JlptMockResult::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $bestByLevel = $results->groupBy('level')->map(fn ($group) => $group->max('score'));

        return $this->ok([
            'results' => $results,
            'best_by_level' => $bestByLevel,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'level' => ['required', 'in:N5,N4,N3,N2,N1'],
            'total_questions' => ['required', 'integer', 'min:1'],
            'correct_count' => ['required', 'integer', 'min:0'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
        ]);

        $score = (int) round(($validated['correct_count'] / $validated['total_questions']) * 100);

        $result = $request->user()->jlptMockResults()->create([
            'level' => $validated['level'],
            'total_questions' => $validated['total_questions'],
            'correct_count' => $validated['correct_count'],
            'score' => min(100, max(0, $score)),
            'duration_seconds' => $validated['duration_seconds'] ?? null,
        ]);

        return $this->ok($result, 'Hasil simulasi disimpan', 201);
    }
}
