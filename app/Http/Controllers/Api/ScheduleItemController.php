<?php

namespace App\Http\Controllers\Api;

use App\Models\ScheduleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScheduleItemController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = ScheduleItem::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('date')
            ->orderBy('time');

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date', [$request->date('from'), $request->date('to')]);
        } elseif ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->date('from'));
        }

        return $this->ok($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'time' => ['nullable', 'date_format:H:i'],
            'type' => ['nullable', 'in:kuliah,deadline,tugas,uts,uas,kegiatan,pengingat'],
            'course' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'in:low,medium,high'],
        ]);

        $item = $request->user()->scheduleItems()->create($validated);

        return $this->ok($item, 'Agenda dibuat', 201);
    }

    public function show(Request $request, ScheduleItem $scheduleItem): JsonResponse
    {
        abort_if($scheduleItem->user_id !== $request->user()->id, 404, 'Agenda tidak ditemukan');

        return $this->ok($scheduleItem);
    }

    public function update(Request $request, ScheduleItem $scheduleItem): JsonResponse
    {
        abort_if($scheduleItem->user_id !== $request->user()->id, 404, 'Agenda tidak ditemukan');

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['sometimes', 'date'],
            'time' => ['nullable', 'date_format:H:i'],
            'type' => ['nullable', 'in:kuliah,deadline,tugas,uts,uas,kegiatan,pengingat'],
            'course' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'is_done' => ['nullable', 'boolean'],
        ]);

        $scheduleItem->update($validated);

        return $this->ok($scheduleItem, 'Agenda diperbarui');
    }

    public function destroy(Request $request, ScheduleItem $scheduleItem): JsonResponse
    {
        abort_if($scheduleItem->user_id !== $request->user()->id, 404, 'Agenda tidak ditemukan');

        $scheduleItem->delete();

        return $this->ok(null, 'Agenda dihapus');
    }
}
