<?php

namespace App\Http\Controllers\Api;

use App\Models\GrammarPattern;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GrammarController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = GrammarPattern::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('pattern');

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('pattern', 'like', "%{$q}%")
                    ->orWhere('meaning', 'like', "%{$q}%")
                    ->orWhere('structure', 'like', "%{$q}%");
            });
        }

        if ($request->filled('bungo')) {
            $query->where('is_bungo', $request->boolean('bungo'));
        }

        return $this->ok($query->get());
    }

    public function show(Request $request, GrammarPattern $grammarPattern): JsonResponse
    {
        abort_if($grammarPattern->user_id !== $request->user()->id, 404, 'Pola grammar tidak ditemukan');

        return $this->ok($grammarPattern);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pattern' => ['required', 'string'],
            'meaning' => ['nullable', 'string'],
            'structure' => ['nullable', 'string'],
            'usage' => ['nullable', 'string'],
            'examples' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'is_bungo' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'array'],
        ]);

        $pattern = $request->user()->grammarPatterns()->create($validated);

        return $this->ok($pattern, 'Pola grammar disimpan', 201);
    }

    public function update(Request $request, GrammarPattern $grammarPattern): JsonResponse
    {
        abort_if($grammarPattern->user_id !== $request->user()->id, 404, 'Pola grammar tidak ditemukan');

        $validated = $request->validate([
            'pattern' => ['sometimes', 'string'],
            'meaning' => ['nullable', 'string'],
            'structure' => ['nullable', 'string'],
            'usage' => ['nullable', 'string'],
            'examples' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'is_bungo' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'array'],
        ]);

        $grammarPattern->update($validated);

        return $this->ok($grammarPattern, 'Pola grammar diperbarui');
    }

    public function destroy(Request $request, GrammarPattern $grammarPattern): JsonResponse
    {
        abort_if($grammarPattern->user_id !== $request->user()->id, 404, 'Pola grammar tidak ditemukan');

        $grammarPattern->delete();

        return $this->ok(null, 'Pola grammar dihapus');
    }
}
