<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChapterController extends ApiController
{
    public function index(Request $request, Book $book): JsonResponse
    {
        abort_if($book->user_id !== $request->user()->id, 404, 'Buku tidak ditemukan');

        $chapters = $book->chapters()
            ->orderBy('sort_order')
            ->with('notes')
            ->get();

        return $this->ok($chapters);
    }

    public function store(Request $request, Book $book): JsonResponse
    {
        abort_if($book->user_id !== $request->user()->id, 404, 'Buku tidak ditemukan');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'page_start' => ['nullable', 'integer', 'min:0'],
            'page_end' => ['nullable', 'integer', 'min:0'],
            'is_completed' => ['nullable', 'boolean'],
        ]);

        $validated['sort_order'] ??= ($book->chapters()->max('sort_order') ?? -1) + 1;

        $chapter = $book->chapters()->create($validated);

        return $this->ok($chapter, 'Bab ditambahkan', 201);
    }

    public function show(Request $request, Chapter $chapter): JsonResponse
    {
        abort_if($chapter->book->user_id !== $request->user()->id, 404, 'Bab tidak ditemukan');

        return $this->ok($chapter->load('notes', 'book:id,title'));
    }

    public function update(Request $request, Chapter $chapter): JsonResponse
    {
        abort_if($chapter->book->user_id !== $request->user()->id, 404, 'Bab tidak ditemukan');

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'page_start' => ['nullable', 'integer', 'min:0'],
            'page_end' => ['nullable', 'integer', 'min:0'],
            'is_completed' => ['nullable', 'boolean'],
        ]);

        $chapter->update($validated);

        return $this->ok($chapter, 'Bab diperbarui');
    }

    public function destroy(Request $request, Chapter $chapter): JsonResponse
    {
        abort_if($chapter->book->user_id !== $request->user()->id, 404, 'Bab tidak ditemukan');

        $chapter->delete();

        return $this->ok(null, 'Bab dihapus');
    }

    public function updateProgress(Request $request, Chapter $chapter): JsonResponse
    {
        abort_if($chapter->book->user_id !== $request->user()->id, 404, 'Bab tidak ditemukan');

        $validated = $request->validate([
            'is_completed' => ['required', 'boolean'],
        ]);

        $chapter->update($validated);

        $book = $chapter->book;
        $completedCount = $book->chapters()->where('is_completed', true)->count();
        $totalCount = $book->chapters()->count();

        if ($totalCount > 0 && $completedCount === $totalCount) {
            $book->update(['status' => 'completed']);
        } elseif ($book->status === 'completed') {
            $book->update(['status' => 'reading']);
        } elseif ($book->status === 'to_read' && $completedCount > 0) {
            $book->update(['status' => 'reading']);
        }

        return $this->ok($chapter->load('book'), 'Progres bab diperbarui');
    }

    public function storeNote(Request $request, Chapter $chapter): JsonResponse
    {
        abort_if($chapter->book->user_id !== $request->user()->id, 404, 'Bab tidak ditemukan');

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'page_no' => ['nullable', 'integer', 'min:0'],
        ]);

        $note = $chapter->notes()->create($validated);

        return $this->ok($note, 'Catatan disimpan', 201);
    }

    public function destroyNote(Request $request, ChapterNote $note): JsonResponse
    {
        abort_if($note->chapter->book->user_id !== $request->user()->id, 404, 'Catatan tidak ditemukan');

        $note->delete();

        return $this->ok(null, 'Catatan dihapus');
    }
}
