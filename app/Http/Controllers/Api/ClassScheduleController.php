<?php

namespace App\Http\Controllers\Api;

use App\Models\ClassSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ClassScheduleController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $schedules = ClassSchedule::where('user_id', $userId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Cari jadwal kuliah hari ini yang terdekat (termasuk deteksi pengingat 2 jam sebelum mulai)
        $todayDayOfWeek = Carbon::now()->dayOfWeekIso; // 1 (Senin) - 7 (Minggu)
        $currentTime = Carbon::now()->format('H:i:s');

        $upcomingToday = ClassSchedule::where('user_id', $userId)
            ->where('day_of_week', $todayDayOfWeek)
            ->where('end_time', '>=', $currentTime)
            ->orderBy('start_time')
            ->get()
            ->map(function ($item) use ($currentTime) {
                $startTime = Carbon::createFromTimeString($item->start_time);
                $now = Carbon::createFromTimeString($currentTime);
                $diffMinutes = $now->diffInMinutes($startTime, false);

                // Jika kuliah dimulai dalam 2 jam (<= 120 menit dan belum lewat)
                $item->is_imminent = ($diffMinutes >= 0 && $diffMinutes <= $item->reminder_minutes);
                $item->minutes_until_start = max(0, $diffMinutes);
                return $item;
            });

        return $this->ok([
            'schedules' => $schedules,
            'today_day' => $todayDayOfWeek,
            'upcoming_today' => $upcomingToday,
            'total_credits' => $schedules->sum('credits'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'lecturer' => ['nullable', 'string', 'max:255'],
            'room' => ['nullable', 'string', 'max:100'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'credits' => ['nullable', 'integer', 'between:1,8'],
            'reminder_minutes' => ['nullable', 'integer'],
            'color' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        $schedule = $request->user()->classSchedules()->create([
            'subject' => $validated['subject'],
            'code' => $validated['code'] ?? null,
            'lecturer' => $validated['lecturer'] ?? null,
            'room' => $validated['room'] ?? null,
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'credits' => $validated['credits'] ?? 2,
            'reminder_minutes' => $validated['reminder_minutes'] ?? 120,
            'color' => $validated['color'] ?? '#4F6EF7',
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->ok($schedule, 'Jadwal kuliah berhasil ditambahkan', 201);
    }

    public function show(Request $request, ClassSchedule $classSchedule): JsonResponse
    {
        abort_if($classSchedule->user_id !== $request->user()->id, 404, 'Jadwal tidak ditemukan');
        return $this->ok($classSchedule);
    }

    public function update(Request $request, ClassSchedule $classSchedule): JsonResponse
    {
        abort_if($classSchedule->user_id !== $request->user()->id, 404, 'Jadwal tidak ditemukan');

        $validated = $request->validate([
            'subject' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'lecturer' => ['nullable', 'string', 'max:255'],
            'room' => ['nullable', 'string', 'max:100'],
            'day_of_week' => ['sometimes', 'required', 'integer', 'between:1,7'],
            'start_time' => ['sometimes', 'required', 'string'],
            'end_time' => ['sometimes', 'required', 'string'],
            'credits' => ['nullable', 'integer', 'between:1,8'],
            'reminder_minutes' => ['nullable', 'integer'],
            'color' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        $classSchedule->update($validated);
        return $this->ok($classSchedule, 'Jadwal kuliah berhasil diperbarui');
    }

    public function destroy(Request $request, ClassSchedule $classSchedule): JsonResponse
    {
        abort_if($classSchedule->user_id !== $request->user()->id, 404, 'Jadwal tidak ditemukan');
        $classSchedule->delete();
        return $this->ok(null, 'Jadwal kuliah berhasil dihapus');
    }
}
