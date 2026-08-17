<?php

namespace App\Http\Controllers\Api;

use App\Models\ThesisMilestone;
use App\Models\ThesisProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ThesisController extends ApiController
{
    private const DEFAULT_MILESTONES = [
        'Bab 1 - Pendahuluan',
        'Bab 2 - Tinjauan Pustaka',
        'Bab 3 - Metodologi Penelitian',
        'Bab 4 - Hasil & Pembahasan',
        'Bab 5 - Penutup',
        'Sidang Akhir',
    ];

    public function show(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $profile = ThesisProfile::firstOrCreate(['user_id' => $userId]);

        if (ThesisMilestone::where('user_id', $userId)->doesntExist()) {
            foreach (self::DEFAULT_MILESTONES as $index => $title) {
                ThesisMilestone::create([
                    'user_id' => $userId,
                    'title' => $title,
                    'status' => 'todo',
                    'order' => $index,
                ]);
            }
        }

        $milestones = ThesisMilestone::where('user_id', $userId)->orderBy('order')->get();

        $daysLeft = $profile->target_defense_date
            ? Carbon::today()->diffInDays(Carbon::parse($profile->target_defense_date), false)
            : null;

        $done = $milestones->where('status', 'done')->count();

        return $this->ok([
            'profile' => $profile,
            'milestones' => $milestones,
            'days_left' => $daysLeft,
            'progress_percent' => $milestones->count() > 0 ? round(($done / $milestones->count()) * 100) : 0,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'advisor_1' => ['nullable', 'string', 'max:255'],
            'advisor_2' => ['nullable', 'string', 'max:255'],
            'target_defense_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $profile = ThesisProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated,
        );

        return $this->ok($profile, 'Profil skripsi diperbarui');
    }

    public function storeMilestone(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $nextOrder = ThesisMilestone::where('user_id', $request->user()->id)->max('order') + 1;

        $milestone = $request->user()->thesisMilestones()->create([
            ...$validated,
            'status' => 'todo',
            'order' => $nextOrder,
        ]);

        return $this->ok($milestone, 'Milestone ditambahkan', 201);
    }

    public function updateMilestone(Request $request, ThesisMilestone $thesisMilestone): JsonResponse
    {
        abort_if($thesisMilestone->user_id !== $request->user()->id, 404, 'Milestone tidak ditemukan');

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'in:todo,doing,done'],
            'notes' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ]);

        $thesisMilestone->update($validated);

        return $this->ok($thesisMilestone, 'Milestone diperbarui');
    }

    public function destroyMilestone(Request $request, ThesisMilestone $thesisMilestone): JsonResponse
    {
        abort_if($thesisMilestone->user_id !== $request->user()->id, 404, 'Milestone tidak ditemukan');

        $thesisMilestone->delete();

        return $this->ok(null, 'Milestone dihapus');
    }
}
