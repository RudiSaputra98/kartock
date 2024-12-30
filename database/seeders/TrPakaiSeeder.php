<?php

namespace Database\Seeders;

use App\Models\TrPakai;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrPakaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TrPakai::create([
            "tanggal" => "2024-11-26",
            "pakai_ball" => "3",
            "isi_perball_id" => "1",
            "pakai_pcs" => "500",
            "jumlah_pakai" => "1500",
            "reject" => "10",
            "category_id" => "1",
            "mesin_id" => "5",
            "note" => "Rusak 10",
        ]);
    }
}
