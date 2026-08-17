<?php

namespace App\Http\Controllers\Api;

use App\Models\CampusPhoto;
use App\Models\CampusPhotoComment;
use App\Models\CampusPhotoLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampusPhotoController extends ApiController
{
    /**
     * Timeline publik / feed album kampus yang bisa dilihat seluruh mahasiswa FIB UNDIP
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;

        $query = CampusPhoto::with([
            'user:id,name,university,jlpt_level',
            'comments.user:id,name',
        ])->where('is_public', true);

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $photos = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        // Tambahkan flag `is_liked` untuk user yang sedang login
        $likedPhotoIds = $userId 
            ? CampusPhotoLike::where('user_id', $userId)->whereIn('campus_photo_id', $photos->pluck('id'))->pluck('campus_photo_id')->toArray() 
            : [];

        $photos->getCollection()->transform(function ($photo) use ($likedPhotoIds) {
            $photo->is_liked = in_array($photo->id, $likedPhotoIds);
            return $photo;
        });

        return $this->ok($photos);
    }

    /**
     * Upload & Simpan Foto Dokumentasi Baru
     * Menyimpan ke public/uploads/photos atau Base64 Data URI agar tidak pernah broken
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'file', 'max:15360'], // Maksimal 15MB
            'photo_url' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $photoUrl = $validated['photo_url'] ?? null;

        // Jika upload file gambar langsung dari HP/Web
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            try {
                $uploadDir = public_path('uploads/photos');
                if (!file_exists($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }
                $filename = 'photo_' . time() . '_' . Str::random(8) . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
                $file->move($uploadDir, $filename);
                $photoUrl = url('uploads/photos/' . $filename);
            } catch (\Throwable $e) {
                // Fallback aman ke base64 Data URI jika permission storage server terbatas
                $mime = $file->getClientMimeType() ?: 'image/jpeg';
                $photoUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            }
        }

        if (empty($photoUrl)) {
            return $this->fail('Foto wajib diunggah (pilih gambar atau masukkan tautan)', 422);
        }

        $photo = $request->user()->campusPhotos()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'photo_url' => $photoUrl,
            'event_date' => $validated['event_date'] ?? now()->toDateString(),
            'location' => $validated['location'] ?? 'FIB Universitas Diponegoro',
            'category' => $validated['category'] ?? 'kegiatan',
            'likes_count' => 0,
            'comments_count' => 0,
            'is_public' => $validated['is_public'] ?? true,
        ]);

        $photo->load(['user:id,name,university,jlpt_level', 'comments.user:id,name']);
        $photo->is_liked = false;

        return $this->ok($photo, 'Foto dokumentasi kampus berhasil diunggah', 201);
    }

    public function show(Request $request, CampusPhoto $campusPhoto): JsonResponse
    {
        $userId = $request->user()?->id;
        $campusPhoto->load(['user:id,name,university,jlpt_level', 'comments.user:id,name']);
        
        $campusPhoto->is_liked = $userId 
            ? CampusPhotoLike::where('user_id', $userId)->where('campus_photo_id', $campusPhoto->id)->exists()
            : false;

        return $this->ok($campusPhoto);
    }

    /**
     * Toggle Like / Batal Suka
     */
    public function like(Request $request, CampusPhoto $campusPhoto): JsonResponse
    {
        $userId = $request->user()->id;

        $existing = CampusPhotoLike::where('user_id', $userId)
            ->where('campus_photo_id', $campusPhoto->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $campusPhoto->decrement('likes_count');
            $liked = false;
        } else {
            CampusPhotoLike::create([
                'user_id' => $userId,
                'campus_photo_id' => $campusPhoto->id,
            ]);
            $campusPhoto->increment('likes_count');
            $liked = true;
        }

        $campusPhoto->refresh();

        return $this->ok([
            'liked' => $liked,
            'likes_count' => $campusPhoto->likes_count,
        ], $liked ? 'Foto disukai' : 'Batal menyukai foto');
    }

    /**
     * Daftar Komentar pada Foto
     */
    public function getComments(CampusPhoto $campusPhoto): JsonResponse
    {
        $comments = $campusPhoto->comments()
            ->with('user:id,name,university')
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->ok($comments);
    }

    /**
     * Kirim Komentar Baru pada Foto
     */
    public function addComment(Request $request, CampusPhoto $campusPhoto): JsonResponse
    {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $comment = $campusPhoto->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        $campusPhoto->increment('comments_count');

        $comment->load('user:id,name,university');

        return $this->ok($comment, 'Komentar berhasil dikirim', 201);
    }

    /**
     * Hapus Komentar
     */
    public function deleteComment(Request $request, CampusPhotoComment $comment): JsonResponse
    {
        abort_if($comment->user_id !== $request->user()->id, 403, 'Anda tidak memiliki akses menghapus komentar ini');

        $photo = $comment->photo;
        $comment->delete();
        
        if ($photo) {
            $photo->decrement('comments_count');
        }

        return $this->ok(null, 'Komentar berhasil dihapus');
    }

    public function destroy(Request $request, CampusPhoto $campusPhoto): JsonResponse
    {
        abort_if($campusPhoto->user_id !== $request->user()->id, 403, 'Anda tidak memiliki akses menghapus foto ini');
        $campusPhoto->delete();
        return $this->ok(null, 'Foto dokumentasi berhasil dihapus');
    }
}
