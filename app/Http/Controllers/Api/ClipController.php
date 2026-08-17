<?php

namespace App\Http\Controllers\Api;

use App\Models\Card;
use App\Models\Clip;
use App\Models\Deck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClipController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $clips = Clip::query()
            ->where('user_id', $request->user()->id)
            ->with('book:id,title', 'card:id,front,state')
            ->orderByDesc('created_at')
            ->get();

        return $this->ok($clips);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expression' => ['required', 'string'],
            'reading' => ['nullable', 'string'],
            'meaning' => ['nullable', 'string'],
            'context_sentence' => ['nullable', 'string'],
            'translation' => ['nullable', 'string'],
            'book_id' => ['nullable', 'exists:books,id'],
            'chapter_id' => ['nullable', 'exists:chapters,id'],
            'to_deck_id' => ['nullable', 'exists:decks,id'],
        ]);

        $user = $request->user();

        if (isset($validated['book_id']) && ! $user->books()->whereKey($validated['book_id'])->exists()) {
            abort(404, 'Buku tidak ditemukan');
        }

        $clip = $user->clips()->create([
            'expression' => $validated['expression'],
            'reading' => $validated['reading'] ?? null,
            'meaning' => $validated['meaning'] ?? null,
            'context_sentence' => $validated['context_sentence'] ?? null,
            'translation' => $validated['translation'] ?? null,
            'book_id' => $validated['book_id'] ?? null,
            'chapter_id' => $validated['chapter_id'] ?? null,
        ]);

        // Opsional: langsung buat kartu SRS dari clip (menutup celah baca -> hafal)
        if (isset($validated['to_deck_id'])) {
            $deck = $user->decks()->findOrFail($validated['to_deck_id']);

            $card = $deck->cards()->create([
                'user_id' => $user->id,
                'front' => $clip->expression,
                'readings' => $clip->reading ? ['kun' => $clip->reading] : null,
                'meaning' => $clip->meaning,
                'example_sentence' => $clip->context_sentence,
                'example_translation' => $clip->translation,
                'source' => 'clip',
                'clip_id' => $clip->id,
            ]);

            $clip->update(['card_id' => $card->id]);
        }

        return $this->ok($clip->load('book:id,title', 'card:id,front'), 'Klip disimpan', 201);
    }

    public function show(Request $request, Clip $clip): JsonResponse
    {
        abort_if($clip->user_id !== $request->user()->id, 404, 'Klip tidak ditemukan');

        return $this->ok($clip->load('book:id,title', 'card:id,front,state,deck_id'));
    }

    public function update(Request $request, Clip $clip): JsonResponse
    {
        abort_if($clip->user_id !== $request->user()->id, 404, 'Klip tidak ditemukan');

        $validated = $request->validate([
            'expression' => ['sometimes', 'string'],
            'reading' => ['nullable', 'string'],
            'meaning' => ['nullable', 'string'],
            'context_sentence' => ['nullable', 'string'],
            'translation' => ['nullable', 'string'],
        ]);

        $clip->update($validated);

        return $this->ok($clip, 'Klip diperbarui');
    }

    /**
     * Ubah klip menjadi kartu SRS di deck tertentu.
     */
    public function toCard(Request $request, Clip $clip): JsonResponse
    {
        abort_if($clip->user_id !== $request->user()->id, 404, 'Klip tidak ditemukan');

        $validated = $request->validate([
            'deck_id' => ['required', 'exists:decks,id'],
        ]);

        $user = $request->user();
        $deck = $user->decks()->findOrFail($validated['deck_id']);

        if ($clip->card_id) {
            return $this->ok($clip->load('card'), 'Klip sudah memiliki kartu', 200);
        }

        $card = $deck->cards()->create([
            'user_id' => $user->id,
            'front' => $clip->expression,
            'readings' => $clip->reading ? ['kun' => $clip->reading] : null,
            'meaning' => $clip->meaning,
            'example_sentence' => $clip->context_sentence,
            'example_translation' => $clip->translation,
            'source' => 'clip',
            'clip_id' => $clip->id,
        ]);

        $clip->update(['card_id' => $card->id]);

        return $this->ok($clip->load('card'), 'Klip dikonversi menjadi kartu', 201);
    }

    public function destroy(Request $request, Clip $clip): JsonResponse
    {
        abort_if($clip->user_id !== $request->user()->id, 404, 'Klip tidak ditemukan');

        $clip->delete();

        return $this->ok(null, 'Klip dihapus');
    }
}
