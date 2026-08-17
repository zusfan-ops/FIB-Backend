<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Models\Deck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CardController extends ApiController
{
    public function index(Request $request, Deck $deck): JsonResponse
    {
        abort_if($deck->user_id !== $request->user()->id, 404, 'Deck tidak ditemukan');

        $cards = Card::query()
            ->where('deck_id', $deck->id)
            ->orderBy('due_date')
            ->get();

        return $this->ok($cards);
    }

    public function store(Request $request, Deck $deck): JsonResponse
    {
        abort_if($deck->user_id !== $request->user()->id, 404, 'Deck tidak ditemukan');

        $validated = $request->validate([
            'front' => ['required', 'string'],
            'readings' => ['nullable', 'array'],
            'readings.on' => ['nullable', 'string'],
            'readings.kun' => ['nullable', 'string'],
            'meaning' => ['nullable', 'string'],
            'example_sentence' => ['nullable', 'string'],
            'example_translation' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
        ]);

        $card = $deck->cards()->create([
            'user_id' => $request->user()->id,
            'front' => $validated['front'],
            'readings' => $validated['readings'] ?? null,
            'meaning' => $validated['meaning'] ?? null,
            'example_sentence' => $validated['example_sentence'] ?? null,
            'example_translation' => $validated['example_translation'] ?? null,
            'tags' => $validated['tags'] ?? null,
        ]);

        return $this->ok($card, 'Kartu dibuat', 201);
    }

    public function update(Request $request, Card $card): JsonResponse
    {
        abort_if($card->user_id !== $request->user()->id, 404, 'Kartu tidak ditemukan');

        $validated = $request->validate([
            'front' => ['sometimes', 'string'],
            'readings' => ['nullable', 'array'],
            'meaning' => ['nullable', 'string'],
            'example_sentence' => ['nullable', 'string'],
            'example_translation' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
        ]);

        $card->update($validated);

        return $this->ok($card, 'Kartu diperbarui');
    }

    public function destroy(Request $request, Card $card): JsonResponse
    {
        abort_if($card->user_id !== $request->user()->id, 404, 'Kartu tidak ditemukan');

        $card->delete();

        return $this->ok(null, 'Kartu dihapus');
    }
}
