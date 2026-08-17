<?php

namespace App\Http\Controllers\Api;

use App\Models\CampusDiary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampusDiaryController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $query = CampusDiary::where('user_id', $userId);

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('mood')) {
            $query->where('mood', $request->string('mood'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $diaries = $query->orderByDesc('is_pinned')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 20));

        return $this->ok($diaries);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'entry_date' => ['required', 'date'],
            'mood' => ['nullable', 'string', 'max:30'],
            'category' => ['nullable', 'string', 'max:50'],
            'tags' => ['nullable', 'array'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $diary = $request->user()->campusDiaries()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'entry_date' => $validated['entry_date'],
            'mood' => $validated['mood'] ?? 'semangat',
            'category' => $validated['category'] ?? 'kuliah',
            'tags' => $validated['tags'] ?? [],
            'is_pinned' => $validated['is_pinned'] ?? false,
        ]);

        return $this->ok($diary, 'Catatan diary kampus berhasil disimpan', 201);
    }

    public function show(Request $request, CampusDiary $campusDiary): JsonResponse
    {
        abort_if($campusDiary->user_id !== $request->user()->id, 404, 'Catatan tidak ditemukan');
        return $this->ok($campusDiary);
    }

    public function update(Request $request, CampusDiary $campusDiary): JsonResponse
    {
        abort_if($campusDiary->user_id !== $request->user()->id, 404, 'Catatan tidak ditemukan');

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            'entry_date' => ['sometimes', 'required', 'date'],
            'mood' => ['nullable', 'string', 'max:30'],
            'category' => ['nullable', 'string', 'max:50'],
            'tags' => ['nullable', 'array'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $campusDiary->update($validated);
        return $this->ok($campusDiary, 'Catatan diary kampus berhasil diperbarui');
    }

    public function destroy(Request $request, CampusDiary $campusDiary): JsonResponse
    {
        abort_if($campusDiary->user_id !== $request->user()->id, 404, 'Catatan tidak ditemukan');
        $campusDiary->delete();
        return $this->ok(null, 'Catatan diary kampus berhasil dihapus');
    }
}
