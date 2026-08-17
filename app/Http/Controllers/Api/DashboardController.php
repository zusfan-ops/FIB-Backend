<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
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

        $reviewsToday = ReviewLog::query()
            ->where('user_id', $user->id)
            ->whereDate('reviewed_at', Carbon::today())
            ->count();

        $streak = $this->streakDays($user->id);

        $activeTarget = JlptTarget::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->with('checklistItems')
            ->first();

        $todaySchedule = ScheduleItem::query()
            ->where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->orderBy('time')
            ->get();

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
            'today_schedule' => $todaySchedule,
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
