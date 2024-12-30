<?php

namespace Database\Seeders;

use App\Models\Mesin;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MesinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Mesin::create([
            "name" => "Giling 1"
        ]);
    }
}
