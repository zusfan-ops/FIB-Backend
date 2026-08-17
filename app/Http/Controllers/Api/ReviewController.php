<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Models\ReviewLog;
use App\Services\Sm2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReviewController extends ApiController
{
    public function __construct(protected Sm2Service $sm2)
    {
    }

    /**
     * Ambil kartu yang jatuh tempo (due), opsional difilter per deck.
     */
    public function due(Request $request): JsonResponse
    {
        $query = Card::query()
            ->where('user_id', $request->user()->id)
            ->where('due_date', '<=', Carbon::now())
            ->orderBy('due_date')
            ->with('deck:id,name,color');

        if ($request->filled('deck_id')) {
            $query->where('deck_id', $request->integer('deck_id'));
        }

        $cards = $query->limit($request->integer('limit', 50))->get();

        return $this->ok([
            'cards' => $cards,
            'total' => $cards->count(),
        ]);
    }

    public function submit(Request $request, Card $card): JsonResponse
    {
        abort_if($card->user_id !== $request->user()->id, 404, 'Kartu tidak ditemukan');

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:0,5'],
        ]);

        $reviewed = $this->sm2->review($card, $validated['rating']);
        $reviewed->save();

        $reviewed->reviewLogs()->create([
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'interval' => $reviewed->interval,
            'ease_factor' => $reviewed->ease_factor,
            'reviewed_at' => Carbon::now(),
        ]);

        return $this->ok($reviewed, 'Review disimpan');
    }

    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        return $this->ok([
            'reviews_today' => ReviewLog::where('user_id', $userId)->whereDate('reviewed_at', Carbon::today())->count(),
            'reviews_week' => ReviewLog::where('user_id', $userId)->whereBetween('reviewed_at', [Carbon::now()->startOfWeek(), Carbon::now()])->count(),
            'avg_rating' => (float) ReviewLog::where('user_id', $userId)->avg('rating'),
            'lapsed_cards' => Card::where('user_id', $userId)->where('lapses', '>', 0)->count(),
        ]);
    }
}
