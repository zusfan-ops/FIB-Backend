<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Models\ClassSchedule;
use App\Models\Clip;
use App\Models\JlptTarget;
use App\Models\ReviewLog;
use App\Models\ScheduleItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $dueCards = Card::query()
            ->where('user_id', $user->id)
            ->where('due_date', '<=', Carbon::now())
            ->count();

        $newCards = Card::query()
            ->where('user_id', $user->id)
            ->where('state', 'new')
            ->count();

        $now = Carbon::now(config('app.timezone', 'Asia/Jakarta'));
        $today = Carbon::today(config('app.timezone', 'Asia/Jakarta'));

        $reviewsToday = ReviewLog::query()
            ->where('user_id', $user->id)
            ->whereDate('reviewed_at', $today)
            ->count();

        $streak = $this->streakDays($user->id);

        $activeTarget = JlptTarget::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->with('checklistItems')
            ->first();

        $todayDayOfWeek = $now->dayOfWeekIso; // 1 = Senin, ..., 7 = Minggu

        $todayClasses = ClassSchedule::query()
            ->where('user_id', $user->id)
            ->where('day_of_week', $todayDayOfWeek)
            ->orderBy('start_time')
            ->get()
            ->map(function ($c) use ($today) {
                $start = substr($c->start_time, 0, 5);
                $end = substr($c->end_time, 0, 5);
                return [
                    'id' => $c->id,
                    'title' => $c->subject,
                    'type' => 'kuliah',
                    'date' => $today->toDateString(),
                    'time' => $start,
                    'end_time' => $end,
                    'course' => ($c->code ? "[$c->code] " : '') . "{$c->credits} SKS",
                    'location' => $c->room ?? 'FIB UNDIP',
                    'lecturer' => $c->lecturer,
                    'notes' => $c->notes,
                    'priority' => 'high',
                    'is_done' => false,
                    'is_class_schedule' => true,
                ];
            });

        $todayScheduleItems = ScheduleItem::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->orderBy('time')
            ->get();

        $allTodaySchedule = $todayClasses->concat($todayScheduleItems)->sortBy(function ($item) {
            if (is_array($item)) {
                return $item['time'] ?? '23:59';
            }
            return $item->time ?? '23:59';
        })->values();

        $data = [
            'due_cards' => $dueCards,
            'new_cards' => $newCards,
            'reviews_today' => $reviewsToday,
            'total_cards' => Card::where('user_id', $user->id)->count(),
            'total_clips' => Clip::where('user_id', $user->id)->count(),
            'streak_days' => $streak,
            'books_reading' => $user->books()->where('status', 'reading')->count(),
            'books_completed' => $user->books()->where('status', 'completed')->count(),
            'jlpt_target' => $activeTarget ? [
                'id' => $activeTarget->id,
                'level' => $activeTarget->level,
                'title' => $activeTarget->title,
                'target_date' => $activeTarget->target_date?->toDateString(),
                'days_left' => $activeTarget->target_date
                    ? (int) Carbon::today()->diffInDays($activeTarget->target_date, false)
                    : null,
                'checklist_done' => $activeTarget->checklistItems->where('is_done', true)->count(),
                'checklist_total' => $activeTarget->checklistItems->count(),
            ] : null,
            'today_schedule' => $allTodaySchedule,
            'today_classes' => $todayClasses,
        ];

        return $this->ok($data);
    }

    protected function streakDays(int $userId): int
    {
        $days = ReviewLog::query()
            ->where('user_id', $userId)
            ->selectRaw('DISTINCT DATE(reviewed_at) as day')
            ->orderByDesc('day')
            ->pluck('day');

        $streak = 0;
        $cursor = Carbon::today();

        while ($days->contains($cursor->toDateString())) {
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }
}
