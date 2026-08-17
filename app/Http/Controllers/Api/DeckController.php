<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Models\Deck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeckController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $decks = Deck::query()
            ->where('user_id', $request->user()->id)
            ->withCount('cards')
            ->withSum('cards', 'repetition')
            ->orderBy('name')
            ->get();

        return $this->ok($decks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'card_type' => ['nullable', 'in:kanji,kosakata,klip'],
        ]);

        $deck = $request->user()->decks()->create($validated);

        return $this->ok($deck, 'Deck dibuat', 201);
    }

    public function show(Request $request, Deck $deck): JsonResponse
    {
        $this->authorizeDeck($request, $deck);

        return $this->ok($deck->loadCount('cards'));
    }

    public function update(Request $request, Deck $deck): JsonResponse
    {
        $this->authorizeDeck($request, $deck);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'card_type' => ['nullable', 'in:kanji,kosakata,klip'],
            'is_shared' => ['nullable', 'boolean'],
        ]);

        $deck->update($validated);

        return $this->ok($deck, 'Deck diperbarui');
    }

    public function destroy(Request $request, Deck $deck): JsonResponse
    {
        $this->authorizeDeck($request, $deck);

        $deck->delete();

        return $this->ok(null, 'Deck dihapus');
    }

    /**
     * Import cards in bulk. Expects array of cards:
     * [{front, readings:{on,kun}, meaning, example_sentence, example_translation, tags}]
     */
    public function import(Request $request, Deck $deck): JsonResponse
    {
        $this->authorizeDeck($request, $deck);

        $validated = $request->validate([
            'cards' => ['required', 'array', 'min:1', 'max:1000'],
            'cards.*.front' => ['required', 'string'],
            'cards.*.readings' => ['nullable', 'array'],
            'cards.*.meaning' => ['nullable', 'string'],
            'cards.*.example_sentence' => ['nullable', 'string'],
            'cards.*.example_translation' => ['nullable', 'string'],
            'cards.*.tags' => ['nullable', 'array'],
        ]);

        $userId = $request->user()->id;
        $cards = collect($validated['cards'])->map(fn ($c) => [
            'user_id' => $userId,
            'deck_id' => $deck->id,
            'front' => $c['front'],
            'readings' => isset($c['readings']) ? json_encode($c['readings']) : null,
            'meaning' => $c['meaning'] ?? null,
            'example_sentence' => $c['example_sentence'] ?? null,
            'example_translation' => $c['example_translation'] ?? null,
            'tags' => isset($c['tags']) ? json_encode($c['tags']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        Card::insert($cards);

        return $this->ok(['imported' => count($cards)], 'Import berhasil', 201);
    }

    protected function authorizeDeck(Request $request, Deck $deck): void
    {
        abort_if($deck->user_id !== $request->user()->id, 404, 'Deck tidak ditemukan');
    }
}
