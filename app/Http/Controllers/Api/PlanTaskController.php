<?php

namespace App\Http\Controllers\Api;

use App\Models\PlanTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanTaskController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $tasks = PlanTask::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('column')
            ->orderBy('order')
            ->get();

        return $this->ok([
            'todo' => $tasks->where('column', 'todo')->values(),
            'doing' => $tasks->where('column', 'doing')->values(),
            'done' => $tasks->where('column', 'done')->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'column' => ['nullable', 'in:todo,doing,done'],
            'due_date' => ['nullable', 'date'],
            'schedule_item_id' => ['nullable', 'exists:schedule_items,id'],
        ]);

        $validated['column'] ??= 'todo';
        $validated['order'] = PlanTask::query()
            ->where('user_id', $request->user()->id)
            ->where('column', $validated['column'])
            ->max('order') + 1;

        $task = $request->user()->planTasks()->create($validated);

        return $this->ok($task, 'Tugas rencana dibuat', 201);
    }

    public function show(Request $request, PlanTask $planTask): JsonResponse
    {
        abort_if($planTask->user_id !== $request->user()->id, 404, 'Tugas tidak ditemukan');

        return $this->ok($planTask);
    }

    public function update(Request $request, PlanTask $planTask): JsonResponse
    {
        abort_if($planTask->user_id !== $request->user()->id, 404, 'Tugas tidak ditemukan');

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        $planTask->update($validated);

        return $this->ok($planTask, 'Tugas diperbarui');
    }

    /**
     * Pindahkan tugas antar kolom kanban.
     */
    public function move(Request $request, PlanTask $planTask): JsonResponse
    {
        abort_if($planTask->user_id !== $request->user()->id, 404, 'Tugas tidak ditemukan');

        $validated = $request->validate([
            'column' => ['required', 'in:todo,doing,done'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $planTask->update([
            'column' => $validated['column'],
            'order' => $validated['order'] ?? $planTask->order,
        ]);

        return $this->ok($planTask, 'Tugas dipindahkan');
    }

    public function destroy(Request $request, PlanTask $planTask): JsonResponse
    {
        abort_if($planTask->user_id !== $request->user()->id, 404, 'Tugas tidak ditemukan');

        $planTask->delete();

        return $this->ok(null, 'Tugas dihapus');
    }
}
