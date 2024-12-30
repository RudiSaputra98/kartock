<?php

namespace Database\Seeders;

use App\Models\IsiPerball;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IsiPerballSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IsiPerball::create([
            "name" => "500",
            "note" => "Karung Bekas",
        ]);
    }
}
