<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ID pengguna yang melakukan aktivitas
            $table->string('activity_type'); // Jenis aktivitas (login, crud, cetak_laporan, dll)
            $table->string('entity')->nullable(); // Entitas yang terlibat (misalnya: Produk, User, Kategori, dll)
            $table->text('description'); // Deskripsi aktivitas
            $table->json('data')->nullable(); // Data terkait aktivitas (misalnya perubahan yang dilakukan)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
