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
        Schema::create('tr_masuk', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal'); // Tanggal transaksi masuk
            $table->integer('masuk_ball'); // Jumlah ball yang masuk
            $table->integer('masuk_pcs'); // Jumlah pcs tambahan yang masuk
            $table->unsignedBigInteger('isi_perball_id'); // Relasi ke tabel isi_per_ball
            $table->unsignedBigInteger('category_id'); // Relasi ke tabel kategori
            $table->integer('jumlah_masuk')->nullable(); // Total jumlah masuk (masuk_ball * isi_per_ball + masuk_pcs)
            $table->string('note')->nullable(); // Catatan tambahan
            $table->timestamps();

            // Foreign keys
            $table->foreign('isi_perball_id')->references('id')->on('isi_perball');
            $table->foreign('category_id')->references('id')->on('categories');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_masuk');
    }
};
