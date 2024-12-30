<?php

namespace Database\Seeders;

use App\Models\TrMasuk;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrMasukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TrMasuk::create([
            "tanggal" => "2024-11-26",
            "masuk_ball" => "50",
            "isi_perball_id" => "3",
            "masuk_pcs" => "200",
            "jumlah_masuk" => "25200",
            "category_id" => "3",
            "note" => "Dari Kapal Satya Berjaya",
        ]);
    }
}
