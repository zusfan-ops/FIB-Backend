<?php

namespace App\Http\Controllers\Api;

use App\Models\TranslationExercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $translations = TranslationExercise::query()
            ->where('user_id', $request->user()->id)
            ->withCount('revisions')
            ->orderByDesc('updated_at')
            ->get();

        return $this->ok($translations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'source_text' => ['required', 'string'],
            'source_lang' => ['nullable', 'string', 'max:10'],
            'target_lang' => ['nullable', 'string', 'max:10'],
            'my_translation' => ['nullable', 'string'],
            'best_translation' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,in_progress,done'],
            'notes' => ['nullable', 'string'],
        ]);

        $translation = $request->user()->translations()->create($validated);

        if (! empty($validated['my_translation'])) {
            $translation->revisions()->create([
                'content' => $validated['my_translation'],
            ]);
        }

        return $this->ok($translation->loadCount('revisions'), 'Latihan terjemahan dibuat', 201);
    }

    public function show(Request $request, TranslationExercise $translation): JsonResponse
    {
        abort_if($translation->user_id !== $request->user()->id, 404, 'Latihan tidak ditemukan');

        return $this->ok($translation->load('revisions'));
    }

    public function update(Request $request, TranslationExercise $translation): JsonResponse
    {
        abort_if($translation->user_id !== $request->user()->id, 404, 'Latihan tidak ditemukan');

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'source_text' => ['sometimes', 'string'],
            'source_lang' => ['nullable', 'string', 'max:10'],
            'target_lang' => ['nullable', 'string', 'max:10'],
            'my_translation' => ['nullable', 'string'],
            'best_translation' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,in_progress,done'],
            'notes' => ['nullable', 'string'],
        ]);

        if (isset($validated['my_translation']) && $validated['my_translation'] !== $translation->my_translation) {
            $translation->revisions()->create([
                'content' => $validated['my_translation'],
            ]);
        }

        $translation->update($validated);

        return $this->ok($translation->load('revisions'), 'Latihan diperbarui');
    }

    /**
     * Simpan versi revisi terjemahan dan bandingkan dengan versi sebelumnya.
     */
    public function submitRevision(Request $request, TranslationExercise $translation): JsonResponse
    {
        abort_if($translation->user_id !== $request->user()->id, 404, 'Latihan tidak ditemukan');

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'status' => ['nullable', 'in:draft,in_progress,done'],
        ]);

        $revision = $translation->revisions()->create([
            'content' => $validated['content'],
        ]);

        $data = $translation->load('revisions')->toArray();
        $data['revision'] = $revision;

        if (($validated['status'] ?? null) === 'done') {
            $translation->update(['status' => 'done', 'best_translation' => $validated['content']]);
        }

        return $this->ok($data, 'Revisi disimpan', 201);
    }

    public function destroy(Request $request, TranslationExercise $translation): JsonResponse
    {
        abort_if($translation->user_id !== $request->user()->id, 404, 'Latihan tidak ditemukan');

        $translation->delete();

        return $this->ok(null, 'Latihan dihapus');
    }
}
