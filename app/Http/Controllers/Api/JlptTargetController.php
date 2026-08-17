<?php

namespace App\Http\Controllers\Api;

use App\Models\JlptTarget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class JlptTargetController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $targets = JlptTarget::query()
            ->where('user_id', $request->user()->id)
            ->with('checklistItems')
            ->orderByDesc('is_active')
            ->get()
            ->map(fn (JlptTarget $t) => $this->withMeta($t));

        return $this->ok($targets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'level' => ['required', 'in:N1,N2,N3,N4,N5'],
            'target_date' => ['nullable', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['required', 'string'],
        ]);

        $target = $request->user()->jlptTargets()->create($validated);

        foreach (($validated['checklist'] ?? []) as $name) {
            $target->checklistItems()->create(['name' => $name]);
        }

        return $this->ok($this->withMeta($target->load('checklistItems')), 'Target JLPT dibuat', 201);
    }

    public function show(Request $request, JlptTarget $jlptTarget): JsonResponse
    {
        abort_if($jlptTarget->user_id !== $request->user()->id, 404, 'Target tidak ditemukan');

        return $this->ok($this->withMeta($jlptTarget->load('checklistItems')));
    }

    public function update(Request $request, JlptTarget $jlptTarget): JsonResponse
    {
        abort_if($jlptTarget->user_id !== $request->user()->id, 404, 'Target tidak ditemukan');

        $validated = $request->validate([
            'level' => ['sometimes', 'in:N1,N2,N3,N4,N5'],
            'target_date' => ['nullable', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $jlptTarget->update($validated);

        return $this->ok($this->withMeta($jlptTarget->load('checklistItems')), 'Target diperbarui');
    }

    public function destroy(Request $request, JlptTarget $jlptTarget): JsonResponse
    {
        abort_if($jlptTarget->user_id !== $request->user()->id, 404, 'Target tidak ditemukan');

        $jlptTarget->delete();

        return $this->ok(null, 'Target dihapus');
    }

    public function checkItem(Request $request, JlptTarget $jlptTarget): JsonResponse
    {
        abort_if($jlptTarget->user_id !== $request->user()->id, 404, 'Target tidak ditemukan');

        $validated = $request->validate([
            'item_id' => ['required', 'exists:jlpt_checklist_items,id'],
            'is_done' => ['required', 'boolean'],
        ]);

        $item = $jlptTarget->checklistItems()->findOrFail($validated['item_id']);
        $item->update(['is_done' => $validated['is_done']]);

        return $this->ok($this->withMeta($jlptTarget->load('checklistItems')), 'Checklist diperbarui');
    }

    protected function withMeta(JlptTarget $target): JlptTarget
    {
        $target->setAttribute('days_left', $target->target_date
            ? (int) Carbon::today()->diffInDays($target->target_date, false)
            : null);

        $target->setAttribute('progress_percent', $target->checklistItems->count() > 0
            ? (int) round($target->checklistItems->where('is_done', true)->count() / $target->checklistItems->count() * 100)
            : 0);

        return $target;
    }
}
