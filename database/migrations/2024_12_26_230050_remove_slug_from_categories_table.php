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
        Schema::table('categories', function (Blueprint $table) {
            // Menambah kolom max_stok dan warning
            $table->integer('max_stok')->default(500); // Kolom untuk stok maksimum
            $table->integer('warning_stok')->default(250); // Kolom untuk ambang peringatan
            $table->string('photo')->nullable();
            // Menghapus kolom slug
            $table->dropColumn('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('photo');
            $table->dropColumn('max_stok');
            $table->dropColumn('warning_threshold');

            // Menambahkan kembali kolom slug jika migrasi dibatalkan
            $table->string('slug')->unique();
        });
    }
};
