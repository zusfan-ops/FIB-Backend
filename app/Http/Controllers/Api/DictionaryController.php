<?php

namespace App\Http\Controllers\Api;

use App\Models\Deck;
use App\Models\DictionaryEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DictionaryController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = DictionaryEntry::query()->orderBy('term');

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('term', 'like', "%{$q}%")
                    ->orWhere('meaning', 'like', "%{$q}%")
                    ->orWhere('reading_on', 'like', "%{$q}%")
                    ->orWhere('reading_kun', 'like', "%{$q}%");
            });
        }

        if ($request->filled('level')) {
            $query->where('jlpt_level', $request->string('level'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        return $this->ok($query->limit(100)->get());
    }

    public function show(Request $request, DictionaryEntry $dictionaryEntry): JsonResponse
    {
        return $this->ok($dictionaryEntry);
    }

    /**
     * Ambil sejumlah entri acak untuk satu level JLPT, dipakai membangun
     * soal pilihan ganda pada fitur Simulasi Ujian JLPT.
     */
    public function quiz(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'level' => ['required', 'in:N5,N4,N3,N2,N1'],
            'count' => ['nullable', 'integer', 'min:4', 'max:30'],
        ]);

        $count = $validated['count'] ?? 10;

        $entries = DictionaryEntry::query()
            ->where('jlpt_level', $validated['level'])
            ->inRandomOrder()
            ->limit($count)
            ->get(['id', 'term', 'reading_on', 'reading_kun', 'meaning']);

        if ($entries->count() < 4) {
            return $this->fail('Belum cukup kosakata level ini untuk membuat simulasi (minimal 4).', 422);
        }

        return $this->ok($entries);
    }

    /**
     * Salin sebuah entri kamus menjadi kartu SRS di deck milik user.
     */
    public function saveToDeck(Request $request, DictionaryEntry $dictionaryEntry): JsonResponse
    {
        $validated = $request->validate([
            'deck_id' => ['required', 'integer'],
        ]);

        $deck = Deck::find($validated['deck_id']);
        abort_if(!$deck || $deck->user_id !== $request->user()->id, 404, 'Deck tidak ditemukan');

        $card = $deck->cards()->create([
            'user_id' => $request->user()->id,
            'front' => $dictionaryEntry->term,
            'readings' => [
                'on' => $dictionaryEntry->reading_on,
                'kun' => $dictionaryEntry->reading_kun,
            ],
            'meaning' => $dictionaryEntry->meaning,
            'example_sentence' => $dictionaryEntry->example_sentence,
            'example_translation' => $dictionaryEntry->example_translation,
            'tags' => $dictionaryEntry->tags,
            'source' => 'dictionary',
        ]);

        return $this->ok($card, 'Ditambahkan ke deck', 201);
    }
}
