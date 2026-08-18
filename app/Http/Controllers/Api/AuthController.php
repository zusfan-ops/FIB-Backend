<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'jlpt_level' => ['nullable', 'string', 'in:N1,N2,N3,N4,N5'],
            'university' => ['nullable', 'string', 'max:255'],
            'study_program' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'jlpt_level' => $validated['jlpt_level'] ?? null,
            'university' => $validated['university'] ?? null,
            'study_program' => $validated['study_program'] ?? null,
        ]);

        return $this->ok($this->tokenPayload($user), 'Registrasi berhasil', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $user = Auth::user();

        return $this->ok($this->tokenPayload($user), 'Login berhasil');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->ok($request->user());
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'university' => ['nullable', 'string', 'max:255'],
            'study_program' => ['nullable', 'string', 'max:255'],
            'jlpt_level' => ['nullable', 'string', 'in:N1,N2,N3,N4,N5'],
            'bio' => ['nullable', 'string'],
            'avatar' => ['nullable', 'file', 'max:15360'],
            'avatar_url' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            try {
                $uploadDir = public_path('uploads/avatars');
                if (!file_exists($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }
                $filename = 'avatar_' . $user->id . '_' . time() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
                $file->move($uploadDir, $filename);
                $validated['avatar_url'] = url('uploads/avatars/' . $filename);
            } catch (\Throwable $e) {
                $mime = $file->getClientMimeType() ?: 'image/jpeg';
                $validated['avatar_url'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            }
        }

        unset($validated['avatar']);
        $user->update($validated);

        return $this->ok($user->fresh(), 'Profil mahasiswa berhasil diperbarui');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->ok(null, 'Logout berhasil');
    }

    protected function tokenPayload(User $user): array
    {
        return [
            'token' => $user->createToken('app')->plainTextToken,
            'user' => $user,
        ];
    }
}
