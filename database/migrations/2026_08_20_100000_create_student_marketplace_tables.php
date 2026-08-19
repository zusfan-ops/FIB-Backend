<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // Nama barang / jasa (cth: Buku Minna no Nihongo Bekas, Kamus Kanji, Merchandise Bunkasai)
            $table->text('description'); // Deskripsi detail
            $table->unsignedBigInteger('price')->default(0); // Harga dalam Rupiah
            $table->string('category', 50)->default('buku'); // buku, merchandise, elektronik, fashion, makanan, jasa, lainnya
            $table->string('condition', 30)->default('bekas_seperti_baru'); // baru, bekas_seperti_baru, bekas_layak
            $table->longText('image_url')->nullable(); // Foto produk
            $table->string('contact_whatsapp', 30)->nullable(); // Nomor WA penjual
            $table->string('location', 100)->nullable(); // Lokasi COD / Penjual
            $table->boolean('is_sold')->default(false); // Status terjual
            $table->timestamps();

            $table->index(['user_id', 'is_sold']);
            $table->index(['category', 'is_sold']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_products');
    }
};
