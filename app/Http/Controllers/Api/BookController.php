<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $books = Book::query()
            ->where('user_id', $request->user()->id)
            ->withCount('chapters')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Book $b) => $this->withProgress($b));

        return $this->ok($books);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'author_jp' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'in:novel,cerpen,puisi,esai,manga,lainnya'],
            'original_language' => ['nullable', 'string', 'max:50'],
            'total_pages' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:to_read,reading,completed'],
            'cover_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'notes' => ['nullable', 'string'],
        ]);

        $book = $request->user()->books()->create($validated);

        return $this->ok($this->withProgress($book), 'Buku ditambahkan', 201);
    }

    public function show(Request $request, Book $book): JsonResponse
    {
        $this->authorizeBook($request, $book);

        $book->load(['chapters' => fn ($q) => $q->orderBy('sort_order')->with('notes')]);

        return $this->ok($this->withProgress($book));
    }

    public function update(Request $request, Book $book): JsonResponse
    {
        $this->authorizeBook($request, $book);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'author_jp' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'in:novel,cerpen,puisi,esai,manga,lainnya'],
            'original_language' => ['nullable', 'string', 'max:50'],
            'total_pages' => ['nullable', 'integer', 'min:1'],
            'current_page' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:to_read,reading,completed'],
            'cover_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'notes' => ['nullable', 'string'],
        ]);

        if (($validated['status'] ?? null) === 'completed') {
            $validated['current_page'] = $book->total_pages ?? $book->current_page;
        }

        $book->update($validated);

        return $this->ok($this->withProgress($book), 'Buku diperbarui');
    }

    public function destroy(Request $request, Book $book): JsonResponse
    {
        $this->authorizeBook($request, $book);

        $book->delete();

        return $this->ok(null, 'Buku dihapus');
    }

    protected function withProgress(Book $book): Book
    {
        $progress = 0;
        if ($book->total_pages && $book->total_pages > 0) {
            $progress = (int) round($book->current_page / $book->total_pages * 100);
        }

        $book->setAttribute('progress_percent', min(100, $progress));

        return $book;
    }

    protected function authorizeBook(Request $request, Book $book): void
    {
        abort_if($book->user_id !== $request->user()->id, 404, 'Buku tidak ditemukan');
    }
}
