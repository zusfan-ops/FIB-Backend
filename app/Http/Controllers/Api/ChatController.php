<?php

namespace App\Http\Controllers\Api;

use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends ApiController
{
    /**
     * Daftar seluruh percakapan aktif mahasiswa (WhatsApp style threads)
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Ambil ID user lain yang pernah bertukar pesan dengan user saat ini
        $contactIds = DirectMessage::where('sender_id', $userId)
            ->pluck('receiver_id')
            ->merge(
                DirectMessage::where('receiver_id', $userId)->pluck('sender_id')
            )
            ->unique()
            ->values();

        if ($contactIds->isEmpty()) {
            return $this->ok([]);
        }

        $users = User::whereIn('id', $contactIds)
            ->select(['id', 'name', 'email', 'nim', 'phone_number', 'study_program', 'semester', 'angkatan', 'jlpt_level', 'bio', 'avatar_url'])
            ->get()
            ->keyBy('id');

        $conversations = [];

        foreach ($contactIds as $contactId) {
            $otherUser = $users->get($contactId);
            if (! $otherUser) {
                continue;
            }

            // Ambil pesan terakhir
            $lastMessage = DirectMessage::where(function ($q) use ($userId, $contactId) {
                $q->where('sender_id', $userId)->where('receiver_id', $contactId);
            })->orWhere(function ($q) use ($userId, $contactId) {
                $q->where('sender_id', $contactId)->where('receiver_id', $userId);
            })->orderByDesc('created_at')->first();

            // Hitung pesan belum dibaca dari kontak ini
            $unreadCount = DirectMessage::where('sender_id', $contactId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();

            $conversations[] = [
                'user' => $otherUser,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount,
                'updated_at' => $lastMessage?->created_at?->toIso8601String() ?? now()->toIso8601String(),
            ];
        }

        // Urutkan percakapan berdasarkan waktu pesan terakhir menurun
        usort($conversations, function ($a, $b) {
            return strcmp($b['updated_at'], $a['updated_at']);
        });

        return $this->ok($conversations);
    }

    /**
     * Direktori mahasiswa FIB UNDIP untuk memulai chat baru
     */
    public function directory(Request $request): JsonResponse
    {
        $authId = $request->user()->id;

        $query = User::where('id', '!=', $authId)
            ->select(['id', 'name', 'email', 'nim', 'phone_number', 'study_program', 'semester', 'angkatan', 'jlpt_level', 'bio', 'avatar_url']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('study_program', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('study_program')) {
            $query->where('study_program', $request->string('study_program'));
        }

        $students = $query->orderBy('name', 'asc')->paginate($request->integer('per_page', 30));

        return $this->ok($students);
    }

    /**
     * Riwayat obrolan dengan mahasiswa tertentu (sekaligus tandai pesan masuk sebagai telah dibaca)
     */
    public function show(Request $request, int|string $recipientId): JsonResponse
    {
        $userId = $request->user()->id;
        $recipient = User::select(['id', 'name', 'email', 'nim', 'phone_number', 'study_program', 'semester', 'angkatan', 'jlpt_level', 'bio', 'avatar_url'])
            ->findOrFail($recipientId);

        // Tandai pesan dari recipient ke auth user sebagai telah dibaca
        DirectMessage::where('sender_id', $recipientId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // Ambil riwayat pesan
        $messages = DirectMessage::where(function ($q) use ($userId, $recipientId) {
            $q->where('sender_id', $userId)->where('receiver_id', $recipientId);
        })->orWhere(function ($q) use ($userId, $recipientId) {
            $q->where('sender_id', $recipientId)->where('receiver_id', $userId);
        })
        ->orderBy('created_at', 'asc')
        ->get();

        return $this->ok([
            'recipient' => $recipient,
            'messages' => $messages,
        ]);
    }

    /**
     * Kirim pesan langsung ke mahasiswa lain (teks dan/atau gambar)
     */
    public function store(Request $request, int|string $recipientId): JsonResponse
    {
        $senderId = $request->user()->id;

        // Pastikan recipient ada dan bukan diri sendiri
        if ((int)$recipientId === (int)$senderId) {
            return $this->fail('Tidak dapat mengirim pesan ke diri sendiri', 422);
        }

        $recipient = User::findOrFail($recipientId);

        $validated = $request->validate([
            'message' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:15360'],
            'attachment_url' => ['nullable', 'string'],
            'attachment_type' => ['nullable', 'string'],
        ]);

        if (empty($validated['message']) && !$request->hasFile('attachment') && empty($validated['attachment_url'])) {
            return $this->fail('Pesan atau lampiran tidak boleh kosong', 422);
        }

        $attachmentUrl = $validated['attachment_url'] ?? null;
        $attachmentType = $validated['attachment_type'] ?? null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentType = 'image';
            try {
                $uploadDir = public_path('uploads/chat');
                if (!file_exists($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }
                $filename = 'chat_' . $senderId . '_' . time() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
                $file->move($uploadDir, $filename);
                $attachmentUrl = url('uploads/chat/' . $filename);
            } catch (\Throwable $e) {
                $mime = $file->getClientMimeType() ?: 'image/jpeg';
                $attachmentUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            }
        }

        $message = DirectMessage::create([
            'sender_id' => $senderId,
            'receiver_id' => (int)$recipientId,
            'message' => $validated['message'] ?? null,
            'attachment_url' => $attachmentUrl,
            'attachment_type' => $attachmentType,
            'is_read' => false,
        ]);

        return $this->ok($message, 'Pesan berhasil dikirim', 201);
    }

    /**
     * Tandai semua pesan dari mahasiswa tertentu sebagai sudah dibaca
     */
    public function markAsRead(Request $request, int|string $recipientId): JsonResponse
    {
        $userId = $request->user()->id;

        DirectMessage::where('sender_id', $recipientId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->ok(null, 'Pesan ditandai telah dibaca');
    }

    /**
     * Hitung total seluruh pesan belum dibaca untuk badge aplikasi
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $count = DirectMessage::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        return $this->ok(['unread_count' => $count]);
    }
}
