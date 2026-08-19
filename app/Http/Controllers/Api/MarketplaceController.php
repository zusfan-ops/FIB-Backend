<?php

namespace App\Http\Controllers\Api;

use App\Models\MarketplaceProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarketplaceController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = MarketplaceProduct::query()
            ->with(['user:id,name,study_program,avatar_url']);

        // Filter search keyword
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter category
        if ($category = $request->input('category')) {
            if ($category !== 'semua' && $category !== 'Semua Kategori') {
                $query->where('category', $category);
            }
        }

        // Filter condition
        if ($condition = $request->input('condition')) {
            if ($condition !== 'semua') {
                $query->where('condition', $condition);
            }
        }

        // Filter status (available vs sold)
        if ($request->has('is_sold')) {
            $query->where('is_sold', filter_var($request->input('is_sold'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = (int) $request->input('per_page', 20);
        $products = $query
            ->orderBy('is_sold', 'asc') // barang belum terjual tampil di atas
            ->latest()
            ->paginate($perPage);

        return $this->ok($products);
    }

    public function myProducts(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $products = MarketplaceProduct::query()
            ->where('user_id', $userId)
            ->with(['user:id,name,study_program,avatar_url'])
            ->latest()
            ->get();

        return $this->ok($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'category' => ['required', 'string', 'max:50'],
            'condition' => ['required', 'string', 'max:30'],
            'image_url' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:10240'],
            'contact_whatsapp' => ['nullable', 'string', 'max:30'],
            'location' => ['nullable', 'string', 'max:100'],
        ]);

        $imageUrl = $validated['image_url'] ?? null;

        // Handle multipart image upload jika ada
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('marketplace', 'public');
            $imageUrl = Storage::url($path);
        }

        $product = $request->user()->marketplaceProducts()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'category' => $validated['category'],
            'condition' => $validated['condition'],
            'image_url' => $imageUrl,
            'contact_whatsapp' => $validated['contact_whatsapp'] ?? $request->user()->phone,
            'location' => $validated['location'] ?? 'FIB UNDIP Tembalang',
            'is_sold' => false,
        ]);

        $product->load('user:id,name,study_program,avatar_url');

        return $this->ok($product, 'Barang berhasil dipasang di Toko Mahasiswa/i', 201);
    }

    public function show(Request $request, MarketplaceProduct $marketplaceProduct): JsonResponse
    {
        $marketplaceProduct->load('user:id,name,study_program,avatar_url,phone,bio');
        return $this->ok($marketplaceProduct);
    }

    public function update(Request $request, MarketplaceProduct $marketplaceProduct): JsonResponse
    {
        abort_if($marketplaceProduct->user_id !== $request->user()->id, 403, 'Anda tidak memiliki akses ke barang ini');

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'price' => ['sometimes', 'required', 'integer', 'min:0'],
            'category' => ['sometimes', 'required', 'string', 'max:50'],
            'condition' => ['sometimes', 'required', 'string', 'max:30'],
            'image_url' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:10240'],
            'contact_whatsapp' => ['nullable', 'string', 'max:30'],
            'location' => ['nullable', 'string', 'max:100'],
            'is_sold' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('marketplace', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $marketplaceProduct->update($validated);
        $marketplaceProduct->load('user:id,name,study_program,avatar_url');

        return $this->ok($marketplaceProduct, 'Data jualan berhasil diperbarui');
    }

    public function destroy(Request $request, MarketplaceProduct $marketplaceProduct): JsonResponse
    {
        abort_if($marketplaceProduct->user_id !== $request->user()->id, 403, 'Anda tidak memiliki akses ke barang ini');

        $marketplaceProduct->delete();
        return $this->ok(null, 'Barang berhasil dihapus dari Toko Mahasiswa/i');
    }

    public function toggleSold(Request $request, MarketplaceProduct $marketplaceProduct): JsonResponse
    {
        abort_if($marketplaceProduct->user_id !== $request->user()->id, 403, 'Anda tidak memiliki akses ke barang ini');

        $marketplaceProduct->is_sold = !$marketplaceProduct->is_sold;
        $marketplaceProduct->save();

        $statusStr = $marketplaceProduct->is_sold ? 'ditandai sudah terjual' : 'ditandai tersedia kembali';
        return $this->ok($marketplaceProduct, "Barang {$statusStr}");
    }
}
