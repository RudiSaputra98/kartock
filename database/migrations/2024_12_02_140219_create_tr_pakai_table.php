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
        Schema::create('tr_pakai', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->integer('pakai_ball');
            $table->integer('pakai_pcs');
            $table->unsignedBigInteger('isi_perball_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('mesin_id');
            $table->integer('jumlah_pakai')->nullable();
            $table->text('note')->nullable();

            $table->foreign('isi_perball_id')->references('id')->on('isi_perball');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('mesin_id')->references('id')->on('mesin');
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_pakai');
    }
};
